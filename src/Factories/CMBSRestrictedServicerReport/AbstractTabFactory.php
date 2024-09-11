<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use Carbon\Carbon;
use DPRMC\RemitSpiderCTSLink\Exceptions\DateNotFoundInHeaderException;
use DPRMC\RemitSpiderCTSLink\Exceptions\DuplicatesInHeaderRowException;
use DPRMC\RemitSpiderCTSLink\Exceptions\HeadersTooLongForMySQLException;
use DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions\DifferentSpellingOfTransactionIdNeededException;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions\TabWithSimilarNameAndDifferentHeaders;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps\AbstractFactoryToModelMap;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps\InterfaceFactoryToModelMap;
use DPRMC\RemitSpiderCTSLink\Factories\HeaderTrait;
use DPRMC\RemitSpiderCTSLink\Models\CMBSRestrictedServicerReport\CMBSRestrictedServicerReport;
use Illuminate\Support\Facades\Log;
use Matrix\Exception;

abstract class AbstractTabFactory {

    use HeaderTrait;

    const DEFAULT_TIMEZONE = 'America/New_York';
    protected string $timezone;

    protected int $headerRowIndex = 0;

    /**
     * @var array These are the headers that were parsed out of the XLS that was passed in.
     */
    protected array $localHeaders = [];

    /**
     * @var array Index of the array are sheetnames (ex: watchlist) under those are the headers that have been found in every sheet of that type.
     * I came to find out that certain sheets contained multiple "watchlist" sheets, for example. Those records need to be parsed
     * and aggregated from each of the separate lists of each type.
     */
    protected array $globalHeadersBySheetName = [];

    protected array $cleanRows = [];

    /**
     * @var Carbon|null Date of the file
     */
    protected ?Carbon $date;
    protected ?int    $documentId;

    protected array $firstColumnValidTextValues = [];
    protected ?int  $dateRowIndex               = NULL;


    protected array $replacementHeaders = [];

    protected string $sheetName = '';

    protected array $headerKeywords = [];

    //temp
    protected ?int $headerRowIndexThatContainsTheWordID = NULL;

    /**
     * @var string|null The class name of the Factory Model Map. The map is a static array.
     */
    protected ?string $factoryToModelMapName = NULL;


    public function __construct( string $timezone = NULL,
                                 string $factoryToModelMapName = NULL,
                                 Carbon $dateOfFile = NULL,
                                 int    $documentId = NULL,
                                 array  $headerKeywords = [] ) {
        if ( $timezone ):
            $this->timezone = $timezone;
        else:
            $this->timezone = self::DEFAULT_TIMEZONE;
        endif;

        $this->factoryToModelMapName = $factoryToModelMapName;

        $this->date           = $dateOfFile;
        $this->documentId     = $documentId;
        $this->headerKeywords = $headerKeywords;
    }

    abstract protected function _removeInvalidRows( array $rows = [] ): array;


    /**
     * @param array       $rows
     * @param array       $cleanHeadersByProperty
     * @param string      $sheetName
     * @param array       $existingCleanRows I found that in a given sheet there can be multiple tabs with "the same" name. I need to consolidate those.
     * @param string|NULL $debugFilename
     *
     * @return array
     * @throws HeadersTooLongForMySQLException
     * @throws NoDataInTabException
     * @throws TabWithSimilarNameAndDifferentHeaders
     */
    public function parse( array $rows, array &$cleanHeadersByProperty, string $sheetName, array $existingCleanRows = [], string $debugFilename = NULL ): array {


        // Empty first time through, if there is only one tab.
        $this->globalHeadersBySheetName = $cleanHeadersByProperty;

        $this->sheetName = $sheetName;
        try {
            $this->_setDate( $rows );
        } catch ( DateNotFoundInHeaderException $exception ) {
            // In some spreadsheets in some tabs, there is no date present.
            // I think the best strategy is to move forward, and see if I can
            // "borrow" the date from another tab in the sheet.
        }


        $this->_setLocalHeaders( $rows,
                                 $this->firstColumnValidTextValues,
                                 $sheetName,
                                 $debugFilename );

        $cleanHeadersByProperty[ $sheetName ] = $this->_integrateLocalHeadersWithGlobalHeaders( $cleanHeadersByProperty[ $sheetName ] ?? [],
                                                                                                $sheetName );

        $this->_setParsedRows( $rows, $sheetName, $existingCleanRows );

        return $this->cleanRows;
    }


