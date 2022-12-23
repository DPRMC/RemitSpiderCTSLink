<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * When searching for a security by CUSIP, this Exception will be
 * thrown if CTS Link can not find this CUSIP.
 */
class CUSIPNotFoundException extends \Exception {

    public string $cusip;

    public string $html;


    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 ?string     $cusip = NULL,
                                 string      $html = '' ) {
        parent::__construct( $message, $code, $previous );
        $this->cusip = $cusip;
        $this->html  = $html;
    }
}