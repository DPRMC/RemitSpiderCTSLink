<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps;

use App\Console\Custodians\CTSLink\CMBS\AbstractCMBSCommand;
use DPRMC\FIMS\API\V1\Console\Commands\F2BaseCommand;
use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsCmbsRestrictedServicerReportCfsr;
use Illuminate\Support\Facades\Storage;

abstract class AbstractFactoryToModelMap implements InterfaceFactoryToModelMap {

    public static array $jsonFieldsToIgnore = [];

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

        throw new FieldNotFoundException( "CTS: Add to MAP array. 'Look at the comment above to know which map.' The json field did not have a mapping to a property in the eloquent model: [" . $jsonField . "] so search for something spelled like this field.",
                                          0,
                                          NULL,
                                          $map,
                                          $jsonField,
                                          $filePath );
    }


    /**
     * @param array $map Ex: DlsrMap::$map
     * @param string $fieldNameVariation Ex: num_rentable_sq_ft_or_rooms
     * @return string
     * @throws FieldNotFoundException
     */
    public static function getCommonFieldName( array $map, string $fieldNameVariation ): string {
        foreach ( $map as $field => $spellings ):
            foreach ( $spellings as $spelling ):
                if ( $fieldNameVariation == $spelling ):
                    return $field;
                endif;
            endforeach;
        endforeach;

        throw new FieldNotFoundException( "CTS Problem Parsing: Add to MAP array. The json field did not have a mapping to a property in the eloquent model: " . $fieldNameVariation . " so search for something spelled like this field.",
                                          0,
                                          NULL,
                                          $map,
                                          $fieldNameVariation,
                                          null );
    }
}
