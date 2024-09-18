<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class HLMFCLRFactory extends AbstractTabFactory {

    protected array $firstColumnValidTextValues = [ 'Trans ID',
                                                    'Trans',
                                                    "Trans\nID",
                                                    "Trans \nID",
                                                    "TRANSACTION_ID",
    ];


    const LOAN_MOD_FORBEAR     = 'loan_modifications_forbearance';   // Loan Modifications/Forbearance
    const CORRECTED_MORT_LOANS = 'corrected_mortgage_loans';         // Corrected Mortgage Loans:
    const LAST_ROW             = 'last_row';                         // Total For All Loans:


    protected array $indexes = [
        self::LOAN_MOD_FORBEAR     => NULL,
        self::CORRECTED_MORT_LOANS => NULL,
        self::LAST_ROW             => NULL,
    ];


    const START = 'start';
    const END   = 'end';


    protected array $rowCategoryIndexes = [
        self::LOAN_MOD_FORBEAR     => [ self::START => NULL, self::END => NULL ],
        self::CORRECTED_MORT_LOANS => [ self::START => NULL, self::END => NULL ],
    ];


    protected function _setParsedRows( array $allRows, string $sheetName = NULL, array $existingRows = [] ): void {
        $this->_setCategoryIndexes( $allRows );


// I wrote this, but in fact some of the HLM sheets just WON'T have these indexes.
//        if ( $this->_isMissingSomeIndexes() ):
//            throw new HLMFLCRTabMissingSomeCategoriesException( "Patch the parser. HLMFLCR missing some cats. Look in the indexes array of this exception. Anything that is NULL could not be found. You will need to edit the methods used in _setCategoryIndexes()",
//                                                                0,
//                                                                NULL,
//                                                                $this->indexes );
//        endif;

        $this->_setRowCategoryIndexes();

        $this->_setCleanRows( $allRows, $existingRows );

        $this->cleanRows = $this->_removeInvalidRows( $this->cleanRows );
//        if ( $sheetName == 'hlmfclr' ) {
//            dd( $this->cleanRows );
//        }
    }


    protected function _getRowsToBeParsed( array $allRows ): array {
        $firstBlankRowIndex  = 0;
        $firstRowOfDataIndex = $this->_getFirstRowOfDataIfItExists( $allRows );
        $totalNumRows        = count( $allRows );

        for ( $i = $firstRowOfDataIndex; $i < $totalNumRows; $i++ ):
            if ( empty( $allRows[ $i ][ 0 ] ) ):
                $firstBlankRowIndex = $i;
                break;
            endif;
        endfor;

        $numRows = $firstBlankRowIndex - $firstRowOfDataIndex;

        return array_slice( $allRows, $firstRowOfDataIndex, $numRows );
    }


    protected function _setCategoryIndexes( array $allRows ): void {
        $numRows = count( $allRows );

        $possibleFirstRowOfData = $this->headerRowIndex + 1;

        // Some sheets don't even have a header that starts with 'loan modif', which is
        // the test performed below in the _isLoanModForbear() method below.
        // So let's default the loan mod forbear header line to the top/MAIN header of the sheet.
        $this->indexes[ self::LOAN_MOD_FORBEAR ] = $possibleFirstRowOfData;

        for ( $i = $possibleFirstRowOfData; $i < $numRows; $i++ ):
            if ( $this->_isLoanModForbear( $allRows[ $i ] ) ):
                $this->indexes[ self::LOAN_MOD_FORBEAR ] = $i;

            elseif ( $this->_isCorrectedMortgageLoans( $allRows[ $i ] ) ):
                $this->indexes[ self::CORRECTED_MORT_LOANS ] = $i;

            elseif ( $this->_isLastRow( $allRows[ $i ] ) ):
                $this->indexes[ self::LAST_ROW ] = $i;
            endif;
        endfor;
    }


    /**
     * Test to determine if more parsing needs to take place.
     *
     * @return bool
     */
    protected function _isMissingSomeIndexes(): bool {
        foreach ( $this->indexes as $key => $index ):
            if ( is_null( $index ) ):
                return TRUE;
            endif;
        endforeach;
        return FALSE;
    }


    /**
     * @param array  $row
     * @param string $strStartsWith
     *
     * @return bool
     */
    protected function _catStartsWith( array $row, string $strStartsWith ): bool {
        // Usually the word identifying the "sub" category of rows on the sheet is in the 1st column.
        // I found an example where it is in the 2nd column.
        // So of course now I have to search both.
        $colsToCheck = [ 0, 1 ];

        foreach ( $colsToCheck as $colToCheck ):
            $data = trim( $row[ $colToCheck ] ?? '' );
            $data = strtolower( $data );
            if ( empty( $data ) ):
                continue;
            endif;

            if ( str_starts_with( $data, $strStartsWith ) ):
                return TRUE;
            endif;
        endforeach;


        return FALSE;
    }


    protected function _isLoanModForbear( $row ): bool {

        // Commented this out because I started defaulting the index for the
        // loan mod forbear header row, because sometimes they leave it off.
        // In those cases, the "header" for that section is just the top/main
        // header of the sheet.
//        if ( isset( $this->indexes[ self::LOAN_MOD_FORBEAR ] ) ):
//            return FALSE;
//        endif;

        if ( $this->_catStartsWith( $row, 'loan modif' ) ):
            return TRUE;
        endif;

        foreach ( $row as $i => $cell ):
            $haystack = strtolower( $cell );
            $needle   = 'forbearance';
            if ( str_contains( $haystack, $needle ) ):
                return TRUE;
            endif;
        endforeach;

        return FALSE;
    }

    protected function _isCorrectedMortgageLoans( $row ): bool {
        if ( isset( $this->indexes[ self::CORRECTED_MORT_LOANS ] ) ):
            return FALSE;
        endif;

        return $this->_catStartsWith( $row, 'correct' );
    }


    protected function _isLastRow( $row ): bool {
        if ( isset( $this->indexes[ self::LAST_ROW ] ) ):
            return FALSE;
        endif;

        if ( $this->_catStartsWith( $row, 'total for' ) ):
            return TRUE;
        endif;

        $needle = strtolower( 'THIS REPORT IS HISTORICAL' );
        foreach ( $row as $i => $cell ):
            $haystack = strtolower( $cell );
            if ( str_contains( $haystack, $needle ) ):
                return TRUE;
            endif;
        endforeach;

        return FALSE;
    }


    /**
     * @return void
     */
    protected function _setRowCategoryIndexes(): void {
        foreach ( $this->indexes as $name => $subHeaderIndex ):
            if ( self::LAST_ROW == $name ):
                return;
            endif;

            $firstLineOfData = $subHeaderIndex + 1;
            $nextHeaderLine  = next( $this->indexes );

            if ( $firstLineOfData == $nextHeaderLine ):
                $this->rowCategoryIndexes[ $name ] = [
                    self::START => NULL,
                    self::END   => NULL,
                ];
                continue;
            endif;

            $lastLineOfData = $nextHeaderLine - 1;

            $this->rowCategoryIndexes[ $name ] = [
                self::START => $firstLineOfData,
                self::END   => $lastLineOfData,
            ];
        endforeach;
    }

    protected function _setCleanRows( array $allRows, array $existingRows = [] ): void {

        $cleanRows = $existingRows;

        foreach ( $this->rowCategoryIndexes as $name => $bookends ):
            $cleanRows[ $name ] = [];

            // These would be null if there were no rows for this category.
            if ( is_null( $bookends[ self::START ] ) || is_null( $bookends[ self::END ] ) ):
                continue;
            endif;

            $length    = $bookends[ self::END ] - $bookends[ self::START ];
            $validRows = array_slice( $allRows, $bookends[ self::START ], $length );


            // SUPER KLUDGE
            //dump( $name );
            //dump($validRows);
            //dd($this->localHeaders);
            if ( $this->_doKludgeFor6735068( $validRows, $name ) ):
                $newValidRows = [];
                foreach ( $validRows as $aRow ):
                    unset( $aRow[ 1 ] );                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           // Remove the empty cell.
                    $aRow           = array_values( $aRow );                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               // Reset the indexes.
                    $newValidRows[] = $aRow;
                endforeach;
                $validRows = $newValidRows;
            endif;


            foreach ( $validRows as $i => $validRow ):
                $firstCell = trim( $validRow[ 0 ] ?? '' );
                if ( empty( $firstCell ) ):
                    continue;
                endif;
                $newCleanRow = [];


                foreach ( $this->localHeaders as $j => $header ):
                    //dump( $j, $header, $validRow[ $j ] );
                    $newCleanRow[ $header ] = trim( $validRow[ $j ] ?? '' );
                endforeach;
                $newCleanRow[ 'date' ]        = empty( $this->date ) ? NULL : $this->date->toDateString();
                $newCleanRow[ 'category' ]    = $name;
                $newCleanRow[ 'document_id' ] = $this->documentId;
                $cleanRows[ $name ][]         = $newCleanRow;

                //dump($validRow);
                //dump( $newCleanRow );
            endforeach;
        endforeach;

        //dump( $this->localHeaders );

        $this->cleanRows = $cleanRows;
    }

    protected function _removeInvalidRows( array $rows = [] ): array {
        $validRows = [];


        foreach ( $rows as $category => $rowsByCategory ):
            if ( !isset( $validRows[ $category ] ) ):
                $validRows[ $category ] = [];
            endif;

            foreach ( $rowsByCategory as $i => $row ):
                // Remove empty rows.
                if ( count( $row ) < 4 ):
                    continue;
                endif;

                // Garbage rows will not have a loan id present.
                if ( empty( $row[ 'loan_id' ] ) ):
                    continue;
                endif;

                // An errand header row was sneaking in there.
                if ( str_contains( $row[ 'loan_id' ], 'Loan ID' ) ):
                    continue;
                endif;


                if ( strtolower( 'NONE TO REPORT' ) == strtolower( $row[ 'loan_id' ] ) ):
                    continue;
                endif;

                if ( strtolower( 'NONE TO REPORT' ) == strtolower( $row[ 'trans_id' ] ) ):
                    continue;
                endif;

                $validRows[ $category ][] = $row;
            endforeach;
        endforeach;


        return $validRows;
    }


    /**
     * Identify Document ID _doKludgeFor6735068
     * Because if it is that Doc, then I have to shift these HLMFCLR data points down one.
     * This file gets parsed with an extra cell for some reason in like the #2 spot.
     *
     * These rows are grouped into sub categories.
     * So I have to test fo the categoryName as well. 2nd param.
     *
     * @param array $validRows
     *
     * @return bool
     */
    protected function _doKludgeFor6735068( array $validRows = [], string $categoryName = NULL ): bool {

        if ( empty( $validRows ) ):
            return FALSE;
        endif;

        try {
            //WBCMT07C33
            if (
                'loan_modifications_forbearance' == $categoryName &&

                (!str_contains( $validRows[ 0 ][ 0 ], 'WBCMT07C33' ) ||
                 !str_contains( $validRows[ 0 ][ 2 ], '1' ) ||
                 !str_contains( $validRows[ 3 ][ 3 ], '200691763' ) ||
                 !str_contains( $validRows[ 3 ][ 7 ], 'Combination' ) ||
                 $validRows[ 4 ][ 10 ] != 21200000.0)
                //||
                //!str_contains( $validRows[ 4 ][ 15 ], '101318.33' ) ||
                //!str_contains( $validRows[ 13 ][ 3 ], '69000133' )
            ):
                return FALSE;
            endif;

            //if ( $categoryName == 'corrected_mortgage_loans' ):
            //    dd( $validRows );
            //endif;

            if (
                'corrected_mortgage_loans' == $categoryName &&

                (!str_contains( $validRows[ 0 ][ 0 ], 'WBCMT07C33' ) ||
                 !str_contains( $validRows[ 0 ][ 2 ], '1' ) ||
                 !str_contains( $validRows[ 0 ][ 3 ], '70101000' ) ||
                 !str_contains( $validRows[ 0 ][ 4 ], '1B' ) ||
                 $validRows[ 2 ][ 3 ] != 502861764 ||
                 $validRows[ 2 ][ 5 ] != 'KEENE'
                )
                //||
                //!str_contains( $validRows[ 4 ][ 15 ], '101318.33' ) ||
                //!str_contains( $validRows[ 13 ][ 3 ], '69000133' )
            ):
                return FALSE;
            endif;
            return TRUE;
        } catch ( \Exception $exception ) {
            dd( $validRows, $categoryName );;
        }


    }
}