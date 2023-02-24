<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class TotalLoanFactory extends AbstractTabFactory {

    public function parse( array $rows ): array {
        $this->_setDate( $rows );
        $this->_setCleanHeaders( $rows, [ 'Transaction ID' ] );
        $this->_setParsedRows( $rows );

        return $this->cleanRows;
    }
}