<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * This will get thrown if there are no fields for a tab in a given sheet.
 * Find a new sheet to use to generate models.
 */
class UnableToCreateEloquentFileException extends \Exception {

    public string $propertyName;

    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 ?string     $propertyName = NULL ) {
        parent::__construct( $message, $code, $previous );
        $this->propertyName = $propertyName;
    }
}