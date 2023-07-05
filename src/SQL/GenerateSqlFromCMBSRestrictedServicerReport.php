<?php

namespace DPRMC\RemitSpiderCTSLink\SQL;


use DPRMC\RemitSpiderCTSLink\Exceptions\UnableToGenerateCreateTableException;
use DPRMC\RemitSpiderCTSLink\Models\CMBSRestrictedServicerReport\CMBSMonthlyAdministratorReport;

class GenerateSqlFromCMBSRestrictedServicerReport {


    public function __construct( public readonly CMBSMonthlyAdministratorReport $cmbsRestrictedServicerReport ) {
    }


    /**
     * @var array|string[] Indexes are the propety names in the CMBSRestrictedServicerReport, values are the table suffixes/names.
     */
    protected array $propertyToTableNames = [
        CMBSMonthlyAdministratorReport::watchlist       => 'watchlists',
        CMBSMonthlyAdministratorReport::reosr           => 'reosrs',
        CMBSMonthlyAdministratorReport::csfr            => 'cfsrs',
        CMBSMonthlyAdministratorReport::llResLOC        => 'll_res_locs',
        CMBSMonthlyAdministratorReport::totalLoan       => 'total_loans',
        CMBSMonthlyAdministratorReport::advanceRecovery => 'advance_recoveries',
        CMBSMonthlyAdministratorReport::dlsr            => 'dlsrs',
        CMBSMonthlyAdministratorReport::hlmfclr         => 'hlmfclrs',
    ];


    /**
     * @var array If a property doesn't have data, then I can't make a table to store it.
     */
    public array $failedTables = [];


    public function generateSQL( string $tablePrefix = 'custodian_cts_cmbs_restricted_servicer_report_' ): string {
        $createTableStatements = [];

//        dump( $this->propertyToTableNames );
//        dd( $this->cmbsRestrictedServicerReport->cleanHeadersByProperty );

        foreach ( $this->propertyToTableNames as $propertyName => $tableNameSuffix ):
            try {
                $createTableStatements[ $propertyName ] = $this->_getCreateTableStatement( $propertyName,
                                                                                           $tablePrefix,
                                                                                           $tableNameSuffix );
            } catch ( UnableToGenerateCreateTableException $exception ) {
                $this->failedTables[ $propertyName ] = $tableNameSuffix;
                dump( 'UnableToGenerateCreateTableException: ' . $exception->getMessage() );
            }
        endforeach;

        return implode( "\n\n", $createTableStatements );
    }


    /**
     * @param string $propertyName
     * @param string $tablePrefix
     * @param string $tableNameSuffix
     * @return string
     * @throws UnableToGenerateCreateTableException
     */
    protected function _getCreateTableStatement( string $propertyName,
                                                 string $tablePrefix,
                                                 string $tableNameSuffix ): string {
        $tableName = $tablePrefix . $tableNameSuffix;
        $fields    = $this->cmbsRestrictedServicerReport->cleanHeadersByProperty[ $propertyName ] ?? NULL;

        if ( ! $fields ):
            throw new UnableToGenerateCreateTableException( "No fields for " . $propertyName,
                                                            0,
                                                            NULL,
                                                            $tableName );
        endif;

        // Calulate max size of varchar fields.
        $numFields     = count( $fields );
        $charsPerField = 65000 / $numFields;
        if ( $charsPerField > 400 ):
            $charsPerField = 400;
        else:
            $charsPerField = floor( $charsPerField );
        endif;

        $newCreateTableStatement = "CREATE TABLE " . $tableName;

        $fieldParts   = [];
        $fieldParts[] = 'id int auto_increment primary key';
        $fieldParts[] = 'created_at timestamp null';
        $fieldParts[] = 'updated_at timestamp null';

        foreach ( $fields as $field ):
            $fieldParts[] = $field . ' varchar(' . $charsPerField . ') null';
        endforeach;

        $fieldsToCreate = '(' . implode( ",", $fieldParts ) . ')';

        $newCreateTableStatement .= ' ' . $fieldsToCreate . ';';

        return $newCreateTableStatement;
//
//
//        $firstRow = $this->{$propertyName}[ 0 ] ?? NULL;
//        if ( $firstRow ):
////            $fields        = array_keys( $firstRow );
//            $fields = $this->cleanHeadersByProperty[ $propertyName ];
//
//            if ( ! $fields ):
//                dd( $propertyName );
//            endif;
//
//            $numFields     = count( $fields );
//            $charsPerField = 65000 / $numFields;
//            if ( $charsPerField > 500 ):
//                $charsPerField = 500;
//            else:
//                $charsPerField = floor( $charsPerField );
//            endif;
//
//            $newCreateTableStatement = "CREATE TABLE " . $tableName;
//
//            $fieldParts   = [];
//            $fieldParts[] = 'id int auto_increment primary key';
//            $fieldParts[] = 'created_at timestamp null';
//            $fieldParts[] = 'updated_at timestamp null';
//
//            foreach ( $fields as $field ):
//                $fieldParts[] = $field . ' varchar(' . $charsPerField . ') null';
//            endforeach;
//
//            $fieldsToCreate = '(' . implode( ",", $fieldParts ) . ')';
//
//            $newCreateTableStatement .= ' ' . $fieldsToCreate;
//
//            return $newCreateTableStatement;
//        else:
//            $this->failedTables[ $propertyName ] = $tableNameSuffix;
//            throw new UnableToGenerateCreateTableException( "Probably no rows in this property: " . $propertyName,
//                                                            0,
//                                                            NULL,
//                                                            $tableName );
//        endif;
    }

}