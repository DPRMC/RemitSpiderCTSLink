<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers\Generic;

use DPRMC\RemitSpiderCTSLink\Helpers\AbstractHelper;
use DPRMC\RemitSpiderCTSLink\Helpers\Debug;
use DPRMC\RemitSpiderCTSLink\Helpers\Generic\Exceptions\LoggedOutException;
use DPRMC\RemitSpiderCTSLink\Helpers\Generic\Exceptions\UnknownUrlException;
use DPRMC\RemitSpiderCTSLink\Models\CTSLinkShelf;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Page;


class SeriesLinkHelper extends AbstractHelper {

    const BASE_URL = 'https://www.ctslink.com';

    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        parent::__construct( $Page, $Debug, $timezone );
    }


    /**
     * @param string $seriesListHref
     * @param string|NULL $debugHtml Raw html in a saved file. For debugging, so I don't have to query their site.
     * @param bool $forceSeriesPage For debugging. It sets the current url to force the parser down a logic path. It needs to mesh with the HTML set above
     * @return array
     * @throws LoggedOutException
     * @throws UnknownUrlException
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\ResponseHasError
     * @throws \HeadlessChromium\Exception\NavigationExpired
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     */
    public function getLinks( string $seriesListHref, string $debugHtml = NULL, bool $forceSeriesPage = FALSE ): array {
        if ( $debugHtml ):
            $html       = $debugHtml;
            $currentUrl = $forceSeriesPage ? 'serieslist.html' : 'seriesdocs.html';
        else:
            $this->Page->navigate( self::BASE_URL . $seriesListHref )->waitForNavigation();
            $html       = $this->Page->getHtml();
            $currentUrl = $this->Page->getCurrentUrl();
        endif;


        // These are the pages that have the "Additional History" links on them.
        $seriesDocsPageUrls = [];

        // These are pages that have links to the docs.
        if ( $this->_isSeriesDocsPage( $currentUrl ) ):
            $seriesDocsPageUrls[] = str_replace( self::BASE_URL, '', $currentUrl);
        // These are pages that have links to the SeriesDocs pages (that get caught above)
        elseif ( $this->_isSeriesListPage( $currentUrl ) ):
            $seriesDocsPageUrls = $this->_getSeriesDocPageLinksFromListPage( $html );
        elseif ( $this > $this->_isLoggedOut( $currentUrl ) ):
            throw new LoggedOutException( "Spider was logged out getting the Series Docs pages." );
        else:
            throw new UnknownUrlException( "Not sure how to process this URL: " . $currentUrl );

        endif;

        return $seriesDocsPageUrls;
    }


    /**
     * @param string $html
     * @return array
     */
    protected function _getSeriesDocPageLinksFromListPage( string $html ): array {
        $docPageLinks = [];
        $dom          = new \DOMDocument();
        @$dom->loadHTML( $html );

        $elements = $dom->getElementsByTagName( 'a' );

        /**
         * @var \DOMElement $element
         */
        foreach ( $elements as $element ):
            $href = $element->getAttribute( 'href' );
            if ( $this->_isSeriesDocsPage( $href ) ):
                $docPageLinks[] = $href;
            endif;
        endforeach;

        return $docPageLinks;
    }


    protected function _isSeriesListPage( string $currentUrl ): bool {
        if ( str_contains( $currentUrl, 'serieslist.html' ) ):
            return TRUE;
        endif;
        return FALSE;
    }


    protected function _isSeriesDocsPage( string $currentUrl ): bool {
        if ( str_contains( $currentUrl, 'seriesdocs.html' ) ):
            return TRUE;
        endif;
        return FALSE;
    }


    /**
     * @param string $currentUrl
     * @return bool
     */
    protected function _isLoggedOut( string $currentUrl ): bool {
        if ( str_contains( $currentUrl, 'loginrequired.html' ) ):
            return TRUE;
        endif;
        return FALSE;
    }


}