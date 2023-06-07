<?php

namespace DPRMC\RemitSpiderCTSLink\Models;


class CTSLinkShelf {

    public string $productType    = '';
    public string $issuerName     = '';
    public string $seriesListHref = '';
    public string $shelfDocsHref  = '';
    public string $shelf          = '';

    public function __construct( string $productType, string $issuerName, string $seriesListHref, string $shelfDocsHref = '' ) {
        $this->productType    = $productType;
        $this->issuerName     = $issuerName;
        $this->seriesListHref = $seriesListHref;
        $this->shelfDocsHref  = $shelfDocsHref;
        $this->shelf          = $this->_getShelfFromHref( $seriesListHref );
    }


    /**
     * @param string $href /a/serieslist.html?shelfId=WFCM
     * @return string WFCM
     */
    protected function _getShelfFromHref( string $href ): string {
        $parts = explode( '=', $href );
        return end( $parts );
    }

}