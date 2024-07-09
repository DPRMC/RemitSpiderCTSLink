<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions;


use Illuminate\Support\Facades\Log;

class DifferentSpellingOfTransactionIdNeededException extends \Exception {

    public string $fileToLookAt = '';
    public string $sheetName    = '';

    public array $existingSpellingsOfTransactionId = [];

    public array $allRows = [];

    public array $suspectedNewSpellings = [];

    public function __construct( string $message = "", int $code = 0, ?\Throwable $previous = NULL, string $fileToLookAt = '', string $sheetName = '', array $existingSpellingsOfTransactionId = [], array $allRows = [] ) {
        parent::__construct( $message, $code, $previous );
        $this->fileToLookAt = $fileToLookAt;
        $this->sheetName    = $sheetName;
        $this->existingSpellingsOfTransactionId = $existingSpellingsOfTransactionId;
        $this->allRows           = $allRows;

        $this->suspectedNewSpellings = $this->_getSuspectedNewSpellingOfTransactionId();
    }

    protected function _getSuspectedNewSpellingOfTransactionId(): array {
        foreach($this->allRows as $i => $rows):
            foreach($rows as $j => $row):
                dump($row);
            endforeach;
        endforeach;
    }
}