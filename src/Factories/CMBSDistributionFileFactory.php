<?php

namespace DPRMC\RemitSpiderCTSLink\Factories;

use Carbon\Carbon;
use DPRMC\CUSIP;
use DPRMC\RemitSpiderCTSLink\Exceptions\DistributionFileExceptions\UnableToFindIndexOfLabelException;
use DPRMC\RemitSpiderCTSLink\Models\CMBSDistributionFile;
use Smalot\PdfParser\Page;
use function PHPUnit\Framework\arrayHasKey;

class CMBSDistributionFileFactory {

    const DEFAULT_TIMEZONE = 'America/New_York';
    protected string $timezone;

    protected array                  $pages;
    protected array                  $pagesAsArrays;
    protected \Smalot\PdfParser\Page $pageWithTableOfContents;

    /**
     * @var array These get parsed out from the Table of Contents page.
     */
    protected array $dates = [];

    const distribution_date      = 'Distribution Date';
    const determination_date     = 'Determination Date';
    const next_distribution_date = 'Next Distribution Date';
    const record_date            = 'Record Date';


    // Certificate Distribution Detail fields
    const security_class          = 'Class';
    const cusip                   = 'CUSIP';
    const pass_through_rate       = 'Pass-Through Rate';
    const original_balance        = 'Original Balance';
    const beginning_balance       = 'Beginning Balance';
    const principal_distribution  = 'Principal Distribution';
    const interest_distribution   = 'Interest Distribution';
    const prepayment_penalties    = 'Prepayment Penalties';
    const realized_losses         = 'Realized Losses';
    const total_distribution      = 'Total Distribution';
    const ending_balance          = 'Ending Balance';
    const current_credit_support  = 'Current Credit Support';
    const original_credit_support = 'Original Credit Support';

    // Certificate Factor Detail fields (constants defined above are obv omitted here.)
    const interest_shortfalls            = 'Interest Shortfalls / (Paybacks)';
    const cumulative_interest_shortfalls = 'Cumulative Interest Shortfalls';
    const losses                         = 'Losses';

    // Certificate Interest Reconciliation Detail fields ((constants defined above are obv omitted here.)
    const start_accrual_date                          = 'Start Accrual Date';
    const end_accrual_date                            = 'End Accrual Date';
    const accrual_days                                = 'Accrual Days';
    const prior_interest_shortfalls                   = 'Prior Interest Shortfalls';
    const accrued_certificate_interest                = 'Accrued Certificate Interest';
    const net_aggregate_prepayment_interest_shortfall = 'Net Aggregate Prepayment Interest Shortfall';
    const distributable_certificate_interest          = 'Distributable Certificate Interest';
    const payback_of_prior_realized_losses            = 'Payback of Prior Realized Losses';
    const additional_interest_distribution_amount     = 'Additional Interest Distribution Amount';


    // Modified Loan Detail fields
    const pros_id                     = 'Pros ID';
    const loan_number                 = 'Loan Number';
    const pre_modification_balance    = 'Pre-Modification Balance';
    const pre_modification_rate       = 'Pre-Modification Rate';
    const post_modification_balance   = 'Post-Modification Balance';
    const post_modification_rate      = 'Post-Modification Rate';
    const modification_code           = 'Modification Code';
    const modification_booking_date   = 'Modification Booking Date';
    const modification_closing_date   = 'Modification Closing Date';
    const modification_effective_date = 'Modification Effective Date';

    // Delinquency Loan Detail fields
    const paid_through_date             = 'Paid Through Date';
    const months_delinquent             = 'Months Delinquent';
    const mortgage_loan_status          = 'Mortgage Loan Status';
    const current_p_and_i_advances      = 'Current P&I Advances';
    const outstanding_p_and_i_advances  = 'Outstanding P&I Advances';
    const outstanding_servicer_advances = 'Outstanding Servicer Advances';
    const actual_principal_balance      = 'Actual Principal Balance';
    const servicing_transfer_date       = 'Servicing Transfer Date';
    const resolution_strategy_code      = 'Resolution Strategy Code';
    const bankruptcy_date               = 'Bankruptcy Date';
    const foreclosure_date              = 'Foreclosure Date';
    const reo_date                      = 'REO Date';


