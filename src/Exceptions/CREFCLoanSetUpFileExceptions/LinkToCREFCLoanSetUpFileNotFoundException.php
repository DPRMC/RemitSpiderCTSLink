<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions\CREFCLoanSetUpFileExceptions;

/**
 * Just in case a given SHELF and SERIES does not have a CREFC Loan Set Up File.
 */
class LinkToCREFCLoanSetUpFileNotFoundException extends \Exception {

    public string $shelf;
    public string $series;


    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 ?string     $shelf = NULL,
                                 string      $series = '' ) {
        parent::__construct( $message, $code, $previous );
        $this->shelf  = $shelf;
        $this->series = $series;
    }
}