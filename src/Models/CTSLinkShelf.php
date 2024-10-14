<?php

namespace DPRMC\RemitSpiderCTSLink\Models;


use Carbon\Carbon;

class CTSLinkShelf {

    public string $productType    = '';
    public string $issuerName     = '';
    public string $seriesListHref = '';
    public string $shelfDocsHref  = '';
    public string $shelf          = '';

    public ?Carbon $currentCycle = NULL;
    public ?Carbon $nextCycle    = NULL;

    public function __construct( string $productType,
                                 string $issuerName,
                                 string $seriesListHref,
                                 string $shelfDocsHref = '',
                                 Carbon $currentCycle = NULL,
                                 Carbon $nextCycle = NULL ) {
        $this->productType    = $productType;
        $this->issuerName     = $issuerName;
        $this->seriesListHref = $seriesListHref;
        $this->shelfDocsHref  = $shelfDocsHref;
        $this->shelf          = $this->_getShelfFromHref( $seriesListHref );
        $this->currentCycle   = $currentCycle;
        $this->nextCycle      = $nextCycle;
    }


    /**
     * @param string $href /a/serieslist.html?shelfId=WFCM
     *
     * @return string WFCM
     */
    protected function _getShelfFromHref( string $href ): string {
        $parts = explode( '=', $href );
        return end( $parts );
    }

}