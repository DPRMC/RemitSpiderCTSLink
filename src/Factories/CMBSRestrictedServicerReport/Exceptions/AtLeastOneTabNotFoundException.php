<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions;


class AtLeastOneTabNotFoundException extends \Exception {

    public array  $tabsFound          = [];
    public string $fileWithNewTabName = '';

    public function __construct( string $message = "", int $code = 0, ?\Throwable $previous = NULL, array $tabsFound = [], string $fileWithNewTabName = '' ) {
        parent::__construct( $message, $code, $previous );

        $this->tabsFound          = $tabsFound;
        $this->fileWithNewTabName = $fileWithNewTabName;
    }
}