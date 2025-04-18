<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use Carbon\Carbon;
use DPRMC\Excel\Excel;
use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsLink;
use DPRMC\RemitSpiderCTSLink\Exceptions\DuplicatesInHeaderRowException;
use DPRMC\RemitSpiderCTSLink\Exceptions\HLMFLCRTabMissingSomeCategoriesException;
use DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException;
use DPRMC\RemitSpiderCTSLink\Exceptions\ValidationHeadersNotFoundException;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions\AtLeastOneTabNotFoundException;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions\NoDatesInTabsException;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions\ProbablyExcelDateException;
use DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions\TabWithSimilarNameAndDifferentHeaders;
use DPRMC\RemitSpiderCTSLink\Models\CMBSRestrictedServicerReport\CMBSRestrictedServicerReport;

class CMBSRestrictedServicerReportFactory {

    const DEFAULT_TIMEZONE = 'America/New_York';
    public readonly string $timezone;

    public array $exceptions = [];

    const FOOTNOTES = 'FOOTNOTES';
    const WATCHLIST = 'WATCHLIST';
    const DLSR      = 'DLSR';
    const REOSR     = 'REOSR';
    const CFSR      = 'CFSR';
    const HLMFCLR   = 'HLMFCLR';
    const LLRES     = 'LLRES';
    const TOTALLOAN = 'TOTALLOAN';
    const RECOVERY  = 'RECOVERY';


    const PROPERTIES = 'PROPERTIES';

    public array $tabsThatHaveBeenFound = [
        self::WATCHLIST  => FALSE,
        self::DLSR       => FALSE,
        self::REOSR      => FALSE,
        self::CFSR       => FALSE,
        self::HLMFCLR    => FALSE,
        self::LLRES      => FALSE,
        self::TOTALLOAN  => FALSE,
        self::RECOVERY   => FALSE,
        self::PROPERTIES => FALSE,
    ];


    public array $tabs = [
        self::FOOTNOTES  => [
            'FootNotes',
        ],
        self::WATCHLIST  => [
            'Watchlist',
            'Servicer Watch List',
            'Servicer Watch',
            'rptSvcWatchList',
            'rptWServicerWatchlistIRP',
            'Servicer_Watchlist',
            'Watch List Report',
            'WATCHLIST',
            'WatchList',
        ], // SERVICER WATCHLIST
        self::DLSR       => [
            'DLSR',
            'Del Loan Status Report',
            'Delinquent Loan Status',
            'rptDDelinquentLoanStatus',
            'Delinquent',
        ], // Delinquent Loan Status Report
        self::REOSR      => [
            'REOSR',
            'REO Status Report',
            'REO STATUS',
            'REOStatus',
            'rptREOStatus',
        ], // REO STATUS REPORT
        self::CFSR       => [
            'CFSR',
            'Comp Finan Status Report',
            'Comparative_Fin',
            'Comparative Fin',
            'tCComparativeFinancialStatusIRP',
            'Comparative Fin Status Report',
            'Comparative Financial Report',
            'rptCComparativeFinancialStatus',
        ], // COMPARATIVE FINANCIAL STATUS REPORT
        self::HLMFCLR    => [
            'HLMFCLR',
            'Hist Mod-Corr Mtg ln',
            'HistLoanModForbCMLR',
            'Hist Mod-Corr Mtg Loan',
            'Historical Modification Report',
            'rptMHistoricalLoanMod',
            'HistLoanMod',
            'LoanMod',
        ], // HISTORICAL LOAN MODIFICATION/FORBEARANCE and CORRECTED MORTGAGE LOAN REPORT
        self::LLRES      => [
            'LL Res, LOC',
            'LL Reserve Rpt',
            'LL_Res_LOC',
            'LL Res LOC',
            'rptRsvLOC',
            'Loan Level Reserve  LOC Report',
            'Reserve_LOC',
            'LoanLevelReserve', // 20240608:mdd
        ], // LOAN LEVEL RESERVE/LOC REPORT
        self::TOTALLOAN  => [
            'Total Loan',
            'Total Loan Report',
            'TLR',
            'Total_Loan_Report',
            'Total_Loan',
            'rptTotalLoan',
            'TotalLoan',
            'rptTTotalLoan',
            'Total_Loan_Report',
            'BSCMS_2007PWR17_tlr_0524', // 20240608:mdd
        ], // TOTAL LOAN REPORT
        self::RECOVERY   => [
            'Advance Recovery',
            'Recovery',
            'Advance_Recovery',
            'Adv Recovery',
            'rptAdvRecovery',
        ], // ADVANCE RECOVERY REPORT
        self::PROPERTIES => [
            '_property',
        ],
    ];

