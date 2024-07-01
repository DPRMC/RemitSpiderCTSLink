<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsCmbsRestrictedServicerReportTotalLoan;
use DPRMC\RemitSpiderCTSLink\Exceptions\ValidationHeadersNotFoundException;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps\TotalLoanMap;

class TotalLoanFactory extends AbstractTabFactory {

    /** This number represents the number of cells in a row that need to have a value other than 'NULL'
     * @var int
     */
    protected int $nullValueThreshold = 10;

    /** This number represents the number of cells in a row that need to have a value not found in the Header row
     * @var int
     */
    protected int $cellValuesFoundInHeaderThreshold = 4;

    protected array $firstColumnValidTextValues = [ 'Transaction ID', 'Trans', 'Transaction' ];

    protected array $rowIndexZeroDisqualifyingValues = [ 'total', '#N/A', 'nav = not available' ];

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

            // The values in specific cells are needed for the following validation code.  Since keys of the associative
            //  array 'row' can have different spellings a check against the spelling found in the 'TotalLoanMap' object
            // is needed to ensure the key for value needed to validate is present
            foreach ( $row as $header => $cellValue ) :
                foreach ( $totalLoanMap as $mappedHeader => $possibleHeaderValues ) :
                    if ( in_array( $header, $possibleHeaderValues ) ) :
                        $validatedRow[ $mappedHeader ] = $cellValue;
                        continue;
                    endif;
                endforeach;
            endforeach;

            $missingValidationHeaders = [];

            if ( ! array_key_exists( CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id, $validatedRow ) ) :
                $missingValidationHeaders[] = CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id;
            endif;

            if ( ! empty( $missingValidationHeaders ) ) :
                throw new ValidationHeadersNotFoundException( $missingValidationHeaders );
            endif;


            // There is sometimes a spurious second row of headers on this tab.
            // Skip that.
            // If the first cell contains part of the word 'Transaction' then
            // you can be sure its a header or junk row. Skip it.
            if ( str_contains( strtolower( $row[ CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id ] ), 'ransac' ) ):
                continue;
            endif;

            // Since these files sometimes have headers that span several rows, we will also skip rows whre the first
            // cell contains 'ID'
            if ( str_contains( strtolower( $row[ CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id ] ), 'id' ) ):
                continue;
            endif;

            // There can also be a row of just integer counters for columns.
            // Certainly don't need that either.
            // Cell 0 (zero) should be a Transaction_ID which is alphanumeric.
            // If it's just plain numeric, skip it.
            if ( is_numeric( $row[ CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id ] ) ):
                continue;
            endif;

            // Skip items found in the first cell of the row that contain string values found in property '$rowIndexZeroDisqualifyingValues'
            $disqualifyingValueFlag = FALSE;
            foreach ( $this->rowIndexZeroDisqualifyingValues as $disqualifyingValue ) :
                if ( str_contains( strtolower( trim( $row[ CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id ] ) ), $disqualifyingValue ) ) :
                    $disqualifyingValueFlag = TRUE;
                endif;
            endforeach;

            if ( $disqualifyingValueFlag ) :
                continue;
            endif;

            // Valid rows should contain a minimum amount of data.  Rows that have NULL cells that exceed the threshold
            // can be skipped.
            if ( $this->_nullCellsExceedThreshold( $row ) ) :
                continue;
            endif;

            // Additional header rows are common in the Total Loan tab.  This check removes rows where the values in
            // the cells of the row exceed the threshold of allowed values found within the column header
            if ( $this->_cellValuesFoundInHeaderExceedThreshold( $row ) ) :
                continue;
            endif;

            $validRows[] = $row;

        endforeach;

        return $validRows;
    }

    /**
     *  This method needed an override because the total loan tab can have a blank row in the middle of the document.
     *  This method was excluding valid rows below the blank row.
     *  See Total Loan tab of CCRE_2016C3_6655160_CCRE_2016C3_RSRV.xls for an example
     * @param array $allRows
     * @return array
     * @throws \DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException
     */
    protected function _getRowsToBeParsed( array $allRows ): array {

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

    /**
     * Use this method to determine if the cells in the row have more null values than the accepted threshold.
     * This is used to remove 'spacer' rows in the Excel sheets that contain only null values in the row cells or
     * cells that contain footnotes often found at the bottom of the sheets
     * @param $row
     * @return bool
     */
    protected function _nullCellsExceedThreshold( $row ): bool {
        // Count cells that have no value in the row
        $nulls = 0;
        foreach ( $row as $cell ):
            $cell = trim( $cell );
            if ( empty( $cell ) && ! is_numeric( $cell ) ):
                $nulls++;
            endif;
        endforeach;

        // If the threshold is less than the nulls in the row, skip the row
        if ( $this->nullValueThreshold < $nulls ):
            return TRUE;
        endif;

        return FALSE;

    }

    /** Use this method to determine if the row is an additional header row. (Example in sheet with document ID: 6680803)
     * The threshold is needed as false positives can occur if the cell contains a value of only one or two characters.
     * (Example in sheet with document ID: 6680803, Column:R  M will be found in the column header 'Master Servicer').
     * Additional header rows will overcome this threshold, however,  and return true
     * @param $row
     * @return bool
     */
    protected function _cellValuesFoundInHeaderExceedThreshold( $row ): bool {
        // Count the number of cells where the string cell value is found within the header string
        $cellValueFoundInHeader = 0;
        foreach ( $row as $header => $cellValue ) :
            $cellValue = strtolower( trim( $cellValue ) );

            // str_contains used below returns true on empty values, so skip those.
            if ( empty( $cellValue ) ) :
                continue;
            endif;

            if ( str_contains( $header, $cellValue ) ) :
                $cellValueFoundInHeader++;
            endif;
        endforeach;

        // If the threshold is less than the amount of cells with values found in the header, skip the row
        if ( $this->cellValuesFoundInHeaderThreshold < $cellValueFoundInHeader ) :
            return TRUE;
        endif;

        return FALSE;

    }


}
