<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps;

use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsCmbsRestrictedServicerReportTotalLoan;

class TotalLoanMap extends AbstractFactoryToModelMap {


//    public static array $jsonFieldsToIgnore = [
//        'strategy_loan_no.', // This has consistently been the same as the loan_id value.
//        'investor',
//        'determination_dt',
//        'officer_code', // The above four lines are in a block that is unique to just one spreadsheet.
//    ];

    /**
     * @var array These are Eloquent model fields that are dates, however the date value is stored in Excel's format. I use this array to "automate" conversion of the data.
     */
    public static array $excelDateFields = [

    ];


    /**
     * @var array Remove commas and currency glyphs from these guys.
     */
    public static array $numericFields = [
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::current_net_rentable_sqft_or_number_of_units_beds_rooms,
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::current_ending_scheduled_balance,
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::cumulative_aser_amount,
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::total_p_and_i_advance_outstanding,
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::other_expenses_advances_outstanding,
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::total_t_and_i_advance_outstanding,
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::cumulative_accrued_unpaid_advance_interest,
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::total_exposure,
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::total_scheduled_p_and_i_due,
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::dscr_noi_ncf,
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::most_recent_value,
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::loss_using_90percent_of_most_recent_value,
//        CustodianCtsCmbsRestrictedServicerReportWatchlist::ara_appraisal_reduction_amount
    ];

    public static array $map = [
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::transaction_id                                  => ['transaction_id'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::loan_id                                         => ['loan_id'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::split_loan_id                                   => ['split_loan_id'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::original_split_loan_amount                      => ['original_split_loan_amount'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::prospectus_loan_id                              => ['prospectus_loan_id'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::loan_contributor_to_securitization              => ['loan_contributor_to_securitization'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::prospectus_loan_name                            => ['prospectus_loan_name'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::original_shadow_rating_m_s_f_d                  => ['original_shadow_rating_m_s_f_d'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::total_loan_amount_at_origination                => ['total_loan_amount_at_origination'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::scheduled_principal_balance_at_contribution     => ['scheduled_principal_balance_at_contribution'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::current_ending_scheduled_balance                => ['current_ending_scheduled_balance'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::total_scheduled_p_and_i_due                     => ['total_scheduled_p_and_i_due'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::current_note_rate                               => ['current_note_rate'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::paid_through_date                               => ['paid_through_date'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::sequential_pay_order                            => ['sequential_pay_order'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::trustee                                         => ['trustee'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::master_servicer                                 => ['master_servicer'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::advancing_servicer                              => ['advancing_servicer'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::special_servicer                                => ['special_servicer'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::special_servicer_workout_control_type           => ['special_servicer_workout_control_type'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::current_controlling_holder_or_operating_advisor => ['current_controlling_holder_or_operating_advisor'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::controlling_class_rights                        => ['controlling_class_rights'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::current_lockbox_status                          => ['current_lockbox_status'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::date                                            => ['date'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::document_id                                     => ['document_id'],
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::group_id                                        => ['group_id']
    ];
}