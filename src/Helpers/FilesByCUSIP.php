<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers;

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
     */
    public function getFilePathsByCUSIP(string $cusip): string {
        $this->Debug->_debug( "Searching by CUSIP" );
        $this->Page->evaluate( "document.querySelector('#searchtype').value = 'CUSIP';" );
        $this->Page->evaluate( "document.querySelector('#searchValue').value = '" . $cusip . "';" );
        $this->Page->evaluate( "document.querySelector('#navsearch').submit();" );
        $this->Page->waitForReload();

        $this->Debug->_screenshot( 'on_the_cusip_page' );
        $postLoginHTML = $this->Page->getHtml();

        $this->Debug->_html( "on_the_cusip_page" );

        return $postLoginHTML;
    }



}