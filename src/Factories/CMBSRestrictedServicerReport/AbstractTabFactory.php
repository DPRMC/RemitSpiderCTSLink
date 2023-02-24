<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use Carbon\Carbon;

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


    abstract public function parse( array $rows ): array;

    protected function _setDate( array $allRows, int $dateRowIndex = 3 ): void {
        $dateRow      = $allRows[ $dateRowIndex ][ 0 ];
        $parts        = explode( ' ', $dateRow );
        $stringDate   = end( $parts );
        $this->date   = Carbon::parse( $stringDate, $this->timezone );
    }


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

            $newHeader = strtolower( $header );
            $newHeader = str_replace( ' ', '_', $newHeader );
            $newHeader = str_replace( "\n", '_', $newHeader );
            $newHeader = str_replace( "(s)", '', $newHeader );
            $newHeader = str_replace( "_(", '_', $newHeader );
            $newHeader = str_replace( ")", '', $newHeader );
            $newHeader = str_replace( '/', '_', $newHeader );
            $newHeader = str_replace( '_-_', '_', $newHeader );
            $newHeader = str_replace( '--', '_', $newHeader );

            $newHeader = ltrim( $newHeader, '_' );
            $newHeader = rtrim($newHeader, '_1');

            $cleanHeaders[ $i ] = $newHeader;
        endforeach;
        $this->cleanHeaders = $cleanHeaders;
    }

    /**
     * @param array $allRows
     * @return void
     */
    protected function _setParsedRows( array $allRows ): void {
        $cleanRows = [];
        $validRows = $this->_getRowsToBeParsed( $allRows );

        foreach ( $validRows as $i => $validRow ):
            $newCleanRow           = [];
            $newCleanRow[ 'date' ] = $this->date->toDateString();
            foreach ( $this->cleanHeaders as $j => $header ):
                $newCleanRow[ $header ] = $validRow[ $j ];
            endforeach;
            $cleanRows[] = $newCleanRow;
        endforeach;

        $this->cleanRows = $cleanRows;
    }

    protected function _getRowsToBeParsed( array $allRows ): array {
        $firstBlankRowIndex  = 0;
        $firstRowOfDataIndex = $this->headerRowIndex + 3;
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
}