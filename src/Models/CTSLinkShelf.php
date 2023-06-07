<?php

namespace DPRMC\RemitSpiderCTSLink\Models;


class CTSLinkShelf {

    public string $productType = '';
    public string $issuerName  = '';
    public string $seriesHref  = '';
    public string $shelfHref   = '';
    public string $shelf       = '';

    public function __construct( string $productType, string $issuerName, string $seriesHref, string $shelfHref = '' ) {
        $this->productType = $productType;
        $this->issuerName  = $issuerName;
        $this->seriesHref  = $seriesHref;
        $this->shelfHref   = $shelfHref;
        $this->shelf       = $this->_getShelfFromHref( $seriesHref );
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