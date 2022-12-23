<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers;

use DPRMC\RemitSpiderCTSLink\Exceptions\CUSIPNotFoundException;
use DPRMC\RemitSpiderCTSLink\Exceptions\WrongNumberOfTitleElementsException;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Clip;
use HeadlessChromium\Cookies\CookiesCollection;
use HeadlessChromium\Page;

/**
 *
 */
class FilesByCUSIP {

    protected Page   $Page;
    protected Debug  $Debug;
    protected string $timezone;

    public CookiesCollection $cookies;


    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        $this->Page     = $Page;
        $this->Debug    = $Debug;
        $this->timezone = $timezone;
    }


    /**
     * [Monday 2:51 PM] Kiva Patten distribution data statement
     * [Monday 2:51 PM] Kiva Patten crefc restricted servicer report
     * [Monday 2:51 PM] Kiva Patten are the 2 files.
     * @param string $cusip
     * @return string
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\FilesystemException
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     * @throws \HeadlessChromium\Exception\ScreenshotFailed
     * @throws CUSIPNotFoundException
     */
    public function getFilePathsByCUSIP( string $cusip ): array {
        $this->Debug->_debug( "Searching by CUSIP" );
        $this->Page->evaluate( "document.querySelector('#searchtype').value = 'CUSIP';" );
        $this->Page->evaluate( "document.querySelector('#searchValue').value = '" . $cusip . "';" );
        $this->Page->evaluate( "document.querySelector('#navsearch').submit();" );
        $this->Page->waitForReload();

        $this->Debug->_screenshot( 'on_the_cusip_page' );
        $cusipHTML = $this->Page->getHtml();
        $this->Debug->_html( "on_the_cusip_page" );

        if ( $this->_cusipNotFound( $cusipHTML ) ):
            throw new CUSIPNotFoundException( "CUSIP: " . $cusip . " not found in CTS Link.",
                                              0,
                                              NULL,
                                              $cusip,
                                              $cusipHTML );
        endif;


        return [];
    }


    /**
     * Examine the HTML. If CTS couldn't find the CUSIP, then we get a
     * very specific HTML title tag back. Search there.
     * @param string $html
     * @return bool
     * @throws WrongNumberOfTitleElementsException
     */
    protected function _cusipNotFound( string $html ): bool {
        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );

        /**
         * @var \DOMNodeList $elements
         */
        $elements = $dom->getElementsByTagName( 'title' );

        if ( 1 != $elements->count() ):
            throw new WrongNumberOfTitleElementsException( $elements->count() . " were found. Should only see 1.",
                                                           0,
                                                           NULL,
                                                           $elements,
                                                           $html );
        endif;

        $titleText = trim( $elements->item( 0 )->textContent );
        $pattern   = '/^Cusipnotfound.*$/';


        $matches = [];
        $success = preg_match( $pattern, $titleText, $matches );
        if ( 1 === $success ):
            return TRUE;
        endif;

        return FALSE;
    }


}