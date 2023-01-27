<?php

namespace DPRMC\RemitSpiderCTSLink\Exceptions;


class ExceededMaxAttemptsToDownloadFileException extends \Exception {

    public int $maxTimesToCheckForDownloadBeforeGivingUp;

    public string $hrefOfFileToDownload;


    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 ?int        $maxTimesToCheckForDownloadBeforeGivingUp = NULL,
                                 string      $hrefOfFileToDownload = '' ) {
        parent::__construct( $message, $code, $previous );
        $this->maxTimesToCheckForDownloadBeforeGivingUp = $maxTimesToCheckForDownloadBeforeGivingUp;
        $this->hrefOfFileToDownload                     = $hrefOfFileToDownload;
    }
}