<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsCmbsRestrictedServicerReportTotalLoan;
use DPRMC\RemitSpiderCTSLink\Exceptions\ValidationHeadersNotFoundException;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps\TotalLoanMap;

class TotalLoanFactory extends AbstractTabFactory {

    protected array $firstColumnValidTextValues = [ 'Transaction ID', 'Trans', 'Transaction' ];

    protected array $rowIndexZeroDisqualifyingValues = [ 'total', '#N/A', 'nav = not available' ];

    protected array $totalLoanAmountAtOriginationValuesThatShouldBeNull = [
        'west la office - 1950 sawtelle boulevard',
        'vertex pharmaceuticals hq',
        'the shops at crystals',
        'the shops at coconut point',
        'simon premium outlets',
        'international square',
        'gaffney premium outlets',
        'flagler corporate center',
        'easton town center',
        'columbia center',
        'briarwood mall'
    ];

    protected function _removeInvalidRows( array $rows = [] ): array {

        /**
         * $row
         * array:24 [
         * 0 => "GSMS2020GC45"            // Transaction ID
         * 1 => "n/a"                     // Group ID
         * 2 => 30317577                  // Loan ID
         * 3 => "A1-C2"                   // Split Loan ID
         * 4 => 45000000                  // Original Split Loan Ammout
         * 5 => "1A1C2"                   // Prospectus Loan Id
         * 6 => null                      // Loan Contributor to Securitization
         * 7 => "1633 Broadway"           // Prospectus Loan Name
         * 8 => "nav"                     // Original Shadow Rating M/S/F/D
         * 9 => null                      // Total Loan Amount at Origination
         * 10 => null                     // Scheduled Principal Balance at Contribution
         * 11 => 45000000                 // Current Ending Scheduled Balance
         * 12 => 115862.5                 // Total Scheduled P&I Due
         * 13 => "2.99000%"               // Current Note Rate
         * 14 => 20240406                 // Paid Through Date
         * 15 => 1                        // Sequential Pay Order
         * 16 => "Wells Fargo"            // Trustee
         * 17 => "Midland Loan Services"  // Master Servicer
         * 18 => "Midland Loan Services"  // Advancing Servicer
         * 19 => "Situs"                  // Special Servicer
         * 20 => 0.0                      // Special Servicer Workout Control Type
         * 21 => "Pentalpha Surveillance" // Current Controlling Holder or Operating Advisor
         * 22 => "nav"                    // Controlling Class Rights
         * 23 => "S1"                     // Current Lockbox Status
         * ]
         */


        $validRows = [];
        $totalLoanMap = TotalLoanMap::$map;
        foreach ( $rows as $i => $row ) :

            $validatedRow = [];
            foreach( $row as $header => $cellValue ) :
                foreach( $totalLoanMap as $mappedHeader => $possibleHeaderValues ) :
                    if( in_array( $header, $possibleHeaderValues ) ) :
                        $validatedRow[$mappedHeader] = $cellValue;
                        continue;
                    endif;
                endforeach;
            endforeach;

            $missingValidationHeaders = [];

            if( ! array_key_exists(CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id, $validatedRow ) ) :
                $missingValidationHeaders[] = CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id;
            endif;

            if( ! array_key_exists( CustodianCtsCmbsRestrictedServicerReportTotalLoan::total_loan_amount_at_origination, $validatedRow ) ) :
                $missingValidationHeaders[] = CustodianCtsCmbsRestrictedServicerReportTotalLoan::total_loan_amount_at_origination;
            endif;

            if( ! empty( $missingValidationHeaders ) ) :
                throw new ValidationHeadersNotFoundException( $missingValidationHeaders );
            endif;


            // There is sometimes a spurious second row of headers on this tab.
            // Skip that.
            // If the first cell contains part of the word 'Transaction' then
            // you can be sure its a header or junk row. Skip it.
            if ( str_contains( strtolower( $row[CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id] ), 'ransac' ) ):
                continue;
            endif;

            // Since these files sometimes have headers that span several rows, we will also skip rows whre the first
            // cell contains 'ID'
            if ( str_contains( strtolower( $row[CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id] ), 'id' ) ):
                continue;
            endif;

            // There can also be a row of just integer counters for columns.
            // Certainly don't need that either.
            // Cell 0 (zero) should be a Transaction_ID which is alphanumeric.
            // If it's just plain numeric, skip it.
            if ( is_numeric( $row[CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id] ) ):
                continue;
            endif;

            // Skip items found in the first cell of the row that contain string values found in property '$rowIndexZeroDisqualifyingValues'
            $disqualifyingValueFlag = FALSE;
            foreach( $this->rowIndexZeroDisqualifyingValues as $disqualifyingValue ) :
                if( str_contains( strtolower( trim( $row[CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id] ) ), $disqualifyingValue ) ) :
                    $disqualifyingValueFlag = TRUE;
                endif;
            endforeach;

            if( $disqualifyingValueFlag ) :
                continue;
            endif;

            // At this point we can validate against the column 'total_loan_amount_at_origination' which should be either NULL
            // or a numeric value.  Except for document id '6665524' which is an outlier with property names in this field.
            // Those have been added to an exceptions array and will be set to NULL.   Anything else is a junk row.
            if( ! is_null( $row[CustodianCtsCmbsRestrictedServicerReportTotalLoan::total_loan_amount_at_origination] ) &&
                ! is_numeric( $row[CustodianCtsCmbsRestrictedServicerReportTotalLoan::total_loan_amount_at_origination] ) ) :
                if( ! in_array( strtolower( trim( $row[CustodianCtsCmbsRestrictedServicerReportTotalLoan::total_loan_amount_at_origination] ) ), $this->totalLoanAmountAtOriginationValuesThatShouldBeNull ) ) :
                    continue;
                endif;
            endif;

//            $nulls = 0;
//            foreach ( $row as $cell ):
//                $cell = trim( $cell );
//                if ( empty( $cell ) && ! is_numeric( $cell ) ):
//                    $nulls++;
//                endif;
//            endforeach;
//
//            if ( $nulls <= 10 ):
//                $validRows[] = $row;
//            endif;

            $validRows[] = $row;

        endforeach;

        return $validRows;
    }

    protected function _getRowsToBeParsed( array $allRows ): array {
        /**
         * This method needed an override because the total loan tab can have a blank row in the middle of the document.
         * This method was excluding valid rows below the blank row.
         * See Total Loan tab of CCRE_2016C3_6655160_CCRE_2016C3_RSRV.xls for an example
         */

//        $firstBlankRowIndex = 0;

        $firstRowOfDataIndex = $this->_getFirstRowOfDataIfItExists( $allRows );

        $totalNumRows = count( $allRows );

//        for ( $i = $firstRowOfDataIndex; $i < $totalNumRows; $i++ ):
//            if ( empty( $allRows[ $i ][ 0 ] ) ):
//                $firstBlankRowIndex = $i;
//                break;
//            endif;
//        endfor;

        $numRows = $totalNumRows - $firstRowOfDataIndex;

        return array_slice( $allRows, $firstRowOfDataIndex, $numRows );
    }

}
