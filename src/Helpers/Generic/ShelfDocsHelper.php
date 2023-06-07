<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers\Generic;

use DPRMC\RemitSpiderCTSLink\Helpers\AbstractHelper;
use DPRMC\RemitSpiderCTSLink\Helpers\Debug;
use DPRMC\RemitSpiderCTSLink\Models\CTSLinkShelf;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Page;

class ShelfDocsHelper extends AbstractHelper {
    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        parent::__construct( $Page, $Debug, $timezone );
    }

    public function getLinks(string $href): array {
        $links = [];

        return $links;
    }
}