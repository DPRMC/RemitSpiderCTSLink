<?php


namespace DPRMC\RemitSpiderCTSLink\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class CustodianCtsProperty extends Model {

    public $table        = 'custodian_cts_cmbs_properties';
    public $primaryKey   = self::id;
    public $keyType      = 'integer';
    public $incrementing = TRUE;

    const id                    = 'id';
    const key                   = 'key';
    const created_at            = 'created_at';
    const updated_at            = 'updated_at';
    const shelf                 = 'shelf';
    const series                = 'series';
    const custodian_security_id = 'custodian_security_id';
    const url                   = 'url';

    const transaction_id                                        = 'transaction_id';
    const loan_id                                               = 'loan_id';
    const prospectus_loan_id                                    = 'prospectus_loan_id';
    const property_id                                           = 'property_id';
    const distribution_date                                     = 'distribution_date';
    const cross_collateralized_loan_grouping                    = 'cross_collateralized_loan_grouping';
    const property_name                                         = 'property_name';
    const property_address                                      = 'property_address';
    const property_city                                         = 'property_city';
    const property_state                                        = 'property_state';
    const property_zip_code                                     = 'property_zip_code';
    const property_county                                       = 'property_county';
    const property_type                                         = 'property_type';
    const year_built                                            = 'year_built';
    const year_last_renovated                                   = 'year_last_renovated';
    const current_net_rentable_sq_feet_at_contribution          = 'current_net_rentable_sq_feet_at_contribution';
    const current_number_of_units_beds_rooms_at_contribution    = 'current_number_of_units_beds_rooms_at_contribution';
    const property_status                                       = 'property_status';
    const allocated_percentage_of_loan_at_contribution          = 'allocated_percentage_of_loan_at_contribution';
    const current_allocated_percentage                          = 'current_allocated_percentage';
    const current_allocated_ending_scheduled_loan_amount        = 'current_allocated_ending_scheduled_loan_amount';
    const ground_lease_y_n_s                                    = 'ground_lease_y_n_s';
    const empty_field_fka_other_escrow_reserve_balances         = 'empty_field_fka_other_escrow_reserve_balances';
    const most_recent_valuation_date                            = 'most_recent_valuation_date';
    const most_recent_value                                     = 'most_recent_value';
    const date_asset_expected_to_be_resolved_or_foreclosed      = 'date_asset_expected_to_be_resolved_or_foreclosed';
    const foreclosure_start_date                                = 'foreclosure_start_date';
    const reo_date                                              = 'reo_date';
    const most_recent_physical_occupancy                        = 'most_recent_physical_occupancy';
    const most_recent_occupancy_as_of_date                      = 'most_recent_occupancy_as_of_date';
    const date_lease_rollover_review                            = 'date_lease_rollover_review';
    const pct_sq_feet_expiring_1_12_months                      = 'pct_sq_feet_expiring_1_12_months';
    const pct_sq_feet_expiring_13_24_months                     = 'pct_sq_feet_expiring_13_24_months';
    const pct_sq_feet_expiring_25_36_months                     = 'pct_sq_feet_expiring_25_36_months';
    const pct_sq_feet_expiring_37_48_months                     = 'pct_sq_feet_expiring_37_48_months';
    const pct_sq_feet_expiring_49_or_more_months                = 'pct_sq_feet_expiring_49_or_more_months';
    const largest_tenant                                        = 'largest_tenant';
    const square_feet_of_largest_tenant                         = 'square_feet_of_largest_tenant';
    const second_largest_tenant                                 = 'second_largest_tenant';
    const square_feet_of_second_2nd_largest_tenant              = 'square_feet_of_second_2nd_largest_tenant';
    const third_largest_tenant                                  = 'third_largest_tenant';
    const square_feet_of_third_3rd_largest_tenant               = 'square_feet_of_third_3rd_largest_tenant';
    const fiscal_year_end_month                                 = 'fiscal_year_end_month';
    const contribution_financials_as_of_date                    = 'contribution_financials_as_of_date';
    const revenue_at_contribution                               = 'revenue_at_contribution';
    const operating_expenses_at_contribution                    = 'operating_expenses_at_contribution';
    const noi_at_contribution                                   = 'noi_at_contribution';
    const dscr_noi_at_contribution                              = 'dscr_noi_at_contribution';
    const valuation_amount_at_contribution                      = 'valuation_amount_at_contribution';
    const valuation_date_at_contribution                        = 'valuation_date_at_contribution';
    const physical_occupancy_at_contribution                    = 'physical_occupancy_at_contribution';
    const date_of_last_inspection                               = 'date_of_last_inspection';
    const preceding_fiscal_year_financial_as_of_date            = 'preceding_fiscal_year_financial_as_of_date';
    const preceding_fiscal_year_revenue                         = 'preceding_fiscal_year_revenue';
    const preceding_fiscal_year_operating_expenses              = 'preceding_fiscal_year_operating_expenses';
    const preceding_fiscal_year_noi                             = 'preceding_fiscal_year_noi';
    const preceding_fiscal_year_debt_service_amount             = 'preceding_fiscal_year_debt_service_amount';
    const preceding_fiscal_year_dscr_noi                        = 'preceding_fiscal_year_dscr_noi';
    const preceding_fiscal_year_physical_occupancy              = 'preceding_fiscal_year_physical_occupancy';
    const second_preceding_fiscal_year_financial_as_of_date     = 'second_preceding_fiscal_year_financial_as_of_date';
    const second_preceding_fiscal_year_revenue                  = 'second_preceding_fiscal_year_revenue';
    const second_preceding_fiscal_year_operating_expenses       = 'second_preceding_fiscal_year_operating_expenses';
    const second_preceding_fiscal_year_noi                      = 'second_preceding_fiscal_year_noi';
    const second_preceding_fiscal_year_debt_service_amount      = 'second_preceding_fiscal_year_debt_service_amount';
    const second_preceding_fiscal_year_dscr_noi                 = 'second_preceding_fiscal_year_dscr_noi';
    const second_preceding_fiscal_year_physical_occupancy       = 'second_preceding_fiscal_year_physical_occupancy';
    const property_collateral_contribution_date                 = 'property_collateral_contribution_date';
    const most_recent_revenue                                   = 'most_recent_revenue';
    const most_recent_operating_expenses                        = 'most_recent_operating_expenses';
    const most_recent_noi                                       = 'most_recent_noi';
    const most_recent_debt_service_amount                       = 'most_recent_debt_service_amount';
    const most_recent_dscr_noi                                  = 'most_recent_dscr_noi';
    const most_recent_financial_as_of_start_date                = 'most_recent_financial_as_of_start_date';
    const most_recent_financial_as_of_end_date                  = 'most_recent_financial_as_of_end_date';
    const most_recent_financial_indicator                       = 'most_recent_financial_indicator';
    const ncf_at_contribution                                   = 'ncf_at_contribution';
    const dscr_ncf_at_contribution                              = 'dscr_ncf_at_contribution';
    const preceding_fiscal_year_ncf                             = 'preceding_fiscal_year_ncf';
    const preceding_fiscal_year_dscr_ncf                        = 'preceding_fiscal_year_dscr_ncf';
    const second_preceding_fiscal_year_ncf                      = 'second_preceding_fiscal_year_ncf';
    const second_preceding_fiscal_year_dscr_ncf                 = 'second_preceding_fiscal_year_dscr_ncf';
    const most_recent_ncf                                       = 'most_recent_ncf';
    const most_recent_dscr_ncf                                  = 'most_recent_dscr_ncf';
    const noi_ncf_indicator                                     = 'noi_ncf_indicator';
    const deferred_maintenance_flag_y_n                         = 'deferred_maintenance_flag_y_n';
    const date_of_lease_expiration_of_largest_tenant            = 'date_of_lease_expiration_of_largest_tenant';
    const date_of_lease_expiration_of_second_2nd_largest_tenant = 'date_of_lease_expiration_of_second_2nd_largest_tenant';
    const date_of_lease_expiration_of_third_3rd_largest_tenant  = 'date_of_lease_expiration_of_third_3rd_largest_tenant';
    const property_condition                                    = 'property_condition';
    const most_recent_valuation_source                          = 'most_recent_valuation_source';
    const credit_tenant_lease_y_n                               = 'credit_tenant_lease_y_n';
    const fourth_largest_tenant                                 = 'fourth_largest_tenant';
    const square_feet_of_fourth_4th_largest_tenant              = 'square_feet_of_fourth_4th_largest_tenant';
    const fifth_largest_tenant                                  = 'fifth_largest_tenant';
    const square_feet_of_fifth_5th_largest_tenant               = 'square_feet_of_fifth_5th_largest_tenant';
    const date_of_lease_expiration_of_fourth_4th_largest_tenant = 'date_of_lease_expiration_of_fourth_4th_largest_tenant';
    const date_of_lease_expiration_of_fifth_5th_largest_tenant  = 'date_of_lease_expiration_of_fifth_5th_largest_tenant';