    // Historical Detail fields
    const delinquencies_30_59_days_number                 = 'Delinquencies 30-59 Days - #';
    const delinquencies_30_59_days_balance                = 'Delinquencies 30-59 Days - Balance';
    const delinquencies_60_89_days_number                 = 'Delinquencies 60-89 Days - #';
    const delinquencies_60_89_days_balance                = 'Delinquencies 60-89 Days - Balance';
    const delinquencies_90_plus_days_number               = 'Delinquencies 90 Days or More - #';
    const delinquencies_90_plus_days_balance              = 'Delinquencies 90 Days or More - Balance';
    const foreclosure_number                              = 'Foreclosure #';
    const foreclosure_balance                             = 'Foreclosure Balance';
    const reo_number                                      = 'REO #';
    const reo_balance                                     = 'REO Balance';
    const modifications_number                            = 'Modifications #';
    const modifications_balance                           = 'Modifications Balance';
    const prepayments_curtailments_number                 = 'Prepayments - Curtailments #';
    const prepayments_curtailments_amount                 = 'Prepayments - Curtailments Amount';
    const prepayments_payoff_number                       = 'Prepayments - Payoff #';
    const prepayments_payoff_amount                       = 'Prepayments - Payoff Amount';
    const rate_and_maturities_net_weighted_average_coupon = 'Rate and Maturities - Net Weighted Avg. - Coupon';
    const rate_and_maturities_net_weighted_average_remit  = 'Rate and Maturities - Net Weighted Avg. - Remit';
    const rate_and_maturities_wam                         = 'Rate and Maturities - WAM';

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


    public function make( string $pathToDistributionFilePDF ): CMBSDistributionFile {
        $distributionFile                = new CMBSDistributionFile();
        $fileContents                    = file_get_contents( $pathToDistributionFilePDF );
        $parser                          = new \Smalot\PdfParser\Parser();
        $pdf                             = $parser->parseContent( $fileContents );
        $this->pages                     = $pdf->getPages();
        $this->pagesAsArrays             = $this->_getPagesAsArrays( $this->pages );
        $distributionFile->numberOfPages = count( $this->pages );

        $this->pageWithTableOfContents                             = $this->pages[ 0 ];
        $this->dates                                               = $this->_getDates( $this->pagesAsArrays[ 0 ] );
        $distributionFile->dates                                   = $this->dates;
        $distributionFile->certificateDistributionDetail           = $this->getCertificateDistributionDetail();
        $distributionFile->certificateFactorDetail                 = $this->getCertificateFactorDetail();
        $distributionFile->certificateInterestReconciliationDetail = $this->getCertificateInterestReconciliationDetail();
        $distributionFile->modifiedLoanDetail                      = $this->getModifiedLoanDetail();
        $distributionFile->delinquencyLoanDetail                   = $this->getDelinquencyLoanDetail();
        $distributionFile->historicalDetail                        = $this->getHistoricalDetail();
        $distributionFile->mortgageLoanDetailPart1                 = $this->getMortgageLoanDetailPartOne();


        return $distributionFile;
    }


