<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use Carbon\Carbon;
use DPRMC\RemitSpiderCTSLink\Exceptions\DateNotFoundInHeaderException;
use DPRMC\RemitSpiderCTSLink\Exceptions\HeadersTooLongForMySQLException;
use DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException;

abstract class AbstractTabFactory {

    const DEFAULT_TIMEZONE = 'America/New_York';
    protected string $timezone;

    protected int   $headerRowIndex = 0;
    protected array $cleanHeaders   = [];
    protected array $cleanRows      = [];

    protected ?Carbon $date;

    protected array $firstColumnValidTextValues = [];
    protected ?int  $dateRowIndex               = NULL;


    protected array $replacementHeaders = [];

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
     * @param array $cleanHeadersByProperty
     * @param string $sheetName
     * @return array
     * @throws DateNotFoundInHeaderException
     * @throws HeadersTooLongForMySQLException
     * @throws NoDataInTabException
     */
    public function parse( array $rows, array &$cleanHeadersByProperty, string $sheetName ): array {

        try {
            $this->_setDate( $rows );
        } catch ( DateNotFoundInHeaderException $exception ) {
            // In some spreadsheets in some tabs, there is no date present.
            // I think the best strategy is to move forward, and see if I can
            // "borrow" the date from another tab in the sheet.
        }


        $this->_setCleanHeaders( $rows, $this->firstColumnValidTextValues );
        $cleanHeadersByProperty[ $sheetName ] = $this->getCleanHeaders();
        $this->_setParsedRows( $rows );

        return $this->cleanRows;
    }


