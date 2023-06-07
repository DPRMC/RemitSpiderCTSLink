<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers;

use DPRMC\RemitSpiderCTSLink\Models\CTSLinkShelf;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Page;


class GenericHelper extends AbstractHelper {

    public array $validProductTypes = [
        "CMBS",
        "ABS",
        "CDO",
        "MBS",
        "LEASE",
        "INFO",
        "CDSS",
        "FMLP",
    ];

    const BASE_URL       = 'https://www.ctslink.com';
    const SHELFLIST_PAGE = 'https://www.ctslink.com/a/shelflist.html';

    // EX: https://www.ctslink.com/a/history.html?shelfId=JPC&seriesId=2007CIBC20&doc=JPC_2007CIBC20_RSRV
    const HISTORY_URL = self::BASE_URL . '/a/history.html?';

    // EX: https://www.ctslink.com/a/seriesdocs.html?shelfId=GSM&seriesId=2014GC24&tab=DEALDOCS
    const SERIES_DOCS_URL = self::BASE_URL . '/a/seriesdocs.html?';


    const type   = 'type';
    const shelf  = 'shelf';
    const series = 'series';

    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        parent::__construct( $Page, $Debug, $timezone );
    }


    /**
     * @param string|NULL $html
     * @return array
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\ResponseHasError
     * @throws \HeadlessChromium\Exception\NavigationExpired
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     */
    public function getCtsLinkShelfModels( string $html = NULL ): array {
        if ( $html ):
            // Used for debugging.
        else:
            $this->Page->navigate( self::SHELFLIST_PAGE )->waitForNavigation();
            $html = $this->Page->getHtml();
        endif;

        return $this->_getModels( $html );
    }


    /**
     * @param string $html
     * @return array
     */
    private function _getModels( string $html ): array {
        $cleanRows = [];
        $models    = [];
        $dom       = new \DOMDocument();
        @$dom->loadHTML( $html );

        $trs = $dom->getElementsByTagName( 'tr' );

        /**
         * @var \DOMElement $tr
         */
        foreach ( $trs as $tr ):
            $newRow = [];

            /**
             * @var \DOMElement $td
             */
            foreach ( $tr->childNodes as $td ):
                $newRow[] = trim( $td->textContent );
            endforeach;
            $newRow      = array_filter( $newRow );
            $cleanRows[] = $newRow;
        endforeach;

        foreach ( $cleanRows as $i => $row ):
            if ( 3 != count( $row ) && 4 != count( $row ) ):
                continue;
            endif;

            if ( ! in_array( $row[ 1 ], $this->validProductTypes ) ):
                continue;
            endif;

            $myTds = $trs->item( $i )->childNodes;

            $productType = trim( $myTds->item( 1 )->textContent );
            $issuerName  = trim( $myTds->item( 3 )->textContent );

            /**
             * @var \DOMElement $anchorWithSeriesLink
             */
            $anchorWithSeriesLink = $myTds->item( 5 )->childNodes->item( 1 );
            $seriesHref           = $anchorWithSeriesLink->getAttribute( 'href' );

            /**
             * @var \DOMElement $anchorWithShelfLink
             */
            $anchorWithShelfLink = $myTds->item( 7 )->childNodes->item( 1 );
            if ( $anchorWithShelfLink ):
                $shelfHref = $anchorWithShelfLink->getAttribute( 'href' );
            else:
                $shelfHref = '';
            endif;


            $models[] = new CTSLinkShelf( $productType, $issuerName, $seriesHref, $shelfHref );
        endforeach;

        return $models;
    }


    /**
     * @return array
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\ResponseHasError
     * @throws \HeadlessChromium\Exception\NavigationExpired
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     */
    public function getShelfLinks(): array {
        $this->Page->navigate( self::SHELFLIST_PAGE )->waitForNavigation();
        $cmbsHTML   = $this->Page->getHtml();
        $shelfLinks = [];

        $dom = new \DOMDocument();
        @$dom->loadHTML( $cmbsHTML );

        /**
         * @var \DOMNodeList $elements
         */
        $elements = $dom->getElementsByTagName( 'a' );

        foreach ( $elements as $element ):
            $href = $element->getAttribute( 'href' );
            if ( $this->_isShelfLinkOnPage( $href ) ):
                $this->Debug->_debug( $href );
                $shelfLinks[] = self::BASE_URL . $href;
            endif;
        endforeach;

        return $shelfLinks;
    }


    /**
     * @param string $potentialShelfLink
     * @return array
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\ResponseHasError
     * @throws \HeadlessChromium\Exception\NavigationExpired
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     */
    public function getSeriesLinks( string $potentialShelfLink ): array {
        $seriesLinks = [];

        $this->Debug->_debug( "Navigating to " . $potentialShelfLink );

        $this->Page->navigate( self::SHELFLIST_PAGE )->waitForNavigation();

        $this->Page->navigate( $potentialShelfLink )->waitForNavigation();
        $html = $this->Page->getHtml();

        $this->Debug->_html( urlencode( $potentialShelfLink ) );

        // If the Shelf link we checked goes right to a Series page, that is the CTS site telling us that
        // there is only one Series under this Shelf.
        if ( $this->_isSeriesPage( $html ) ):
            $this->Debug->_debug( $potentialShelfLink . " is a Series Page" );
            $seriesLinks[] = $potentialShelfLink;
        else:
            $this->Debug->_debug( $potentialShelfLink . " is a SHELF Page" );
            $this->Debug->_screenshot( urlencode( $potentialShelfLink ) );
            $seriesLinks = $this->_getSeriesLinksFromShelfPageHtml( $html );

//            $this->Debug->_debug( print_r( $seriesLinks, TRUE ) );
//            foreach ( $seriesLinks as $seriesLink ):
//                $this->Debug->_debug( " Navigating to: " . $seriesLink );
//
//                $this->Page->navigate( $seriesLink )->waitForNavigation();
//                $html = $this->Page->getHtml();
//                if ( $this->_isSeriesPage( $html ) ):
//                    $seriesLinks[] = $seriesLink;
//                else:
//                    $this->Debug->_debug( "WTF is: " . $seriesLink );
//                endif;
//            endforeach;
        endif;

        return $seriesLinks;
    }


    /**
     * @param string $seriesLink
     * @return array
     * @throws \Exception
     */
    public function getPartsFromSeriesLink( string $seriesLink ): array {
        $parts      = parse_url( $seriesLink );
        $queryParts = explode( '&', $parts[ 'query' ] );

        if ( count( $queryParts ) < 2 ):
            throw new \Exception();
        endif;

        $shelf  = str_replace( 'shelfId=', '', $queryParts[ 0 ] );
        $series = str_replace( 'seriesId=', '', $queryParts[ 1 ] );

        return [
            self::shelf  => $shelf,
            self::series => $series,
        ];
    }


    /**
     * When I load the CMBS page, there will be a list of links to
     * CMBS shelves. There can be 1 or more series under a shelf, FYI.
     * @param string $href
     * @return bool
     */
    protected function _isShelfLinkOnPage( string $href ): bool {
        $pattern = '/a\/serieslist\.html\?shelfId=/';
        $found   = preg_match( $pattern, $href, $matches );
        if ( 1 !== $found ):
            return FALSE;
        endif;
        return TRUE;
    }


    /**
     * This method assumes that $href refers to a SERIES page.
     * @param string $html
     * @param string $href A link to a SERIES page.
     * @return array
     * @throws \Exception
     */
    protected function _getSecurityNamePartsFromHtmlOnSeriesPage( string $html, string $href ): array {
        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );
        $xpath     = new \DOMXPath( $dom );
        $classname = 'c67';
        $nodes     = $xpath->query( "//*[contains(@class, '$classname')]" );
        $ul        = $nodes->item( 0 );
        $lis       = $ul->childNodes;

        $bulletItems = [];
        /**
         * @var \DOMNode $li
         */
        foreach ( $lis as $li ):
            $bulletItems[] = trim( $li->textContent );
        endforeach;

        // Remove empty elements
        $bulletItems = array_filter( $bulletItems );

        // Remove "Home" from the rest
        array_shift( $bulletItems );

        if ( 3 != count( $bulletItems ) ):
            throw new \Exception( "Not the right number of security name parts in " . $href );
        endif;

        $data                 = [];
        $data[ self::type ]   = $bulletItems[ 0 ];
        $data[ self::shelf ]  = $bulletItems[ 1 ];
        $data[ self::series ] = $bulletItems[ 2 ];

        return $data;
    }


    /**
     * If this page is a series page, then the string "Restricted Report"
     * will be in an H2 element towards the top of the page.
     * The SERIES page will have the links to the PDFs.
     * The alternative is a SHELF page, that will have a bunch of links to
     * SERIES pages.
     * @param string $html
     * @return bool
     */
    protected function _isSeriesPage( string $html ): bool {
        $pattern = '/Restricted Reports/';
        $found   = preg_match( $pattern, $html, $matches );
        if ( 1 !== $found ):
            return FALSE;
        endif;
        return TRUE;
    }

    /**
     * If there are multiple SERIES under a SHELF, then I need to parse out the links
     * to the SERIES pages from this shelf HTML.
     * @param string $html
     * @return array
     */
    protected function _getSeriesLinksFromShelfPageHtml( string $html ): array {
        $seriesLinks = [];

        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );

        /**
         * @var \DOMNodeList $elements
         */
        $elements = $dom->getElementsByTagName( 'a' );

        /**
         *
         */
        foreach ( $elements as $element ):
            $seriesLink = trim( $element->getAttribute( 'href' ) );
            if ( empty( $seriesLink ) ):
                continue;
            endif;

            if ( FALSE === str_contains( $seriesLink, '/a/seriesdocs.html?shelfId=' ) ):
                continue;
            endif;
            $seriesLinks[] = self::BASE_URL . $seriesLink;
        endforeach;

        return $seriesLinks;
    }
}