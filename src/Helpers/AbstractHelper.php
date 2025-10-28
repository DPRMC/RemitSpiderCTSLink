<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers;

use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Cookies\CookiesCollection;
use HeadlessChromium\Exception\ElementNotFoundException;
use HeadlessChromium\Page;

/**
 * QUERY SELECTOR EXAMPLES: protected string $querySelectorForLinks = '#results-table > tbody > tr';
 */
abstract class AbstractHelper {

    protected Page   $Page;
    protected Debug  $Debug;
    protected string $timezone;

    public CookiesCollection $cookies;

    const BASE_URL = 'https://www.ctslink.com';

    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        $this->Page     = $Page;
        $this->Debug    = $Debug;
        $this->timezone = $timezone;
    }


    /**
     * @param string $regex
     * @return bool
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\JavascriptException
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     */
    public function clickLinkWithMatchingLinkText( string $regex ): bool {

        $htmlContent = $this->Page->getHtml();

        // 1. Load the HTML content into DOMDocument
        $dom = new \DOMDocument();
        // Suppress warnings/errors for malformed HTML common in scraped pages
        @$dom->loadHTML($htmlContent);

        $xpath = new \DOMXPath($dom);

        // Select all anchor tags
        $links = $xpath->query('//a');
        $selector = null;

        // 2. Iterate and match the link text using PHP's regex
        foreach ($links as $linkElement) {
            /** @var \DOMElement $linkElement */
            $linkText = trim($linkElement->textContent);

            if (preg_match($regex, $linkText)) {
                // Found a match!

                // Priority 1: Use a unique ID (most reliable)
                if ($linkElement->hasAttribute('id') && !empty($linkElement->getAttribute('id'))) {
                    $selector = '#' . $linkElement->getAttribute('id');
                }
                // Priority 2: Use the HREF attribute to build a selector
                elseif ($linkElement->hasAttribute('href') && !empty($linkElement->getAttribute('href'))) {
                    $href = $linkElement->getAttribute('href');

                    // Escape quotes within the HREF attribute value for the CSS selector string
                    $safeHref = str_replace(['\\', '"'], ['\\\\', '\\"'], $href);

                    // Generate the attribute selector: a[href="..."]
                    $selector = 'a[href="' . $safeHref . '"]';
                }

                // If a usable selector (ID or HREF) was generated, break the loop
                if ($selector) {
                    break;
                }
            }
        }

        // 3. Use the generated selector with the find() method
        if ($selector) {
            try {
                // find() will locate the element (e.g., a[href="/about"]) and scroll to it.
                $this->Page->mouse()->find($selector)->click();
                return true;
            } catch (ElementNotFoundException $e) {
                // The HTML was stale, or multiple links had the same HREF/ID, and find() failed.
                throw new ElementNotFoundException("Link found by DOM but not uniquely found by Chromium selector ('{$selector}'): " . $e->getMessage(), 0, $e);
            }
        }

        return false; // No link found matching the regex
    }


    protected function getSelectorForElement( \DOMElement $element ): ?string {
        // Priority 1: Use ID (most unique)
        if ( $element->hasAttribute( 'id' ) && ! empty( $element->getAttribute( 'id' ) ) ) {
            return '#' . $element->getAttribute( 'id' );
        }

        // Priority 2: Use Class Names (if present)
        if ( $element->hasAttribute( 'class' ) && ! empty( $element->getAttribute( 'class' ) ) ) {
            $classNames = trim( $element->getAttribute( 'class' ) );
            // Convert 'class1 class2' to '.class1.class2'
            $selector = $element->tagName . '.' . str_replace( ' ', '.', $classNames );
            return $selector;
        }

        // Priority 3: Use the Tag Name (least unique, but better than nothing for a parent)
        // NOTE: For the root node (e.g., 'html' or 'body'), this is often sufficient.
        if ( $element->tagName ) {
            return strtolower( $element->tagName );
        }

        return NULL; // Could not generate a simple selector
    }


    public function clickLinkWithMatchingHref( string $regex ): void {

    }

}