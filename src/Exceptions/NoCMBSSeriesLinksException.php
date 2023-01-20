<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * Thrown when trying to get CMBS series links, and none were found.
 * There is probably a better exception to be thrown here, but this is a good start.
 */
class NoCMBSSeriesLinksException extends \Exception {

    public string $html;


    public function __construct( string        $message = "",
                                 int           $code = 0,
                                 ?\Throwable   $previous = NULL,
                                 string        $html = '' ) {
        parent::__construct( $message, $code, $previous );
        $this->html          = $html;
    }
}