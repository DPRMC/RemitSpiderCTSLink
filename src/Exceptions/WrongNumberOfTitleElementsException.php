<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;

/**
 * Thrown when CTS Link is requiring us to reset our password.
 * I don't expect this Exception to every get thrown, but might as
 * well have a robust code base.
 */
class WrongNumberOfTitleElementsException extends \Exception {

    public \DOMNodeList $titleElements;

    public string $html;

    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param \DOMNodeList|null $elements Either 0 or >1 title elements in the HTML.
     */
    public function __construct( string        $message = "",
                                 int           $code = 0,
                                 ?\Throwable   $previous = NULL,
                                 ?\DOMNodeList $elements = NULL,
                                 string        $html = '' ) {
        parent::__construct( $message, $code, $previous );
        $this->titleElements = $elements;
        $this->html          = $html;
    }
}