    protected CustodianCtsLink $custodianCtsLink;
    protected ?Carbon          $dateOfFile;
    public readonly int        $documentId;

    public array $headerKeywords = [];

    /**
     * @param string|NULL $timezone
     */
    public function __construct( string $timezone = NULL,
                                 Carbon $dateOfFile = NULL,
                                 int    $documentId = NULL,
                                 array  $headerKeywords = [] ) {
        if ( $timezone ):
            $this->timezone = $timezone;
        else:
            $this->timezone = self::DEFAULT_TIMEZONE;
        endif;

        $this->dateOfFile     = $dateOfFile;
        $this->documentId     = $documentId;
        $this->headerKeywords = $headerKeywords;
    }


    /**
     * TODO I should create an INTERFACE that this and the CMBSMonthlyAdministratorReportFactory both implement.
     *
     * @param string                $pathToRestrictedServicerReportXlsx
     * @param CustodianCtsLink|NULL $ctsLink Not used in this particular make method()
     * @param Carbon|NULL           $dateOfFile
     *
     * @return CMBSRestrictedServicerReport
     * @throws NoDatesInTabsException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function make( string           $pathToRestrictedServicerReportXlsx,
                          CustodianCtsLink $ctsLink = NULL,
                          Carbon           $dateOfFile = NULL ): CMBSRestrictedServicerReport {
        $this->custodianCtsLink = $ctsLink;
        $this->dateOfFile       = $dateOfFile ?? $this->dateOfFile;
        $sheetNames             = Excel::getSheetNames( $pathToRestrictedServicerReportXlsx );

        // Intialize these arrays that will get passed to the CMBSRestrictedServicerReport __constructor.
        $watchlist       = [];
        $dlsr            = [];
        $reosr           = [];
        $csfr            = [];
        $hlmfclr         = [];
        $llResLOC        = [];
        $totalLoan       = [];
        $advanceRecovery = [];

        $properties = [];


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


        foreach ( $sheetNames as $i => $sheetName ):

            // DEBUG
            /**
             * ^ "Comp Finan Status Report"
             * ^ "Servicer Watch List"
             * ^ "Comparative_Fin"
             */
            //$debugArray = [
            //    //'Comp Finan Status Report',
            //    //'Comparative_Fin',
            //    //'Watchlist',
            //    'Hist Mod-Corr Mtg ln',
            //];
            //if( ! in_array( $sheetName, $debugArray ) ):
            //    continue;
            //endif;
            //
            //dump("SHEETNAME: ". $sheetName);

            //$debugArray = [
            //   'srpt14star_202410_property',
            //];
            //if( ! in_array( $sheetName, $debugArray ) ):
            //    continue;
            //endif;


