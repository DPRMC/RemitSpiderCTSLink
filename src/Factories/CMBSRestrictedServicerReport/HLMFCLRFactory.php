<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use DPRMC\RemitSpiderCTSLink\Exceptions\HLMFLCRTabMissingSomeCategoriesException;

class HLMFCLRFactory extends AbstractTabFactory {

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

    public function parse( array $rows ): array {
        $this->_setDate( $rows );
        $this->_setCleanHeaders( $rows, [ 'Trans ID' ] );
        $this->_setParsedRows( $rows );

        return $this->cleanRows;
    }


    protected function _setParsedRows( array $allRows ): void {
        $this->_setCategoryIndexes( $allRows );

        if ( $this->_isMissingSomeIndexes() ):
            throw new HLMFLCRTabMissingSomeCategoriesException( "Patch the parser. HLMFLCR missing some cats",
                                                                0,
                                                                NULL,
                                                                $this->indexes );
        endif;

        $this->_setRowCategoryIndexes();
        $this->_setCleanRows( $allRows );
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
        $data = trim( $row[ 0 ] ?? '' );
        $data = strtolower( $data );
        if ( empty( $data ) ):
            return FALSE;
        endif;

        if ( str_starts_with( $data, $strStartsWith ) ):
            return TRUE;
        endif;

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


    protected function _setRowCategoryIndexes(): void {
        foreach ( $this->indexes as $name => $subHeaderIndex ):

            if ( self::LAST_ROW == $name ):
                return;
            endif;

            $firstLine = $subHeaderIndex + 1;

            $lastLine = next( $this->indexes ) - 1;

            $this->rowCategoryIndexes[ $name ] = [
                self::START => $firstLine,
                self::END   => $lastLine,
            ];
        endforeach;

        dd($this->rowCategoryIndexes);
    }

    protected function _setCleanRows( array $allRows ): void {
        $cleanRows = [];

        foreach ( $this->rowCategoryIndexes as $name => $bookends ):
            $cleanRows[ $name ] = [];
            $length             = $bookends[ self::END ] - $bookends[ self::START ];
            $validRows          = array_slice( $allRows, $bookends[ self::START ], $length );

            foreach ( $validRows as $i => $validRow ):
                $firstCell = trim( $validRow[ 0 ] ?? '' );
                if ( empty( $firstCell ) ):
                    continue;
                endif;
                $newCleanRow               = [];
                $newCleanRow[ 'date' ]     = $this->date->toDateString();
                $newCleanRow[ 'category' ] = $name;
                foreach ( $this->cleanHeaders as $j => $header ):
                    $newCleanRow[ $header ] = $validRow[ $j ];
                endforeach;
                $cleanRows[ $name ][] = $newCleanRow;
            endforeach;
        endforeach;

        $this->cleanRows = $cleanRows;
    }
}