<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps;

use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsCmbsRestrictedServicerReportHlmfclr;

class HLMFCLRMap extends AbstractFactoryToModelMap {

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


    /**
     * "date" => "2023-10-11"
     * "category" => "loan_modifications_forbearance"
     * "trans_id" => "MS2013C10"
     * "group_id" => ""
     * "loan_id" => "30305127"
     * "prospectus_loan_id" => "52"
     * "property_city" => "Onalaska"
     * "property_state" => "WI"
     * "modification_code" => "Other"
     * "most_recent_master_servicer_return_date" => ""
     * "date_of_last_modification" => "20200601"
     * "balance_when_sent_to_special_servicer" => ""
     * "balance_at_the_effective_date_of_modification" => ""
     * "old_note_rate" => ""
     * "number_of_months_for_rate_change" => ""
     * "modified_note_rate" => ""
     * "old_p_and_i" => ""
     * "modified_payment_amount" => ""
     * "old_maturity_date" => ""
     * "maturity_date" => ""
     * "total_months_for_change_of_modification" => ""
     * "realized_loss_to_trust" => ""
     * "estimated_future_interest_loss_to_trust_rate_reduction" => ""
     * "comments_hlmfclr" => "Forbearance Period: 6/1/2020-8/31/2020 (Payments), 9/1/2020-8/31/2021 (Replenishment)"
     * "modification_execution_date" => "20200526"
     * "modification_booking_date" => "20200529"
     */
    public static array $map = [
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::date                                                   => [ 'date' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::category                                               => [ 'category' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::trans_id                                               => [ 'trans_id' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::group_id                                               => [ 'group_id' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::loan_id                                                => [ 'loan_id' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::prospectus_loan_id                                     => [ 'prospectus_loan_id' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::property_city                                          => [ 'property_city' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::property_state                                         => [ 'property_state' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::modification_code                                      => [ 'modification_code' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::most_recent_master_servicer_return_date                => [ 'most_recent_master_servicer_return_date' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::date_of_last_modification                              => [ 'date_of_last_modification' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::balance_when_sent_to_special_servicer                  => [ 'balance_when_sent_to_special_servicer' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::balance_at_the_effective_date_of_modification          => [ 'balance_at_the_effective_date_of_modification' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::old_note_rate                                          => [ 'old_note_rate' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::number_of_months_for_rate_change                       => [ 'number_of_months_for_rate_change' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::modified_note_rate                                     => [ 'modified_note_rate' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::old_p_and_i                                            => [ 'old_p_and_i' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::modified_payment_amount                                => [ 'modified_payment_amount' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::old_maturity_date                                      => [ 'old_maturity_date' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::maturity_date                                          => [ 'maturity_date' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::total_months_for_change_of_modification                => [ 'total_months_for_change_of_modification' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::realized_loss_to_trust                                 => [ 'realized_loss_to_trust' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::estimated_future_interest_loss_to_trust_rate_reduction => [ 'estimated_future_interest_loss_to_trust_rate_reduction' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::comments_hlmr_cml                                      => [ 'comments_hlmfclr' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::modification_execution_date                            => [ 'modification_execution_date' ],
        CustodianCtsCmbsRestrictedServicerReportHlmfclr::modification_booking_date                              => [ 'modification_booking_date' ],
    ];

}