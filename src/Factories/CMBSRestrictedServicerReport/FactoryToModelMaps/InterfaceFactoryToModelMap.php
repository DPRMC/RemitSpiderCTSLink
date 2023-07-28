<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps;


interface InterfaceFactoryToModelMap {


    /**
     * @param array $map
     * @param string $jsonField
     * @param string $filePath
     * @return string
     * @throws FieldNotFoundException
     */
    public static function getField( array $map, string $jsonField, string $filePath = '' );

}
