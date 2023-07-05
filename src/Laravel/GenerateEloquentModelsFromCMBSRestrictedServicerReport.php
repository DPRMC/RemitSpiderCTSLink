<?php

namespace DPRMC\RemitSpiderCTSLink\Laravel;


use DPRMC\RemitSpiderCTSLink\Exceptions\UnableToCreateEloquentFileException;
use DPRMC\RemitSpiderCTSLink\Exceptions\UnableToGenerateCreateTableException;
use DPRMC\RemitSpiderCTSLink\Models\CMBSRestrictedServicerReport\CMBSMonthlyAdministratorReport;
use Illuminate\Support\Str;

class GenerateEloquentModelsFromCMBSRestrictedServicerReport {

    const CLASS_NAME = '<class_name>';
    const TABLE      = '<table>';
    const CONSTS     = '<consts>';
    const CASTS      = '<casts>';
    const FILLABLE   = '<fillable>';
    const CONNECTION = '<connection>';


    public function __construct( public readonly CMBSMonthlyAdministratorReport $cmbsRestrictedServicerReport ) {
    }


    /**
     * @var array|string[] Indexes are the property names in the CMBSRestrictedServicerReport, values are the table suffixes/names.
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


    public function generateModels( string $tablePrefix = 'custodian_cts_cmbs_restricted_servicer_report_',
                                    string $connection = 'DB_CONNECTION_CUSTODIAN_CTS' ): array {
        $modelFiles = [];

        $template = $this->_getTemplateString();

        foreach ( $this->propertyToTableNames as $propertyName => $tableNameSuffix ):
            try {
                $modelFiles[] = $this->_createEloquentFile( $template, $tablePrefix, $tableNameSuffix, $propertyName, $connection );
            } catch ( \Exception $exception ) {
                dump( get_class( $exception ) . ': ' . $exception->getMessage() );
            }
        endforeach;

        return $modelFiles;
    }


    /**
     * @param string $template
     * @param string $tablePrefix
     * @param string $tableSuffix
     * @param string $propertyName
     * @param string $connection
     * @return string
     * @throws UnableToCreateEloquentFileException
     */
    protected function _createEloquentFile( string $template,
                                            string $tablePrefix,
                                            string $tableSuffix,
                                            string $propertyName,
                                            string $connection ): string {

        $tableName = $tablePrefix . $tableSuffix;
        $className = Str::studly( Str::singular( $tableName ) );

        $template = str_replace( self::CLASS_NAME, $className, $template );
        $template = str_replace( self::TABLE, $tableName, $template );

        $constants = $this->_getConstants( $propertyName );
        $template  = str_replace( self::CONSTS, $constants, $template );

        $casts    = $this->_getCasts( $propertyName );
        $template = str_replace( self::CASTS, $casts, $template );

        $fillable = $this->_getFillable( $propertyName );
        $template = str_replace( self::FILLABLE, $fillable, $template );

        $template = str_replace( self::CONNECTION, $connection, $template );

        $filename = $className . '.php';

        file_put_contents( $filename, $template );

        return $filename;
    }

    protected function _getTemplateString(): string {
        return file_get_contents( getcwd() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Laravel' . DIRECTORY_SEPARATOR . 'Output' . DIRECTORY_SEPARATOR . 'template.txt' );
    }


    /**
     * @param string $propertyName
     * @return string
     * @throws UnableToCreateEloquentFileException
     */
    protected function _getConstants( string $propertyName ): string {
        $fields = $this->cmbsRestrictedServicerReport->cleanHeadersByProperty[ $propertyName ] ?? NULL;

        if ( is_null( $fields ) ):
            throw new UnableToCreateEloquentFileException( "Unable to create: " . $propertyName,
                                                           0,
                                                           NULL,
                                                           $propertyName );
        endif;

        $constantParts = [];

        foreach ( $fields as $field ):
            $constantParts[] = 'const ' . $field . ' = ' . "'" . $field . "';";
        endforeach;

        return implode( "\n", $constantParts );
    }


    protected function _getCasts( string $propertyName ): string {
        $fields     = $this->cmbsRestrictedServicerReport->cleanHeadersByProperty[ $propertyName ] ?? NULL;
        $castsParts = [];

        foreach ( $fields as $field ):
            $castsParts[] = 'self:: ' . $field . ' => ' . "'string',";
        endforeach;

        return implode( "\n", $castsParts );
    }


    protected function _getFillable( string $propertyName ): string {
        $fields        = $this->cmbsRestrictedServicerReport->cleanHeadersByProperty[ $propertyName ] ?? NULL;
        $fillableParts = [];

        foreach ( $fields as $field ):
            $fillableParts[] = 'self:: ' . $field . ',';
        endforeach;

        return implode( "\n", $fillableParts );
    }

}