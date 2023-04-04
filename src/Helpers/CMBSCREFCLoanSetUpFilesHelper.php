<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers;

use DPRMC\RemitSpiderCTSLink\Exceptions\CREFCLoanSetUpFileExceptions\LinkToCREFCLoanSetUpFileNotFoundException;
use DPRMC\RemitSpiderCTSLink\Exceptions\NoAccessToRestrictedServicerReportException;
use DPRMC\RemitSpiderCTSLink\Exceptions\NoAccessToSecurityException;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Page;

/**
 * QUERY SELECTOR EXAMPLES: protected string $querySelectorForLinks = '#results-table > tbody > tr';
 */
class CMBSCREFCLoanSetUpFilesHelper extends CMBSHelper {

    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        parent::__construct( $Page, $Debug, $timezone );

    }


    /**
     * https://www.ctslink.com/a/seriesdocs.html?shelfId=GSM&seriesId=2014GC24&tab=DEALDOCS
     * @param string $shelf
     * @param string $series
     * @return string
     * @throws LinkToCREFCLoanSetUpFileNotFoundException
     * @throws NoAccessToSecurityException
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\ResponseHasError
     * @throws \HeadlessChromium\Exception\FilesystemException
     * @throws \HeadlessChromium\Exception\NavigationExpired
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     * @throws \HeadlessChromium\Exception\ScreenshotFailed
     */
    public function getCREFCLoanSetUpFileLink( string $shelf, string $series ): string {
        $documentLinks     = [];
        $dealDocumentsLink = self::SERIES_DOCS_URL . 'shelfId=' . $shelf . '&seriesId=' . $series . '&tab=DEALDOCS';
        $this->Debug->_debug( " Navigating to: " . $dealDocumentsLink );
        $this->Page->navigate( $dealDocumentsLink )->waitForNavigation();
        $this->Debug->_screenshot( urlencode( $dealDocumentsLink ) );
        $this->Debug->_html( urlencode( $dealDocumentsLink ) );

        $html = $this->Page->getHtml();

        if ( str_contains( strtolower( $html ), strtolower( 'Get Access' ) ) ):
            throw new NoAccessToSecurityException( "We do not have access to this Series: " . $dealDocumentsLink,
                                                                   0,
                                                                   NULL,
                                                                   $shelf,
                                                                   $series );
        endif;

        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );

        /**
         * @var \DOMNodeList $links
         */
        $inputs = $dom->getElementsByTagName( 'input' );
        foreach ( $inputs as $input ):
            $ariaLabel = $input->getAttribute( 'aria-label' );
            if ( str_contains( $ariaLabel, 'CREFC Loan Set Up' ) ):
                $documentId = $input->getAttribute( 'value' );
                return CMBSHelper::BASE_URL . '/a/document.html?key=' . $documentId;
            endif;
        endforeach;

        throw new LinkToCREFCLoanSetUpFileNotFoundException( "Unable to find the CREFC Loan Set Up file link for $shelf $series",
                                                             0,
                                                             NULL,
                                                             $shelf,
                                                             $series );
    }

}