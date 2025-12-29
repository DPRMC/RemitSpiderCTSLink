<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers\Generic;

use DPRMC\RemitSpiderCTSLink\Helpers\AbstractHelper;
use DPRMC\RemitSpiderCTSLink\Helpers\Debug;
use DPRMC\RemitSpiderCTSLink\Helpers\Generic\Exceptions\NoAccessToDealException;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Page;

class ShelfDocsHelper extends AbstractHelper {
    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        parent::__construct( $Page, $Debug, $timezone );
    }


    /**
     * This is the parent method. All other code below, goes to add functionality to this.
     * @param string $href
     * @param string|NULL $debugHtml
     * @return DocLink[] An array of DocLink objects
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\ResponseHasError
     * @throws \HeadlessChromium\Exception\FilesystemException
     * @throws \HeadlessChromium\Exception\NavigationExpired
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     * @throws \HeadlessChromium\Exception\ScreenshotFailed
     * @throws NoAccessToDealException
     */
    public function getLinks( string $href, string $debugHtml = NULL ): array {
        $links = [];

        if ( $debugHtml ):
            $html = $debugHtml;
        //$currentUrl = $forceSeriesPage ? 'serieslist.html' : 'seriesdocs.html';
        else:
            $this->Page->navigate( self::BASE_URL . $href )->waitForNavigation();
            $html       = $this->Page->getHtml();
            $currentUrl = $this->Page->getCurrentUrl();
        endif;

        $tabsToCheck = $this->_getTabLinks( $html );


        foreach ( $tabsToCheck as $href ):
            // DEBUG
            // $href    = 'https://www.ctslink.com/a/seriesdocs.html?shelfId=WBC&seriesId=2004C11';
            $tabType = $this->_getTabTypeFromHref( $href );

            switch ( $tabType ):
                case 'PERIODICRPT':
                    $href = self::strToUpperQueryParams( $href );
                    $this->Debug->_debug( "Loading tab: " . self::BASE_URL . $href );
                    $this->Page->navigate( self::BASE_URL . $href )->waitForNavigation();
                    sleep( 1 );
                    $html = $this->Page->getHtml();


                    $newLinks = $this->_getLinksToDocs( $html );

                    $links = array_merge( $links, $newLinks );
                    break;

                // Other tabs are called:
//                    DEALDOCS
//                    ADDDOC
//                    SPECNOTE
//                    ADDRPT
//                    TAXRPT
                // But those dont have links we care about.
            endswitch;

        endforeach;

        return $links;
    }



    /**
     * @param string $href https://www.ctslink.com/a/seriesdocs.html?shelfId=AKFL&seriesId=2006A&tab=PERIODICRPT
     * @return string PERIODICRPT
     */
    protected function _getTabTypeFromHref( string $href ): string {
        $parts = explode( '=', $href );
        return end( $parts );
    }


    /**
     * Given the HTML of the page with Docs on it, this method
     * will grab the URLs of the tabs to each set of docs.
     * @param string $html HTML of the docs page.
     * @return array An array filled with: /a/seriesdocs.html?shelfId=A10BAF&seriesId=2021D&tab=SPECNOTE
     */
    protected function _getTabLinks( string $html ): array {
        $tabLinks = [];
        $dom      = new \DOMDocument();
        @$dom->loadHTML( $html );

        /**
         * @var \DOMNodeList $elements
         */
        $elements = $dom->getElementsByTagName( 'a' );

        /**
         * @var \DOMElement $anchorElement
         */
        foreach ( $elements as $i => $anchorElement ):
            $href = trim( $anchorElement->getAttribute( 'href' ) );
            if ( $this->_isTabLink( $href ) ):
                $tabLinks[] = $href;
            endif;
        endforeach;

        return $tabLinks;
    }


    /**
     * @param string $href Ex: /a/seriesdocs.html?shelfId=A10BAF&seriesId=2021D&tab=RISKRETENTIONSPECIALNOTICES
     * @return bool
     */
    protected function _isTabLink( string $href ): bool {
        return str_contains( $href, '&tab=' );
    }


    /**
     * Click on a tab, and a new page appears with links to docs.
     * The html of that page gets passed in, and an array of DocLink
     * objects is returned. The developer can then do whatever he wants
     * with those objects. Store them in a database. Download the docs. Etc.
     * @param string $html
     * @return array
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\FilesystemException
     * @throws \HeadlessChromium\Exception\ScreenshotFailed
     */
    protected function _getLinksToDocs( string $html ): array {
        $docLinks                = [];
        $dom                     = new \DOMDocument();
        $dom->preserveWhiteSpace = FALSE;
        @$dom->loadHTML( $html );

        $hasAccess = TRUE;
        if ( str_contains( $html, 'getaccess.html' ) ):
            $hasAccess = FALSE;
        endif;

        /**
         * @var \DOMNodeList $elements
         */
        $elements = $dom->getElementsByTagName( 'a' );

        $this->Debug->_debug( " I found " . count( $elements ) . " anchors." );
        $this->Debug->_screenshot( 'links_to_docs' );

        /**
         * @var \DOMElement $anchorElement
         */
        foreach ( $elements as $i => $anchorElement ):
            $href = trim( $anchorElement->getAttribute( 'href' ) );
            $this->Debug->_debug( $i . " " . $href );

//            if ( $this->_isGetAccessLink( $href ) ):
////                throw new NoAccessToDealException( "You don't have access to this deal.",
////                                                   0,
////                                                   NULL );
//                $hasAccess = FALSE;
//            else:
//                $hasAccess = TRUE;
//            endif;

            if ( $this->_isDocLink( $href ) ):
                //dump( trim( $anchorElement->textContent ) );
                $trElement  = $anchorElement->parentNode->parentNode;
                $tdElements = $trElement->childNodes;


                // 1 - Name of file
                // 5 - Current Cycle
                // 7 - Next Cycle
                // 9 - next available date time
                // 11 - td with additional information link.
                /**
                 * @var \DOMElement $tdElement
                 */
//                foreach ( $tdElements as $i => $tdElement ):
//                    dump( $i . " " . trim( $tdElement->textContent ) );
//                endforeach;

                $nameOfFile            = trim( $tdElements->item( 1 )->textContent );
                $currentCycle          = trim( $tdElements->item( 5 )->textContent );
                $nextCycle             = trim( $tdElements->item( 7 )->textContent );
                $nextAvailableDateTime = trim( $tdElements->item( 9 )->textContent );

                $tdWithAdditionalHistory         = $tdElements->item( 11 );
                $anchorWithAdditionalHistoryLink = $tdWithAdditionalHistory->childNodes;
                $anchorWithAdditionalHistoryLink = $anchorWithAdditionalHistoryLink->item( 1 );

                if ( $anchorWithAdditionalHistoryLink ):
                    $additionalHistoryHref = $anchorWithAdditionalHistoryLink->getAttribute( 'href' );
                else:
                    $additionalHistoryHref = '';
                endif;

                $docLinks[] = new DocLink( $nameOfFile,
                                           $currentCycle,
                                           $nextCycle,
                                           $nextAvailableDateTime,
                                           $additionalHistoryHref,
                                           $href,
                                           $hasAccess );
            endif;
        endforeach;

        return $docLinks;
    }


    protected function _isDocLink( string $href ): bool {
        return str_contains( $href, 'document.html?key=' );

    }


    protected function _isGetAccessLink( string $href ): bool {
        return str_contains( $href, 'getaccess.html' );
    }
}