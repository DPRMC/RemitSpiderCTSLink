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


    const access                     = 'access';
    const current_cycle              = 'current_cycle';
    const next_cycle                 = 'next_cycle';
    const next_available             = 'next_available';
    const link_to_doc                = 'link_to_doc';
    const link_to_additional_history = 'link_to_additional_history';
    const revised_date               = 'revised_date';


    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        $this->Page     = $Page;
        $this->Debug    = $Debug;
        $this->timezone = $timezone;
    }


    public function getAllRecentCMBSDistributionFiles(): array {
        $distributionFiles = [];

        $this->Debug->_debug( "Getting Series Report Links" );

        $this->Page->navigate( self::CMBS_MAIN_PAGE )->waitForNavigation();

        $this->Debug->_screenshot( 'cmbs_main_page' );
        $this->Debug->_debug( "Loaded the CMBS main page. All series links should be here." );
        $cmbsHTML = $this->Page->getHtml();

        $shelfLinks = $this->_getShelfLinksFromHTML( $cmbsHTML );

        //$this->Debug->_debug( "SHELF LINKS - Found this many: " . count( $shelfLinks ) );


        foreach ( $shelfLinks as $link ):
            //$this->Debug->_debug( " SHELF LINK: " . $link );

            $this->Page->navigate( $link )->waitForNavigation();
            $html = $this->Page->getHtml();

            // If this link leads to a SERIES page, then we can start looking for
            // a link to a recent Distribution File page.
            if ( $this->_isSeriesPage( $html ) ):
                //$this->Debug->_debug( "IS SERIES PAGE: " . $link );
                $distributionFiles[] = $this->_getDistributionFile( $html );
            else:
                //$this->Debug->_debug( "IS SHELF PAGE: " . $link );
            endif;

        endforeach;

        return $distributionFiles;
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
    protected function _getShelfLinksFromHTML( string $html ): array {
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
    private function _isShelfLinkOnCMBSPage( string $href ): bool {
        $pattern = '/a\/serieslist\.html\?shelfId=/';
        $found   = preg_match( $pattern, $href, $matches );
        if ( 1 !== $found ):
            return FALSE;
        endif;
        return TRUE;
    }


    protected function _getDistributionFile( string $html ) {
        $distributionFile = '';
        //$this->Debug->_debug( "Getting shelf links from a SERIES PAGE." );

        $distributionFileData = $this->_getDistributionDateStatementData( $html );

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


    private function _getDistributionDateStatementData( string $html ): array {
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
                $rowData = $this->__getDistributionDateStatementRowData( $tr );

                print_r( $rowData );
                $data[] = $rowData;
            endif;
        endforeach;


        return $data;
    }


    /**
     * There is a bunch of "meta data" stored in the HTML row of the table.
     * May as well rip it out and return it.
     * @param \DOMElement $tr
     * @return array
     */
    private function __getDistributionDateStatementRowData( \DOMElement $tr ): array {
        $data = [];
        $tds  = $tr->getElementsByTagName( 'td' );

        $links = $tr->getElementsByTagName( 'a' );

        $data[ self::access ]         = $this->__weHaveAccessToDistributionFile( $tds->item( 1 )->textContent );
        $data[ self::current_cycle ]  = trim( $tds->item( 2 )->textContent );
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

    private function __getRevisedDate( string $textContent ): string {
        $pattern = '/REVISED/';
        $found   = preg_match( $pattern, $textContent, $matches );
        if ( 1 !== $found ):
            return '';
        endif;
        $parts = explode( ' ', $textContent );
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