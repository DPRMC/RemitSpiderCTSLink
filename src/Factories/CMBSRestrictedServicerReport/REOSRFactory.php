<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class REOSRFactory extends AbstractTabFactory {

    public function parse( array $rows ): array {
        $this->_setDate( $rows );
        $this->_setCleanHeaders( $rows, [ 'Trans ID' ] );
        $this->_setParsedRows( $rows );

        return $this->cleanRows;
    }
}