<?php

namespace DPRMC\RemitSpiderCTSLink\Models;




class CTSLinkShelf {

    public string $productType = '';
    public string $issuerName = '';
    public string $href = '';

    public function __construct(string $productType, string $issuerName, string $href) {
        $this->productType = $productType;
        $this->issuerName = $issuerName;
        $this->href = $href;
    }

}