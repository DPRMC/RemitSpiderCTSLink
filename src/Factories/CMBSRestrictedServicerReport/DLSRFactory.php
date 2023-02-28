<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use DPRMC\RemitSpiderCTSLink\Exceptions\DLSRTabMissingSomeDelinquencyCategoriesException;

class DLSRFactory extends AbstractTabFactory {

    const DEL_90_PLUS   = 'del_90_plus';   // 90 + Days Delinquent
    const DEL_60_PLUS   = 'del_60_plus';   // 60 to 89 Days Delinquent
    const DEL_30_PLUS   = 'del_30_plus';   // 30 to 59 Days Delinquent
    const CUR_SPEC_SERV = 'cur_spec_serv'; // Current and at Special Servicer
    const MAT_PERF      = 'mat_perf';      // Matured Performing Loans
    const MAT_NON_PERF  = 'mat_non_perf';  // Matured Non-Performing Loans
    const LAST_ROW      = 'last_row';      // The 30 to 59, 60 to 89 and 90+ Day Delinquent categories should not include Matured Loans (Performing/Non-Performing).

    protected array $delinquencyIndexes = [
        self::DEL_90_PLUS   => NULL,
        self::DEL_60_PLUS   => NULL,
        self::DEL_30_PLUS   => NULL,
        self::CUR_SPEC_SERV => NULL,
        self::MAT_PERF      => NULL,
        self::MAT_NON_PERF  => NULL,
        self::LAST_ROW      => NULL,
    ];

    const START = 'start';
    const END   = 'end';
    protected array $rowCategoryIndexes = [
        self::DEL_90_PLUS   => [ self::START => NULL, self::END => NULL ],
        self::DEL_60_PLUS   => [ self::START => NULL, self::END => NULL ],
        self::DEL_30_PLUS   => [ self::START => NULL, self::END => NULL ],
        self::CUR_SPEC_SERV => [ self::START => NULL, self::END => NULL ],
        self::MAT_PERF      => [ self::START => NULL, self::END => NULL ],
        self::MAT_NON_PERF  => [ self::START => NULL, self::END => NULL ],
    ];


    /**
     * @param array $rows
     * @return array
     * @throws \DPRMC\RemitSpiderCTSLink\Exceptions\DateNotFoundInHeaderException
     * @throws \DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException
     */
    public function parse( array $rows ): array {
        $this->_setDate( $rows );
        $this->_setCleanHeaders( $rows, [ 'Trans ID' ] );
        $this->_setParsedRows( $rows );

        return $this->cleanRows;
    }


    /**
     * @param array $allRows
     * @return void
     * @throws DLSRTabMissingSomeDelinquencyCategoriesException
     */
    protected function _setParsedRows( array $allRows ): void {
        $this->_setDelinquencyIndexes( $allRows );

        if ( $this->_isMissingSomeDelinquencyIndexes() ):
            throw new DLSRTabMissingSomeDelinquencyCategoriesException( "Patch the parser. DLSR missing some cats",
                                                                        0,
                                                                        NULL,
                                                                        $this->delinquencyIndexes );
        endif;

        $this->_setRowCategoryIndexes();
        $this->_setCleanRows( $allRows );
    }


    /**
     * @param array $allRows
     * @return array
     * @throws \DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException
     */
    protected function _getRowsToBeParsed( array $allRows ): array {
        $firstBlankRowIndex = 0;
        //$firstRowOfDataIndex = $this->headerRowIndex + 1; // Some data starts at +1, other sheets at +3. So... logic.
        $firstRowOfDataIndex = $this->_getFirstRowOfDataIfItExists( $allRows );


        $totalNumRows = count( $allRows );

        for ( $i = $firstRowOfDataIndex; $i < $totalNumRows; $i++ ):
            if ( empty( $allRows[ $i ][ 0 ] ) ):
                $firstBlankRowIndex = $i;
                break;
            endif;
        endfor;

        $numRows = $firstBlankRowIndex - $firstRowOfDataIndex;

        return array_slice( $allRows, $firstRowOfDataIndex, $numRows );
    }


