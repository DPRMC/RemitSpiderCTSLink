<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers\Generic;

use DPRMC\RemitSpiderCTSLink\Helpers\AbstractHelper;
use DPRMC\RemitSpiderCTSLink\Helpers\Debug;
use DPRMC\RemitSpiderCTSLink\Models\CTSLinkShelf;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Page;


class DocLink {


    public function __construct( public string $nameOfFile = '',
                                 public string $currentCycle = '',
                                 public string $nextCycle = '',
                                 public string $nextAvailableDateTime = '',
                                 public string $additionalHistoryHref = '',
                                 public string $href = '',
                                 public bool   $hasAccess = FALSE ) {

    }
}