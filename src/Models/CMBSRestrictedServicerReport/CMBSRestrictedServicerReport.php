<?php

namespace DPRMC\RemitSpiderCTSLink\Models\CMBSRestrictedServicerReport;


use DPRMC\RemitSpiderCTSLink\Exceptions\UnableToGenerateCreateTableException;

class CMBSRestrictedServicerReport {


    public function __construct( public readonly array $watchlist,
                                 public readonly array $dlsr,
                                 public readonly array $reosr,
                                 public readonly array $hlmfclr,
                                 public readonly array $csfr,
                                 public readonly array $llResLOC,
                                 public readonly array $totalLoan,
                                 public readonly array $advanceRecovery,
                                 public readonly array $cleanHeadersByProperty,
                                 public readonly array $alerts,
                                 public readonly array $exceptions ) {
    }


    protected array $propertyToTableNames = [
        'watchlist'       => 'watchlists',
        'reosr'           => 'reosrs',
        'csfr'            => 'cfsrs',
        'llResLOC'        => 'll_res_locs',
        'totalLoan'       => 'total_loans',
        'advanceRecovery' => 'advance_recoveries',

        'dlsr'    => 'dlsrs',
        'hlmfclr' => 'hlmfclrs',
    ];


    /**
     * @var array If a property doesn't have data, then I can't make a table to store it.
     */
    public array $failedTables = [];


    public function generateSQL( string $tablePrefix = 'custodian_cts_cmbs_restricted_servicer_report_' ): string {
        $createTableStatements = [];

        foreach ( $this->propertyToTableNames as $propertyName => $tableNameSuffix ):
            try {
                $createTableStatements[ $propertyName ] = $this->_getCreateTableStatement( $propertyName,
                                                                                           $tablePrefix,
                                                                                           $tableNameSuffix );
            } catch ( UnableToGenerateCreateTableException $exception ) {
                $this->failedTables[ $propertyName ] = $tableNameSuffix;
                dump( $exception->getMessage() );
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
        dd($this->cleanHeadersByProperty);
        $tableName = $tablePrefix . $tableNameSuffix;
        $fields    = $this->cleanHeadersByProperty[ $propertyName ] ?? NULL;

        dump($fields);
        dump($propertyName);


        if ( ! $fields ):
            throw new UnableToGenerateCreateTableException( "No fields for " . $propertyName,
                                                            0,
                                                            NULL,
                                                            $tableName );
        endif;

        // Calulate max size of varchar fields.
        $numFields     = count( $fields );
        $charsPerField = 65000 / $numFields;
        if ( $charsPerField > 500 ):
            $charsPerField = 500;
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

        $newCreateTableStatement .= ' ' . $fieldsToCreate;

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