<?php

namespace DPRMC\RemitSpiderCTSLink\Factories;

use Carbon\Carbon;
use DPRMC\CUSIP;
use DPRMC\RemitSpiderCTSLink\Models\CMBSDistributionFile;
use Smalot\PdfParser\Page;

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

    // Certificate Factor Detail fields (constants defined above are obv omitted here.
    const interest_shortfalls            = 'Interest Shortfalls / (Paybacks)';
    const cumulative_interest_shortfalls = 'Cumulative Interest Shortfalls';
    const losses                         = 'Losses';


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

        $this->pageWithTableOfContents                   = $this->pages[ 0 ];
        $this->dates                                     = $this->_getDates( $this->pagesAsArrays[ 0 ] );
        $distributionFile->dates                         = $this->dates;
        $distributionFile->certificateDistributionDetail = $this->getCertificateDistributionDetail();
        $distributionFile->certificateFactorDetail       = $this->getCertificateFactorDetail();

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
     * Works for Certificate Factor Detail rows as well!
     * @param array $rawRow
     * @return string|null
     */
    private function _getCUSIPFromRawCertificateDistributionDetailRow( array $rawRow ): ?string {

        if ( ! isset( $rawRow[ 1 ] ) ):
            return NULL;
        endif;

        if ( CUSIP::isCUSIP( $rawRow[ 1 ] ) ):
            return trim($rawRow[ 1 ]);
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