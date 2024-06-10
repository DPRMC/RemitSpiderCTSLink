<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class TotalLoanFactory extends AbstractTabFactory {

    protected array $firstColumnValidTextValues = [ 'Transaction ID', 'Trans', 'Transaction' ];

    protected array $rowIndexZeroDisqualifyingValues = [ 'total' ];

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

        //$validRows = [];
        //foreach( $rows as $row ) :
        //    // Only valid if the 'Current Note Rate' contains a decimal
        //    if( is_float( $row[13] ) || str_contains( $row[13], '.' ) ) :
        //        $validRows[] = $row;
        //    endif;
        //endforeach;


        $validRows = [];
        foreach ( $rows as $i => $row ) :

            // There is sometimes a spurious second row of headers on this tab.
            // Skip that.
            // If the first cell contains part of the word 'Transaction' then
            // you can be sure its a header or junk row. Skip it.
            if ( str_contains( strtolower( $row[ 0 ] ), 'ransac' ) ):
                continue;
            endif;

            // Since these files sometimes have headers that span several rows, we will also skip rows whre the first
            // cell contains 'ID'
            if ( str_contains( strtolower( $row[ 0 ] ), 'id' ) ):
                continue;
            endif;

            // There can also be a row of just integer counters for columns.
            // Certainly don't need that either.
            // Cell 0 (zero) should be a Transaction_ID which is alphanumeric.
            // If it's just plain numeric, skip it.
            if ( is_numeric( $row[ 0 ] ) ):
                continue;
            endif;

            // This code will skip items found in the first cell of the row that contain string values found in property '$rowIndexZeroDisqualifyingValues'
            foreach( $this->rowIndexZeroDisqualifyingValues as $disqualifyingValue ) :
                if( str_contains( strtolower( trim( $row[ 0 ] ) ), $disqualifyingValue ) ) :
                    continue;
                endif;
            endforeach;

            $nulls = 0;
            foreach ( $row as $cell ):
                $cell = trim( $cell );
                if ( empty( $cell ) && ! is_numeric( $cell ) ):
                    $nulls++;
                endif;
            endforeach;

            if ( $nulls <= 10 ):
                $validRows[] = $row;
            endif;
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