            try {
                $rows = Excel::sheetToArray( $pathToRestrictedServicerReportXlsx,
                                             $sheetName,
                                             NULL,
                                             NULL,
                                             FALSE, // Was TRUE and was causing a #REF! date error. In Format.php line 139: Unsupported operand types: string + string
                                             FALSE );

                //$headers = Excel::sheetHeaderToArray($pathToRestrictedServicerReportXlsx, $sheetName );
                if ( $this->_foundSheetName( self::WATCHLIST, $sheetName ) ):

                    $this->tabsThatHaveBeenFound[ self::WATCHLIST ] = TRUE;
                    $factory                                        = new WatchlistFactory( self::DEFAULT_TIMEZONE,
                                                                                            NULL,
                                                                                            $this->dateOfFile,
                                                                                            $this->documentId,
                                                                                            $this->headerKeywords );



                    $watchlist                                      = $factory->parse( $rows,
                                                                                       $cleanHeadersBySheetName,
                                                                                       CMBSRestrictedServicerReport::watchlist,
                                                                                       $watchlist,
                                                                                       $pathToRestrictedServicerReportXlsx );


                    //dump($rows);
                    //dump($watchlist);
                    //dd('dead');
                    unset( $factory );
                elseif ( $this->_foundSheetName( self::DLSR, $sheetName ) ):
                    //dump( self::DLSR . " " . $sheetName );
                    $this->tabsThatHaveBeenFound[ self::DLSR ] = TRUE;
                    $factory                                   = new DLSRFactory( self::DEFAULT_TIMEZONE,
                                                                                  NULL,
                                                                                  $this->dateOfFile,
                                                                                  $this->documentId,
                                                                                  $this->headerKeywords );
                    $dlsr                                      = $factory->parse( $rows,
                                                                                  $cleanHeadersBySheetName,
                                                                                  CMBSRestrictedServicerReport::dlsr,
                                                                                  $dlsr,
                                                                                  $pathToRestrictedServicerReportXlsx );
                    unset( $factory );
                elseif ( $this->_foundSheetName( self::REOSR, $sheetName ) ):
                    //dump( self::REOSR . " " . $sheetName );
                    $this->tabsThatHaveBeenFound[ self::REOSR ] = TRUE;
                    $factory                                    = new REOSRFactory( self::DEFAULT_TIMEZONE,
                                                                                    NULL,
                                                                                    $this->dateOfFile,
                                                                                    $this->documentId,
                                                                                    $this->headerKeywords );
                    $reosr                                      = $factory->parse( $rows,
                                                                                   $cleanHeadersBySheetName,
                                                                                   CMBSRestrictedServicerReport::reosr,
                                                                                   $reosr,
                                                                                   $pathToRestrictedServicerReportXlsx );
                    unset( $factory );
                elseif ( $this->_foundSheetName( self::HLMFCLR, $sheetName ) ):
                    //dump( self::HLMFCLR . " " . $sheetName );
                    $this->tabsThatHaveBeenFound[ self::HLMFCLR ] = TRUE;
                    $factory                                      = new HLMFCLRFactory( self::DEFAULT_TIMEZONE,
                                                                                        NULL,
                                                                                        $this->dateOfFile,
                                                                                        $this->documentId,
                                                                                        $this->headerKeywords );
                    $hlmfclr                                      = $factory->parse( $rows,
                                                                                     $cleanHeadersBySheetName,
                                                                                     CMBSRestrictedServicerReport::hlmfclr,
                                                                                     $hlmfclr,
                                                                                     $pathToRestrictedServicerReportXlsx );


                    unset( $factory );
                elseif ( $this->_foundSheetName( self::CFSR, $sheetName ) ):
                    //dump( self::CFSR . " " . $sheetName );
                    $this->tabsThatHaveBeenFound[ self::CFSR ] = TRUE;
                    $factory                                   = new CFSRFactory( self::DEFAULT_TIMEZONE,
                                                                                  NULL,
                                                                                  $this->dateOfFile,
                                                                                  $this->documentId,
                                                                                  $this->headerKeywords );

                    $csfr = $factory->parse( $rows,
                                             $cleanHeadersBySheetName,
                                             CMBSRestrictedServicerReport::csfr,
                                             $csfr,
                                             $pathToRestrictedServicerReportXlsx );
                    unset( $factory );
                elseif ( $this->_foundSheetName( self::LLRES, $sheetName ) ):
                    //dump( self::LLRES . " " . $sheetName );
                    $this->tabsThatHaveBeenFound[ self::LLRES ] = TRUE;
                    $factory                                    = new LLResLOCFactory( self::DEFAULT_TIMEZONE,
                                                                                       NULL,
                                                                                       $this->dateOfFile,
                                                                                       $this->documentId,
                                                                                       $this->headerKeywords );
                    $llResLOC                                   = $factory->parse( $rows,
                                                                                   $cleanHeadersBySheetName,
                                                                                   CMBSRestrictedServicerReport::llResLOC,
                                                                                   $llResLOC,
                                                                                   $pathToRestrictedServicerReportXlsx );
                    unset( $factory );
                elseif ( $this->_foundSheetName( self::TOTALLOAN, $sheetName ) ):
                    //dump( self::TOTALLOAN . " " . $sheetName );
                    $this->tabsThatHaveBeenFound[ self::TOTALLOAN ] = TRUE;
                    $factory                                        = new TotalLoanFactory( self::DEFAULT_TIMEZONE,
                                                                                            NULL,
                                                                                            $this->dateOfFile,
                                                                                            $this->documentId,
                                                                                            $this->headerKeywords );
                    $totalLoan                                      = $factory->parse( $rows,
                                                                                       $cleanHeadersBySheetName,
                                                                                       CMBSRestrictedServicerReport::totalLoan,
                                                                                       $totalLoan,
                                                                                       $pathToRestrictedServicerReportXlsx );
                    unset( $factory );
                elseif ( $this->_foundSheetName( self::RECOVERY, $sheetName ) ):
                    //dump( self::RECOVERY . " " . $sheetName );
                    $this->tabsThatHaveBeenFound[ self::RECOVERY ] = TRUE;
                    $factory                                       = new AdvanceRecoveryFactory( self::DEFAULT_TIMEZONE,
                                                                                                 NULL,
                                                                                                 $this->dateOfFile,
                                                                                                 $this->documentId,
                                                                                                 $this->headerKeywords );
                    $advanceRecovery                               = $factory->parse( $rows,
                                                                                      $cleanHeadersBySheetName,
                                                                                      CMBSRestrictedServicerReport::advanceRecovery,
                                                                                      $advanceRecovery,
                                                                                      $pathToRestrictedServicerReportXlsx );
                    unset( $factory );

                elseif ( $this->_foundSheetName( self::PROPERTIES, $sheetName ) ):

                    $this->tabsThatHaveBeenFound[ self::PROPERTIES ] = TRUE;
                    $factory                                         = new PropertyFactory( self::DEFAULT_TIMEZONE,
                                                                                            NULL,
                                                                                            $this->dateOfFile,
                                                                                            $this->documentId,
                                                                                            $this->headerKeywords );

                    $properties                                      = $factory->parse( $rows,
                                                                                        $cleanHeadersBySheetName,
                                                                                        CMBSRestrictedServicerReport::properties,
                                                                                        $properties,
                                                                                        $pathToRestrictedServicerReportXlsx );
                    unset( $factory );
                else:
                    //dump( "doing nothing with " . $sheetName );
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
// I wrote this to be a show-stopper, but it should be an ALERT. See below.
// Some HLM sheets will just not have certain categories.
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
            } catch ( TabWithSimilarNameAndDifferentHeaders $exception ) {
                $exceptions[] = $exception;
            } catch ( DuplicatesInHeaderRowException $exception ) {
                $newException = new \Exception( 'In tab "' . $sheetName . '" ' . $exception->generateMessage() );
                $exceptions[] = $newException;
            } catch ( ValidationHeadersNotFoundException $exception ) {
                $newException = new \Exception( 'In tab "' . $sheetName . '" ' . $exception->generateMessage() );
                $exceptions[] = $newException;
            } catch ( \Exception $exception ) {
                $exceptions[] = $exception;
            }
        endforeach;

