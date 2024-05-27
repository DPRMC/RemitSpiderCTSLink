<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport;

class REOSRFactory extends AbstractTabFactory {

    protected array $firstColumnValidTextValues = [ 'Trans ID', 'Trans' ];

    protected function _removeInvalidRows( array $rows = [] ): array {
        $validRows = [];
        foreach( $rows as $row):
            if( is_null($row[1])):
                dump("Not adding the following REOSR row.");
                dump("Check to see if its valid.");
                dump($row);
            else:
                $validRows[] = $row;
            endif;
        endforeach;

        return $validRows;
    }

}