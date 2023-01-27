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

    public function __construct() {
    }

    public function make( string $pathToDistributionFilePDF ): CMBSDistributionFile {
        $distributionFile                = new CMBSDistributionFile();
        $fileContents                    = file_get_contents( $pathToDistributionFilePDF );
        $parser                          = new \Smalot\PdfParser\Parser();
        $pdf                             = $parser->parseContent( $fileContents );
        $pages                           = $pdf->getPages();
        $distributionFile->numberOfPages = count( $pages );

        $distributionFile->certificateDistributionDetail = $this->getCertificateDistributionDetail($pages[0]);

        return $distributionFile;
    }



    protected function  getCertificateDistributionDetail(\Smalot\PdfParser\Page $firstPage): array {
        $aFirstPage = $firstPage->getTextArray($firstPage);
        $certificateDistributionDetail = [];

        $indexOfLabel = array_search('Certificate Distribution Detail',$aFirstPage);

        // DEBUG
        //$indexOfLabel = 61;
        $pagesString = $aFirstPage[$indexOfLabel + 1];
        $pageNumberParts = explode('-', $pagesString);

        $startPage = $pageNumberParts[0] ?? false;
        $endPage = $pageNumberParts[1] ?? false;

        var_dump($startPage);
        var_dump($endPage);
        die('asdfasd');

        return $certificateDistributionDetail;
    }

}