        if ( $this->_atLeastOneTabNotFound() ):
            $exceptions[] = new AtLeastOneTabNotFoundException( "Need to update the tabs array in CMBSRestrictedServicerReportFactory. CTS has a different spelling for one of their tabs. Update every FALSE tab attached to this exception.",
                                                                0,
                                                                NULL,
                                                                $this->tabsThatHaveBeenFound,
                                                                $pathToRestrictedServicerReportXlsx,
                                                                $sheetNames );
        endif;

//        $theDate = $this->_getTheDate( [ $watchlist,
//                                         $dlsr,
//                                         $reosr,
//                                         $hlmfclr,
//                                         $csfr,
//                                         $llResLOC,
//                                         $totalLoan,
//                                         $advanceRecovery,
//                                       ] );
//
//
//        $watchlist       = $this->_fillDateIfMissing( $watchlist, $theDate );
//        $dlsr            = $this->_fillDateIfMissing( $dlsr, $theDate );
//        $reosr           = $this->_fillDateIfMissing( $reosr, $theDate );
//        $hlmfclr         = $this->_fillDateIfMissing( $hlmfclr, $theDate );
//        $csfr            = $this->_fillDateIfMissing( $csfr, $theDate );
//        $llResLOC        = $this->_fillDateIfMissing( $llResLOC, $theDate );
//        $totalLoan       = $this->_fillDateIfMissing( $totalLoan, $theDate );
//        $advanceRecovery = $this->_fillDateIfMissing( $advanceRecovery, $theDate );


