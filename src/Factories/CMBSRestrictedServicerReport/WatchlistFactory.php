<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;


/**
 * FYI, the parse function works perfectly. 2023-07-12:mdd
 */
class WatchlistFactory extends AbstractTabFactory {
    protected array $firstColumnValidTextValues = [ 'Trans ID',
                                                    'Trans Id',
                                                    'Trans',
                                                    'Tran ID',
                                                    'transaction_id',
                                                    'tran_id', // 2025-02-28:mdd
    ];


    /**
     * @param array       $allRows
     * @param string|NULL $sheetName
     * @param array       $existingRows
     *
     * @return void
     * @throws \DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException
     */
    protected function _setParsedRows( array $allRows, string $sheetName = NULL, array $existingRows = [] ): void {
        $this->cleanRows = $existingRows;

        $validRows = $this->_getRowsToBeParsed( $allRows );

        foreach ( $validRows as $i => $validRow ):
            $newCleanRow = [];

            foreach ( $this->localHeaders as $j => $header ):
                $data                   = trim( $validRow[ $j ] ?? '' );
                $newCleanRow[ $header ] = $data;
            endforeach;

            // Some tabs leave the date off.
            // So set a placeholder of NULL for now, and I will "borrow" the date from another tab.
            if ( $this->date ):
                $newCleanRow[ 'date' ] = $this->date->toDateString();
            else:
                $newCleanRow[ 'date' ] = NULL;
            endif;

            $newCleanRow[ 'document_id' ] = $this->documentId;

            // KLUDGE
            // I have empty rows coming in, and I dont want to bother finding out why.
            // Only the date is showing up since I added it above.
            if ( sizeof( $newCleanRow ) == 1 ):
                continue;
            endif;

            $this->cleanRows[] = $newCleanRow;
        endforeach;


        // REMOVE THE TOTAL ROW
        foreach ( $this->cleanRows as $k => $cleanRow ):
            if (
                isset( $cleanRow[ 'trans_id' ] ) &&
                str_contains( strtolower( $cleanRow[ 'trans_id' ] ), 'total' )
            ):
                unset( $this->cleanRows[ $k ] );
            endif;
        endforeach;

        $this->cleanRows = $this->_removeInvalidRows( $this->cleanRows );

        $this->cleanRows = array_values( $this->cleanRows ); // Reindex the array so the indexes are sequential again.
    }


    /**
     * @param array $allRows
     *
     * @return array
     * @throws \DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException
     */
    protected function _getRowsToBeParsed( array $allRows ): array {
        $firstBlankRowIndex = 0;

        $firstRowOfDataIndex = $this->_getFirstRowOfDataIfItExists( $allRows );
        $totalNumRows        = count( $allRows );

        $possibleDataRows = $this->_removeJunkRowsBetweenHeaderAndData( $firstRowOfDataIndex, $allRows );

        foreach ( $possibleDataRows as $i => $possibleDataRow ):
            if ( empty( $possibleDataRow[ 0 ] ) ):
                $firstBlankRowIndex = $i;
                break;
            endif;
        endforeach;

        $numRows = count( $possibleDataRows ) - $firstBlankRowIndex;

        $slicedRows = array_slice( $possibleDataRows, 0, $firstBlankRowIndex );

        return $slicedRows;
    }


    /**
     * @param int   $firstRowOfDataIndex
     * @param array $allRows
     *
     * @return array Possible data rows with junk rows that existed between the header row and the first valid row of data have been removed.
     */
    protected function _removeJunkRowsBetweenHeaderAndData( int $firstRowOfDataIndex, array $allRows ): array {
        $filteredRows     = [];
        $totalNumRows     = count( $allRows );
        $length           = $totalNumRows - $firstRowOfDataIndex;
        $possibleDataRows = array_slice( $allRows, $firstRowOfDataIndex, $length );
        $possibleDataRows = array_values( $possibleDataRows );


        foreach ( $possibleDataRows as $possibleDataRow ):
            if ( $this->_rowContainsDisqualifyingString( $possibleDataRow ) ):
                continue;
            endif;
            $filteredRows[] = $possibleDataRow;
        endforeach;

        return $filteredRows;
    }


