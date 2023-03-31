<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * Some sheets don't have data immediately following the header row.
 * So I had to include some logic looking for the first row of data.
 */
class NoDataInTabException extends \Exception {

    public array  $rowsChecked;
    public array  $cleanHeaders;
    public string $sheetName = '';


    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 ?array      $rowsChecked = [],
                                 ?array      $cleanHeaders = [] ) {
        parent::__construct( $message, $code, $previous );
        $this->rowsChecked  = $rowsChecked;
        $this->cleanHeaders = $cleanHeaders;
    }
}