        $watchlist       = $this->_fillDateIfMissing( $watchlist, $this->dateOfFile );
        $dlsr            = $this->_fillDateIfMissing( $dlsr, $this->dateOfFile );
        $reosr           = $this->_fillDateIfMissing( $reosr, $this->dateOfFile );
        $hlmfclr         = $this->_fillDateIfMissing( $hlmfclr, $this->dateOfFile );
        $csfr            = $this->_fillDateIfMissing( $csfr, $this->dateOfFile );
        $llResLOC        = $this->_fillDateIfMissing( $llResLOC, $this->dateOfFile );
        $totalLoan       = $this->_fillDateIfMissing( $totalLoan, $this->dateOfFile );
        $advanceRecovery = $this->_fillDateIfMissing( $advanceRecovery, $this->dateOfFile );


        $watchlist       = $this->_fillDocumentIdIfMissing( $watchlist, $this->documentId );
        $dlsr            = $this->_fillDocumentIdIfMissing( $dlsr, $this->documentId );
        $reosr           = $this->_fillDocumentIdIfMissing( $reosr, $this->documentId );
        $hlmfclr         = $this->_fillDocumentIdIfMissing( $hlmfclr, $this->documentId );
        $csfr            = $this->_fillDocumentIdIfMissing( $csfr, $this->documentId );
        $llResLOC        = $this->_fillDocumentIdIfMissing( $llResLOC, $this->documentId );
        $totalLoan       = $this->_fillDocumentIdIfMissing( $totalLoan, $this->documentId );
        $advanceRecovery = $this->_fillDocumentIdIfMissing( $advanceRecovery, $this->documentId );


        return new CMBSRestrictedServicerReport( $watchlist,
                                                 $dlsr,
                                                 $reosr,
                                                 $hlmfclr,
                                                 $csfr,
                                                 $llResLOC,
                                                 $totalLoan,
                                                 $advanceRecovery,
                                                 $properties,
                                                 $cleanHeadersBySheetName,
                                                 $alerts,
                                                 $exceptions,
                                                 $this->dateOfFile,
                                                 $this->documentId );
    }


    /**
     * A sanity check to make sure there is ONE and ONLY ONE date among the tabs.
     *
     * @param array $tabs
     *
     * @return string
     * @throws NoDatesInTabsException
     */
    protected function _getTheDate( array $tabs ): string {

        if ( !is_null( $this->dateOfFile ) ):
            return $this->dateOfFile->toDateString();
        endif;

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
                if ( !isset( $dateCount[ $date ] ) ):
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
                                              NULL,
                                              $tabs );
        endif;

        $date = reset( $uniqueDates ); // THE date

        return $date;
    }


    /**
     * @param array  $rows
     * @param string $date
     *
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


    protected function _fillDocumentIdIfMissing( array $rows, int $documentId ): array {
        foreach ( $rows as $i => $row ):

            if ( array_key_exists( 'document_id', $row ) && empty( $row[ 'document_id' ] ) ):
                $rows[ $i ][ 'document_id' ] = $documentId;
            endif;
        endforeach;
        return $rows;
    }


    /**
     * A little encapsulated logic to make the above method cleaner.
     *
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
     *
     * @return bool
     */
    protected function _foundSheetName( string $index, string $sheetName ): bool {

        foreach ( $this->tabs[ $index ] as $tabName ):

            // Added strtolower to make comparisons cleaner.
            // I have seen Watchlist vs WatchList... for example.
            if ( strtolower( $sheetName ) == strtolower( $tabName ) ):
                return TRUE;
            endif;

            // I found a tab labeled 'Total Loan Report CXP-2022-CXP1'
            // So if 'Total Loan' exists in the tab, let's call it a win.
            if ( str_contains( $sheetName, $tabName ) ):
                return TRUE;
            endif;

            // The property tabs almost always end in _property
            if ( str_ends_with( $sheetName, $tabName ) ):
                return TRUE;
            endif;

        endforeach;
        return FALSE;
    }

    public function hasExceptions(): bool {
        return !empty( $this->exceptions );
    }

    public function getExceptions(): array {
        return $this->exceptions;
    }
}
