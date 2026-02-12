<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers;

use DPRMC\RemitSpiderCTSLink\Exceptions\NoAccessToSecurityException;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Page;

/**
 * Parses the Additional Documents tab for a CMBS series.
 */
class CMBSAdditionalDocsHelper extends CMBSHelper {

    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        parent::__construct( $Page, $Debug, $timezone );
    }

    /**
     * https://www.ctslink.com/a/seriesdocs.html?shelfId=WFCM&seriesId=2013LC12&tab=ADDDOC
     * @param string $shelf
     * @param string $series
     * @param string|null $html
     * @return array
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
    public function getAdditionalDocumentLinks( string $shelf, string $series, string $html = NULL ): array {
        $documentLinks = [];
        $additionalDocsLink = self::SERIES_DOCS_URL . 'shelfId=' . $shelf . '&seriesId=' . $series . '&tab=ADDDOC';

        if ( NULL === $html ):
            $this->Debug->_debug( " Navigating to: " . $additionalDocsLink );
            $this->Page->navigate( $additionalDocsLink )->waitForNavigation();
            $this->Debug->_screenshot( urlencode( $additionalDocsLink ) );
            $this->Debug->_html( urlencode( $additionalDocsLink ) );
            $html = $this->Page->getHtml();
        endif;

        if ( str_contains( strtolower( $html ), strtolower( 'Get Access' ) ) ):
            throw new NoAccessToSecurityException( "We do not have access to this Series: " . $additionalDocsLink,
                                                   0,
                                                   NULL,
                                                   $shelf,
                                                   $series );
        endif;

        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );
        $xpath = new \DOMXPath( $dom );

        $rows = $xpath->query( "//table[@id='SeriesDocument']//tr[td]" );

        /**
         * @var \DOMElement $row
         */
        foreach ( $rows as $row ):
            $name = trim( $xpath->evaluate( "string(td[@header='c1'])", $row ) );
            $postingDate = trim( $xpath->evaluate( "string(td[@header='c4'])", $row ) );
            $revisedDate = trim( $xpath->evaluate( "string(td[@header='c5'])", $row ) );

            $href = '';
            $key = '';

            $linkNode = $xpath->query( ".//a[contains(@href, '/a/document.html?key=')]", $row )->item( 0 );
            if ( $linkNode ):
                $href = $linkNode->getAttribute( 'href' );
            endif;

            $inputNode = $xpath->query( ".//input[@name='documentChkBx' and @value]", $row )->item( 0 );
            if ( $inputNode ):
                $key = $inputNode->getAttribute( 'value' );
            endif;

            if ( $href === '' && $key === '' ):
                continue;
            endif;

            if ( $key === '' && $href !== '' ):
                $hrefParts = explode( '=', $href );
                $key = $hrefParts[ 1 ] ?? '';
            endif;

            if ( $href === '' && $key !== '' ):
                $href = '/a/document.html?key=' . $key;
            endif;

            $documentLinks[] = [
                'shelf'       => $shelf,
                'series'      => $series,
                'name'        => $name,
                'link'        => CMBSHelper::BASE_URL . $href,
                'postingDate' => $postingDate,
                'revisedDate' => $revisedDate,
                'key'         => $key,
            ];
        endforeach;

        return $documentLinks;
    }
}
