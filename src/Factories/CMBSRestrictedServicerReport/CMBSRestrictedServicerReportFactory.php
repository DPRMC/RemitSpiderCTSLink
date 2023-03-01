<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

use DPRMC\Excel\Excel;
use DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException;
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

    public array $tabs = [
        self::FOOTNOTES => [
            'FootNotes',
        ],
        self::WATCHLIST => [
            'Watchlist',
            'Servicer Watch List',
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
        ], // COMPARATIVE FINANCIAL STATUS REPORT
        self::HLMFCLR   => [
            'HLMFCLR',
            'Hist Mod-Corr Mtg ln',
        ], // HISTORICAL LOAN MODIFICATION/FORBEARANCE and CORRECTED MORTGAGE LOAN REPORT
        self::LLRES     => [
            'LL Res, LOC',
            'LL Reserve Rpt',
        ], // LOAN LEVEL RESERVE/LOC REPORT
        self::TOTALLOAN => [
            'Total Loan',
        ], // TOTAL LOAN REPORT
        self::RECOVERY  => [
            'Advance Recovery',
            'Recovery',
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
     */
    public function make( string $pathToRestrictedServicerReportXlsx ): CMBSRestrictedServicerReport {
        $sheetNames = Excel::getSheetNames( $pathToRestrictedServicerReportXlsx );

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


        $cleanHeadersByProperty = [];

        foreach ( $sheetNames as $sheetName ):
            try {
                $rows = Excel::sheetToArray( $pathToRestrictedServicerReportXlsx, $sheetName );
                if ( $this->_foundSheetName( self::WATCHLIST, $sheetName ) ):
                    $factory                               = new WatchlistFactory( self::DEFAULT_TIMEZONE );
                    $cleanHeadersByProperty[ 'watchlist' ] = $factory->getCleanHeaders();
                    $watchlist                             = $factory->parse( $rows );
                endif;

                if ( $this->_foundSheetName( self::DLSR, $sheetName ) ):
                    $factory                          = new DLSRFactory( self::DEFAULT_TIMEZONE );
                    $cleanHeadersByProperty[ 'dlsr' ] = $factory->getCleanHeaders();
                    $dlsr                             = $factory->parse( $rows );
                endif;

                if ( $this->_foundSheetName( self::REOSR, $sheetName ) ):
                    $factory                           = new REOSRFactory( self::DEFAULT_TIMEZONE );
                    $cleanHeadersByProperty[ 'reosr' ] = $factory->getCleanHeaders();
                    $reosr                             = $factory->parse( $rows );
                endif;

                if ( $this->_foundSheetName( self::HLMFCLR, $sheetName ) ):
                    $factory                             = new HLMFCLRFactory( self::DEFAULT_TIMEZONE );
                    $cleanHeadersByProperty[ 'hlmfclr' ] = $factory->getCleanHeaders();
                    $hlmfclr                             = $factory->parse( $rows );
                endif;

                if ( $this->_foundSheetName( self::CFSR, $sheetName ) ):
                    $factory                          = new CFSRFactory( self::DEFAULT_TIMEZONE );
                    $cleanHeadersByProperty[ 'csfr' ] = $factory->getCleanHeaders();
                    $csfr                             = $factory->parse( $rows );
                endif;

                if ( $this->_foundSheetName( self::LLRES, $sheetName ) ):
                    $factory                              = new LLResLOCFactory( self::DEFAULT_TIMEZONE );
                    $cleanHeadersByProperty[ 'llResLOC' ] = $factory->getCleanHeaders();
                    $llResLOC                             = $factory->parse( $rows );

                endif;

                if ( $this->_foundSheetName( self::TOTALLOAN, $sheetName ) ):
                    $factory                               = new TotalLoanFactory( self::DEFAULT_TIMEZONE );
                    $cleanHeadersByProperty[ 'totalLoan' ] = $factory->getCleanHeaders();
                    $totalLoan                             = $factory->parse( $rows );

                endif;

                if ( $this->_foundSheetName( self::RECOVERY, $sheetName ) ):
                    $factory                                     = new AdvanceRecoveryFactory( self::DEFAULT_TIMEZONE );
                    $cleanHeadersByProperty[ 'advanceRecovery' ] = $factory->getCleanHeaders();
                    $advanceRecovery                             = $factory->parse( $rows );
                endif;
            } catch ( NoDataInTabException $exception ) {
                $alerts[] = $exception;
            } catch ( \Exception $exception ) {
                $exceptions[] = $exception;
            }
        endforeach;

        return new CMBSRestrictedServicerReport( $watchlist,
                                                 $dlsr,
                                                 $reosr,
                                                 $hlmfclr,
                                                 $csfr,
                                                 $llResLOC,
                                                 $totalLoan,
                                                 $advanceRecovery,
                                                 $cleanHeadersByProperty,
                                                 $alerts,
                                                 $exceptions );
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