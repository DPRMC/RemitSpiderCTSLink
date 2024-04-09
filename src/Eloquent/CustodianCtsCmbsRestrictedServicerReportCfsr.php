<?php


namespace DPRMC\RemitSpiderCTSLink\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class CustodianCtsCmbsRestrictedServicerReportCfsr extends Model {

    public $table        = 'custodian_cts_cmbs_restricted_servicer_report_cfsrs';
    public $primaryKey   = self::id;
    public $keyType      = 'integer';
    public $incrementing = TRUE;

    // Pulled from the filename of the parsed json file.1
    const shelf       = 'shelf';
    const series      = 'series';
    const document_id = 'document_id';

    const id                                                           = 'id';
    const date                                                         = 'date';
    const created_at                                                   = 'created_at';
    const updated_at                                                   = 'updated_at';
    const trans_id                                                     = 'trans_id';
    const loan_id                                                      = 'loan_id';
    const prospectus_loan_id                                           = 'prospectus_loan_id';
    const property_id                                                  = 'property_id';
    const property_city                                                = 'property_city';
    const property_state                                               = 'property_state';
    const date_of_last_inspection                                      = 'date_of_last_inspection';
    const property_condition                                           = 'property_condition';
    const current_allocated_ending_scheduled_loan_amount               = 'current_allocated_ending_scheduled_loan_amount';
    const paid_through_date                                            = 'paid_through_date';
    const at_contribution_information_base_year_financials_as_of_date  = 'at_contribution_information_base_year_financials_as_of_date';
    const at_contribution_information_base_year_physical_occup_percent = 'at_contribution_information_base_year_physical_occup_percent';
    const at_contribution_information_base_year_revenue                = 'at_contribution_information_base_year_revenue';
    const at_contribution_information_base_year_noi_ncf                = 'at_contribution_information_base_year_noi_ncf';
    const at_contribution_information_base_year_dscr_noi_ncf           = 'at_contribution_information_base_year_dscr_noi_ncf';


    const sec_prec_fy_operating_info_as_of_financials_as_of_date       = 'sec_prec_fy_operating_info_as_of_financials_as_of_date';
    const sec_prec_fy_operating_info_normalized_physical_occup_percent = 'sec_prec_fy_operating_info_normalized_physical_occup_percent';
    const sec_prec_fy_operating_info_normalized_revenue                = 'sec_prec_fy_operating_info_normalized_revenue';
    const sec_prec_fy_operating_info_normalized_noi_ncf                = 'sec_prec_fy_operating_info_normalized_noi_ncf';
    const sec_prec_fy_operating_info_normalized_dscr_noi_ncf           = 'sec_prec_fy_operating_info_normalized_dscr_noi_ncf';
    const prec_fy_operating_info_as_of_financials_as_of_date           = 'prec_fy_operating_info_as_of_financials_as_of_date';
    const prec_fy_operating_info_normalized_physical_occup_percent     = 'prec_fy_operating_info_normalized_physical_occup_percent';
    const prec_fy_operating_info_normalized_revenue                    = 'prec_fy_operating_info_normalized_revenue';
    const prec_fy_operating_info_normalized_noi_ncf                    = 'prec_fy_operating_info_normalized_noi_ncf';
    const prec_fy_operating_info_normalized_dscr_noi_ncf               = 'prec_fy_operating_info_normalized_dscr_noi_ncf';
    const most_recent_financial_information_financial_as_of_start_date = 'most_recent_financial_information_financial_as_of_start_date';
    const most_recent_financial_information_financial_as_of_end_date   = 'most_recent_financial_information_financial_as_of_end_date';
    const most_recent_financial_information_occup_as_of_date           = 'most_recent_financial_information_occup_as_of_date';
    const most_recent_financial_information_physical_occup_percent     = 'most_recent_financial_information_physical_occup_percent';
    const most_recent_financial_information_normalized_revenue         = 'most_recent_financial_information_normalized_revenue';
    const most_recent_financial_information_normalized_noi_ncf         = 'most_recent_financial_information_normalized_noi_ncf';
    const most_recent_financial_information_normalized_dscr_noi_ncf    = 'most_recent_financial_information_normalized_dscr_noi_ncf';
    const net_change_preceding_and_base_year_percent_occup             = 'net_change_preceding_and_base_year_percent_occup';
    const net_change_preceding_and_base_year_percent_total_revenue     = 'net_change_preceding_and_base_year_percent_total_revenue';
    const net_change_preceding_and_base_year_dscr                      = 'net_change_preceding_and_base_year_dscr';

