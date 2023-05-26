<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions;


/**
 * It should be incredibly RARE that a sheet is downloaded that does not have a date in the headers.
 * Most likely, this Exception signifies that I need to make an addition to the _getTheDate() method.
 */
class NoDatesInTabsException extends \Exception {

    public array $tabs = [];

    public function __construct( string $message = "", int $code = 0, ?\Throwable $previous = NULL, array $tabs = [] ) {
        parent::__construct( $message, $code, $previous );

        $this->tabs = $tabs;
    }
}