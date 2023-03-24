<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions\DistributionFileExceptions;


/**
 * This exception gets thrown when a PDF has a sheet name that I don't
 * have a case for.
 */
class NewSectionNameFoundException extends \Exception {

    /**
     * @var string This is the new string that was found as a tab name.
     */
    public string $newSectionName = '';

    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 string      $newSectionName = ''
    ) {
        parent::__construct( $message, $code, $previous );

        $this->newSectionName = $newSectionName;
    }
}