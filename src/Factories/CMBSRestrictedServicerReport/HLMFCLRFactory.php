<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class HLMFCLRFactory extends AbstractTabFactory {

    protected array $firstColumnValidTextValues = [ 'Trans ID',
                                                    'Trans',
                                                    "Trans\nID"
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
     * @param array $row
     * @param string $strStartsWith
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
        if ( isset( $this->indexes[ self::LOAN_MOD_FORBEAR ] ) ):
            return FALSE;
        endif;
        return $this->_catStartsWith( $row, 'loan modif' );
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

        return $this->_catStartsWith( $row, 'total for' );
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

            foreach ( $validRows as $i => $validRow ):
                $firstCell = trim( $validRow[ 0 ] ?? '' );
                if ( empty( $firstCell ) ):
                    continue;
                endif;
                $newCleanRow               = [];
                $newCleanRow[ 'date' ]     = empty( $this->date ) ? NULL : $this->date->toDateString();
                $newCleanRow[ 'category' ] = $name;
                foreach ( $this->localHeaders as $j => $header ):
                    $newCleanRow[ $header ] = trim( $validRow[ $j ] ?? '' );
                endforeach;
                $cleanRows[ $name ][] = $newCleanRow;
            endforeach;
        endforeach;

        $this->cleanRows = $cleanRows;
    }

    protected function _removeInvalidRows( array $rows = [] ): array {
        return $rows;
    }
}