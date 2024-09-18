<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps;

use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsCmbsRestrictedServicerReportWatchlist;

class WatchlistMap extends AbstractFactoryToModelMap {


    public static array $jsonFieldsToIgnore = [
        'strategy_loan_no.', // This has consistently been the same as the loan_id value.
        'investor',
        'determination_dt',
        'officer_code', // The above four lines are in a block that is unique to just one spreadsheet.
    ];

    /**
     * @var array These are Eloquent model fields that are dates, however the date value is stored in Excel's format. I use this array to "automate" conversion of the data.
     */
    public static array $excelDateFields = [

        CustodianCtsCmbsRestrictedServicerReportWatchlist::date_added_to_servicer_watchlist,
        CustodianCtsCmbsRestrictedServicerReportWatchlist::paid_thru_date,
        CustodianCtsCmbsRestrictedServicerReportWatchlist::maturity_date,
        CustodianCtsCmbsRestrictedServicerReportWatchlist::preceding_fiscal_year_financial_as_of_date,
        CustodianCtsCmbsRestrictedServicerReportWatchlist::most_recent_financial_as_of_start_date,
        CustodianCtsCmbsRestrictedServicerReportWatchlist::most_recent_financial_as_of_end_date,
        CustodianCtsCmbsRestrictedServicerReportWatchlist::most_recent_occupancy_as_of_date,

    ];


    /**
     * @var array Remove commas and currency glyphs from these guys.
     */
    public static array $numericFields = [
        CustodianCtsCmbsRestrictedServicerReportWatchlist::current_ending_scheduled_balance,
        CustodianCtsCmbsRestrictedServicerReportWatchlist::preceding_fy_dscr_noi_ncf,
        CustodianCtsCmbsRestrictedServicerReportWatchlist::most_recent_dscr_noi_ncf,

    ];


    public static array $percentFields = [
        CustodianCtsCmbsRestrictedServicerReportWatchlist::most_recent_physical_occupancy,

    ];


    public static array $map = [
        CustodianCtsCmbsRestrictedServicerReportWatchlist::date                                       => [ 'date' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::document_id                                => [ 'document_id' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::trans_id                                   => [ 'trans_id',
                                                                                                           'transaction_id',
                                                                                                           'tran_id', ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::group_id                                   => [ 'group_id' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::loan_id                                    => [ 'loan_id' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::prospectus_loan_id                         => [ 'prospectus_loan_id' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::property_name                              => [ 'property_name' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::property_type                              => [ 'property_type' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::property_city                              => [ 'property_city',
                                                                                                           'city', ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::property_state                             => [ 'property_state',
                                                                                                           'state', ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::current_ending_scheduled_balance           => [ 'current_ending_scheduled_balance',
                                                                                                           'scheduled_balance',
                                                                                                           'ending_scheduled_loan_balance', ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::maturity_date                              => [ 'maturity_date' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::date_added_to_servicer_watchlist           => [ 'date_added_to_servicer_watchlist',
                                                                                                           'date_added_to_watchlist', ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::paid_thru_date                             => [ 'paid_thru_date',
                                                                                                           'paid_through_date',
                                                                                                           'through_date' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::preceding_fy_dscr_noi_ncf                  => [ 'preceding_fy_dscr_noi_ncf',
                                                                                                           'preceding_fiscal_yr_dscr_noi_ncf' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::preceding_fiscal_year_financial_as_of_date => [ 'preceding_fiscal_year_financial_as_of_date',
                                                                                                           'preceding_fy_financial_as_of_date',
                                                                                                           'preceding_fiscal_yr_financial_as_of_date' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::most_recent_dscr_noi_ncf                   => [ 'most_recent_dscr_noi_ncf',
                                                                                                           'dscr_noi_ncf' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::most_recent_financial_as_of_start_date     => [ 'most_recent_financial_as_of_start_date',
                                                                                                           'financial_as_of_start_date' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::most_recent_financial_as_of_end_date       => [ 'most_recent_financial_as_of_end_date',
                                                                                                           'financial_as_of_end_date',
                                                                                                           'financial_as_of_date' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::servicer_watch_list_code                   => [ 'servicer_watch_list_code',
                                                                                                           'servicer_watchlist_code',
                                                                                                           'watchlist_code',
                                                                                                           'trigger_codes', ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::comments_servicer_watchlist                => [ 'comments_servicer_watchlist',
                                                                                                           'servicer_watchlist',
                                                                                                           'watchlist_comments',
                                                                                                           'comment_action_to_be_taken', ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::informational_or_credit                    => [ 'informational_or_credit',
                                                                                                           'or_credit',
                                                                                                           'informational_credit',
                                                                                                           'credit' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::most_recent_physical_occupancy             => [ 'most_recent_physical_occupancy',
                                                                                                           'physical_occupancy' ],
        CustodianCtsCmbsRestrictedServicerReportWatchlist::most_recent_occupancy_as_of_date           => [ 'most_recent_occupancy_as_of_date',
                                                                                                           'occupancy_as_of_date',
                                                                                                           'most_recent_occupancy_date',
                                                                                                           'most_recent_physical_occupancy_as_of_date', ],


    ];
}