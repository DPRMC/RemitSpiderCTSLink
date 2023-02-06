<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers;

use DPRMC\RemitSpiderCTSLink\Exceptions\WrongNumberOfTitleElementsException;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Exception\CommunicationException\CannotReadResponse;
use HeadlessChromium\Exception\CommunicationException\InvalidResponse;
use HeadlessChromium\Exception\CommunicationException\ResponseHasError;
use HeadlessChromium\Exception\NavigationExpired;
use HeadlessChromium\Exception\NoResponseAvailable;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Page;

/**
 * QUERY SELECTOR EXAMPLES: protected string $querySelectorForLinks = '#results-table > tbody > tr';
 */
class CMBSDistributionFilesHelper extends CMBSHelper {

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


    /**
     * @param Page $Page
     * @param Debug $Debug
     * @param string $timezone
     */
    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        parent::__construct( $Page, $Debug, $timezone );
    }





    /**
     * Step two in the process.
     * Given a SHELF href (or potentially SERIES href if there is only 1 SERIES under that SHELF)
     * This method will return all the Distribution Date Statement Data.
     * Given all of that data, the calling script can determine which PDFs it downloads.
     * @param string $potentialShelfLink
     * @return array
     * @throws CannotReadResponse
     * @throws CommunicationException
     * @throws InvalidResponse
     * @throws NavigationExpired
     * @throws NoResponseAvailable
     * @throws OperationTimedOut
     * @throws ResponseHasError
     */
    public function getDistributionDateStatementDataFromLink( string $potentialShelfLink ): array {
        $distributionFileData = [];

        $seriesLinks = $this->getSeriesLinks( $potentialShelfLink);

        foreach($seriesLinks as $i => $seriesLink):
            $this->Page->navigate( $seriesLink )->waitForNavigation();
            $html = $this->Page->getHtml();
            $seriesData = $this->_getSecurityNamePartsFromHtmlOnSeriesPage( $html, $potentialShelfLink );

            $newDistributionFileData = $this->_getDistributionDateStatementDataFromHtml( $html,
                                                                                         $seriesData,
                                                                                         $potentialShelfLink );
            if ( $newDistributionFileData ):
                $distributionFileData = array_merge( $distributionFileData, $newDistributionFileData );
            endif;
        endforeach;

        return $distributionFileData;
    }


    /**
     * @param string $html
     * @param array $seriesData
     * @param string $href
     * @return array
     */
    protected function _getDistributionDateStatementDataFromHtml( string $html, array $seriesData, string $href ): array {
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
                $data[]                  = $rowData;
            endif;
        endforeach;
        return $data;
    }


    public function getLinksWeWereUnableToPull(): array {
        return $this->linksWeWereUnableToPull;
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


    protected function _getDistributionFile( string $href, string $tempPathToStoreDownloadedFiles, string $finalDownloadPath = '/' ) {

    }


    /**
     * Headless Chromium creates a temp file ending with '.crdownload' that it streams the data into.
     * Don't count that file.
     * If the download is not complete, then set the $files var to an empty array to force
     * the code to stay in the DoWhile loop.
     *
     * @param array $files
     *
     * @return bool
     */
    private function _downloadComplete( array $files ): bool {
        array_shift( $files ); // Remove .
        array_shift( $files ); // Remove ..

        if ( ! isset( $files[ 0 ] ) ):
            return FALSE;
        endif;
        $fileName = $files[ 0 ];

        $needle = '.crdownload';
        if ( str_ends_with( $fileName, $needle ) ):
            return FALSE;
        endif;
        return TRUE;
    }

    /**
     * @param array $files An array of files from the scandir() call above.
     *
     * @return string The filename of the downloaded file from US Bank.
     * @throws \Exception This should never happen because of error checking above where this method is called.
     */
    protected function _getFilenameFromFiles( array $files ): string {
        array_shift( $files ); // Remove .
        array_shift( $files ); // Remove ..
        if ( ! isset( $files[ 0 ] ) ):
            throw new \Exception( "Unable to find the downloaded file in the files array." );
        endif;

        return $files[ 0 ];
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