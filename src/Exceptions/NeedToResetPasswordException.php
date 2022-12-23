<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * Thrown when CTS Link is requiring us to reset our password.
 */
class NeedToResetPasswordException extends \Exception {
    public function __construct( string $message = "", int $code = 0, ?\Throwable $previous = NULL ) {
        parent::__construct( $message, $code, $previous );
    }
}