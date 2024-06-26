<?php


namespace DPRMC\RemitSpiderCTSLink\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class CustodianCtsCmbsRestrictedServicerReportReosr extends AbstractCustodianCtsCmbsRestrictedServicerReportTab {

    public $table        = 'custodian_cts_cmbs_restricted_servicer_report_reosrs';
    public $primaryKey   = self::id;
    public $keyType      = 'integer';
    public $incrementing = TRUE;

    const id                                                          = 'id';
//    const date                                                        = 'date';
//    const document_id                                                 = 'document_id';
    const created_at                                                  = 'created_at';
    const updated_at                                                  = 'updated_at';
    const trans_id                                                    = 'trans_id';
    const loan_id                                                     = 'loan_id';
    const prospectus_loan_id                                          = 'prospectus_loan_id';
    const property_id                                                 = 'property_id';
    const property_name                                               = 'property_name';
    const property_type                                               = 'property_type';
    const property_city                                               = 'property_city';
    const property_state                                              = 'property_state';
    const current_net_rentable_sq_ft_or_number_of_units_beds_rooms    = 'current_net_rentable_sq_ft_or_number_of_units_beds_rooms';
    const paid_through_date                                           = 'paid_through_date';
    const current_allocated_ending_scheduled_loan_amount              = 'current_allocated_ending_scheduled_loan_amount';
    const cumulative_aser_amount                                      = 'cumulative_aser_amount';
    const total_p_and_i_advance_outstanding                           = 'total_p_and_i_advance_outstanding';
    const other_expense_advance_outstanding                           = 'other_expense_advance_outstanding';
    const total_t_and_i_advance_outstanding                           = 'total_t_and_i_advance_outstanding';
    const cumulative_accrued_unpaid_advance_interest                  = 'cumulative_accrued_unpaid_advance_interest';
    const total_exposure                                              = 'total_exposure';
    const total_scheduled_p_and_i_due                                 = 'total_scheduled_p_and_i_due';
    const maturity_date                                               = 'maturity_date';
    const preced_fy_finan_as_of_date_most_recent_finan_as_of_end_date = 'preced_fy_finan_as_of_date_most_recent_finan_as_of_end_date';
    const preced_fy_dscr_most_recent_dscr_noi_ncf                     = 'preced_fy_dscr_most_recent_dscr_noi_ncf';
    const most_recent_valuation_date                                  = 'most_recent_valuation_date';
    const most_recent_valuation_source                                = 'most_recent_valuation_source';
    const most_recent_value                                           = 'most_recent_value';
    const loss_using_90percent_of_most_recent_value                   = 'loss_using_90percent_of_most_recent_value';
    const ara_appraisal_reduction_amount                              = 'ara_appraisal_reduction_amount';
    const most_recent_special_servicer_transfer_date                  = 'most_recent_special_servicer_transfer_date';
    const reo_date                                                    = 'reo_date';
    const date_asset_expected_to_be_resolved_or_foreclosed            = 'date_asset_expected_to_be_resolved_or_foreclosed';
    const comments_reo                                                = 'comments_reo';

    protected $casts = [
        self::date                                                        => 'date',
        self::document_id                                                 => 'integer',
        self::trans_id                                                    => 'string',
        self::loan_id                                                     => 'string',
        self::prospectus_loan_id                                          => 'string',
        self::property_id                                                 => 'string',
        self::property_name                                               => 'string',
        self::property_type                                               => 'string',
        self::property_city                                               => 'string',
        self::property_state                                              => 'string',
        self::current_net_rentable_sq_ft_or_number_of_units_beds_rooms    => 'string',
        self::paid_through_date                                           => 'string',
        self::current_allocated_ending_scheduled_loan_amount              => 'string',
        self::cumulative_aser_amount                                      => 'string',
        self::total_p_and_i_advance_outstanding                           => 'string',
        self::other_expense_advance_outstanding                           => 'string',
        self::total_t_and_i_advance_outstanding                           => 'string',
        self::cumulative_accrued_unpaid_advance_interest                  => 'string',
        self::total_exposure                                              => 'string',
        self::total_scheduled_p_and_i_due                                 => 'string',
        self::maturity_date                                               => 'string',
        self::preced_fy_finan_as_of_date_most_recent_finan_as_of_end_date => 'string',
        self::preced_fy_dscr_most_recent_dscr_noi_ncf                     => 'string',
        self::most_recent_valuation_date                                  => 'string',
        self::most_recent_valuation_source                                => 'string',
        self::most_recent_value                                           => 'string',
        self::loss_using_90percent_of_most_recent_value                   => 'string',
        self::ara_appraisal_reduction_amount                              => 'string',
        self::most_recent_special_servicer_transfer_date                  => 'string',
        self::reo_date                                                    => 'string',
        self::date_asset_expected_to_be_resolved_or_foreclosed            => 'string',
        self::comments_reo                                                => 'string',
    ];

    protected $fillable = [
        self::date,
        self::trans_id,
        self::loan_id,
        self::prospectus_loan_id,
        self::property_id,
        self::property_name,
        self::property_type,
        self::property_city,
        self::property_state,
        self::current_net_rentable_sq_ft_or_number_of_units_beds_rooms,
        self::paid_through_date,
        self::current_allocated_ending_scheduled_loan_amount,
        self::cumulative_aser_amount,
        self::total_p_and_i_advance_outstanding,
        self::other_expense_advance_outstanding,
        self::total_t_and_i_advance_outstanding,
        self::cumulative_accrued_unpaid_advance_interest,
        self::total_exposure,
        self::total_scheduled_p_and_i_due,
        self::maturity_date,
        self::preced_fy_finan_as_of_date_most_recent_finan_as_of_end_date,
        self::preced_fy_dscr_most_recent_dscr_noi_ncf,
        self::most_recent_valuation_date,
        self::most_recent_valuation_source,
        self::most_recent_value,
        self::loss_using_90percent_of_most_recent_value,
        self::ara_appraisal_reduction_amount,
        self::most_recent_special_servicer_transfer_date,
        self::reo_date,
        self::date_asset_expected_to_be_resolved_or_foreclosed,
        self::comments_reo,
    ];


    public function __construct( array $attributes = [] ) {
        parent::__construct( $attributes );
        $this->connection = env( 'DB_CONNECTION_CUSTODIAN_CTS' );
    }
}