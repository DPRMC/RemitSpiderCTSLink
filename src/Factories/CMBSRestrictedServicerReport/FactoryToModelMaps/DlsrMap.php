<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps;

use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsCmbsRestrictedServicerReportDlsr;

class DlsrMap extends AbstractFactoryToModelMap {

    /**
     * @var array These are Eloquent model fields that are dates, however the date value is stored in Excel's format. I use this array to "automate" conversion of the data.
     */
    public static array $excelDateFields = [
        CustodianCtsCmbsRestrictedServicerReportDlsr::paid_through_date,
        CustodianCtsCmbsRestrictedServicerReportDlsr::maturity_date,
        CustodianCtsCmbsRestrictedServicerReportDlsr::preced_fy_finan_as_of_date,
        CustodianCtsCmbsRestrictedServicerReportDlsr::most_recent_finan_as_of_end_date,
        CustodianCtsCmbsRestrictedServicerReportDlsr::most_recent_valuation_date,
        CustodianCtsCmbsRestrictedServicerReportDlsr::most_recent_special_servicer_transfer_date,
        CustodianCtsCmbsRestrictedServicerReportDlsr::date_asset_expected_to_be_resolved_or_foreclosed,
    ];


    /**
     * @var array Remove commas and currency glyphs from these guys.
     */
    public static array $numericFields = [
        CustodianCtsCmbsRestrictedServicerReportDlsr::current_net_rentable_sqft_or_number_of_units_beds_rooms,
        CustodianCtsCmbsRestrictedServicerReportDlsr::current_ending_scheduled_balance,
        CustodianCtsCmbsRestrictedServicerReportDlsr::cumulative_aser_amount,
        CustodianCtsCmbsRestrictedServicerReportDlsr::total_p_and_i_advance_outstanding,
        CustodianCtsCmbsRestrictedServicerReportDlsr::other_expenses_advances_outstanding,
        CustodianCtsCmbsRestrictedServicerReportDlsr::total_t_and_i_advance_outstanding,
        CustodianCtsCmbsRestrictedServicerReportDlsr::cumulative_accrued_unpaid_advance_interest,
        CustodianCtsCmbsRestrictedServicerReportDlsr::total_exposure,
        CustodianCtsCmbsRestrictedServicerReportDlsr::total_scheduled_p_and_i_due,
        CustodianCtsCmbsRestrictedServicerReportDlsr::dscr_noi_ncf,
        CustodianCtsCmbsRestrictedServicerReportDlsr::most_recent_value,
        CustodianCtsCmbsRestrictedServicerReportDlsr::loss_using_90percent_of_most_recent_value,
        CustodianCtsCmbsRestrictedServicerReportDlsr::ara_appraisal_reduction_amount,
    ];

    public static array $map = [
        CustodianCtsCmbsRestrictedServicerReportDlsr::date                                                    => [ 'date' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::category                                                => [ 'category' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::trans_id                                                => [ 'trans_id',
                                                                                                                   'transaction_id', ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::group_id                                                => [ 'group_id' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::loan_id                                                 => [ 'loan_id' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::prospectus_loan_id                                      => [ 'prospectus_loan_id' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::property_name                                           => [ 'property_name' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::property_type                                           => [ 'property_type' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::property_city                                           => [ 'property_city' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::property_state                                          => [ 'property_state' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::current_net_rentable_sqft_or_number_of_units_beds_rooms => [ 'current_net_rentable_sq_ft_or_number_of_units_beds_rooms',
                                                                                                                   'current_net_rentable_sqft_or_number_of_units_beds_rooms',
                                                                                                                   'current_net_rentable_square_feet',
                                                                                                                   'current_number_of_units_beds_rooms' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::paid_through_date                                       => [ 'paid_through_date' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::current_ending_scheduled_balance                        => [ 'current_ending_scheduled_balance' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::cumulative_aser_amount                                  => [ 'cumulative_aser_amount' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::total_p_and_i_advance_outstanding                       => [ 'total_p_and_i_advance_outstanding' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::other_expenses_advances_outstanding                     => [ 'other_expense_advance_outstanding',
                                                                                                                   'other_expenses_advances_outstanding', ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::total_t_and_i_advance_outstanding                       => [ 'total_t_and_i_advance_outstanding' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::cumulative_accrued_unpaid_advance_interest              => [ 'cumulative_accrued_unpaid_advance_interest' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::total_exposure                                          => [ 'total_exposure' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::total_scheduled_p_and_i_due                             => [ 'total_scheduled_p_and_i_due' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::current_note_rate                                       => [ 'current_note_rate' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::maturity_date                                           => [ 'maturity_date' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::preced_fy_finan_as_of_date                              => [ 'preced_fy_finan_as_of_date',
                                                                                                                   'preceding_fy_financial_as_of_date' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::most_recent_finan_as_of_end_date                        => [ 'most_recent_finan_as_of_end_date',
                                                                                                                   'preced_fy_finan_as_of_date_most_recent_finan_as_of_end_date' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::preced_fy_noi_ncf_most_recent_noi_ncf                   => [ 'preced_fy_noi_ncf_most_recent_noi_ncf' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::preced_fy_dscr_most_recent_dscr_noi_ncf                 => [ 'preced_fy_dscr_most_recent_dscr_noi_ncf' ],

        //
        CustodianCtsCmbsRestrictedServicerReportDlsr::dscr_noi_ncf                                            => [ 'dscr_noi_ncf' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::most_recent_valuation_date                              => [ 'most_recent_valuation_date' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::most_recent_value                                       => [ 'most_recent_value' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::loss_using_90percent_of_most_recent_value               => [ 'loss_using_90percent_of_most_recent_value' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::ara_appraisal_reduction_amount                          => [ 'ara_appraisal_reduction_amount' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::most_recent_special_servicer_transfer_date              => [ 'most_recent_special_servicer_transfer_date' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::date_asset_expected_to_be_resolved_or_foreclosed        => [ 'date_asset_expected_to_be_resolved_or_foreclosed' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::workout_strategy                                        => [ 'workout_strategy' ],
        CustodianCtsCmbsRestrictedServicerReportDlsr::comments_dlsr                                           => [ 'comments_dlsr' ],
    ];
}