    protected $casts = [
        self::transaction_id                                        => 'string',
        self::key                                                   => 'integer',
        self::custodian_security_id                                 => 'string',
        self::shelf                                                 => 'string',
        self::series                                                => 'string',
        self::url                                                   => 'string',
        self::loan_id                                               => 'string',
        self::prospectus_loan_id                                    => 'string',
        self::property_id                                           => 'string',
        self::distribution_date                                     => 'date',
        self::cross_collateralized_loan_grouping                    => 'string',
        self::property_name                                         => 'string',
        self::property_address                                      => 'string',
        self::property_city                                         => 'string',
        self::property_state                                        => 'string',
        self::property_zip_code                                     => 'string',
        self::property_county                                       => 'string',
        self::property_type                                         => 'string',
        self::year_built                                            => 'integer',
        self::year_last_renovated                                   => 'integer',
        self::current_net_rentable_sq_feet_at_contribution          => 'integer',
        self::current_number_of_units_beds_rooms_at_contribution    => 'integer',
        self::property_status                                       => 'integer',
        self::allocated_percentage_of_loan_at_contribution          => 'float',
        self::current_allocated_percentage                          => 'float',
        self::current_allocated_ending_scheduled_loan_amount        => 'float',
        self::ground_lease_y_n_s                                    => 'boolean',
        self::empty_field_fka_other_escrow_reserve_balances         => 'string',
        self::most_recent_valuation_date                            => 'date',
        self::most_recent_value                                     => 'integer',
        self::date_asset_expected_to_be_resolved_or_foreclosed      => 'date',
        self::foreclosure_start_date                                => 'date',
        self::reo_date                                              => 'date',
        self::most_recent_physical_occupancy                        => 'float',
        self::most_recent_occupancy_as_of_date                      => 'date',
        self::date_lease_rollover_review                            => 'date',
        self::pct_sq_feet_expiring_1_12_months                      => 'float',
        self::pct_sq_feet_expiring_13_24_months                     => 'float',
        self::pct_sq_feet_expiring_25_36_months                     => 'float',
        self::pct_sq_feet_expiring_37_48_months                     => 'float',
        self::pct_sq_feet_expiring_49_or_more_months                => 'float',
        self::largest_tenant                                        => 'string',
        self::square_feet_of_largest_tenant                         => 'integer',
        self::second_largest_tenant                                 => 'string',
        self::square_feet_of_second_2nd_largest_tenant              => 'integer',
        self::third_largest_tenant                                  => 'string',
        self::square_feet_of_third_3rd_largest_tenant               => 'integer',
        self::fiscal_year_end_month                                 => 'int',
        self::contribution_financials_as_of_date                    => 'date',
        self::revenue_at_contribution                               => 'float',
        self::operating_expenses_at_contribution                    => 'float',
        self::noi_at_contribution                                   => 'float',
        self::dscr_noi_at_contribution                              => 'float',
        self::valuation_amount_at_contribution                      => 'float',
        self::valuation_date_at_contribution                        => 'date',
        self::physical_occupancy_at_contribution                    => 'float',
        self::date_of_last_inspection                               => 'date',
        self::preceding_fiscal_year_financial_as_of_date            => 'date',
        self::preceding_fiscal_year_revenue                         => 'float',
        self::preceding_fiscal_year_operating_expenses              => 'float',
        self::preceding_fiscal_year_noi                             => 'float',
        self::preceding_fiscal_year_debt_service_amount             => 'float',
        self::preceding_fiscal_year_dscr_noi                        => 'float',
        self::preceding_fiscal_year_physical_occupancy              => 'float',
        self::second_preceding_fiscal_year_financial_as_of_date     => 'date',
        self::second_preceding_fiscal_year_revenue                  => 'float',
        self::second_preceding_fiscal_year_operating_expenses       => 'float',
        self::second_preceding_fiscal_year_noi                      => 'float',
        self::second_preceding_fiscal_year_debt_service_amount      => 'float',
        self::second_preceding_fiscal_year_dscr_noi                 => 'float',
        self::second_preceding_fiscal_year_physical_occupancy       => 'float',
        self::property_collateral_contribution_date                 => 'date',
        self::most_recent_revenue                                   => 'float',
        self::most_recent_operating_expenses                        => 'float',
        self::most_recent_noi                                       => 'float',
        self::most_recent_debt_service_amount                       => 'float',
        self::most_recent_dscr_noi                                  => 'float',
        self::most_recent_financial_as_of_start_date                => 'date',
        self::most_recent_financial_as_of_end_date                  => 'date',
        self::most_recent_financial_indicator                       => 'string',
        self::ncf_at_contribution                                   => 'float',
        self::dscr_ncf_at_contribution                              => 'float',
        self::preceding_fiscal_year_ncf                             => 'float',
        self::preceding_fiscal_year_dscr_ncf                        => 'float',
        self::second_preceding_fiscal_year_ncf                      => 'float',
        self::second_preceding_fiscal_year_dscr_ncf                 => 'float',
        self::most_recent_ncf                                       => 'float',
        self::most_recent_dscr_ncf                                  => 'float',
        self::noi_ncf_indicator                                     => 'string',
        self::deferred_maintenance_flag_y_n                         => 'boolean',
        self::date_of_lease_expiration_of_largest_tenant            => 'date',
        self::date_of_lease_expiration_of_second_2nd_largest_tenant => 'date',
        self::date_of_lease_expiration_of_third_3rd_largest_tenant  => 'date',
        self::property_condition                                    => 'string',
        self::most_recent_valuation_source                          => 'string',
        self::credit_tenant_lease_y_n                               => 'boolean',
        self::fourth_largest_tenant                                 => 'string',
        self::square_feet_of_fourth_4th_largest_tenant              => 'integer',
        self::fifth_largest_tenant                                  => 'string',
        self::square_feet_of_fifth_5th_largest_tenant               => 'integer',
        self::date_of_lease_expiration_of_fourth_4th_largest_tenant => 'date',
        self::date_of_lease_expiration_of_fifth_5th_largest_tenant  => 'date',
    ];

    protected $guarded = [];


    public function __construct( array $attributes = [] ) {
        parent::__construct( $attributes );
        $this->connection = env( 'DB_CONNECTION_CUSTODIAN_CTS' );
    }

}