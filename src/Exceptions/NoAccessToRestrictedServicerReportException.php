<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * Thrown when we do not have access to the Restricted Servicer Reports for a given Series.
 */
class NoAccessToRestrictedServicerReportException extends \Exception {

    protected string $shelf  = '';
    protected string $series = '';

    public function __construct( string        $message = "",
                                 int           $code = 0,
                                 ?\Throwable   $previous = NULL,
                                 string        $shelf = '',
                                 string        $series = '' ) {
        parent::__construct( $message, $code, $previous );
        $this->shelf  = $shelf;
        $this->series = $series;
    }
}