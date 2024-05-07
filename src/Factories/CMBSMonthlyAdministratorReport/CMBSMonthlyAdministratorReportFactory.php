<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSMonthlyAdministratorReport;

use Carbon\Carbon;
use DPRMC\Excel\Excel;
use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsLink;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions\NoDatesInTabsException;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions\ProbablyExcelDateException;
use DPRMC\RemitSpiderCTSLink\Factories\HeaderTrait;
use DPRMC\RemitSpiderCTSLink\Models\CMBSMonthlyAdministratorReport\CMBSMonthlyAdministratorReport;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Date;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Helpers;

class CMBSMonthlyAdministratorReportFactory {

    use HeaderTrait;

    const DEFAULT_TIMEZONE = 'America/New_York';
    public readonly string $timezone;

    const LPU = 'LPU';

    public array $exceptions = [];
    public array $alerts     = [];

    protected ?Carbon   $dateOfFile;
    public readonly int $documentId;

    /**
     * @param string|NULL $timezone
     */
    public function __construct( string $timezone = NULL, Carbon $dateOfFile = NULL, int $documentId = NULL ) {
        if ( $timezone ):
            $this->timezone = $timezone;
        else:
            $this->timezone = self::DEFAULT_TIMEZONE;
        endif;

        $this->dateOfFile = $dateOfFile;
        $this->documentId = $documentId;
    }


    /**
     * @param string $pathToMonthlyAdministratorReportXlsx
     * @param CustodianCtsLink $ctsLink
     * @return CMBSMonthlyAdministratorReport
     */
    public function make( string $pathToMonthlyAdministratorReportXlsx, CustodianCtsLink $ctsLink ): CMBSMonthlyAdministratorReport {
        $sheetNames = Excel::getSheetNames( $pathToMonthlyAdministratorReportXlsx );


        // Initialize these arrays that will get passed to the CMBSMonthlyAdministratorReport __constructor.
        $lpu = [];


        /**
         * Exceptions that aren't fatal. It could just be that I couldn't
         * find any data in a tab.
         */
        $alerts = [];

        /**
         * These are errors that might indicate a failure to parse a file.
         * These should be reported and investigated as possible places
         * I need to patch the parser code.
         */
        $exceptions = [];

        // This will save us a ton of time.
        $cleanHeadersBySheetName = [];

        foreach ( $sheetNames as $sheetName ):

            try {
                $rows = Excel::sheetToArray( $pathToMonthlyAdministratorReportXlsx,
                                             $sheetName,
                                             NULL,
                                             NULL,
                                             TRUE, // Was TRUE and was causing a #REF! date error. In Format.php line 139: Unsupported operand types: string + string
                                             FALSE );

                array_shift( $rows ); // Garbage row of integers
                $topHeader    = array_shift( $rows );
                $bottomHeader = array_shift( $rows );

                $headers = $this->_combineTheHeaderRows( $topHeader, $bottomHeader );
                $headers = $this->_cleanTheHeaders( $headers );

                $lpu = $this->_getLpuData( $headers, $rows, $ctsLink );

                dd( $lpu );
            } catch ( \Carbon\Exceptions\InvalidFormatException $exception ) {
                $newException = new ProbablyExcelDateException( $exception->getMessage(),
                                                                $exception->getCode(),
                                                                $exception->getPrevious(),
                                                                $sheetName );
                $exceptions[] = $newException;
            } catch ( \Exception $exception ) {
                $exceptions[] = $exception;
            }
        endforeach;


        return new CMBSMonthlyAdministratorReport( $lpu, $exceptions, $alerts );
    }


    protected function _combineTheHeaderRows( array $topHeader, array $bottomHeader ): array {
        $combinedHeaders = [];
        foreach ( $topHeader as $i => $header ):
            $combinedHeaders[ $i ] = trim( $header ) . ' ' . ( $bottomHeader[ $i ] );
        endforeach;
        return $combinedHeaders;
    }

