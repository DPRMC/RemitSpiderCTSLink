<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps;

use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsCmbsRestrictedServicerReportReosr;

class ReosrMap extends AbstractFactoryToModelMap {

    /**
     * @var array These are Eloquent model fields that are dates, however the date value is stored in Excel's format. I use this array to "automate" conversion of the data.
     */
    public static array $excelDateFields = [
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::paid_through_date,
    ];


    /**
     * @var array Remove commas and currency glyphs from these guys.
     */
    public static array $numericFields = [
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::current_net_rentable_sqft_or_number_of_units_beds_rooms,
    ];


    public static array $percentFields = [

    ];



    public static array $map = [
        CustodianCtsCmbsRestrictedServicerReportReosr::date                                                        => [ 'date' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::document_id                                                 => [ 'document_id' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::trans_id                                                    => [ 'trans_id' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::loan_id                                                     => [ 'loan_id' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::prospectus_loan_id                                          => [ 'prospectus_loan_id',
                                                                                                                        'prospectus_id' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::property_id                                                 => [ 'property_id' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::property_name                                               => [ 'property_name' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::property_type                                               => [ 'property_type' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::property_city                                               => [ 'property_city' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::property_state                                              => [ 'property_state' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::current_net_rentable_sq_ft_or_number_of_units_beds_rooms    => [ 'current_net_rentable_sq_ft_or_number_of_units_beds_rooms',
                                                                                                                        'current_net_rentable_square_feet',
                                                                                                                        'current_number_of_units_beds_rooms' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::paid_through_date                                           => [ 'paid_through_date',
                                                                                                                        'through_date',
                                                                                                                        'paid_thru_date', ],
        CustodianCtsCmbsRestrictedServicerReportReosr::current_allocated_ending_scheduled_loan_amount              => [ 'current_allocated_ending_scheduled_loan_amount' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::cumulative_aser_amount                                      => [ 'cumulative_aser_amount' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::total_p_and_i_advance_outstanding                           => [ 'total_p_and_i_advance_outstanding',
                                                                                                                        'total_p_and_i_advanced_outstanding' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::other_expense_advance_outstanding                           => [ 'other_expense_advance_outstanding' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::total_t_and_i_advance_outstanding                           => [ 'total_t_and_i_advance_outstanding' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::cumulative_accrued_unpaid_advance_interest                  => [ 'cumulative_accrued_unpaid_advance_interest' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::total_exposure                                              => [ 'total_exposure' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::total_scheduled_p_and_i_due                                 => [ 'total_scheduled_p_and_i_due' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::maturity_date                                               => [ 'maturity_date' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::preced_fy_finan_as_of_date_most_recent_finan_as_of_end_date => [ 'preced_fy_finan_as_of_date_most_recent_finan_as_of_end_date' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::preced_fy_dscr_most_recent_dscr_noi_ncf                     => [ 'preced_fy_dscr_most_recent_dscr_noi_ncf' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::most_recent_valuation_date                                  => [ 'most_recent_valuation_date' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::most_recent_valuation_source                                => [ 'most_recent_valuation_source' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::most_recent_value                                           => [ 'most_recent_value' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::loss_using_90percent_of_most_recent_value                   => [ 'loss_using_90percent_of_most_recent_value',
                                                                                                                        'loss_using_90percent_of_most_recent_value(f' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::ara_appraisal_reduction_amount                              => [ 'ara_appraisal_reduction_amount' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::most_recent_special_servicer_transfer_date                  => [ 'most_recent_special_transfer_date' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::reo_date                                                    => [ 'reo_date' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::date_asset_expected_to_be_resolved_or_foreclosed            => [ 'date_asset_expected_to_be_resolved_or_foreclosed' ],
        CustodianCtsCmbsRestrictedServicerReportReosr::comments_reo                                                => [ 'comments_reo' ],
    ];

}