<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers;

use DPRMC\RemitSpiderCTSLink\Exceptions\CUSIPNotFoundException;
use DPRMC\RemitSpiderCTSLink\Exceptions\LoginTimedOutException;
use DPRMC\RemitSpiderCTSLink\Exceptions\WrongNumberOfTitleElementsException;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Clip;
use HeadlessChromium\Cookies\CookiesCollection;
use HeadlessChromium\Page;

/**
 * QUERY SELECTOR EXAMPLES: protected string $querySelectorForLinks = '#results-table > tbody > tr';
 */
class CMBSDistributionFiles {

    protected Page   $Page;
    protected Debug  $Debug;
    protected string $timezone;

    public CookiesCollection $cookies;

    const BASE_URL       = 'https://www.ctslink.com';
    const CMBS_MAIN_PAGE = 'https://www.ctslink.com/a/shelflist.html?shelfType=CMBS';


    const type   = 'type';
    const shelf  = 'shelf';
    const series = 'series';

    const href                       = 'href'; // The URL of the page with this data.
    const access                     = 'access';
    const current_cycle              = 'current_cycle';
    const next_cycle                 = 'next_cycle';
    const next_available             = 'next_available';
    const link_to_doc                = 'link_to_doc';
    const link_to_additional_history = 'link_to_additional_history';
    const revised_date               = 'revised_date';