    /**
     * @param array $allRows
     * @return void
     * @throws DateNotFoundInHeaderException
     */
    protected function _setDate( array $allRows ): void {
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
     * TODO put the patterns in an array and loop through them.
     */
    protected function _searchForDate( array $allRows, int $numRowsToCheck = 6 ): Carbon {

        $this->date = NULL;
        $pattern    = '/^\d{1,2}\/\d{1,2}\/\d{4}$/'; // Will match dates like 1/1/2023 or 12/31/2023
        $pattern_2  = '/^\d{8}$/';                   // Matches 20230311
        $pattern_3  = '/^4\d{4}$/';                  // Matches an Excel date. Will DEFINITELY BREAK IN THE FUTURE.

        $columnsToCheck = [ 0, 1 ]; // Now I need to check the 2nd column too.... thanks CTS!

        for ( $i = 0; $i <= $numRowsToCheck; $i++ ):
            foreach ( $columnsToCheck as $columnIndex ):
                // Split on all white space. Not just spaces like I was doing before.
                $parts = preg_split( '/\s/', $allRows[ $i ][ $columnIndex ] ?? '' );
                // There shouldn't be any whitespace around the parts, but be sure...
                $parts = array_map( 'trim', $parts );

                foreach ( $parts as $part ):
//                    var_dump( $part );
                    if ( 1 === preg_match( $pattern, $part ) ):
                        return Carbon::parse( $part, $this->timezone );
                    endif;

                    if ( 1 === preg_match( $pattern_2, $part ) ):
                        return Carbon::parse( $part, $this->timezone );
                    endif;

                    if ( 1 === preg_match( $pattern_3, $part ) ):

                        $unixDate = ( (int)$part - 25569 ) * 86400;
                        $carbon   = Carbon::createFromTimestamp( $unixDate, $this->timezone );

                        return $carbon;
                    endif;
                endforeach;


            endforeach;
        endfor;

        throw new DateNotFoundInHeaderException( "Patch the parser. Can't find the date. Some headers don't have the date present. Going to add code now to 'borrow' the date from another sheet.",
                                                 8732465782, // Gibberish
                                                 NULL,
                                                 array_slice( $allRows,
                                                              0,
                                                              $numRowsToCheck ) );
    }


    /**
     * @param array $allRows
     * @param array $firstColumnValidTextValues
     * @return void
     */
    protected function _setCleanHeaders( array $allRows, array $firstColumnValidTextValues = [] ): void {
        $headerRow = [];
        foreach ( $allRows as $i => $row ):

            $trimmedValue = trim( $row[ 0 ] ?? '' );

            if ( empty( $trimmedValue ) ):
                continue;
            endif;


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
        $newHeader = str_replace( 'as of', 'as_of', $newHeader );// _as of_

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

        $newHeader = str_replace( '?_r_n', '', $newHeader ); // is_it_still_recoverable_or_nonrecoverable?_r_n
        $newHeader = str_replace( ',_', '_', $newHeader );   // if_nonrecoverable_advances_reimbursed_from_principal,_realized_loss_amount

        $newHeader = str_replace( 'non-recoverable', 'non_recoverable', $newHeader ); // wodra_deemed_non-recoverable_date

        $newHeader = str_replace( 'workout_strategy*', 'workout_strategy', $newHeader ); // workout_strategy*

        $newHeader = str_replace( 'total_t&i_advance_outstanding',
                                  'total_t_and_i_advance_outstanding',
                                  $newHeader );// workout_strategy*

        $newHeader = str_replace( 'reimburse-ment',
                                  'reimbursement_date',
                                  $newHeader ); // servicer_info_initial_reimburse-ment_date

        // most_recent_financial_information_normalized_$_noi_ncf
        $newHeader = str_replace( 'most_recent_financial_information_normalized_$_noi_ncf',
                                  'most_recent_financial_information_normalized_noi_ncf',
                                  $newHeader );


        // Too long.
        // if_nonrecoverable_advances_reimbursed_from_principal_realized_loss_amount
        $newHeader = str_replace( 'if_nonrecoverable_advances_reimbursed_from_principal_realized_loss_amount',
                                  'if_nonrec_adv_reimb_from_prin_realized_loss_amount',
                                  $newHeader ); //if_nonrecoverable_advances_reimbursed_from_principal_realized_loss_amount


        //
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
            $newCleanRow = [];


            // Some tabs leave the date off.
            // So set a placeholder of NULL for now, and I will "borrow" the date from another tab.
            if ( $this->date ):
                $newCleanRow[ 'date' ] = $this->date->toDateString();
            else:
                $newCleanRow[ 'date' ] = NULL;
            endif;


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
                                        array_slice( $allRows, $this->headerRowIndex, $maxBlankRowsBeforeData ),
                                        $this->getCleanHeaders() );
    }


    /**
     * @return array
     * @throws HeadersTooLongForMySQLException
     */
    public function getCleanHeaders(): array {

        // Some of the headers from the XLS are too long to create MySQL headers from.
        // When this happens, I need to add a new str_replace translator into the munge code.
        $tooLongHeadersForMySQL = [];

        foreach ( $this->cleanHeaders as $i => $cleanHeader ):
            if ( strlen( $cleanHeader ) > 64 ):
                $tooLongHeadersForMySQL[ $i ] = $cleanHeader;
            endif;
        endforeach;

        if ( ! empty( $tooLongHeadersForMySQL ) ):
            throw new HeadersTooLongForMySQLException( "At least one header from XLSX was too long to create an MySQL column name.",
                                                       0,
                                                       NULL,
                                                       $tooLongHeadersForMySQL );
        endif;


        return $this->cleanHeaders;
    }


    /**
     * Rename replacements.
     * Some sheets have fields named different things.
     * Make the replacements here.
     * @param array $cleanHeaders
     * @return array
     */
    protected function _applyReplacementHeaders( array $cleanHeaders ): array {
        foreach ( $cleanHeaders as $i => $cleanHeader ):
            if ( array_key_exists( $cleanHeader, $this->replacementHeaders ) ):
                $cleanHeaders[ $i ] = $this->replacementHeaders[ $cleanHeader ];
            endif;
        endforeach;
        return $cleanHeaders;
    }

}
