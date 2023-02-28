<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class AdvanceRecoveryFactory extends AbstractTabFactory {

    /**
     * @param array $rows
     * @return array
     * @throws \DPRMC\RemitSpiderCTSLink\Exceptions\DateNotFoundInHeaderException
     * @throws \DPRMC\RemitSpiderCTSLink\Exceptions\NoDataInTabException
     */
    public function parse( array $rows ): array {
        $this->_setDate( $rows, 4 );
        $this->_setCleanHeaders( $rows, [ 'Tran ID', 'Trans ID' ] );
        $this->_setParsedRows( $rows );

        return $this->cleanRows;
    }
}