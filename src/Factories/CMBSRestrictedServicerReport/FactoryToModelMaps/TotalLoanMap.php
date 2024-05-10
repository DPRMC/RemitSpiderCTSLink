<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps;

use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsCmbsRestrictedServicerReportTotalLoan;

class TotalLoanMap extends AbstractFactoryToModelMap {


    public static array $jsonFieldsToIgnore = [];

    /**
     * @var array These are Eloquent model fields that are dates, however the date value is stored in Excel's format. I use this array to "automate" conversion of the data.
     */
    public static array $excelDateFields = [
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::date,
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::paid_through_date
    ];


    /**
     * @var array Remove commas and currency glyphs from these guys.
     */
    public static array $numericFields = [
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::document_id,
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::total_loan_amount_at_origination,
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::original_split_loan_amount,
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::total_loan_amount_at_origination,
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::scheduled_principal_balance_at_contribution,
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::current_ending_scheduled_balance,
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::total_scheduled_p_and_i_due,
        CustodianCtsCmbsRestrictedServicerReportTotalLoan::current_note_rate

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