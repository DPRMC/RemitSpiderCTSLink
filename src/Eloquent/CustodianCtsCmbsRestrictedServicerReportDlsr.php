<?php


namespace DPRMC\RemitSpiderCTSLink\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class CustodianCtsCmbsRestrictedServicerReportDlsr extends Model {

    public $table        = 'custodian_cts_cmbs_restricted_servicer_report_dlsrs';
    public $primaryKey   = self::id;
    public $keyType      = 'integer';
    public $incrementing = TRUE;

    const id                                                          = 'id';
    const date                                                        = 'date';
    const created_at                                                  = 'created_at';
    const updated_at                                                  = 'updated_at';
    const trans_id                                                    = 'trans_id';
    const group_id                                                    = 'group_id';
    const loan_id                                                     = 'loan_id';
    const prospectus_loan_id                                          = 'prospectus_loan_id';
    const property_name                                               = 'property_name';
    const property_type                                               = 'property_type';
    const property_city                                               = 'property_city';
    const property_state                                              = 'property_state';
    const current_net_rentable_sqft_or_number_of_units_beds_rooms     = 'current_net_rentable_sqft_or_number_of_units_beds_rooms';
    const paid_through_date                                           = 'paid_through_date';
    const current_ending_scheduled_balance                            = 'current_ending_scheduled_balance';
    const cumulative_aser_amount                                      = 'cumulative_aser_amount';
    const total_p_and_i_advance_outstanding                           = 'total_p_and_i_advance_outstanding';
    const other_expenses_advances_outstanding                         = 'other_expenses_advances_outstanding';
    const total_t_and_i_advance_outstanding                           = 'total_t_and_i_advance_outstanding';
    const cumulative_accrued_unpaid_advance_interest                  = 'cumulative_accrued_unpaid_advance_interest';
    const total_exposure                                              = 'total_exposure';
    const total_scheduled_p_and_i_due                                 = 'total_scheduled_p_and_i_due';
    const current_note_rate                                           = 'current_note_rate';
    const maturity_date                                               = 'maturity_date';
    const preced_fy_finan_as_of_date_most_recent_finan_as_of_end_date = 'preced_fy_finan_as_of_date_most_recent_finan_as_of_end_date';
    const preced_fy_noi_ncf_most_recent_noi_ncf                       = 'preced_fy_noi_ncf_most_recent_noi_ncf';
    const preced_fy_dscr_most_recent_dscr_noi_ncf                     = 'preced_fy_dscr_most_recent_dscr_noi_ncf';
    const most_recent_valuation_date                                  = 'most_recent_valuation_date';
    const most_recent_value                                           = 'most_recent_value';
    const loss_using_90percent_of_most_recent_value                   = 'loss_using_90percent_of_most_recent_value';
    const ara_appraisal_reduction_amount                              = 'ara_appraisal_reduction_amount';
    const most_recent_special_servicer_transfer_date                  = 'most_recent_special_servicer_transfer_date';
    const date_asset_expected_to_be_resolved_or_foreclosed            = 'date_asset_expected_to_be_resolved_or_foreclosed';
    const workout_strategy                                            = 'workout_strategy';
    const comments_dlsr                                               = 'comments_dlsr';

    const dscr_noi_ncf = 'dscr_noi_ncf';
    const category     = 'category';

    protected $casts = [
        self::date                                                        => 'date',
        self::trans_id                                                    => 'string',
        self::group_id                                                    => 'string',
        self::loan_id                                                     => 'string',
        self::prospectus_loan_id                                          => 'string',
        self::property_name                                               => 'string',
        self::property_type                                               => 'string',
        self::property_city                                               => 'string',
        self::property_state                                              => 'string',
        self::current_net_rentable_sqft_or_number_of_units_beds_rooms     => 'float',
        self::paid_through_date                                           => 'date',
        self::current_ending_scheduled_balance                            => 'float',
        self::cumulative_aser_amount                                      => 'float',
        self::total_p_and_i_advance_outstanding                           => 'float',
        self::other_expenses_advances_outstanding                         => 'float',
        self::total_t_and_i_advance_outstanding                           => 'float',
        self::cumulative_accrued_unpaid_advance_interest                  => 'float',
        self::total_exposure                                              => 'float',
        self::total_scheduled_p_and_i_due                                 => 'float',
        self::current_note_rate                                           => 'float',
        self::maturity_date                                               => 'date',
        self::preced_fy_finan_as_of_date_most_recent_finan_as_of_end_date => 'date',
        self::preced_fy_noi_ncf_most_recent_noi_ncf                       => 'float',
        self::preced_fy_dscr_most_recent_dscr_noi_ncf                     => 'float',
        self::most_recent_valuation_date                                  => 'date',
        self::most_recent_value                                           => 'float',
        self::loss_using_90percent_of_most_recent_value                   => 'float',
        self::ara_appraisal_reduction_amount                              => 'float',
        self::most_recent_special_servicer_transfer_date                  => 'date',
        self::date_asset_expected_to_be_resolved_or_foreclosed            => 'date',
        self::workout_strategy                                            => 'string',
        self::comments_dlsr                                               => 'string',
        self::dscr_noi_ncf                                                => 'string',
        self::category                                                    => 'string',
    ];

    protected $guarded = [];


    public function __construct( array $attributes = [] ) {
        parent::__construct( $attributes );
        $this->connection = env( 'DB_CONNECTION_CUSTODIAN_CTS' );
    }
}