    /**
     * There are some dates on the front page.
     * Parse those out here.
     * @param array $pageWithTableOfContents
     * @return array
     */
    protected function _getDates( array $pageWithTableOfContents ): array {
        $distributionDateIndex     = array_search( 'Distribution Date:', $pageWithTableOfContents );
        $determinationDateIndex    = array_search( 'Determination Date:', $pageWithTableOfContents );
        $nextDistributionDateIndex = array_search( 'Next Distribution Date:', $pageWithTableOfContents );
        $recordDateIndex           = array_search( 'Record Date:', $pageWithTableOfContents );

        $distributionDate     = Carbon::parse( $pageWithTableOfContents[ $distributionDateIndex + 1 ], $this->timezone );
        $determinationDate    = Carbon::parse( $pageWithTableOfContents[ $determinationDateIndex + 1 ], $this->timezone );
        $nextDistributionDate = Carbon::parse( $pageWithTableOfContents[ $nextDistributionDateIndex + 1 ], $this->timezone );
        $recordDate           = Carbon::parse( $pageWithTableOfContents[ $recordDateIndex + 1 ], $this->timezone );

        return [
            self::distribution_date      => $distributionDate,
            self::determination_date     => $determinationDate,
            self::next_distribution_date => $nextDistributionDate,
            self::record_date            => $recordDate,
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getCertificateDistributionDetail(): array {
        $sectionName = 'Certificate Distribution Detail';
        $pageIndexes = $this->getPageRangeBySection( $sectionName );

        $pagesAsArrays = [];
        foreach ( $pageIndexes as $index ):
            $currentPage     = $this->pages[ $index ];
            $pagesAsArrays[] = $currentPage->getTextArray( $currentPage );
        endforeach;

        $rows = $this->_parseCertificateDistributionDetailRowsFromPageArray( $pagesAsArrays );

        // @TODO maybe split the rows into the following 2 sub arrays.
//        $certificateDistributionDetail = [];
//        $regularSubtotal  = array_slice($rows,0,x);
//        $notionalSubtotal = array_slice($rows,x,y);
//        return [
//            'regular_subtotal' => $regularSubtotal,
//            'notional_subtotal' => $notionalSubtotal,
//        ];

        return $rows;
    }


    /**
     * @return array
     * @throws \Exception
     */
    protected function getCertificateFactorDetail(): array {
        $sectionName = 'Certificate Factor Detail';
        $pageIndexes = $this->getPageRangeBySection( $sectionName );

        $pagesAsArrays = [];
        foreach ( $pageIndexes as $index ):
            $currentPage     = $this->pages[ $index ];
            $pagesAsArrays[] = $currentPage->getTextArray( $currentPage );
        endforeach;

        $rows = $this->_parseCertificateFactorDetailRowsFromPageArray( $pagesAsArrays );

        return $rows;
    }


    /**
     * @return array
     * @throws \Exception
     */
    protected function getCertificateInterestReconciliationDetail(): array {
        $sectionName = 'Certificate Interest Reconciliation Detail';
        $pageIndexes = $this->getPageRangeBySection( $sectionName );

        dump( '$pageIndexes' );
        dd( $pageIndexes );

        $pagesAsArrays = [];
        foreach ( $pageIndexes as $index ):
            $currentPage     = $this->pages[ $index ];
            $pagesAsArrays[] = $currentPage->getTextArray( $currentPage );
        endforeach;

        $rows = $this->_parseCertificateInterestReconciliationDetailRowsFromPageArray( $pagesAsArrays );

        return $rows;
    }


    protected function getModifiedLoanDetail(): array {
        $sectionName = 'Modified Loan Detail';
        $pageIndexes = $this->getPageRangeBySection( $sectionName );

        $pagesAsArrays = [];
        foreach ( $pageIndexes as $index ):
            $currentPage     = $this->pages[ $index ];
            $pagesAsArrays[] = $currentPage->getTextArray( $currentPage );
        endforeach;

        $parsedRows = [];

        // Remove Headers
        $numHeaders = 20;
        foreach ( $pagesAsArrays as $page ):
            for ( $i = 0; $i <= $numHeaders; $i++ ):
                array_shift( $page );
            endfor;

            $rawRows = array_chunk( $page, 10 );

            // Remove footer rows
            $rowsOfInterest = [];
            foreach ( $rawRows as $i => $rawRow ):
                if ( 'Totals' == $rawRow[ 0 ] ):
                    break;
                endif;
                $rowsOfInterest[] = $rawRow;
            endforeach;
            // Everything after the Totals row is garbage.

            foreach ( $rowsOfInterest as $rawRow ):
                try {
                    $bookingDate = Carbon::parse( $rawRow[ 7 ], $this->timezone );
                } catch ( \Exception $exception ) {
                    $bookingDate = NULL;
                }

                try {
                    $closingDate = Carbon::parse( $rawRow[ 8 ], $this->timezone );
                } catch ( \Exception $exception ) {
                    $closingDate = NULL;
                }

                try {
                    $effectiveDate = Carbon::parse( $rawRow[ 9 ], $this->timezone );
                } catch ( \Exception $exception ) {
                    $effectiveDate = NULL;
                }

                $parsedRows[] = [
                    self::pros_id                     => $rawRow[ 0 ],
                    self::loan_number                 => $rawRow[ 1 ],
                    self::pre_modification_balance    => $this->_formatNumber( $rawRow[ 2 ] ),
                    self::pre_modification_rate       => $this->_formatPercent( $rawRow[ 3 ] ),
                    self::post_modification_balance   => $this->_formatNumber( $rawRow[ 4 ] ),
                    self::post_modification_rate      => $this->_formatPercent( $rawRow[ 5 ] ),
                    self::modification_code           => $rawRow[ 6 ],
                    self::modification_booking_date   => $bookingDate,
                    self::modification_closing_date   => $closingDate,
                    self::modification_effective_date => $effectiveDate,
                ];
            endforeach;
        endforeach;

        return $parsedRows;
    }


    /**
     * @return array
     * @throws \Exception
     */
    protected function getDelinquencyLoanDetail(): array {
        $sectionName = 'Delinquency Loan Detail';
        $pageIndexes = $this->getPageRangeBySection( $sectionName );

        $pagesAsArrays = [];
        foreach ( $pageIndexes as $index ):
            $currentPage     = $this->pages[ $index ];
            $pagesAsArrays[] = $currentPage->getTextArray( $currentPage );
        endforeach;

        $parsedRows = [];


        // Remove Headers
        $numHeaders = 32;
        foreach ( $pagesAsArrays as $page ):
            for ( $i = 0; $i <= $numHeaders; $i++ ):
                array_shift( $page );
            endfor;

            // Set CHUNK Size
            // If there are no bankruptcies etc, then there won't be dates in the last few columns.
            // If that's the case, then the parser will just skip those cells.
            try {
                Carbon::parse( $page[ 9 ], $this->timezone );
                $chunkSize = 13;
            } catch ( \Exception $exception ) {
                $chunkSize = 9;
            }

            $rawRows = array_chunk( $page, $chunkSize );

            // Remove footer rows
            $rowsOfInterest = [];
            foreach ( $rawRows as $i => $rawRow ):
                if ( 'Totals' == $rawRow[ 0 ] ):
                    break;
                endif;
                $rowsOfInterest[] = $rawRow;
            endforeach;
            // Everything after the Totals row is garbage.

            foreach ( $rowsOfInterest as $rawRow ):
                try {
                    $paidThroughDate = Carbon::parse( $rawRow[ 2 ], $this->timezone );
                } catch ( \Exception $exception ) {
                    $paidThroughDate = NULL;
                }

                // If there has been some trouble with this security, you will have some extra fields.
                if ( 13 == $chunkSize ):
                    try {
                        $servicingTransferDate = Carbon::parse( $rawRow[ 9 ], $this->timezone );
                    } catch ( \Exception $exception ) {
                        $servicingTransferDate = NULL;
                    }

                    try {
                        $bankruptcyDate = Carbon::parse( $rawRow[ 11 ], $this->timezone );
                    } catch ( \Exception $exception ) {
                        $bankruptcyDate = NULL;
                    }

                    try {
                        $foreclosureDate = Carbon::parse( $rawRow[ 12 ], $this->timezone );
                    } catch ( \Exception $exception ) {
                        $foreclosureDate = NULL;
                    }

                    try {
                        $reoDate = Carbon::parse( $rawRow[ 13 ], $this->timezone );
                    } catch ( \Exception $exception ) {
                        $reoDate = NULL;
                    }
                else:
                    $servicingTransferDate = NULL;
                    $bankruptcyDate        = NULL;
                    $foreclosureDate       = NULL;
                    $reoDate               = NULL;
                endif;

                $parsedRows[] = [
                    self::pros_id                       => $rawRow[ 0 ],
                    self::loan_number                   => $rawRow[ 1 ],
                    self::paid_through_date             => $paidThroughDate,
                    self::months_delinquent             => $rawRow[ 3 ],
                    self::mortgage_loan_status          => $rawRow[ 4 ],
                    self::current_p_and_i_advances      => $this->_formatNumber( $rawRow[ 5 ] ),
                    self::outstanding_p_and_i_advances  => $this->_formatNumber( $rawRow[ 6 ] ),
                    self::outstanding_servicer_advances => $this->_formatNumber( $rawRow[ 7 ] ),
                    self::actual_principal_balance      => $this->_formatNumber( $rawRow[ 8 ] ),
                    self::servicing_transfer_date       => $servicingTransferDate,
                    self::resolution_strategy_code      => isset( $rawRow[ 10 ] ) ? $this->_formatNumber( $rawRow[ 10 ] ) : NULL,
                    self::bankruptcy_date               => $bankruptcyDate,
                    self::foreclosure_date              => $foreclosureDate,
                    self::reo_date                      => $reoDate,
                ];
            endforeach;
        endforeach;

        return $parsedRows;
    }


    /**
     * @return array
     * @throws \Exception
     */
    protected function getHistoricalDetail(): array {
        $sectionName = 'Historical Detail';
        $pageIndexes = $this->getPageRangeBySection( $sectionName );

        $pagesAsArrays = [];
        foreach ( $pageIndexes as $index ):
            $currentPage     = $this->pages[ $index ];
            $pagesAsArrays[] = $currentPage->getTextArray( $currentPage );
        endforeach;

        $parsedRows = [];

        // Remove Headers
        $numHeaders = 35;
        foreach ( $pagesAsArrays as $page ):
            for ( $i = 0; $i <= $numHeaders; $i++ ):
                array_shift( $page );
            endfor;

            $rawRows = array_chunk( $page, 20 );

            // Remove footer rows
            $rowsOfInterest = [];
            foreach ( $rawRows as $i => $rawRow ):
                try {
                    Carbon::parse( $rawRow[ 0 ], $this->timezone );
                    $rowsOfInterest[] = $rawRow;
                } catch ( \Exception $exception ) {
                    break;
                }
            endforeach;
            // Everything without a date in the first column is garbage.

            foreach ( $rowsOfInterest as $rawRow ):
                $parsedRows[] = [
                    self::distribution_date                               => Carbon::parse( $rawRow[ 0 ], $this->timezone ),
                    self::delinquencies_30_59_days_number                 => $this->_formatNumber( $rawRow[ 1 ] ),
                    self::delinquencies_30_59_days_balance                => $this->_formatNumber( $rawRow[ 2 ] ),
                    self::delinquencies_60_89_days_number                 => $this->_formatNumber( $rawRow[ 3 ] ),
                    self::delinquencies_60_89_days_balance                => $this->_formatNumber( $rawRow[ 4 ] ),
                    self::delinquencies_90_plus_days_number               => $this->_formatNumber( $rawRow[ 5 ] ),
                    self::delinquencies_90_plus_days_balance              => $this->_formatNumber( $rawRow[ 6 ] ),
                    self::foreclosure_number                              => $this->_formatNumber( $rawRow[ 7 ] ),
                    self::foreclosure_balance                             => $this->_formatNumber( $rawRow[ 8 ] ),
                    self::reo_number                                      => $this->_formatNumber( $rawRow[ 9 ] ),
                    self::reo_balance                                     => $this->_formatNumber( $rawRow[ 10 ] ),
                    self::modifications_number                            => $this->_formatNumber( $rawRow[ 11 ] ),
                    self::modifications_balance                           => $this->_formatNumber( $rawRow[ 12 ] ),
                    self::prepayments_curtailments_number                 => $this->_formatNumber( $rawRow[ 13 ] ),
                    self::prepayments_curtailments_amount                 => $this->_formatNumber( $rawRow[ 14 ] ),
                    self::prepayments_payoff_number                       => $this->_formatNumber( $rawRow[ 15 ] ),
                    self::prepayments_payoff_amount                       => $this->_formatNumber( $rawRow[ 16 ] ),
                    self::rate_and_maturities_net_weighted_average_coupon => $this->_formatPercent( $rawRow[ 17 ] ),
                    self::rate_and_maturities_net_weighted_average_remit  => $this->_formatPercent( $rawRow[ 18 ] ),
                    self::rate_and_maturities_wam                         => $this->_formatNumber( $rawRow[ 19 ] ),
                ];
            endforeach;
        endforeach;

        return $parsedRows;
    }


    protected function getMortgageLoanDetailPartOne(): array {
        $sectionName = 'Mortgage Loan Detail (Part 1)';
        $pageIndexes = $this->getPageRangeBySection( $sectionName );

        $pagesAsArrays = [];
        foreach ( $pageIndexes as $index ):
            /**
             * @var Page $currentPage
             */
            $currentPage = $this->pages[ $index ];
            //$pagesAsArrays[] = $currentPage->getTextArray( $currentPage );
            $text = $currentPage->getText( $currentPage );
            $text = $currentPage->getContent( $currentPage );
            $text = $currentPage->getXObjects();
            $text = $currentPage->getSectionsText( $currentPage );
        endforeach;

        $parsedRows = [];

        print_r( $pagesAsArrays );
        die( 'death' );

        // Remove Headers
        $numHeaders = 33;
        foreach ( $pagesAsArrays as $page ):
            for ( $i = 0; $i <= $numHeaders; $i++ ):
                array_shift( $page );
            endfor;

            $rawRows = array_chunk( $page, 16 );

            print_r( $rawRows );
            die( 'death' );

            // Remove footer rows
            $rowsOfInterest = [];
            foreach ( $rawRows as $i => $rawRow ):
                try {
                    Carbon::parse( $rawRow[ 0 ], $this->timezone );
                    $rowsOfInterest[] = $rawRow;
                } catch ( \Exception $exception ) {
                    break;
                }
            endforeach;
            // Everything without a date in the first column is garbage.

            foreach ( $rowsOfInterest as $rawRow ):
                $parsedRows[] = [
                    self::distribution_date                               => Carbon::parse( $rawRow[ 0 ], $this->timezone ),
                    self::delinquencies_30_59_days_number                 => $this->_formatNumber( $rawRow[ 1 ] ),
                    self::delinquencies_30_59_days_balance                => $this->_formatNumber( $rawRow[ 2 ] ),
                    self::delinquencies_60_89_days_number                 => $this->_formatNumber( $rawRow[ 3 ] ),
                    self::delinquencies_60_89_days_balance                => $this->_formatNumber( $rawRow[ 4 ] ),
                    self::delinquencies_90_plus_days_number               => $this->_formatNumber( $rawRow[ 5 ] ),
                    self::delinquencies_90_plus_days_balance              => $this->_formatNumber( $rawRow[ 6 ] ),
                    self::foreclosure_number                              => $this->_formatNumber( $rawRow[ 7 ] ),
                    self::foreclosure_balance                             => $this->_formatNumber( $rawRow[ 8 ] ),
                    self::reo_number                                      => $this->_formatNumber( $rawRow[ 9 ] ),
                    self::reo_balance                                     => $this->_formatNumber( $rawRow[ 10 ] ),
                    self::modifications_number                            => $this->_formatNumber( $rawRow[ 11 ] ),
                    self::modifications_balance                           => $this->_formatNumber( $rawRow[ 12 ] ),
                    self::prepayments_curtailments_number                 => $this->_formatNumber( $rawRow[ 13 ] ),
                    self::prepayments_curtailments_amount                 => $this->_formatNumber( $rawRow[ 14 ] ),
                    self::prepayments_payoff_number                       => $this->_formatNumber( $rawRow[ 15 ] ),
                    self::prepayments_payoff_amount                       => $this->_formatNumber( $rawRow[ 16 ] ),
                    self::rate_and_maturities_net_weighted_average_coupon => $this->_formatPercent( $rawRow[ 17 ] ),
                    self::rate_and_maturities_net_weighted_average_remit  => $this->_formatPercent( $rawRow[ 18 ] ),
                    self::rate_and_maturities_wam                         => $this->_formatNumber( $rawRow[ 19 ] ),
                ];
            endforeach;
        endforeach;

        return $parsedRows;
    }


    /**
     * @param array $pages
     * @return array
     * @throws \Exception
     */
    private function _getPagesAsArrays( array $pages ): array {
        $pagesAsArrays = [];

        /**
         * @var Page $page
         */
        foreach ( $pages as $page ):
            $pagesAsArrays[] = $page->getTextArray( $page );
        endforeach;
        return $pagesAsArrays;
    }


    /**
     * A little helper method to get page indexes for sections from the Table of Contents.
     * @param string $sectionName
     * @return array
     * @throws \Exception
     */
    protected function getPageRangeBySection( string $sectionName ): array {
        $aFirstPage = $this->pageWithTableOfContents->getTextArray( $this->pageWithTableOfContents );

        $indexOfLabel = array_search( $sectionName, $aFirstPage );

        if ( FALSE === $indexOfLabel ):
            throw new UnableToFindIndexOfLabelException( "Unable to find the index of label for " . $sectionName,
                                                         0,
                                                         NULL,
                                                         $sectionName,
                                                         $aFirstPage );
        endif;

        // Sometimes it's a range of pages.
        $pagesString     = $aFirstPage[ $indexOfLabel + 1 ];
        $pageNumberParts = explode( '-', $pagesString );

        $startPage = $pageNumberParts[ 0 ] ?? FALSE;
        $endPage   = $pageNumberParts[ 1 ] ?? FALSE;

        if ( FALSE === $startPage ):
            throw new \Exception( "Unable to find the section [" . $sectionName . "] in the table of contents." );
        endif;

        $startPage--; // PHP arrays start index at zero.

        if ( $endPage ):
            $endPage--; // PHP arrays start index at zero.
            return range( $startPage, $endPage );
        endif;

        if ( $sectionName == 'Certificate Interest Reconciliation Detail' ):
            dump( $aFirstPage );
            dump( $indexOfLabel );
        endif;

        return [ $startPage ];
    }


    /**
     * @param array $pages
     * @return array
     */
    private function _parseCertificateDistributionDetailRowsFromPageArray( array $pages ): array {
        $parsedRows = [];

        // Remove Headers
        $numHeaders = 24;
        foreach ( $pages as $page ):
            for ( $i = 0; $i <= $numHeaders; $i++ ):
                array_shift( $page );
            endfor;

            $rawRows = array_chunk( $page, 14 );

            foreach ( $rawRows as $rawRow ):
                if ( $this->_isValidCertificateDistributionDetailRow( $rawRow ) ):
                    $cusip                = $this->_getCUSIPFromRawCertificateDistributionDetailRow( $rawRow );
                    $parsedRows[ $cusip ] = $this->_parseCertificateDistributionDetailRow( $rawRow );
                endif;
            endforeach;
        endforeach;

        return $parsedRows;
    }


    /**
     * @param array $pages
     * @return array
     */
    protected function _parseCertificateFactorDetailRowsFromPageArray( array $pages ): array {
        $parsedRows = [];

        // Remove Headers
        $numHeaders = 15;
        foreach ( $pages as $page ):
            for ( $i = 0; $i <= $numHeaders; $i++ ):
                array_shift( $page );
            endfor;

            $rawRows = array_chunk( $page, 11 );

            foreach ( $rawRows as $rawRow ):
                if ( $this->_isValidCertificateDistributionDetailRow( $rawRow ) ):
                    $cusip                = $this->_getCUSIPFromRawCertificateDistributionDetailRow( $rawRow );
                    $parsedRows[ $cusip ] = $this->_parseCertificateFactorDetailRow( $rawRow );
                endif;
            endforeach;
        endforeach;

        return $parsedRows;
    }


    /**
     * @param array $pages
     * @return array
     * @throws \Exception
     */
    protected function _parseCertificateInterestReconciliationDetailRowsFromPageArray( array $pages ): array {
        $parsedRows = [];


        // Remove Headers
        $numHeaders = 28;
        foreach ( $pages as $page ):
            for ( $i = 0; $i <= $numHeaders; $i++ ):
                array_shift( $page );
            endfor;

            $rawRows = array_chunk( $page, 12 );

            foreach ( $rawRows as $rawRow ):
                if ( $this->_isValidCertificateInterestReconciliationDetailRow( $rawRow ) ):
                    $class                = $rawRow[ 0 ];
                    $parsedRows[ $class ] = $this->_parseCertificateInterestReconciliationDetailRow( $rawRow );
                endif;
            endforeach;
        endforeach;

        return $parsedRows;
    }


    /**
     * Works for Certificate Factor Detail rows as well!
     * @param array $rawRow
     * @return string|null
     */
    private function _getCUSIPFromRawCertificateDistributionDetailRow( array $rawRow ): ?string {

        if ( ! isset( $rawRow[ 1 ] ) ):
            return NULL;
        endif;

        if ( CUSIP::isCUSIP( $rawRow[ 1 ] ) ):
            return trim( $rawRow[ 1 ] );
        endif;

        return NULL;
    }


    /**
     * Works for Certificate Factor Detail rows as well!
     * @param array $row
     * @return bool
     */
    private function _isValidCertificateDistributionDetailRow( array $row ): bool {
        if ( CUSIP::isCUSIP( $this->_getCUSIPFromRawCertificateDistributionDetailRow( $row ) ) ):
            return TRUE;
        endif;

        return FALSE;
    }


    /**
     * @param array $row
     * @return bool
     */
    private function _isValidCertificateInterestReconciliationDetailRow( array $row ): bool {
        if ( count( $row ) != 12 ):
            return FALSE;
        endif;

        try {
            $this->_getDatesForAccrualPeriodFromCertificateInterestReconciliationDetailRow( $row );
            return TRUE;
        } catch ( \Exception $exception ) {
            return FALSE;
        }
    }


    /**
     * @param array $row
     * @return array
     * @throws \Exception
     */
    private function _getDatesForAccrualPeriodFromCertificateInterestReconciliationDetailRow( array $row ): array {
        $dateString   = $row[ 1 ];
        $explodedDate = explode( ' - ', $dateString );
        $explodedDate = array_map( 'trim', $explodedDate );

        try {
            $startDate = Carbon::parse( $explodedDate[ 0 ], $this->timezone );
            $endDate   = Carbon::parse( $explodedDate[ 1 ], $this->timezone );
            return [
                'start' => $startDate,
                'end'   => $endDate,
            ];
        } catch ( \Exception $exception ) {
            throw new \Exception( "Not able to get accrual dates. Probably not a valid row. [" . $row[ 1 ] . "]", 0, $exception );
        }
    }


    /**
     * @param array $row
     * @return array
     */
    private function _parseCertificateDistributionDetailRow( array $row ): array {
        unset( $row[ 3 ] );          // It's always blank.
        $row = array_values( $row ); // Re-index the array.

        $newRow = [];

        $newRow[ self::security_class ]          = $row[ 0 ];
        $newRow[ self::cusip ]                   = $row[ 1 ];
        $newRow[ self::pass_through_rate ]       = $this->_formatPercent( $row[ 2 ] );
        $newRow[ self::original_balance ]        = $this->_formatNumber( $row[ 3 ] );
        $newRow[ self::beginning_balance ]       = $this->_formatNumber( $row[ 4 ] );
        $newRow[ self::principal_distribution ]  = $this->_formatNumber( $row[ 5 ] );
        $newRow[ self::interest_distribution ]   = $this->_formatNumber( $row[ 6 ] );
        $newRow[ self::prepayment_penalties ]    = $this->_formatNumber( $row[ 7 ] );
        $newRow[ self::realized_losses ]         = $this->_formatNumber( $row[ 8 ] );
        $newRow[ self::total_distribution ]      = $this->_formatNumber( $row[ 9 ] );
        $newRow[ self::ending_balance ]          = $this->_formatNumber( $row[ 10 ] );
        $newRow[ self::current_credit_support ]  = $this->_formatPercent( $row[ 11 ] );
        $newRow[ self::original_credit_support ] = $this->_formatPercent( $row[ 12 ] );

        return $newRow;
    }


    /**
     * @param array $row
     * @return array
     */
    private function _parseCertificateFactorDetailRow( array $row ): array {
        $newRow = [];

        $newRow[ self::security_class ]                 = $row[ 0 ];
        $newRow[ self::cusip ]                          = $row[ 1 ];
        $newRow[ self::beginning_balance ]              = $this->_formatNumber( $row[ 2 ] );
        $newRow[ self::principal_distribution ]         = $this->_formatNumber( $row[ 3 ] );
        $newRow[ self::interest_distribution ]          = $this->_formatNumber( $row[ 4 ] );
        $newRow[ self::interest_shortfalls ]            = $this->_formatNumber( $row[ 5 ] );
        $newRow[ self::cumulative_interest_shortfalls ] = $this->_formatNumber( $row[ 6 ] );
        $newRow[ self::prepayment_penalties ]           = $this->_formatNumber( $row[ 7 ] );
        $newRow[ self::losses ]                         = $this->_formatNumber( $row[ 8 ] );
        $newRow[ self::total_distribution ]             = $this->_formatNumber( $row[ 9 ] );
        $newRow[ self::ending_balance ]                 = $this->_formatNumber( $row[ 10 ] );

        return $newRow;
    }


    /**
     * @param array $row
     * @return array
     * @throws \Exception
     */
    private function _parseCertificateInterestReconciliationDetailRow( array $row ): array {
        $newRow = [];

        $dates = $this->_getDatesForAccrualPeriodFromCertificateInterestReconciliationDetailRow( $row );

        $newRow[ self::security_class ]     = $row[ 0 ];
        $newRow[ self::start_accrual_date ] = $dates[ 'start' ];
        $newRow[ self::end_accrual_date ]   = $dates[ 'end' ];

        $newRow[ self::accrual_days ]                                = $this->_formatNumber( $row[ 2 ] );
        $newRow[ self::prior_interest_shortfalls ]                   = $this->_formatNumber( $row[ 3 ] );
        $newRow[ self::accrued_certificate_interest ]                = $this->_formatNumber( $row[ 4 ] );
        $newRow[ self::net_aggregate_prepayment_interest_shortfall ] = $this->_formatNumber( $row[ 5 ] );
        $newRow[ self::distributable_certificate_interest ]          = $this->_formatNumber( $row[ 6 ] );
        $newRow[ self::interest_shortfalls ]                         = $this->_formatNumber( $row[ 7 ] );
        $newRow[ self::payback_of_prior_realized_losses ]            = $this->_formatNumber( $row[ 8 ] );
        $newRow[ self::additional_interest_distribution_amount ]     = $this->_formatNumber( $row[ 9 ] );
        $newRow[ self::interest_distribution ]                       = $this->_formatNumber( $row[ 10 ] );
        $newRow[ self::cumulative_interest_shortfalls ]              = $this->_formatNumber( $row[ 11 ] );

        return $newRow;
    }


    /**
     * @param string $number
     * @return float|null
     */
    private function _formatNumber( string $number ): ?float {
        $number = trim( $number );
        if ( empty( $number ) ):
            return NULL;
        endif;
        $number = str_replace( ',', '', $number );
        return (float)$number;
    }


    /**
     * @param string $number
     * @return float|null
     */
    private function _formatPercent( string $number ): ?float {
        $number = str_replace( '%', '', $number );
        $number = trim( $number );
        if ( empty( $number ) ):
            return NULL;
        endif;

        $asPercent = (float)$number / 100;

        return $asPercent;
    }


}