    protected function _cleanTheHeaders( array $headers ): array {
        foreach ( $headers as $i => $header ):
            $headers[ $i ] = $this->cleanHeaderValue( $header );
        endforeach;
        return $headers;
    }

    protected function _getLpuData( array $headers, array $rows, CustodianCtsLink $ctsLink ): array {
        $parsedData = [];

        foreach ( $rows as $i => $row ):
            $newRow = [];

            if ( isset( $ctsLink->{CustodianCtsLink::revised_date} ) ):
                $newRow[ 'date' ] = $ctsLink->{CustodianCtsLink::revised_date}->toDateString();
            else:
                $newRow[ 'date' ] = $ctsLink->{CustodianCtsLink::date_of_file}->toDateString();
            endif;


            foreach ( $row as $j => $value ):
                if ( 'paid_thru_date' == $headers[ $j ] && is_numeric($headers[ $j ]) ):
                    $newRow[ $headers[ $j ] ] = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
                else:
                    $newRow[ $headers[ $j ] ] = $value;
                endif;


            endforeach;
            $parsedData[] = $newRow;
        endforeach;

        return $parsedData;
    }


    /**
     * A sanity check to make sure there is ONE and ONLY ONE date among the tabs.
     * @param array $tabs
     * @return string
     * @throws NoDatesInTabsException
     */
//    protected function _getTheDate( array $tabs ): string {
//        $dates = [];
//        foreach ( $tabs as $tab ):
//            foreach ( $tab as $i => $row ):
//                if ( isset( $row[ 'date' ] ) ):
//                    $dates[] = $row[ 'date' ];
//                endif;
//            endforeach;
//        endforeach;
//
//        $uniqueDates = array_unique( $dates );
//
//        if ( count( $uniqueDates ) > 1 ):
//            $dateCount = [];
//            foreach ( $dates as $date ):
//                if ( ! isset( $dateCount[ $date ] ) ):
//                    $dateCount[ $date ] = 0;
//                endif;
//                $dateCount[ $date ]++;
//            endforeach;
//
//            arsort( $dateCount );
//
//            return array_key_first( $dateCount );
//
//            // Eh...
//            // Examples have shown that IF there is more than one date... The other(s) are one-offs that can be ignored.
//            // Just go with the date that appears the most times.
//            //throw new \Exception( "There is more than one date among the tabs. " . implode( '|', $uniqueDates ) );
//        endif;
//
//        if ( count( $uniqueDates ) < 1 ):
//            throw new NoDatesInTabsException( "There are NO dates in any of the tabs. Which is suuuuuper weird.",
//                                              0,
//                                              NULL,
//                                              $tabs );
//        endif;
//
//        $date = reset( $uniqueDates ); // THE date
//
//        return $date;
//    }


    /**
     * @param array $rows
     * @param string $date
     * @return array
     */
//    protected function _fillDateIfMissing( array $rows, string $date ): array {
//        foreach ( $rows as $i => $row ):
//
//            if ( array_key_exists( 'date', $row ) && empty( $row[ 'date' ] ) ):
//                $rows[ $i ][ 'date' ] = $date;
//            endif;
//        endforeach;
//        return $rows;
//    }


    /**
     * A little encapsulated logic to make the above method cleaner.
     * @return bool
     */
//    protected function _atLeastOneTabNotFound(): bool {
//        foreach ( $this->tabsThatHaveBeenFound as $tabName => $found ):
//            if ( FALSE === $found ):
//                return TRUE;
//            endif;
//        endforeach;
//        return FALSE;
//    }


    /**
     * @param string $index
     * @param string $sheetName
     * @return bool
     */
//    protected function _foundSheetName( string $index, string $sheetName ): bool {
//        foreach ( $this->tabs[ $index ] as $tabName ):
//            if ( $sheetName == $tabName ):
//                return TRUE;
//            endif;
//        endforeach;
//        return FALSE;
//    }
}
