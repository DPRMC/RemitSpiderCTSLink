<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions;


class ProbablyExcelDateException extends \Carbon\Exceptions\InvalidFormatException {

    public string $sheetName = '';

    public function __construct( string $message = "", int $code = 0, ?\Throwable $previous = NULL, string $sheetName = '' ) {
        parent::__construct( $message, $code, $previous );

        $this->sheetName = $sheetName;
    }
}