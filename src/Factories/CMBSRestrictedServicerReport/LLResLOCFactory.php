<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class LLResLOCFactory extends AbstractTabFactory {
    protected array $firstColumnValidTextValues = [ 'Trans ID', 'Trans' ];

    protected function _removeInvalidRows( array $rows = [] ): array {
        return $rows;
    }
}