    /**
     * @param array $allRows
     * @return void
     */
    protected function _setDelinquencyIndexes( array $allRows ): void {
        $numRows = count( $allRows );

        $possibleFirstRowOfData = $this->headerRowIndex + 1;
        for ( $i = $possibleFirstRowOfData; $i < $numRows; $i++ ):
            if ( $this->_isNinetyPlusIndex( $allRows[ $i ] ) ):
                $this->delinquencyIndexes[ self::DEL_90_PLUS ] = $i;

            elseif ( $this->_isSixtyPlusIndex( $allRows[ $i ] ) ):
                $this->delinquencyIndexes[ self::DEL_60_PLUS ] = $i;

            elseif ( $this->_isThirtyPlusIndex( $allRows[ $i ] ) ):
                $this->delinquencyIndexes[ self::DEL_30_PLUS ] = $i;

            elseif ( $this->_isCurrentAtSpecialServicer( $allRows[ $i ] ) ):
                $this->delinquencyIndexes[ self::CUR_SPEC_SERV ] = $i;

            elseif ( $this->_isMaturePerforming( $allRows[ $i ] ) ):
                $this->delinquencyIndexes[ self::MAT_PERF ] = $i;

            elseif ( $this->_isMatureNonPerforming( $allRows[ $i ] ) ):
                $this->delinquencyIndexes[ self::MAT_NON_PERF ] = $i;

            elseif ( $this->_isLastRow( $allRows[ $i ] ) ):
                $this->delinquencyIndexes[ self::LAST_ROW ] = $i;
            endif;
        endfor;
    }


    /**
     * Test to determine if more parsing needs to take place.
     * @return bool
     */
    protected function _isMissingSomeDelinquencyIndexes(): bool {
        foreach ( $this->delinquencyIndexes as $key => $index ):
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
    protected function _isXPlusIndex( array $row, string $strStartsWith ): bool {
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


    /**
     * @param $row
     * @return bool
     */
    protected function _isNinetyPlusIndex( $row ): bool {
        if ( isset( $this->delinquencyIndexes[ self::DEL_90_PLUS ] ) ):
            return FALSE;
        endif;
        return $this->_isXPlusIndex( $row, '90' );
    }

    protected function _isSixtyPlusIndex( $row ): bool {
        if ( isset( $this->delinquencyIndexes[ self::DEL_60_PLUS ] ) ):
            return FALSE;
        endif;
        return $this->_isXPlusIndex( $row, '60' );
    }

    protected function _isThirtyPlusIndex( $row ): bool {
        if ( isset( $this->delinquencyIndexes[ self::DEL_30_PLUS ] ) ):
            return FALSE;
        endif;
        return $this->_isXPlusIndex( $row, '30' );
    }

    protected function _isLastRow( $row ): bool {
        if ( isset( $this->delinquencyIndexes[ self::LAST_ROW ] ) ):
            return FALSE;
        endif;

        return $this->_isXPlusIndex( $row, 'the 30' );
    }

    protected function _isCurrentAtSpecialServicer( $row ): bool {
        if ( isset( $this->delinquencyIndexes[ self::CUR_SPEC_SERV ] ) ):
            return FALSE;
        endif;
        $data = strtolower( $row[ 0 ] ?? '' );
        $data = trim( $data );

        if ( empty( $data ) ):
            return FALSE;
        endif;

        if ( str_starts_with( $data, 'current' ) ):
            return TRUE;
        endif;

        return FALSE;
    }


    protected function _isMaturePerforming( $row ): bool {
        if ( isset( $this->delinquencyIndexes[ self::MAT_PERF ] ) ):
            return FALSE;
        endif;
        $data = strtolower( $row[ 0 ] ?? '' );
        $data = trim( $data );

        if ( empty( $data ) ):
            return FALSE;
        endif;

        if ( str_starts_with( $data, 'matured perform' ) ):
            return TRUE;
        endif;

        return FALSE;
    }


    protected function _isMatureNonPerforming( $row ): bool {
        if ( isset( $this->delinquencyIndexes[ self::MAT_NON_PERF ] ) ):
            return FALSE;
        endif;
        $data = strtolower( $row[ 0 ] ?? '' );
        $data = trim( $data );

        if ( empty( $data ) ):
            return FALSE;
        endif;

        if ( str_starts_with( $data, 'matured non' ) ):
            return TRUE;
        endif;

        return FALSE;
    }


    protected function _setRowCategoryIndexes(): void {
        foreach ( $this->delinquencyIndexes as $name => $subHeaderIndex ):

            if ( self::LAST_ROW == $name ):
                return;
            endif;

            $firstLine = $subHeaderIndex + 1;

            $lastLine = next( $this->delinquencyIndexes ) - 1;

            $this->rowCategoryIndexes[ $name ] = [
                self::START => $firstLine,
                self::END   => $lastLine,
            ];
        endforeach;
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
                    $newCleanRow[ $header ] = trim($validRow[ $j ] ?? '');
                endforeach;
                $cleanRows[ $name ][] = $newCleanRow;
            endforeach;
        endforeach;

        $this->cleanRows = $cleanRows;
    }
}