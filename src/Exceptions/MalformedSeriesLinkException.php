<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * If a series link has the wrong format, this exception gets thrown.
 */
class MalformedSeriesLinkException extends \Exception {

    public string $link;

    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 string      $link = '' ) {
        parent::__construct( $message, $code, $previous );
        $this->link = $link;
    }
}