    /**
     * @param array $allRows
     *
     * @return void
     * @throws DateNotFoundInHeaderException
     */
    protected function _setDate( array $allRows ): void {
        // If the date is already set in the constructor, then no need to search the tabs for a date.
        if ( $this->date ):
            return;
        endif;

//        $dateRow    = $allRows[ $dateRowIndex ][ 0 ];
//        $parts      = explode( ' ', $dateRow );
//        $stringDate = end( $parts );
//        $this->date = Carbon::parse( $stringDate, $this->timezone );

        //dump($allRows);
        $this->date = $this->_searchForDate( $allRows );
    }


    /**
     * @param array $allRows
     * @param int   $numRowsToCheck
     *
     * @return Carbon
     * @throws DateNotFoundInHeaderException
     * TODO put the patterns in an array and loop through them.
     */
    protected function _searchForDate( array $allRows, int $numRowsToCheck = 6 ): Carbon {

        $this->date = NULL;
        //$pattern    = '/^\d{1,2}\/\d{1,2}\/\d{4}$/';                                             // Will match dates like 1/1/2023 or 12/31/2023
        $pattern = '/\d{1,2}\/\d{1,2}\/\d{4}/';                                                  // Will match dates like 1/1/2023 or 12/31/2023 even when wrapped in other text.
        //$pattern_2  = '/^\d{8}$/';                                                               // Matches 20230311, but also matches 28010338, so I replaced it with the pattern below.
        $pattern_2 = '/^(\d{4}(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01]))$/';                       //
        $pattern_3 = '/^4\d{4}$/';                                                               // Matches an Excel date. Will DEFINITELY BREAK IN THE FUTURE.

        $columnsToCheck = [ 0, 1 ]; // Now I need to check the 2nd column too.... thanks CTS!

        for ( $i = 0; $i <= $numRowsToCheck; $i++ ):
            foreach ( $columnsToCheck as $columnIndex ):
                // Split on all white space. Not just spaces like I was doing before.
                $parts = preg_split( '/\s/', $allRows[ $i ][ $columnIndex ] ?? '' );
                // There shouldn't be any whitespace around the parts, but be sure...
                $parts = array_map( 'trim', $parts );

                foreach ( $parts as $part ):
                    if ( 1 === preg_match( $pattern, $part ) ):
                        return Carbon::parse( $part, $this->timezone );
                    endif;

                    if ( 1 === preg_match( $pattern_2, $part ) ):
                        return Carbon::parse( $part, $this->timezone );
                    endif;

                    if ( 1 === preg_match( $pattern_3, $part ) ):

                        $unixDate = ((int)$part - 25569) * 86400;
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
     * @param array       $allRows
     * @param array       $firstColumnValidTextValues
     * @param string|NULL $debugSheetName
     * @param string|NULL $debugFilename
     *
     * @return void
     * @throws \DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions\DifferentSpellingOfTransactionIdNeededException
     */
    protected function _setLocalHeaders( array  $allRows,
                                         array  $firstColumnValidTextValues = [],
                                         string $debugSheetName = NULL,
                                         string $debugFilename = NULL ): void {

        $this->headerRowIndex = $this->_getArrayIndexOfBottomHeaderRow( $allRows, $firstColumnValidTextValues, $debugSheetName, $debugFilename );
        $cleanHeaders         = $this->_consolidateMultipleHeaderRowsUsingKeywords( $allRows );
        $this->localHeaders   = $cleanHeaders;

        // Not all of the tabs/sheets have document_id present in the header row.
        // I want to include document_id in all of the parsed tabs in the JSON file.
        // I am adding document_id manually in this class.
        // The WatchlistFactory class was array_slicing rows to parse, and was cutting off the
        // document_id that I was manually adding.
        if ( !in_array( 'document_id', $this->localHeaders ) ):
            $this->localHeaders[] = 'document_id';
        endif;
        //dump(__LINE__);
        //dd($this->localHeaders);
    }


    /**
     * @param $value
     *
     * @return bool
     */
    private function _valueIsPartOfTheHeader( $value ): bool {
        if ( is_null( $value ) ):
            return FALSE;
        endif;

        $value = trim( $value );
        if ( empty( $value ) ):
            return FALSE;
        endif;

        if ( $this->_cellValueContainsHeaderKeyword( $value ) ):
            return TRUE;
        endif;

        return FALSE;
    }


    /**
     * @param string|NULL $value
     *
     * @return bool
     */
    private function _cellValueContainsHeaderKeyword( string $value = NULL ): bool {
        if ( is_null( $value ) ):
            return FALSE;
        endif;
        $value = strtolower( $value );

        foreach ( $this->headerKeywords as $headerKeyword ):
            if ( str_contains( $value, $headerKeyword ) ):
                return TRUE;
            endif;
        endforeach;
        return FALSE;
    }

    protected function _consolidateMultipleHeaderRowsUsingKeywords( array $allRows = [] ): array {

        $consolidatedHeaderRow         = [];
        $baseHeaderRow                 = $allRows[ $this->headerRowIndex ];
        $rowIndexAboveTheBaseHeaderRow = $this->headerRowIndex - 1;

        // This code should actually never run.
        // I dont think I have ever seen a sheet that had the header in the top row.
        if ( !array_key_exists( $rowIndexAboveTheBaseHeaderRow, $allRows[ 0 ] ) ):
            $cleanHeaderValues = [];
            foreach ( $baseHeaderRow as $headerValue ):
                $cleanHeaderValues[] = $this->cleanHeaderValue( $headerValue );
            endforeach;
            $consolidatedHeaderRow = $cleanHeaderValues;
            return $consolidatedHeaderRow;
        else:
            //dump( "there are definitely POTENTIAL headers to loop at above the base/current row I am looking at." );
        endif;

        foreach ( $baseHeaderRow as $columnIndex => $baseValue ):
            $nextRowUpToCheckForPotentialHeaderValue = $rowIndexAboveTheBaseHeaderRow; // Init every time.
            $partsOfTheHeaderToBeAssembled           = [];
            $partsOfTheHeaderToBeAssembled[]         = $baseValue;

            $nextPotentialHeaderValueFromTheCellAbove = trim( $allRows[ $nextRowUpToCheckForPotentialHeaderValue ][ $columnIndex ] );


            while ( $this->_valueIsPartOfTheHeader( $nextPotentialHeaderValueFromTheCellAbove ) ):
                array_unshift( $partsOfTheHeaderToBeAssembled, $nextPotentialHeaderValueFromTheCellAbove );
                $nextRowUpToCheckForPotentialHeaderValue--;


                //dump('$nextRowUpToCheckForPotentialHeaderValue' . $nextRowUpToCheckForPotentialHeaderValue);
                if ( 0 > $nextRowUpToCheckForPotentialHeaderValue ):
                    return [];
                    //dd( $allRows );
                endif;

                $nextPotentialHeaderValueFromTheCellAbove = $allRows[ $nextRowUpToCheckForPotentialHeaderValue ][ $columnIndex ];
            endwhile;

            $newConcatenatedHeader                 = $this->cleanHeaderValue( implode( ' ', $partsOfTheHeaderToBeAssembled ) );
            $consolidatedHeaderRow[ $columnIndex ] = $newConcatenatedHeader;
        endforeach;

        $consolidatedHeaderRow = array_filter( $consolidatedHeaderRow );
        $consolidatedHeaderRow = array_values( $consolidatedHeaderRow );

        return $consolidatedHeaderRow;

        //$this->localHeaders = $cleanHeaders;
    }


    /**
     * @param int   $headerRowIndex
     * @param array $allRows
     *
     * @return bool
     */
    protected function _isNextRowPartOfTheHeader( int $headerRowIndex, array $allRows = [] ): bool {
        $potentialSecondHeaderRowIndex = $headerRowIndex + 1;
        if ( !isset( $allRows[ $potentialSecondHeaderRowIndex ] ) ):
            return FALSE;
        endif;

        if ( !isset( $allRows[ $potentialSecondHeaderRowIndex ][ 0 ] ) ):
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
     * @param array  $allRows
     * @param array  $firstColumnValidTextValues
     * @param string $debugSheetName
     * @param string $debugFilename
     *
     * @return int
     * @throws DifferentSpellingOfTransactionIdNeededException
     */
    protected function _getArrayIndexOfBottomHeaderRow( array $allRows = [], array $firstColumnValidTextValues = [], string $debugSheetName = NULL, string $debugFilename = NULL ): int {
        $headerRowIndex = NULL;
        foreach ( $allRows as $i => $row ):

            $trimmedValue = trim( $row[ 0 ] ?? '' );

            if ( empty( $trimmedValue ) ):
                continue;
            endif;

            if ( in_array( $trimmedValue, $firstColumnValidTextValues ) ):
                $headerRowIndex = $i; // Used in other methods of this class.
                $headerRow      = $row;

                // Some sheets have the header split between 2 (or more) rows, because that's fun.
                if ( $this->_isNextRowPartOfTheHeader( $headerRowIndex, $allRows ) ):
                    $headerRowIndex++;
                endif;

                break;
            endif;
        endforeach;

        // Uncomment this for debugging. You probably need a new spelling of "Trans Id"
        if ( is_null( $headerRowIndex ) ) {
            throw new DifferentSpellingOfTransactionIdNeededException( "Unable to find an expected spelling of 'Transaction ID. Need to add to the array.",
                                                                       0,
                                                                       NULL,
                                                                       $debugFilename,
                                                                       $debugSheetName,
                                                                       $firstColumnValidTextValues,
                                                                       $allRows );
        }

        return $headerRowIndex;
    }


    /**
     * There are instances where the header is actually split among the top two rows.
     * Combine those values into consistent headers, and increment the header row index down one.
     *
     * @param int   $headerRowIndex
     * @param array $allRows
     *
     * @return bool
     */
    protected function _isSecondRowAlsoHeader( int $headerRowIndex, array $allRows = [] ): bool {
        $potentialSecondHeaderRowIndex = $headerRowIndex + 1;
        if ( !isset( $allRows[ $potentialSecondHeaderRowIndex ] ) ):
            return FALSE;
        endif;

        if ( !isset( $allRows[ $potentialSecondHeaderRowIndex ][ 0 ] ) ):
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
     * If the header is split among multiple rows, then this method will concatenate the header values into one row.
     *
     * @param array      $topHeaderRow
     * @param array      $bottomHeaderRow
     * @param array|NULL $possibleHeaderRowAbove
     *
     * @return array
     * @throws DuplicatesInHeaderRowException
     */
    protected function _consolidateMultipleHeaderRows( array $topHeaderRow = [], array $bottomHeaderRow = [], array $possibleHeaderRowAbove = NULL ): array {
        $headerRow = [];

        foreach ( $topHeaderRow as $i => $topName ):
            $topName = trim( $topName );
            if ( isset( $bottomHeaderRow[ $i ] ) ):
                $bottomName = trim( $bottomHeaderRow[ $i ] );
            else:
                $bottomName = NULL;
            endif;

            if ( isset( $possibleHeaderRowAbove[ $i ] ) ) :
                $aboveName = trim( $possibleHeaderRowAbove[ $i ] );
            else :
                $aboveName = NULL;
            endif;

            if ( $bottomName ):
                $headerName = $topName . ' ' . $bottomName;
            else:
                $headerName = $topName;
            endif;

            // It is possible when concatenating the rows that the concatenation results in a duplication of an existing header value
            // This is likely caused by the existence of an additional header row above the '$topHeaderRow'
            // This code includes the above header row in the concatenation to hopefully deliver a unique header value
            if ( in_array( $headerName, $headerRow ) && $aboveName ) :
                $headerName = $aboveName . ' ' . $topName . ' ' . $bottomName;
            endif;

            $headerRow[] = trim( $headerName );

        endforeach;

        if ( count( $headerRow ) !== count( array_unique( $headerRow ) ) ) :
            throw new DuplicatesInHeaderRowException( $headerRow );
        endif;

        return $headerRow;
    }


    /**
     * @param array       $allRows
     * @param string|NULL $sheetName
     * @param array       $existingRows
     *
     * @return void
     * @throws NoDataInTabException
     */
    protected function _setParsedRows( array $allRows, string $sheetName = NULL, array $existingRows = [] ): void {
        $this->cleanRows = $existingRows;


        //dd( $this->localHeaders );
        //dd( $allRows );
        $validRows = $this->_getRowsToBeParsed( $allRows );
//        $validRows = $this->_removeInvalidRows( $validRows );

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
     * I had thought about disqualifying rows in the if/elseif clause below, but that
     * logic should get pushed into each of the child tab factories, since the code
     * appears to be bespoke for each. Super duper.
     *
     * @param array $allRows
     *
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
                                        $this->_getLocalHeaders(),
                                        $this->sheetName );
    }


    /**
     * @return array
     * @throws HeadersTooLongForMySQLException
     */
    public function _getLocalHeaders(): array {

        // Some headers from the XLS are too long to create MySQL headers from.
        // When this happens, I need to add a new str_replace translator into the munge code.
        $tooLongHeadersForMySQL = [];

        foreach ( $this->localHeaders as $i => $cleanHeader ):
            if ( strlen( $cleanHeader ) > 64 ):
                $tooLongHeadersForMySQL[ $i ] = $cleanHeader;
            endif;
        endforeach;

        if ( !empty( $tooLongHeadersForMySQL ) ):
            throw new HeadersTooLongForMySQLException( "At LEAST one header from XLSX was too long to create an MySQL column name.",
                                                       0,
                                                       NULL,
                                                       $tooLongHeadersForMySQL );
        endif;


        return $this->localHeaders;
    }


    /**
     * @param array       $globalHeaders
     * @param string|NULL $debugSheetName
     *
     * @return array
     * @throws HeadersTooLongForMySQLException
     * @throws TabWithSimilarNameAndDifferentHeaders
     */
    public function _integrateLocalHeadersWithGlobalHeaders( array $globalHeaders, string $debugSheetName = NULL ): array {
        // Some headers from the XLS are too long to create MySQL headers from.
        // When this happens, I need to add a new str_replace translator into the munge code.
        $tooLongHeadersForMySQL = [];


        foreach ( $this->localHeaders as $i => $cleanHeader ):
            if ( strlen( $cleanHeader ) > 64 ):
                $tooLongHeadersForMySQL[ $i ] = $cleanHeader;
            endif;
        endforeach;

        if ( !empty( $tooLongHeadersForMySQL ) ):
            $exception = new HeadersTooLongForMySQLException( "At least one header from XLSX was too long to create an MySQL column name.",
                                                              0,
                                                              NULL,
                                                              $tooLongHeadersForMySQL );
            // Placeholder to dump the Exception message for debugging.
            throw $exception;
        endif;

        if ( empty( $globalHeaders ) ):
            return $this->localHeaders;
        endif;


        // Else we are on TAB 2 (or n) and already have some global headers set.
        foreach ( $this->localHeaders as $localHeader ):
            if ( in_array( $localHeader, $globalHeaders ) ):
                continue;
            else:

//                // TODO determine if I actually need to match the header with the different name to what is in global headers.
//                // I believe this is already being done in the SaveToDatabase() method.
//                if ( $this->factoryToModelMapName ):
//                    $field = AbstractFactoryToModelMap::getField( $this->factoryToModelMapName::$map, $localHeader );
//                endif;
//
//                // Do I add to the list of headers?
//                throw new TabWithSimilarNameAndDifferentHeaders( "This is the case I wanted to punt on " . $localHeader, 0, NULL, $localHeader, $globalHeaders );
            endif;
        endforeach;

        return $this->localHeaders;
    }


    /**
     * Rename replacements.
     * Some sheets have fields named different things.
     * Make the replacements here.
     *
     * @param array $cleanHeaders
     *
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
