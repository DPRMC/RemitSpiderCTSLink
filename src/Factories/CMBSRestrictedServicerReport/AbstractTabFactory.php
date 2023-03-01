<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use Carbon\Carbon;
use DPRMC\RemitSpiderCTSLink\Exceptions\DateNotFoundInHeaderException;
use DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException;

abstract class AbstractTabFactory {

    const DEFAULT_TIMEZONE = 'America/New_York';
    protected string $timezone;

    protected int   $headerRowIndex = 0;
    protected array $cleanHeaders   = [];
    protected array $cleanRows      = [];

    protected Carbon $date;

    /**
     * @param string|NULL $timezone
     */
    public function __construct( string $timezone = NULL ) {
        if ( $timezone ):
            $this->timezone = $timezone;
        else:
            $this->timezone = self::DEFAULT_TIMEZONE;
        endif;
    }


    /**
     * @param array $rows
     * @return array
     */
    abstract public function parse( array $rows ): array;


    /**
     * @param array $allRows
     * @param int $dateRowIndex
     * @return void
     * @throws DateNotFoundInHeaderException
     */
    protected function _setDate( array $allRows, int $dateRowIndex = 3 ): void {
//        $dateRow    = $allRows[ $dateRowIndex ][ 0 ];
//        $parts      = explode( ' ', $dateRow );
//        $stringDate = end( $parts );
//        $this->date = Carbon::parse( $stringDate, $this->timezone );

        $this->date = $this->_searchForDate( $allRows );
    }


    /**
     * @param array $allRows
     * @param int $numRowsToCheck
     * @return Carbon
     * @throws DateNotFoundInHeaderException
     */
    protected function _searchForDate( array $allRows, int $numRowsToCheck = 6 ): Carbon {
        $pattern = '/\d{1,2}\/\d{1,2}\/\d{4}/'; // Will match dates like 1/1/2023 or 12/31/2023
        for ( $i = 0; $i <= $numRowsToCheck; $i++ ):
            $parts = explode( ' ', $allRows[ $i ][ 0 ] ?? '' );
            foreach ( $parts as $part ):
                if ( 1 === preg_match( $pattern, $part ) ):
                    return Carbon::parse( $part, $this->timezone );
                endif;
            endforeach;
        endfor;

        throw new DateNotFoundInHeaderException( "Patch the parser.",
                                                 8732465782,
                                                 NULL,
                                                 array_slice( $allRows, 0, $numRowsToCheck ) );
    }


    /**
     * @param array $allRows
     * @param array $firstColumnValidTextValues
     * @return void
     */
    protected function _setCleanHeaders( array $allRows, array $firstColumnValidTextValues = [] ): void {
        $headerRow = [];
        foreach ( $allRows as $i => $row ):
            if ( empty( $row[ 0 ] ) ):
                continue;
            endif;

            $trimmedValue = trim( $row[ 0 ] );

            if ( in_array( $trimmedValue, $firstColumnValidTextValues ) ):
                $this->headerRowIndex = $i; // Used in other methods of this class.
                $headerRow            = $row;
                break;
            endif;
        endforeach;

        $cleanHeaders = [];

        foreach ( $headerRow as $i => $header ):

            // Avoid deprecation warning about passing null to strtolower.
            if ( empty( $header ) ):
                continue;
            endif;

            $cleanHeaders[ $i ] = $this->_cleanHeaderValue( $header );
        endforeach;
        $this->cleanHeaders = $cleanHeaders;
    }


    /**
     * @param string $header
     * @return string
     */
    protected function _cleanHeaderValue( string $header ): string {
        $newHeader = $header;
        $newHeader = trim( $newHeader );
        $newHeader = strtolower( $header );
        $newHeader = str_replace( 'yyyymmdd', '', $newHeader );
        $newHeader = str_replace( ' ', '_', $newHeader );
        $newHeader = str_replace( "\n", '_', $newHeader );
        $newHeader = str_replace( "(s)", '', $newHeader );
        $newHeader = str_replace( "_(", '_', $newHeader );
        $newHeader = str_replace( ")", '', $newHeader );
        $newHeader = str_replace( '/', '_', $newHeader );
        $newHeader = str_replace( '_-_', '_', $newHeader );
        $newHeader = str_replace( '___', '_', $newHeader );
        $newHeader = str_replace( '__', '_', $newHeader );
        $newHeader = str_replace( '--', '_', $newHeader );
        $newHeader = str_replace( '%', 'percent', $newHeader );

        $newHeader = ltrim( $newHeader, '_' );
        $newHeader = ltrim( $newHeader, '(1_' );
        $newHeader = ltrim( $newHeader, '2_' );

        $newHeader = rtrim( $newHeader, '_1' );
        $newHeader = rtrim( $newHeader, '_$' );

        $newHeader = str_replace( '_$_', '_', $newHeader );
        $newHeader = str_replace( 'p&i', 'p_and_i', $newHeader );

        return $newHeader;
    }


    /**
     * @param array $allRows
     * @return void
     * @throws NoDataInTabException
     */
    protected function _setParsedRows( array $allRows ): void {
        $cleanRows = [];
        $validRows = $this->_getRowsToBeParsed( $allRows );

        foreach ( $validRows as $i => $validRow ):
            $newCleanRow           = [];
            $newCleanRow[ 'date' ] = $this->date->toDateString();
            foreach ( $this->cleanHeaders as $j => $header ):
                $data                   = trim( $validRow[ $j ] ?? '' );
                $newCleanRow[ $header ] = $data;
            endforeach;
            $cleanRows[] = $newCleanRow;
        endforeach;

        $this->cleanRows = $cleanRows;
    }


    /**
     * @param array $allRows
     * @return array
     * @throws NoDataInTabException
     */
    protected function _getRowsToBeParsed( array $allRows ): array {
        $firstBlankRowIndex = 0;

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
     * @return int
     * @throws NoDataInTabException
     */
    protected function _getFirstRowOfDataIfItExists( array $allRows ): int {
        $maxBlankRowsBeforeData = 3;
        $possibleFirstRowOfData = $this->headerRowIndex + 1;
        $firstRowOfDataIndex    = $possibleFirstRowOfData;
        $lastIndexToCheck       = $possibleFirstRowOfData + $maxBlankRowsBeforeData;

        for ( $i = $possibleFirstRowOfData; $i <= $lastIndexToCheck; $i++ ):
            $data = trim( $allRows[ $i ][ 0 ] ?? '' );

            if ( $data ):
                return $firstRowOfDataIndex;
            else:
                $firstRowOfDataIndex++;
            endif;
        endfor;

        throw new NoDataInTabException( "Couldn't find data in this tab.",
                                        0,
                                        NULL,
                                        array_slice( $allRows, $this->headerRowIndex, $maxBlankRowsBeforeData ) );
    }


    /**
     * @return array
     */
    public function getCleanHeaders(): array  {
        return $this->cleanHeaders;
    }
}