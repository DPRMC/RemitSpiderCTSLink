<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class WatchlistFactory extends AbstractTabFactory {

    public function parse( array $rows ): array {
        $this->_setDate( $rows );
        $this->_setCleanHeaders( $rows, [ 'Trans ID', 'Trans Id' ] );

        $this->_setParsedRows( $rows );

        return $this->cleanRows;
    }
}