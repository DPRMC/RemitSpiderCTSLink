<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * When munging Excel headers to MySQL columns, sometimes the values are too long.
 * In that case, I need to add a new str_replace() in the parse code to translate
 * the long name into a shorter name.
 */
class HeadersTooLongForMySQLException extends \Exception {

    public array   $headersThatAreTooLong;
    public ?string $debugSheetName = NULL;


    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 ?array      $headersThatAreTooLong = [],
                                 ?string     $debugSheetName = null ) {
        parent::__construct( $message, $code, $previous );
        $this->headersThatAreTooLong = $headersThatAreTooLong;
        $this->debugSheetName        = $debugSheetName;
    }
}