    /**
     * There are patterns. There are certain strings that exist in a row, that guarantee
     * that the row is an invalid row.
     *
     * @param array $row
     *
     * @return bool
     */
    protected function _rowContainsDisqualifyingString( array $row ): bool {
        $disqualifiedStrings = [
            'CLTranID',
            'so long comments will display correctly',
        ];
        foreach ( $row as $i => $value ):
            foreach ( $disqualifiedStrings as $disqualifiedString ):
                if ( str_contains( $value, $disqualifiedString ) ):
                    return TRUE;
                endif;
            endforeach;
        endforeach;
        return FALSE;
    }


    protected function _isBadTransId( array $row ): bool {
        $disqualifiedStrings = [
            'CLTranID',
            'so long comments will display correctly',
        ];

        foreach ( $this->firstColumnValidTextValues as $possibleTransactionIdColumnHeader ):
            if ( !isset( $row[ $possibleTransactionIdColumnHeader ] ) ):
                continue;
            endif;

            foreach($disqualifiedStrings as $disqualifiedString ):
                if ( !str_contains( $row[ $possibleTransactionIdColumnHeader ], $disqualifiedString ) ):
                    return FALSE;
                endif;
            endforeach;
        endforeach;

        return TRUE;
    }

    protected function _removeInvalidRows( array $rows = [] ): array {


        // TODO add this code to the other Factories as well.
        if ( empty( $this->localHeaders ) ):
            throw new \Exception( "The localHeaders property is empty. That is going to have this method return zero valid rows. So check that property. You might need to add a new value to the firstColumnValidTextValues property." );
        endif;


        $validRows = [];
        foreach ( $rows as $i => $row ):

            $row = array_slice( $row, 0, count( $this->localHeaders ) );

            // 2024-12-09:mdd @see Doc ID: 6047667
            //if ( isset() && 'CLTranID' == $row[ 'trans_id' ] ):
            //    dump( 'BAAAAAAD trans_id' );
            //    continue;
            //endif;
            // 2025-02-28:mdd
            if ( $this->_isBadTransId( $row ) ):
                dump( 'BAAAAAAD trans_id' );
                continue;
            endif;

            $numNullCells           = 0;
            $nonIntegerFound        = FALSE;
            $numStartingWithLetterL = 0;
            foreach ( $row as $j => $value ):
                $value = trim( $value );
                if ( empty( $value ) ):
                    $numNullCells++;
                endif;

                if ( isset( $value ) && !is_integer( (string)$value ) ):
                    $nonIntegerFound = TRUE;
                endif;

                if ( str_starts_with( strtoupper( $value ?? '' ), 'L' ) ):
                    $numStartingWithLetterL++;
                endif;
            endforeach;

            if ( $numNullCells > 8 ):
                continue;
            endif;

            // array:22 [
            //  0 => 1
            //  1 => 2
            //  2 => 3
            //  3 => 4
            //  4 => 5
            //  5 => 6
            //  6 => 7
            //  7 => 8
            //  8 => 9
            //  9 => 10
            //  10 => 11
            //  11 => 12
            //  12 => 13
            //  13 => 14
            //  14 => 15
            //  15 => 16
            //  16 => 17
            //  17 => 18
            //  18 => 19
            //  19 => 20
            //  20 => 21
            //  21 => 22
            //]
            if ( !$nonIntegerFound ):
                continue;
            endif;

            // array:23 [
            //  0 => "L1"
            //  1 => "L2"
            //  2 => "L3"
            //  3 => "S4"
            //  4 => "S55"
            //  5 => "S61"
            //  6 => "S57"
            //  7 => "S58"
            //  8 => "L105"
            //  9 => "L7"
            //  10 => "L8"
            //  11 => "L11"
            //  12 => "L56/L93"
            //  13 => "L58"
            //  14 => "L70/L97"
            //  15 => "L72"
            //  16 => "L73"
            //  17 => null
            //  18 => null
            //  19 => null
            //  20 => "L71, P29"
            //  21 => "P30"
            //  22 => null
            //]
            if ( $numStartingWithLetterL > 7 ):
                continue;
            endif;


            $validRows[] = $row;
            // dump( $row );
        endforeach;

        return $validRows;
    }
}