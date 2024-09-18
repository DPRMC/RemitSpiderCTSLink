<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps;

use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsCmbsRestrictedServicerReportCfsr;

class CsfrMap extends AbstractFactoryToModelMap {

    public static array $jsonFieldsToIgnore = [
        'strategy_loan_no.',
        'investor_#',
        'property_seq_#',
        'determination_date',
        'collateral_id',
    ];

    public static array $excelDateFields = [
        CustodianCtsCmbsRestrictedServicerReportCfsr::date_of_last_inspection,
        CustodianCtsCmbsRestrictedServicerReportCfsr::paid_through_date,
        CustodianCtsCmbsRestrictedServicerReportCfsr::at_contribution_information_base_year_financials_as_of_date,
        CustodianCtsCmbsRestrictedServicerReportCfsr::sec_prec_fy_operating_info_as_of_financials_as_of_date,
        CustodianCtsCmbsRestrictedServicerReportCfsr::prec_fy_operating_info_as_of_financials_as_of_date,
        CustodianCtsCmbsRestrictedServicerReportCfsr::most_recent_financial_information_financial_as_of_start_date,
        CustodianCtsCmbsRestrictedServicerReportCfsr::most_recent_financial_information_financial_as_of_end_date,
        CustodianCtsCmbsRestrictedServicerReportCfsr::most_recent_financial_information_occup_as_of_date,
    ];

    public static array $numericFields = [];

    public static array $map = [
        CustodianCtsCmbsRestrictedServicerReportCfsr::date                                                         => [ 'date' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::document_id                                                  => [ 'document_id' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::trans_id                                                     => [ 'trans_id',
                                                                                                                        'transaction_id',
                                                                                                                        'trans', ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::loan_id                                                      => [ 'loan_id',
                                                                                                                        'loan',
                                                                                                                        'loan_number' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::prospectus_loan_id                                           => [ 'prospectus_id',
                                                                                                                        'prospectus' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::property_id                                                  => [ 'property_id',
                                                                                                                        'property' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::property_city                                                => [ 'city' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::property_state                                               => [ 'state' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::date_of_last_inspection                                      => [ 'last_property_inspection_date',
                                                                                                                        'last', ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::property_condition                                           => [ 'property_condition' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::current_allocated_ending_scheduled_loan_amount               => [ 'current_allocated_loan_amount',
                                                                                                                        'scheduled', ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::paid_through_date                                            => [ 'paid_thru_date',
                                                                                                                        'through' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::at_contribution_information_base_year_financials_as_of_date  => [ 'at_contribution_information_base_year_financial_info_as_of_date',
                                                                                                                        'at_contribution_information_base_year_financials', ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::at_contribution_information_base_year_physical_occup_percent => [ 'at_contribution_information_base_year_percent_occ',
                                                                                                                        'at_contribution_information_base_year_occup', ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::at_contribution_information_base_year_revenue                => [ 'at_contribution_information_base_year_total_revenue' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::at_contribution_information_base_year_noi_ncf                => [ 'at_contribution_information_base_year_noi_ncf' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::at_contribution_information_base_year_dscr_noi_ncf           => [ 'at_contribution_information_base_year_dscr' ],


        CustodianCtsCmbsRestrictedServicerReportCfsr::sec_prec_fy_operating_info_as_of_financials_as_of_date       => [ 'sec_prec_fy_operating_info_as_of_financial_info_as_of_date',
                                                                                                                        'sec_prec_fy_operating_info_as_of_financials', ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::sec_prec_fy_operating_info_normalized_physical_occup_percent => [ 'sec_prec_fy_operating_info_normalized_percent_occ',
                                                                                                                        'sec_prec_fy_operating_info_normalized_occup', ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::sec_prec_fy_operating_info_normalized_revenue                => [ 'sec_prec_fy_operating_info_normalized_total_revenue',
                                                                                                                        'sec_prec_fy_operating_info_normalized_normalized_revenue' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::sec_prec_fy_operating_info_normalized_noi_ncf                => [ 'sec_prec_fy_operating_info_normalized_noi_ncf',
                                                                                                                        'sec_prec_fy_operating_info_normalized_normalized_noi_ncf', ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::sec_prec_fy_operating_info_normalized_dscr_noi_ncf           => [ 'sec_prec_fy_operating_info_normalized_dscr' ],


        CustodianCtsCmbsRestrictedServicerReportCfsr::prec_fy_operating_info_as_of_financials_as_of_date       => [ 'prec_fy_operating_info_as_of_financial_info_as_of_date',
                                                                                                                    'prec_fy_operating_info_as_of_financials',
                                                                                                                    'preceding_fy_operating_information_as_of_financials_as_of_date',
                                                                                                                    'prec_fy_operating_info_as_of_as_of_financial_info_as_of_date', ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::prec_fy_operating_info_normalized_physical_occup_percent => [ 'prec_fy_operating_info_normalized_percent_occ',
                                                                                                                    'prec_fy_operating_info_normalized_occup', ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::prec_fy_operating_info_normalized_revenue                => [ 'prec_fy_operating_info_normalized_total_revenue',
                                                                                                                    'prec_fy_operating_info_normalized_normalized_revenue' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::prec_fy_operating_info_normalized_noi_ncf                => [ 'prec_fy_operating_info_normalized_noi_ncf',
                                                                                                                    'prec_fy_operating_info_normalized_normalized_noi_ncf' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::prec_fy_operating_info_normalized_dscr_noi_ncf           => [ 'prec_fy_operating_info_normalized_dscr' ],


        CustodianCtsCmbsRestrictedServicerReportCfsr::most_recent_financial_information_financial_as_of_start_date => [ 'most_recent_financial_information_financial_as_of_start_date',
                                                                                                                        'most_recent_financial_information_as_of', ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::most_recent_financial_information_financial_as_of_end_date   => [ 'most_recent_financial_information_financial_as_of_end_date' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::most_recent_financial_information_occup_as_of_date           => [ 'most_recent_financial_information_occup_as_of_date',
                                                                                                                        'most_recent_financial_information_occup', ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::most_recent_financial_information_physical_occup_percent     => [ 'most_recent_financial_information_physical_occup_percent' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::most_recent_financial_information_normalized_revenue         => [ 'most_recent_financial_information_normalized_revenue',
                                                                                                                        'most_recent_financial_information_normalized_normalized_revenue' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::most_recent_financial_information_normalized_noi_ncf         => [ 'most_recent_financial_information_normalized_noi_ncf' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::most_recent_financial_information_normalized_dscr_noi_ncf    => [ 'most_recent_financial_information_normalized_dscr' ],


        CustodianCtsCmbsRestrictedServicerReportCfsr::net_change_preceding_and_base_year_percent_occup         => [ 'net_change_preceding_and_base_year_percent_occ',
                                                                                                                    'net_change_preceding_and_base_year_percent',
                                                                                                                    'preceding_and_base_year_percent_occup',
                                                                                                                    'net_change_preceding_and_base_year_occup' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::net_change_preceding_and_base_year_percent_total_revenue => [ 'net_change_preceding_and_base_year_percent_total_revenue',
                                                                                                                    'net_change_preceding_and_base_year_total',
                                                                                                                    'net_change_preceding_and_base_year_total_revenue' ],
        CustodianCtsCmbsRestrictedServicerReportCfsr::net_change_preceding_and_base_year_dscr                  => [ 'net_change_preceding_and_base_year_dscr',
                                                                                                                    'net_change_preceding_and_base_year_', ],
    ];
}