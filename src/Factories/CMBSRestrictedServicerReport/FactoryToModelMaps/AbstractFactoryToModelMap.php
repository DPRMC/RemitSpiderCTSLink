<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps;

use App\Console\Custodians\CTSLink\CMBS\AbstractCMBSCommand;
use DPRMC\FIMS\API\V1\Console\Commands\F2BaseCommand;
use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsCmbsRestrictedServicerReportCfsr;
use Illuminate\Support\Facades\Storage;

abstract class AbstractFactoryToModelMap implements InterfaceFactoryToModelMap {


    /**
     * @param array $map
     * @param string $jsonField
     * @param string $filePath
     * @return string
     * @throws FieldNotFoundException
     */
    public static function getField( array $map, string $jsonField, string $filePath = '' ): string {
        foreach ( $map as $field => $spellings ):
            // If the parsed json has the same field name as the eloquent model.
            if ( $jsonField == $field ):
                return $jsonField;
            endif;

            // It wasn't an exact match, so let's try the variations of spellings.
            foreach ( $spellings as $spelling ):
                if ( $jsonField == $spelling ):
                    return $field;
                endif;
            endforeach;
        endforeach;

        throw new FieldNotFoundException( "CTS: Add to MAP array. The json field did not have a mapping to a property in the eloquent model: " . $jsonField . " so search for something spelled like this field.",
                                          0,
                                          NULL,
                                          $map,
                                          $jsonField,
                                          $filePath );
    }

}
