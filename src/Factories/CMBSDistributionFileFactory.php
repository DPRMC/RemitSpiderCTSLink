<?php

namespace DPRMC\RemitSpiderCTSLink\Factories;

use DPRMC\RemitSpiderCTSLink\Exceptions\CUSIPNotFoundException;
use DPRMC\RemitSpiderCTSLink\Exceptions\LoginTimedOutException;
use DPRMC\RemitSpiderCTSLink\Exceptions\WrongNumberOfTitleElementsException;
use DPRMC\RemitSpiderCTSLink\Models\CMBSDistributionFile;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Clip;
use HeadlessChromium\Cookies\CookiesCollection;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Exception\CommunicationException\CannotReadResponse;
use HeadlessChromium\Exception\CommunicationException\InvalidResponse;
use HeadlessChromium\Exception\CommunicationException\ResponseHasError;
use HeadlessChromium\Exception\NavigationExpired;
use HeadlessChromium\Exception\NoResponseAvailable;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Page;


class CMBSDistributionFileFactory {

    protected array                  $pages;
    protected \Smalot\PdfParser\Page $pageWithTableOfContents;


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


    public function __construct() {
    }

    public function make( string $pathToDistributionFilePDF ): CMBSDistributionFile {
        $distributionFile                = new CMBSDistributionFile();
        $fileContents                    = file_get_contents( $pathToDistributionFilePDF );
        $parser                          = new \Smalot\PdfParser\Parser();
        $pdf                             = $parser->parseContent( $fileContents );
        $this->pages                     = $pdf->getPages();
        $distributionFile->numberOfPages = count( $this->pages );

        $this->pageWithTableOfContents = $this->pages[ 0 ];

        $distributionFile->certificateDistributionDetail = $this->getCertificateDistributionDetail();

        return $distributionFile;
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

        // DEBUG
        //$indexOfLabel = 61;
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


    protected function getCertificateDistributionDetail(): array {
        $certificateDistributionDetail = [];
        $sectionName                   = 'Certificate Distribution Detail';
        $pageIndexes                   = $this->getPageRangeBySection( $sectionName );

        $pagesAsArrays = [];
        foreach ( $pageIndexes as $index ):
            $currentPage     = $this->pages[ $index ];
            $pagesAsArrays[] = $currentPage->getTextArray( $currentPage );
        endforeach;

        $this->parseCertificateDistributionDetailRowsFromPageArray( $pagesAsArrays );


        $regularSubtotal  = [];
        $notionalSubtotal = [];

        return $certificateDistributionDetail;
    }

    private function parseCertificateDistributionDetailRowsFromPageArray( array $pages ): array {
        // Remove Headers
        $numHeaders = 24;
        foreach ( $pages as $page ):
            for ( $i = 0; $i <= $numHeaders; $i++ ):
                array_shift( $page );
            endfor;

            $rawRows = array_chunk( $page, 14 );

            print_r( $rawRows );
            echo "THIS WAASSS PAFGE";
        endforeach;

        return $pages;
    }


    private function parseRow( array $row ): array {
        $newRow = [];

        $newRow[ self::security_class ]          = $row[ 0 ];
        $newRow[ self::cusip ]                   = $row[ 1 ];
        $newRow[ self::pass_through_rate ]       = $row[ 2 ];
        $newRow[ self::original_balance ]        = $row[ 3 ];
        $newRow[ self::beginning_balance ]       = $row[ 4 ];
        $newRow[ self::principal_distribution ]  = $row[ 5 ];
        $newRow[ self::interest_distribution ]   = $row[ 6 ];
        $newRow[ self::prepayment_penalties ]    = $row[ 7 ];
        $newRow[ self::realized_losses ]         = $row[ 8 ];
        $newRow[ self::total_distribution ]      = $row[ 9 ];
        $newRow[ self::ending_balance ]          = $row[ 10 ];
        $newRow[ self::current_credit_support ]  = $row[ 11 ];
        $newRow[ self::original_credit_support ] = $row[ 12 ];


        return $newRow;
    }


}