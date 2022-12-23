<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * Thrown when the Spider tried to click on the login button, and nothing
 * happened for like 30 seconds.
 */
class LoginTimedOutException extends \Exception {
    public function __construct( string $message = "", int $code = 0, ?\Throwable $previous = NULL ) {
        parent::__construct( $message, $code, $previous );
    }
}