    /**
     * Sometimes the CTS link site will time out.
     * A good idea to keep track of the links we were unable to parse, so
     * we can go back and try again.
     * @var array
     */
    protected array $linksWeWereUnableToPull = [];


    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        $this->Page     = $Page;
        $this->Debug    = $Debug;
        $this->timezone = $timezone;
    }

    public function getLinksWeWereUnableToPull(): array {
        return $this->linksWeWereUnableToPull;
    }



    public function getAllRecentCMBSDistributionFileData(): array {
        $distributionFileData = [];

        $this->Page->navigate( self::CMBS_MAIN_PAGE )->waitForNavigation();

        $cmbsHTML = $this->Page->getHtml();

        $shelfLinks = $this->getShelfLinksFromHTML( $cmbsHTML );

        foreach ( $shelfLinks as $i => $link ):
            $this->Debug->_debug( " " . $i . " of " . count($shelfLinks) . " " . $link );
            try {
                $this->Debug->_debug( " SHELF LINK: " . $link );

                $this->Page->navigate( $link )->waitForNavigation();
                $html = $this->Page->getHtml();

                // If this link leads to a SERIES page, then we can start looking for
                // a link to a recent Distribution File page.
                if ( $this->_isSeriesPage( $html ) ):
                    $distributionFileData[] = $this->_getDistributionDateStatementData( $html, $link );
                else:
                    $seriesLinks = $this->_getSeriesLinksFromShelfPageHtml( $html );
                    foreach ( $seriesLinks as $seriesLink ):
                        $this->Page->navigate( $seriesLink )->waitForNavigation();
                        $html = $this->Page->getHtml();
                        if ( $this->_isSeriesPage( $html ) ):
                            $distributionFileData[] = $this->_getDistributionDateStatementData( $html, $seriesLink );
                        else:
                            $this->Debug->_debug( "WTF is: " . $seriesLink );
                        endif;
                    endforeach;
                endif;
            } catch (\HeadlessChromium\Exception\OperationTimedOut $exception) {
                $this->Debug->_debug($exception->getMessage());
                $this->Debug->_debug($link);
                $this->linksWeWereUnableToPull[] = $link;
            } catch (\Exception $exception) {
                $this->Debug->_debug($exception->getMessage());
            }
        endforeach;


        $filesWeHaveAccessTo = [];
        foreach($distributionFileData as $fileData):
            if($fileData[self::access]):
                $filesWeHaveAccessTo[] = $fileData;
            endif;
        endforeach;

        return $filesWeHaveAccessTo;
    }


    public function getAllRecentCMBSDistributionFiles(): array {
        $distributionFiles = [];
        $filesWeHaveAccessTo = $this->getAllRecentCMBSDistributionFileData();

        // TODO add code to download PDFs.

        return $distributionFiles;
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

    /**
     * If this page is a series page, then the string "Restricted Report"
     * will be in an H2 element towards the top of the page.
     * @param string $html
     * @return bool
     */
    private function _isSeriesPage( string $html ): bool {
        $pattern = '/Restricted Reports/';
        $found   = preg_match( $pattern, $html, $matches );
        if ( 1 !== $found ):
            return FALSE;
        endif;
        return TRUE;
    }


    /**
     * @param string $html
     * @return array Ex: https://www.ctslink.com/a/serieslist.html?shelfId=ZHD
     */
    public function getShelfLinksFromHTML( string $html ): array {
        $shelfLinks = [];

        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );

        /**
         * @var \DOMNodeList $elements
         */
        $elements = $dom->getElementsByTagName( 'a' );

        foreach ( $elements as $element ):
            $href = $element->getAttribute( 'href' );
            if ( $this->_isShelfLinkOnCMBSPage( $href ) ):
                $this->Debug->_debug( $href );
                $shelfLinks[] = self::BASE_URL . $href;
            endif;
        endforeach;

        return $shelfLinks;
    }


    /**
     * When I load the CMBS page, there will be a list of links to
     * CMBS shelves. There can be 1 or more series under a shelf, FYI.
     * @param string $href
     * @return bool
     */
    protected function _isShelfLinkOnCMBSPage( string $href ): bool {
        $pattern = '/a\/serieslist\.html\?shelfId=/';
        $found   = preg_match( $pattern, $href, $matches );
        if ( 1 !== $found ):
            return FALSE;
        endif;
        return TRUE;
    }


    protected function _getDistributionFile( string $html, string $href ) {
        $distributionFile = '';
        //$this->Debug->_debug( "Getting shelf links from a SERIES PAGE." );

        //$distributionFileData = $this->_getDistributionDateStatementData( $html, $href );

        //print_r( $distributionFileData );


////            var_dump(get_class($row));
////        die();
//            $href = $row->get( 'href' );
//            //if ( $this->_isSeriesLink( $href ) ):
//            $this->Debug->_debug( $href );

        //endif;
//        endforeach;

        return $distributionFile;
    }


    private function _getDistributionDateStatementData( string $html, string $href ): array {
//        $this->Debug->_html( md5( $html ) );
//        $this->Debug->_screenshot( md5( $html ) );
        $seriesData = $this->_getSecurityNamePartsFromHtml( $html, $href );

        $data = [];
        $dom  = new \DOMDocument();
        @$dom->loadHTML( $html );

        /**
         * @var \DOMNodeList $trs
         */
        $trs = $dom->getElementsByTagName( 'tr' );
        //$this->Debug->_debug( "TR - found this many: " . $trs->count() );

        /**
         * @var \DOMElement $tr
         */
        foreach ( $trs as $tr ):
            if ( $this->_rowContainsDistributionFile( $tr ) ):
                $rowData                 = $this->__getDistributionDateStatementRowData( $tr, $href );
                $rowData[ self::type ]   = $seriesData[ self::type ];
                $rowData[ self::shelf ]  = $seriesData[ self::shelf ];
                $rowData[ self::series ] = $seriesData[ self::series ];
                $data[] = $rowData;
            endif;
        endforeach;

        return $data;
    }


    /**
     * @param string $html
     * @param string $href
     * @return array
     * @throws \Exception
     */
    private function _getSecurityNamePartsFromHtml( string $html, string $href ): array {
        $dom  = new \DOMDocument();
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
            throw new \Exception( "Not the right number of security name parts in . " . $href );
        endif;

        $data                 = [];
        $data[ self::type ]   = $bulletItems[ 0 ];
        $data[ self::shelf ]  = $bulletItems[ 1 ];
        $data[ self::series ] = $bulletItems[ 2 ];

        return $data;
    }


    /**
     * There is a bunch of "metadata" stored in the HTML row of the table.
     * May as well rip it out and return it.
     * @param \DOMElement $tr
     * @param string $href
     * @return array
     */
    private function __getDistributionDateStatementRowData( \DOMElement $tr, string $href ): array {
        $data  = [];
        $tds   = $tr->getElementsByTagName( 'td' );
        $links = $tr->getElementsByTagName( 'a' );

        $data[ self::href ]           = $href;
        $data[ self::access ]         = $this->__weHaveAccessToDistributionFile( $tds->item( 1 )->textContent );
        $data[ self::current_cycle ]  = $this->__getCurrentCycleDate( $tds->item( 2 )->textContent );
        $data[ self::next_cycle ]     = trim( $tds->item( 3 )->textContent );
        $data[ self::next_available ] = trim( $tds->item( 4 )->textContent );


        if ( $data[ self::access ] ):
            $data[ self::link_to_doc ]                = self::BASE_URL . $links->item( 0 )->getAttribute( 'href' );
            $data[ self::link_to_additional_history ] = self::BASE_URL . $links->item( 1 )->getAttribute( 'href' );
        else:
            $data[ self::link_to_doc ]                = '';
            $data[ self::link_to_additional_history ] = '';
        endif;

        $data[ self::revised_date ] = $this->__getRevisedDate( $tds->item( 2 )->textContent );

        return $data;
    }


    private function __getCurrentCycleDate( string $textContent ): string {
        $parts = explode( ' ', trim( $textContent ) );
        return trim( $parts[ 0 ] );
    }


    private function __getRevisedDate( string $textContent ): string {
        $pattern = '/REVISED/';
        $found   = preg_match( $pattern, $textContent, $matches );
        if ( 1 !== $found ):
            return '';
        endif;
        $parts = explode( 'REVISED', trim( $textContent ) );
        return trim( $parts[ count( $parts ) - 1 ] );
    }


    /**
     * @param string $textContent
     * @return int
     */
    private function __weHaveAccessToDistributionFile( string $textContent ): int {
        $pattern = '/Get Access/';
        $found   = preg_match( $pattern, $textContent, $matches );
        if ( 1 !== $found ):
            return 1;
        endif;
        return 0;
    }


    /**
     * A little helper function to determine if the TABLE row in question
     * contains Distribution File data.
     * @param \DOMElement $tr
     * @return bool
     */
    private function _rowContainsDistributionFile( \DOMElement $tr ): bool {
        $tds = $tr->getElementsByTagName( 'td' );

        /**
         * @var \DOMElement $td
         */
        foreach ( $tds as $td ):
            $text = trim( $td->textContent );
            if ( 'Distribution Date Statement' == $text ):
                return TRUE;
            endif;
        endforeach;
        return FALSE;
    }


    /**
     * @param string $href
     * @return bool
     */
    private function _isDistributionFileLink( string $href ): bool {
        $pattern = '/a\/serieslist\.html\?shelfId=/';
        $found   = preg_match( $pattern, $href, $matches );
        if ( 1 !== $found ):
            return FALSE;
        endif;
        return TRUE;
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