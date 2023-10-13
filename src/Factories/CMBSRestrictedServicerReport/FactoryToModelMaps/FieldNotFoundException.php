<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps;

class FieldNotFoundException extends \Exception {

    public array  $map       = [];
    public string $jsonField = '';
    public string $filePath  = '';

    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 array       $map = [],
                                 string      $jsonField = '',
                                 string      $filePath = NULL ) {
        parent::__construct( $message, $code, $previous );

        $this->map       = $map;
        $this->jsonField = $jsonField;
        $this->filePath  = $filePath;
    }

}
