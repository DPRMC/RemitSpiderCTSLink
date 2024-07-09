<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\Exceptions;


class DifferentSpellingOfTransactionIdNeededException extends \Exception {

    public string $fileToLookAt = '';
    public string $sheetName    = '';

    public array $existingSpellingsOfTransactionId = [];

    public array $allRows = [];

    public array $suspectedNewSpellings = [];

    const TRANSACTION_ID_SEARCH_STRING = 'rans';

    public function __construct( string      $message = "",
                                 int         $code = 0,
                                 ?\Throwable $previous = NULL,
                                 string      $fileToLookAt = '',
                                 string      $sheetName = '',
                                 array       $existingSpellingsOfTransactionId = [],
                                 array       $allRows = [] ) {
        parent::__construct( $message, $code, $previous );
        $this->fileToLookAt                     = $fileToLookAt;
        $this->sheetName                        = $sheetName;
        $this->existingSpellingsOfTransactionId = $existingSpellingsOfTransactionId;
        $this->allRows                          = $allRows;

        $this->suspectedNewSpellings = $this->_getSuspectedNewSpellingOfTransactionId();
    }

    protected function _getSuspectedNewSpellingOfTransactionId(): array {
        $suspectedNewSpellings = [];

        foreach ( $this->allRows as $i => $rows ):
            foreach ( $rows as $j => $row ):
                if ( is_null( $row ) ):
                    continue;
                endif;

                if ( is_array( $row ) ):
                    foreach ( $row as $k => $value ):
                        if ( str_contains( $value, self::TRANSACTION_ID_SEARCH_STRING ) ):
                            $suspectedNewSpellings[] = $value;
                        endif;
                    endforeach;

                else:
                    if ( str_contains( $row, self::TRANSACTION_ID_SEARCH_STRING ) ):
                        $suspectedNewSpellings[] = $row;
                    endif;
                endif;


            endforeach;
        endforeach;

        return $suspectedNewSpellings;
    }
}