    protected $casts = [
        self::date                                                         => 'date',
        self::document_id                                                  => 'integer',
        self::trans_id                                                     => 'string',
        self::loan_id                                                      => 'string',
        self::prospectus_loan_id                                           => 'string',
        self::property_id                                                  => 'string',
        self::property_city                                                => 'string',
        self::property_state                                               => 'string',
        self::date_of_last_inspection                                      => 'string',
        self::property_condition                                           => 'string',
        self::current_allocated_ending_scheduled_loan_amount               => 'string',
        self::paid_through_date                                            => 'string',
        self::at_contribution_information_base_year_financials_as_of_date  => 'string',
        self::at_contribution_information_base_year_physical_occup_percent => 'string',
        self::at_contribution_information_base_year_revenue                => 'string',
        self::at_contribution_information_base_year_noi_ncf                => 'string',
        self::at_contribution_information_base_year_dscr_noi_ncf           => 'string',
        self::sec_prec_fy_operating_info_as_of_financials_as_of_date       => 'string',
        self::sec_prec_fy_operating_info_normalized_physical_occup_percent => 'string',
        self::sec_prec_fy_operating_info_normalized_revenue                => 'string',
        self::sec_prec_fy_operating_info_normalized_noi_ncf                => 'string',
        self::sec_prec_fy_operating_info_normalized_dscr_noi_ncf           => 'string',
        self::prec_fy_operating_info_as_of_financials_as_of_date           => 'string',
        self::prec_fy_operating_info_normalized_physical_occup_percent     => 'string',
        self::prec_fy_operating_info_normalized_revenue                    => 'string',
        self::prec_fy_operating_info_normalized_noi_ncf                    => 'string',
        self::prec_fy_operating_info_normalized_dscr_noi_ncf               => 'string',
        self::most_recent_financial_information_financial_as_of_start_date => 'string',
        self::most_recent_financial_information_financial_as_of_end_date   => 'string',
        self::most_recent_financial_information_occup_as_of_date           => 'string',
        self::most_recent_financial_information_physical_occup_percent     => 'string',
        self::most_recent_financial_information_normalized_revenue         => 'string',
        self::most_recent_financial_information_normalized_noi_ncf         => 'string',
        self::most_recent_financial_information_normalized_dscr_noi_ncf    => 'string',
        self::net_change_preceding_and_base_year_percent_occup             => 'string',
        self::net_change_preceding_and_base_year_percent_total_revenue     => 'string',
        self::net_change_preceding_and_base_year_dscr                      => 'string',
    ];

    protected $fillable = [
        self::date,
        self::trans_id,
        self::loan_id,
        self::prospectus_loan_id,
        self::property_id,
        self::property_city,
        self::property_state,
        self::date_of_last_inspection,
        self::property_condition,
        self::current_allocated_ending_scheduled_loan_amount,
        self::paid_through_date,
        self::at_contribution_information_base_year_financials_as_of_date,
        self::at_contribution_information_base_year_physical_occup_percent,
        self::at_contribution_information_base_year_revenue,
        self::at_contribution_information_base_year_noi_ncf,
        self::at_contribution_information_base_year_dscr_noi_ncf,
        self::sec_prec_fy_operating_info_as_of_financials_as_of_date,
        self::sec_prec_fy_operating_info_normalized_physical_occup_percent,
        self::sec_prec_fy_operating_info_normalized_revenue,
        self::sec_prec_fy_operating_info_normalized_noi_ncf,
        self::sec_prec_fy_operating_info_normalized_dscr_noi_ncf,
        self::prec_fy_operating_info_as_of_financials_as_of_date,
        self::prec_fy_operating_info_normalized_physical_occup_percent,
        self::prec_fy_operating_info_normalized_revenue,
        self::prec_fy_operating_info_normalized_noi_ncf,
        self::prec_fy_operating_info_normalized_dscr_noi_ncf,
        self::most_recent_financial_information_financial_as_of_start_date,
        self::most_recent_financial_information_financial_as_of_end_date,
        self::most_recent_financial_information_occup_as_of_date,
        self::most_recent_financial_information_physical_occup_percent,
        self::most_recent_financial_information_normalized_revenue,
        self::most_recent_financial_information_normalized_noi_ncf,
        self::most_recent_financial_information_normalized_dscr_noi_ncf,
        self::net_change_preceding_and_base_year_percent_occup,
        self::net_change_preceding_and_base_year_percent_total_revenue,
        self::net_change_preceding_and_base_year_dscr,
    ];


    public function __construct( array $attributes = [] ) {
        parent::__construct( $attributes );
        $this->connection = env( 'DB_CONNECTION_CUSTODIAN_CTS' );
    }
}