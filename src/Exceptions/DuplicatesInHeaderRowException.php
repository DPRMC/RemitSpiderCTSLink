<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

use Exception;
use Throwable;

/**
 * If the header row is not composed of unique elements, the data associated with duplicate header value will overwrite
 * the data associated with the original header
 */
class DuplicatesInHeaderRowException extends Exception {

    /**
     * An array containing all the headers from the parsed document
     * @var array
     */
    public array  $headerRow;

    /**
     * An array containing all the duplicate headers found in the parsed document
     * @var array
     */
    public array  $duplicates;

    /**
     * @param array $headerRow
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct( array $headerRow = [], int $code = 0, ?Throwable $previous = NULL ) {
        $this->headerRow  = $headerRow;
        $this->duplicates = array_diff( $this->headerRow, array_unique( $this->headerRow ) );
        parent::__construct( $this->generateMessage(), $code, $previous );
    }

    /**
     * Generates the error message with the documentId and duplicate headers
     * @return string
     */
    protected function generateMessage() : string {
        $message = "Header row contains the following duplicate values: ";
        foreach( $this->duplicates as $duplicate ) :
            $message .= "'$duplicate', ";
        endforeach;

        return rtrim( $message, ", " );
    }
}