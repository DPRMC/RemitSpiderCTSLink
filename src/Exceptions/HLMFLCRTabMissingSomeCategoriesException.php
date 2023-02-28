<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * An alert that I probably need to patch the parser.
 */
class HLMFLCRTabMissingSomeCategoriesException extends \Exception {

    public array $categoryIndexes;


    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 ?array      $indexes = [] ) {
        parent::__construct( $message, $code, $previous );
        $this->categoryIndexes = $indexes;
    }
}