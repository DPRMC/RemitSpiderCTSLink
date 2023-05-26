<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions;


class NoDatesInTabsException extends \Exception {

    public array $tabs = [];

    public function __construct( string $message = "", int $code = 0, ?\Throwable $previous = NULL, array $tabs = [] ) {
        parent::__construct( $message, $code, $previous );

        $this->tabs = $tabs;
    }
}