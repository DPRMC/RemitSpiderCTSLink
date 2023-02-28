<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * An alert that I probably need to patch the parser.
 */
class DLSRTabMissingSomeDelinquencyCategoriesException extends \Exception {

    public array $delinquencyIndexes;


    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 ?array      $delinquencyIndexes = [] ) {
        parent::__construct( $message, $code, $previous );
        $this->delinquencyIndexes = $delinquencyIndexes;
    }
}