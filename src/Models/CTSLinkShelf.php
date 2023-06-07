<?php

namespace DPRMC\RemitSpiderCTSLink\Models;


class CTSLinkShelf {

    public string $productType = '';
    public string $issuerName  = '';
    public string $href        = '';
    public string $shelf       = '';

    public function __construct( string $productType, string $issuerName, string $href ) {
        $this->productType = $productType;
        $this->issuerName  = $issuerName;
        $this->href        = $href;
        $this->shelf       = $this->_getShelfFromHref( $href );
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