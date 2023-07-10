<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use Carbon\Carbon;
use DPRMC\RemitSpiderCTSLink\Exceptions\DateNotFoundInHeaderException;
use DPRMC\RemitSpiderCTSLink\Exceptions\HeadersTooLongForMySQLException;
use DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException;
use DPRMC\RemitSpiderCTSLink\Factories\HeaderTrait;

abstract class AbstractTabFactory {

    use HeaderTrait;

    const DEFAULT_TIMEZONE = 'America/New_York';
    protected string $timezone;

    protected int   $headerRowIndex = 0;
    protected array $cleanHeaders   = [];
    protected array $cleanRows      = [];

    protected ?Carbon $date;

    protected array $firstColumnValidTextValues = [];
    protected ?int  $dateRowIndex               = NULL;


    protected array $replacementHeaders = [];

    protected string $sheetName = '';

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

        $this->sheetName = $sheetName;
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

                if ( $this->_isSecondRowAlsoHeader( $this->headerRowIndex, $allRows ) ):
                    $headerRow = $this->_consolidateMultipleHeaderRows( $allRows[ $this->headerRowIndex ], $allRows[ $this->headerRowIndex + 1 ] );
                    $this->headerRowIndex++;
                endif;

                break;
            endif;
        endforeach;

        $cleanHeaders = [];

        foreach ( $headerRow as $i => $header ):

            // Avoid deprecation warning about passing null to strtolower.
            if ( empty( $header ) ):
                continue;
            endif;

            $cleanHeaders[ $i ] = $this->cleanHeaderValue( $header );
        endforeach;
        $this->cleanHeaders = $cleanHeaders;
    }


    /**
     * There are instances where the header is actually split among the top two rows.
     * Combine those values into consistent headers, and increment the header row index down one.
     * @param int $headerRowIndex
     * @param array $allRows
     * @return bool
     */
    protected function _isSecondRowAlsoHeader( int $headerRowIndex, array $allRows = [] ): bool {
        $potentialSecondHeaderRowIndex = $headerRowIndex + 1;
        if ( ! isset( $allRows[ $potentialSecondHeaderRowIndex ] ) ):
            return FALSE;
        endif;

        if ( ! isset( $allRows[ $potentialSecondHeaderRowIndex ][ 0 ] ) ):
            return FALSE;
        endif;

        $trimmedValue   = trim( $allRows[ $potentialSecondHeaderRowIndex ][ 0 ] );
        $lowercaseValue = strtolower( $trimmedValue );

        if ( 'id' == $lowercaseValue ):
            return TRUE;
        endif;

        return FALSE;
    }


    /**
     * @param array $topHeaderRow
     * @param array $bottomHeaderRow
     * @return array
     */
    protected function _consolidateMultipleHeaderRows( array $topHeaderRow = [], array $bottomHeaderRow = [] ): array {
        $headerRow = [];
        foreach ( $topHeaderRow as $i => $topName ):
            $topName = trim( $topName );
            if ( isset( $bottomHeaderRow[ $i ] ) ):
                $bottomName = trim( $bottomHeaderRow[ $i ] );
            else:
                $bottomName = NULL;
            endif;

            if ( $bottomName ):
                $headerRow[] = $topName . ' ' . $bottomName;
            else:
                $headerRow[] = $topName;
            endif;
        endforeach;

        return $headerRow;
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

            // Cut off the total row.
            if ( isset( $newCleanRow[ 'trans_id' ] ) && str_contains( $newCleanRow[ 'trans_id' ], 'Total' ) ):
                continue;
            endif;

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

        throw new NoDataInTabException( "Couldn't find data in this tab: " . $this->sheetName,
                                        0,
                                        NULL,
                                        array_slice( $allRows, $this->headerRowIndex, $maxBlankRowsBeforeData ),
                                        $this->getCleanHeaders(),
                                        $this->sheetName );
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
