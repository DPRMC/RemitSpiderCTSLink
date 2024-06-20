<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

use Exception;
use Throwable;

/**
 * Throw this exception if a header used to validate the row is missing
 */
class ValidationHeadersNotFoundException extends Exception {

    /**
     * An array containing the missing headers needed for row validation
     * @var array
     */
    public array  $missingValidationHeaders = [];


    /**
     * @param array $missingValidationHeaders
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct( array $missingValidationHeaders, int $code = 0, ?Throwable $previous = NULL ) {
        $this->missingValidationHeaders  = $missingValidationHeaders;
        parent::__construct( $this->generateMessage(), $code, $previous );
    }


    /**
     * Generates the error message with the documentId and duplicate headers
     * @return string
     */
    public function generateMessage() : string {
        $message = "The following header columns needed for validation are missing: ";
        foreach( $this->missingValidationHeaders as $missingValidationHeader ) :
            $message .= "'$missingValidationHeader', ";
        endforeach;

        return rtrim( $message, ", " );
    }
}
