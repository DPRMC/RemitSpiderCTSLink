<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers;

use DPRMC\RemitSpiderCTSLink\Exceptions\NoAccessToRestrictedServicerReportException;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Page;

/**
 * QUERY SELECTOR EXAMPLES: protected string $querySelectorForLinks = '#results-table > tbody > tr';
 */
class CMBSRestrictedServicerReportsHelper extends CMBSHelper {

    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        parent::__construct( $Page, $Debug, $timezone );

    }


    /**
     * https://www.ctslink.com/a/history.html?shelfId=JPC&seriesId=2007CIBC20&doc=JPC_2007CIBC20_RSRV
     * @param string $shelf
     * @param string $series
     * @return array
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\ResponseHasError
     * @throws \HeadlessChromium\Exception\FilesystemException
     * @throws \HeadlessChromium\Exception\NavigationExpired
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     * @throws \HeadlessChromium\Exception\ScreenshotFailed
     * @throws NoAccessToRestrictedServicerReportException
     */
    public function getAllRestrictedServicerReportLinksFromSeriesPage( string $shelf, string $series ): array {
        $documentLinks         = [];
        $additionalHistoryLink = self::HISTORY_URL . 'shelfId=' . $shelf . '&seriesId=' . $series . '&doc=' . $shelf . '_' . $series . '_RSRV';
        $this->Debug->_debug( " Navigating to: " . $additionalHistoryLink );
        $this->Page->navigate( $additionalHistoryLink )->waitForNavigation();
        $this->Debug->_screenshot( urlencode( $additionalHistoryLink ) );
        $this->Debug->_html( urlencode( $additionalHistoryLink ) );

        $html = $this->Page->getHtml();

        if ( str_contains( strtolower($html), strtolower('Get Access') ) ):
            throw new NoAccessToRestrictedServicerReportException( "We do not have access to this Series: " . $additionalHistoryLink,
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
        $links = $dom->getElementsByTagName( 'a' );

        /**
         * @var \DOMElement $link
         */
        foreach ( $links as $link ):
            $href = $link->getAttribute( 'href' );
            if ( str_contains( $href, '/a/document.html?key=' ) ):
                $documentLinks[] = CMBSHelper::BASE_URL . $href;
            endif;
        endforeach;

        return $documentLinks;
    }


    /**
     * https://www.ctslink.com/a/serieslist.html?shelfId=JPC
     * https://www.ctslink.com/a/seriesdocs.html?shelfId=JPC&seriesId=2015C31
     * https://www.ctslink.com/a/history.html?shelfId=JPC&seriesId=2015C31&doc=JPC_2015C31_RSRV
     *
     * test
     *  https://www.ctslink.com/a/history.html?shelfId=JPC&seriesId=2007CIBC20&doc=JPC_2007CIBC20_RSRV
     */
    public function getAllRestrictedServicerReportLinks( $seriesLink ): array {
        $restrictedServicerReportLinks = [];

        $this->Page->navigate( $seriesLink )->waitForNavigation();
        $html = $this->Page->getHtml();

        echo $html;


        return $restrictedServicerReportLinks;
    }



//
//    const href                       = 'href'; // The URL of the page with this data.
//    const access                     = 'access';
//    const current_cycle              = 'current_cycle';
//    const next_cycle                 = 'next_cycle';
//    const next_available             = 'next_available';
//    const link_to_doc                = 'link_to_doc';
//    const link_to_additional_history = 'link_to_additional_history';
//    const revised_date               = 'revised_date';
//
//
//
//
//    /**
//     * Sometimes the CTS link site will time out.
//     * A good idea to keep track of the links we were unable to parse, so
//     * we can go back and try again.
//     * @var array
//     */
//    protected array $linksWeWereUnableToPull = [];
//
//
//
//
//
//    /**
//     * Step two in the process.
//     * Given a SHELF href (or potentially SERIES href if there is only 1 SERIES under that SHELF)
//     * This method will return all the Distribution Date Statement Data.
//     * Given all of that data, the calling script can determine which PDFs it downloads.
//     * @param string $href
//     * @return array
//     * @throws CannotReadResponse
//     * @throws CommunicationException
//     * @throws InvalidResponse
//     * @throws NavigationExpired
//     * @throws NoResponseAvailable
//     * @throws OperationTimedOut
//     * @throws ResponseHasError
//     */
//    public function getDistributionDateStatementDataFromLink( string $href ): array {
//        $distributionFileData = [];
//        $this->Page->navigate( $href )->waitForNavigation();
//        $html = $this->Page->getHtml();
//
//        if ( $this->_isSeriesPage( $html ) ):
//            $seriesData = $this->_getSecurityNamePartsFromHtmlOnSeriesPage( $html, $href );
//
//            $newDistributionFileData = $this->_getDistributionDateStatementDataFromHtml( $html, $seriesData, $href );
//            if ( $newDistributionFileData ):
//                $distributionFileData = array_merge( $distributionFileData, $newDistributionFileData );
//            endif;
//        else:
//            $seriesLinks = $this->_getSeriesLinksFromShelfPageHtml( $html );
//            foreach ( $seriesLinks as $seriesLink ):
//                $this->Page->navigate( $seriesLink )->waitForNavigation();
//                $html = $this->Page->getHtml();
//                if ( $this->_isSeriesPage( $html ) ):
//                    $seriesData = $this->_getSecurityNamePartsFromHtmlOnSeriesPage( $html, $seriesLink );
//
//                    $newDistributionFileData = $this->_getDistributionDateStatementDataFromHtml( $html, $seriesData, $seriesLink );
//                    if ( $newDistributionFileData ):
//                        $distributionFileData = array_merge( $distributionFileData, $newDistributionFileData );
//                    endif;
//                else:
//                    $this->Debug->_debug( "WTF is: " . $seriesLink );
//                endif;
//            endforeach;
//
//        endif;
//
//        return $distributionFileData;
//    }
//
//
//    /**
//     * @param string $html
//     * @param array $seriesData
//     * @param string $href
//     * @return array
//     */
//    protected function _getDistributionDateStatementDataFromHtml( string $html, array $seriesData, string $href ): array {
//        $data = [];
//        $dom  = new \DOMDocument();
//        @$dom->loadHTML( $html );
//
//        /**
//         * @var \DOMNodeList $trs
//         */
//        $trs = $dom->getElementsByTagName( 'tr' );
//        //$this->Debug->_debug( "TR - found this many: " . $trs->count() );
//
//        /**
//         * @var \DOMElement $tr
//         */
//        foreach ( $trs as $tr ):
//            if ( $this->_rowContainsDistributionFile( $tr ) ):
//                $rowData                 = $this->__getDistributionDateStatementRowData( $tr, $href );
//                $rowData[ self::type ]   = $seriesData[ self::type ];
//                $rowData[ self::shelf ]  = $seriesData[ self::shelf ];
//                $rowData[ self::series ] = $seriesData[ self::series ];
//                $data[]                  = $rowData;
//            endif;
//        endforeach;
//        return $data;
//    }
//
//
//    public function getLinksWeWereUnableToPull(): array {
//        return $this->linksWeWereUnableToPull;
//    }
//
//
//    /**
//     * If there are multiple SERIES under a SHELF, then I need to parse out the links
//     * to the SERIES pages from this shelf HTML.
//     * @param string $html
//     * @return array
//     */
//    protected function _getSeriesLinksFromShelfPageHtml( string $html ): array {
//        $seriesLinks = [];
//
//        $dom = new \DOMDocument();
//        @$dom->loadHTML( $html );
//
//        /**
//         * @var \DOMNodeList $elements
//         */
//        $elements = $dom->getElementsByTagName( 'a' );
//
//        /**
//         *
//         */
//        foreach ( $elements as $element ):
//            $seriesLink = trim( $element->getAttribute( 'href' ) );
//            if ( empty( $seriesLink ) ):
//                continue;
//            endif;
//
//            if ( FALSE === str_contains( $seriesLink, '/a/seriesdocs.html?shelfId=' ) ):
//                continue;
//            endif;
//            $seriesLinks[] = self::BASE_URL . $seriesLink;
//        endforeach;
//
//        return $seriesLinks;
//    }
//
//
//
//
//    /**
//     * When I load the CMBS page, there will be a list of links to
//     * CMBS shelves. There can be 1 or more series under a shelf, FYI.
//     * @param string $href
//     * @return bool
//     */
//    protected function _isShelfLinkOnCMBSPage( string $href ): bool {
//        $pattern = '/a\/serieslist\.html\?shelfId=/';
//        $found   = preg_match( $pattern, $href, $matches );
//        if ( 1 !== $found ):
//            return FALSE;
//        endif;
//        return TRUE;
//    }
//
//
//    protected function _getDistributionFile( string $href, string $tempPathToStoreDownloadedFiles, string $finalDownloadPath = '/' ) {
//
//    }
//
//
//    /**
//     * Headless Chromium creates a temp file ending with '.crdownload' that it streams the data into.
//     * Don't count that file.
//     * If the download is not complete, then set the $files var to an empty array to force
//     * the code to stay in the DoWhile loop.
//     *
//     * @param array $files
//     *
//     * @return bool
//     */
//    private function _downloadComplete( array $files ): bool {
//        array_shift( $files ); // Remove .
//        array_shift( $files ); // Remove ..
//
//        if ( ! isset( $files[ 0 ] ) ):
//            return FALSE;
//        endif;
//        $fileName = $files[ 0 ];
//
//        $needle = '.crdownload';
//        if ( str_ends_with( $fileName, $needle ) ):
//            return FALSE;
//        endif;
//        return TRUE;
//    }
//
//    /**
//     * @param array $files An array of files from the scandir() call above.
//     *
//     * @return string The filename of the downloaded file from US Bank.
//     * @throws \Exception This should never happen because of error checking above where this method is called.
//     */
//    protected function _getFilenameFromFiles( array $files ): string {
//        array_shift( $files ); // Remove .
//        array_shift( $files ); // Remove ..
//        if ( ! isset( $files[ 0 ] ) ):
//            throw new \Exception( "Unable to find the downloaded file in the files array." );
//        endif;
//
//        return $files[ 0 ];
//    }
//
//
//    /**
//     * This method assumes that $href refers to a SERIES page.
//     * @param string $html
//     * @param string $href A link to a SERIES page.
//     * @return array
//     * @throws \Exception
//     */
//    private function _getSecurityNamePartsFromHtmlOnSeriesPage( string $html, string $href ): array {
//        $dom = new \DOMDocument();
//        @$dom->loadHTML( $html );
//        $xpath     = new \DOMXPath( $dom );
//        $classname = 'c67';
//        $nodes     = $xpath->query( "//*[contains(@class, '$classname')]" );
//        $ul        = $nodes->item( 0 );
//        $lis       = $ul->childNodes;
//
//        $bulletItems = [];
//        /**
//         * @var \DOMNode $li
//         */
//        foreach ( $lis as $li ):
//            $bulletItems[] = trim( $li->textContent );
//        endforeach;
//
//        // Remove empty elements
//        $bulletItems = array_filter( $bulletItems );
//
//        // Remove "Home" from the rest
//        array_shift( $bulletItems );
//
//        if ( 3 != count( $bulletItems ) ):
//            throw new \Exception( "Not the right number of security name parts in . " . $href );
//        endif;
//
//        $data                 = [];
//        $data[ self::type ]   = $bulletItems[ 0 ];
//        $data[ self::shelf ]  = $bulletItems[ 1 ];
//        $data[ self::series ] = $bulletItems[ 2 ];
//
//        return $data;
//    }
//
//
//    /**
//     * There is a bunch of "metadata" stored in the HTML row of the table.
//     * May as well rip it out and return it.
//     * @param \DOMElement $tr
//     * @param string $href
//     * @return array
//     */
//    private function __getDistributionDateStatementRowData( \DOMElement $tr, string $href ): array {
//        $data  = [];
//        $tds   = $tr->getElementsByTagName( 'td' );
//        $links = $tr->getElementsByTagName( 'a' );
//
//        $data[ self::href ]           = $href;
//        $data[ self::access ]         = $this->__weHaveAccessToDistributionFile( $tds->item( 1 )->textContent );
//        $data[ self::current_cycle ]  = $this->__getCurrentCycleDate( $tds->item( 2 )->textContent );
//        $data[ self::next_cycle ]     = trim( $tds->item( 3 )->textContent );
//        $data[ self::next_available ] = trim( $tds->item( 4 )->textContent );
//
//
//        if ( $data[ self::access ] ):
//            $data[ self::link_to_doc ]                = self::BASE_URL . $links->item( 0 )->getAttribute( 'href' );
//            $data[ self::link_to_additional_history ] = self::BASE_URL . $links->item( 1 )->getAttribute( 'href' );
//        else:
//            $data[ self::link_to_doc ]                = '';
//            $data[ self::link_to_additional_history ] = '';
//        endif;
//
//        $data[ self::revised_date ] = $this->__getRevisedDate( $tds->item( 2 )->textContent );
//
//        return $data;
//    }
//
//
//    private function __getCurrentCycleDate( string $textContent ): string {
//        $parts = explode( ' ', trim( $textContent ) );
//        return trim( $parts[ 0 ] );
//    }
//
//
//    private function __getRevisedDate( string $textContent ): string {
//        $pattern = '/REVISED/';
//        $found   = preg_match( $pattern, $textContent, $matches );
//        if ( 1 !== $found ):
//            return '';
//        endif;
//        $parts = explode( 'REVISED', trim( $textContent ) );
//        return trim( $parts[ count( $parts ) - 1 ] );
//    }
//
//
//    /**
//     * @param string $textContent
//     * @return int
//     */
//    private function __weHaveAccessToDistributionFile( string $textContent ): int {
//        $pattern = '/Get Access/';
//        $found   = preg_match( $pattern, $textContent, $matches );
//        if ( 1 !== $found ):
//            return 1;
//        endif;
//        return 0;
//    }
//
//
//    /**
//     * A little helper function to determine if the TABLE row in question
//     * contains Distribution File data.
//     * @param \DOMElement $tr
//     * @return bool
//     */
//    private function _rowContainsDistributionFile( \DOMElement $tr ): bool {
//        $tds = $tr->getElementsByTagName( 'td' );
//
//        /**
//         * @var \DOMElement $td
//         */
//        foreach ( $tds as $td ):
//            $text = trim( $td->textContent );
//            if ( 'Distribution Date Statement' == $text ):
//                return TRUE;
//            endif;
//        endforeach;
//        return FALSE;
//    }
//
//
//    /**
//     * @param string $href
//     * @return bool
//     */
//    private function _isDistributionFileLink( string $href ): bool {
//        $pattern = '/a\/serieslist\.html\?shelfId=/';
//        $found   = preg_match( $pattern, $href, $matches );
//        if ( 1 !== $found ):
//            return FALSE;
//        endif;
//        return TRUE;
//    }
//
//
//    /**
//     * Examine the HTML. If CTS couldn't find the CUSIP, then we get a
//     * very specific HTML title tag back. Search there.
//     * @param string $html
//     * @return bool
//     * @throws WrongNumberOfTitleElementsException
//     */
//    protected function _cusipNotFound( string $html ): bool {
//        $dom = new \DOMDocument();
//        @$dom->loadHTML( $html );
//
//        /**
//         * @var \DOMNodeList $elements
//         */
//        $elements = $dom->getElementsByTagName( 'title' );
//
//        if ( 1 != $elements->count() ):
//            throw new WrongNumberOfTitleElementsException( $elements->count() . " were found. Should only see 1.",
//                                                           0,
//                                                           NULL,
//                                                           $elements,
//                                                           $html );
//        endif;
//
//        $titleText = trim( $elements->item( 0 )->textContent );
//        $pattern   = '/^Cusipnotfound.*$/';
//
//
//        $matches = [];
//        $success = preg_match( $pattern, $titleText, $matches );
//        if ( 1 === $success ):
//            return TRUE;
//        endif;
//
//        return FALSE;
//    }


}