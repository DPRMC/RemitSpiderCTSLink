<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use DPRMC\Excel\Excel;
use DPRMC\RemitSpiderCTSLink\Exceptions\HLMFLCRTabMissingSomeCategoriesException;
use DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions\AtLeastOneTabNotFoundException;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions\NoDatesInTabsException;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions\ProbablyExcelDateException;
use DPRMC\RemitSpiderCTSLink\Models\CMBSRestrictedServicerReport\CMBSRestrictedServicerReport;

class CMBSRestrictedServicerReportFactory {

    const DEFAULT_TIMEZONE = 'America/New_York';
    public readonly string $timezone;

    const FOOTNOTES = 'FOOTNOTES';
    const WATCHLIST = 'WATCHLIST';
    const DLSR      = 'DLSR';
    const REOSR     = 'REOSR';
    const CFSR      = 'CFSR';
    const HLMFCLR   = 'HLMFCLR';
    const LLRES     = 'LLRES';
    const TOTALLOAN = 'TOTALLOAN';
    const RECOVERY  = 'RECOVERY';

    public array $tabsThatHaveBeenFound = [
        self::WATCHLIST => FALSE,
        self::DLSR      => FALSE,
        self::REOSR     => FALSE,
        self::CFSR      => FALSE,
        self::HLMFCLR   => FALSE,
        self::LLRES     => FALSE,
        self::TOTALLOAN => FALSE,
        self::RECOVERY  => FALSE,
    ];

