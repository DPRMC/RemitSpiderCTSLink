<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * I have a little function that generates a CREATE TABLE SQL statement.
 * If there are no rows from which to get the field names, then this
 * exception gets thrown to control program flow.
 */
class UnableToGenerateCreateTableException extends \Exception {

    public string $tableName;

    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 ?string     $tableName = NULL ) {
        parent::__construct( $message, $code, $previous );
        $this->tableName = $tableName;
    }
}