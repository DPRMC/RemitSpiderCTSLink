<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * When parsing an Excel sheet from CTS link, the date of the file is in different locations in
 * the header. I do my best to find it, but if I can't, I throw this exception.
 * Which means I need to change my parsing code.
 */
class DateNotFoundInHeaderException extends \Exception {

    public array  $rowsChecked;


    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 ?array      $rowsChecked = [] ) {
        parent::__construct( $message, $code, $previous );
        $this->rowsChecked = $rowsChecked;
    }
}