    public array $tabs = [
        self::FOOTNOTES => [
            'FootNotes',
        ],
        self::WATCHLIST => [
            'Watchlist',
            'Servicer Watch List',
            'Servicer Watch',
            'rptSvcWatchList',
        ], // SERVICER WATCHLIST
        self::DLSR      => [
            'DLSR',
            'Del Loan Status Report',
        ], // Delinquent Loan Status Report
        self::REOSR     => [
            'REOSR',
            'REO Status Report',
        ], // REO STATUS REPORT
        self::CFSR      => [
            'CFSR',
            'Comp Finan Status Report',
            'Comparative_Fin',
            'tCComparativeFinancialStatusIRP',
        ], // COMPARATIVE FINANCIAL STATUS REPORT
        self::HLMFCLR   => [
            'HLMFCLR',
            'Hist Mod-Corr Mtg ln',
            'HistLoanModForbCMLR',
            'Hist Mod-Corr Mtg Loan'
        ], // HISTORICAL LOAN MODIFICATION/FORBEARANCE and CORRECTED MORTGAGE LOAN REPORT
        self::LLRES     => [
            'LL Res, LOC',
            'LL Reserve Rpt',
            'LL_Res_LOC',
            'LL Res LOC'
        ], // LOAN LEVEL RESERVE/LOC REPORT
        self::TOTALLOAN => [
            'Total Loan',
            'Total Loan Report',
            'TLR',
            'Total_Loan_Report',
            'Total_Loan',
        ], // TOTAL LOAN REPORT
        self::RECOVERY  => [
            'Advance Recovery',
            'Recovery',
            'Advance_Recovery',
        ], // ADVANCE RECOVERY REPORT
    ];

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
     * @param string $pathToRestrictedServicerReportXlsx
     * @return CMBSRestrictedServicerReport
     * @throws AtLeastOneTabNotFoundException
     * @throws NoDatesInTabsException
     */
    public function make( string $pathToRestrictedServicerReportXlsx ): CMBSRestrictedServicerReport {
        $sheetNames = Excel::getSheetNames( $pathToRestrictedServicerReportXlsx );

        // Intialize these arrays that will get passed to the CMBSRestrictedServicerReport __constructor.
        $watchlist       = [];
        $dlsr            = [];
        $reosr           = [];
        $csfr            = [];
        $hlmfclr         = [];
        $llResLOC        = [];
        $totalLoan       = [];
        $advanceRecovery = [];

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
                $rows = Excel::sheetToArray( $pathToRestrictedServicerReportXlsx,
                                             $sheetName,
                                             NULL,
                                             NULL,
                                             TRUE,
                                             FALSE );

                //$headers = Excel::sheetHeaderToArray($pathToRestrictedServicerReportXlsx, $sheetName );
                if ( $this->_foundSheetName( self::WATCHLIST, $sheetName ) ):
                    $this->tabsThatHaveBeenFound[ self::WATCHLIST ] = TRUE;
                    $factory                                        = new WatchlistFactory( self::DEFAULT_TIMEZONE );
                    $watchlist                                      = $factory->parse( $rows,
                                                                                       $cleanHeadersBySheetName,
                                                                                       CMBSRestrictedServicerReport::watchlist );

                elseif ( $this->_foundSheetName( self::DLSR, $sheetName ) ):
                    $this->tabsThatHaveBeenFound[ self::DLSR ] = TRUE;
                    $factory                                   = new DLSRFactory( self::DEFAULT_TIMEZONE );
                    $dlsr                                      = $factory->parse( $rows,
                                                                                  $cleanHeadersBySheetName,
                                                                                  CMBSRestrictedServicerReport::dlsr );

                elseif ( $this->_foundSheetName( self::REOSR, $sheetName ) ):
                    $this->tabsThatHaveBeenFound[ self::REOSR ] = TRUE;
                    $factory                                    = new REOSRFactory( self::DEFAULT_TIMEZONE );
                    $reosr                                      = $factory->parse( $rows,
                                                                                   $cleanHeadersBySheetName,
                                                                                   CMBSRestrictedServicerReport::reosr );

                elseif ( $this->_foundSheetName( self::HLMFCLR, $sheetName ) ):
                    $this->tabsThatHaveBeenFound[ self::HLMFCLR ] = TRUE;
                    $factory                                      = new HLMFCLRFactory( self::DEFAULT_TIMEZONE );
                    $hlmfclr                                      = $factory->parse( $rows,
                                                                                     $cleanHeadersBySheetName,
                                                                                     CMBSRestrictedServicerReport::hlmfclr );

                elseif ( $this->_foundSheetName( self::CFSR, $sheetName ) ):
                    $this->tabsThatHaveBeenFound[ self::CFSR ] = TRUE;
                    $factory                                   = new CFSRFactory( self::DEFAULT_TIMEZONE );
                    $csfr                                      = $factory->parse( $rows,
                                                                                  $cleanHeadersBySheetName,
                                                                                  CMBSRestrictedServicerReport::csfr );

                elseif ( $this->_foundSheetName( self::LLRES, $sheetName ) ):
                    $this->tabsThatHaveBeenFound[ self::LLRES ] = TRUE;
                    $factory                                    = new LLResLOCFactory( self::DEFAULT_TIMEZONE );
                    $llResLOC                                   = $factory->parse( $rows,
                                                                                   $cleanHeadersBySheetName,
                                                                                   CMBSRestrictedServicerReport::llResLOC );

                elseif ( $this->_foundSheetName( self::TOTALLOAN, $sheetName ) ):
                    $this->tabsThatHaveBeenFound[ self::TOTALLOAN ] = TRUE;
                    $factory                                        = new TotalLoanFactory( self::DEFAULT_TIMEZONE );
                    $totalLoan                                      = $factory->parse( $rows,
                                                                                       $cleanHeadersBySheetName,
                                                                                       CMBSRestrictedServicerReport::totalLoan );

                elseif ( $this->_foundSheetName( self::RECOVERY, $sheetName ) ):
                    $this->tabsThatHaveBeenFound[ self::RECOVERY ] = TRUE;
                    $factory                                       = new AdvanceRecoveryFactory( self::DEFAULT_TIMEZONE );
                    $advanceRecovery                               = $factory->parse( $rows,
                                                                                      $cleanHeadersBySheetName,
                                                                                      CMBSRestrictedServicerReport::advanceRecovery );
                endif;
            } catch ( \Carbon\Exceptions\InvalidFormatException $exception ) {
                $newException = new ProbablyExcelDateException( $exception->getMessage(),
                                                                $exception->getCode(),
                                                                $exception->getPrevious(),
                                                                $sheetName );
                $exceptions[] = $newException;
            } catch ( NoDataInTabException $exception ) {
                $exception->sheetName = $sheetName;
                $alerts[]             = $exception;
                //$factory->getCleanHeaders(); // What was I doing with this?
            }
// I wrote this to be a show stopper, but it should be an ALERT. See below.
// Some of the HLM sheets will just not have certain categories.
// This presents a unique problem.
// - Am I unable to find a sub category that exists, or
// - Does the sub category just not exist?
// I am aggregating alerts/exceptions in the factory.
// I need to review the alerts regularly and compare against the XLS sheets to see
// If the parser needs to be updated or if its just a valid non-existent sub category.
            catch ( HLMFLCRTabMissingSomeCategoriesException $exception ) {
                // Let's make this a show-stopper, so the developer needs to edit the parser.
                //throw $exception;
                $alerts[] = $exception;
            } catch ( \Exception $exception ) {
                $exceptions[] = $exception;
            }
        endforeach;

