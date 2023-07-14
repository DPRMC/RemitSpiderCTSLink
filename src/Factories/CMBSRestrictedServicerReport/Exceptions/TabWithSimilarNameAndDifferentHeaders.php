<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions;


/**
 * This exception gets thrown when the CTS CMBS Restricted Servicer Report parser
 * finds another tab with a name similar to a PREVIOUS tab, BUT has at least one header that
 * was not found in the original tab.
 *
 * This exception is basically a placeholder, until I come across an example of a sheet/tab
 * that meets this criteria. When I see this Exception in bugsnag, I need to revisit the code
 * that combines global headers with the local headers and figure out how to reconcile them.
 *
 * Most likely I will have to have some if/rename code in there.
 */
class TabWithSimilarNameAndDifferentHeaders extends \Exception {

    public string $localHeader;
    public array  $globalHeaders;

    public function __construct( string $message = "", int $code = 0, ?\Throwable $previous = NULL, string $localHeader = '', array $globalHeaders = [] ) {
        parent::__construct( $message, $code, $previous );

        $this->localHeader   = $localHeader;
        $this->globalHeaders = $globalHeaders;
    }
}