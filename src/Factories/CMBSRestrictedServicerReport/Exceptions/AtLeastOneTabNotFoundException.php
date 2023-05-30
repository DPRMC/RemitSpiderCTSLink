<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions;


class AtLeastOneTabNotFoundException extends \Exception {

    public array  $tabsFound          = [];
    public string $fileWithNewTabName = '';
    public array  $sheetNames         = [];

    public function __construct( string $message = "", int $code = 0, ?\Throwable $previous = NULL, array $tabsFound = [], string $fileWithNewTabName = '', array $sheetNames = [] ) {
        parent::__construct( $message, $code, $previous );

        $this->tabsFound          = $tabsFound;
        $this->fileWithNewTabName = $fileWithNewTabName;
        $this->sheetNames         = $sheetNames;
    }
}