        if ( $this->_atLeastOneTabNotFound() ):
            throw new AtLeastOneTabNotFoundException( "Need to update the tabs array in CMBSRestrictedServicerReportFactory. CTS has a different spelling for one of their tabs. Update every FALSE tab attached to this exception.",
                                                      0,
                                                      NULL,
                                                      $this->tabsThatHaveBeenFound,
                                                      $pathToRestrictedServicerReportXlsx,
                                                      $sheetNames);
        endif;


        $theDate = $this->_getTheDate( [ $watchlist,
                                         $dlsr,
                                         $reosr,
                                         $hlmfclr,
                                         $csfr,
                                         $llResLOC,
                                         $totalLoan,
                                         $advanceRecovery ] );


        $watchlist       = $this->_fillDateIfMissing( $watchlist, $theDate );
        $dlsr            = $this->_fillDateIfMissing( $dlsr, $theDate );
        $reosr           = $this->_fillDateIfMissing( $reosr, $theDate );
        $hlmfclr         = $this->_fillDateIfMissing( $hlmfclr, $theDate );
        $csfr            = $this->_fillDateIfMissing( $csfr, $theDate );
        $llResLOC        = $this->_fillDateIfMissing( $llResLOC, $theDate );
        $totalLoan       = $this->_fillDateIfMissing( $totalLoan, $theDate );
        $advanceRecovery = $this->_fillDateIfMissing( $advanceRecovery, $theDate );


        return new CMBSRestrictedServicerReport( $watchlist,
                                                 $dlsr,
                                                 $reosr,
                                                 $hlmfclr,
                                                 $csfr,
                                                 $llResLOC,
                                                 $totalLoan,
                                                 $advanceRecovery,
                                                 $cleanHeadersBySheetName,
                                                 $alerts,
                                                 $exceptions );
    }


    /**
     * A sanity check to make sure there is ONE and ONLY ONE date among the tabs.
     * @param array $tabs
     * @return string
     * @throws NoDatesInTabsException
     */
    protected function _getTheDate( array $tabs ): string {
        $dates = [];
        foreach ( $tabs as $tab ):
            foreach ( $tab as $i => $row ):
                if ( isset( $row[ 'date' ] ) ):
                    $dates[] = $row[ 'date' ];
                endif;
            endforeach;
        endforeach;

        $uniqueDates = array_unique( $dates );

        if ( count( $uniqueDates ) > 1 ):
            $dateCount = [];
            foreach ( $dates as $date ):
                if ( ! isset( $dateCount[ $date ] ) ):
                    $dateCount[ $date ] = 0;
                endif;
                $dateCount[ $date ]++;
            endforeach;

            arsort( $dateCount );

            return array_key_first( $dateCount );

            // Eh...
            // Examples have shown that IF there is more than one date... The other(s) are one-offs that can be ignored.
            // Just go with the date that appears the most times.
            //throw new \Exception( "There is more than one date among the tabs. " . implode( '|', $uniqueDates ) );
        endif;

        if ( count( $uniqueDates ) < 1 ):
            throw new NoDatesInTabsException( "There are NO dates in any of the tabs. Which is suuuuuper weird.",
                                              0,
                                              null,
                                              $tabs);
        endif;

        $date = reset( $uniqueDates ); // THE date

        return $date;
    }


    /**
     * @param array $rows
     * @param string $date
     * @return array
     */
    protected function _fillDateIfMissing( array $rows, string $date ): array {
        foreach ( $rows as $i => $row ):

            if ( array_key_exists( 'date', $row ) && empty( $row[ 'date' ] ) ):
                $rows[ $i ][ 'date' ] = $date;
            endif;
        endforeach;
        return $rows;
    }


    /**
     * A little encapsulated logic to make the above method cleaner.
     * @return bool
     */
    protected function _atLeastOneTabNotFound(): bool {
        foreach ( $this->tabsThatHaveBeenFound as $tabName => $found ):
            if ( FALSE === $found ):
                return TRUE;
            endif;
        endforeach;
        return FALSE;
    }


    /**
     * @param string $index
     * @param string $sheetName
     * @return bool
     */
    protected function _foundSheetName( string $index, string $sheetName ): bool {
        foreach ( $this->tabs[ $index ] as $tabName ):
            if ( $sheetName == $tabName ):
                return TRUE;
            endif;
        endforeach;
        return FALSE;
    }
}
