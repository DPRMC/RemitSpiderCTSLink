<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class TotalLoanFactory extends AbstractTabFactory {

    protected array $firstColumnValidTextValues = [ 'Transaction ID', 'Trans' ];

    protected function _removeInvalidRows( array $rows = [] ): array {
        return $rows;
    }

}