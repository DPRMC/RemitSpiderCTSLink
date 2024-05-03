<?php

namespace DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\FactoryToModelMaps;

use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsCmbsRestrictedServicerReportLlResLoc;

class LlResLocMap extends AbstractFactoryToModelMap {
    public static array $jsonFieldsToIgnore = [
        'reserve_seq._#',
        'determination_date',
        'investor_#',
        'strategy_loan_no.',
    ];

    public static array $map = [
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::date                             => [ 'date' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::document_id                      => [ 'document_id' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::trans_id                         => [ 'trans_id' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::group_id                         => [ 'group_id' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::loan_id                          => [ 'loan_id' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::prospectus_loan_id               => [ 'prospectus_loan_id' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::property_name                    => [ 'property_name' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::paid_through_date                => [ 'paid_through_date',
                                                                                                'through_date' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::current_ending_scheduled_balance => [ 'current_ending_scheduled_balance',
                                                                                                'scheduled_balance' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::reserve_account_type             => [ 'reserve_account_type',
                                                                                                'account_type' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::reserve_balance_at_contribution  => [ 'reserve_balance_at_contribution',
                                                                                                'contribution' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::beginning_reserve_balance        => [ 'beginning_reserve_balance',
                                                                                                'reserve_balance' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::reserve_deposits                 => [ 'reserve_deposits' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::reserve_disbursements            => [ 'reserve_disbursements' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::ending_reserve_balance           => [ 'ending_reserve_balance' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::loc_expiration_date              => [ 'loc_expiration_date',
                                                                                                'expiration_date' ],
        CustodianCtsCmbsRestrictedServicerReportLlResLoc::comments_loan_level_reserve_loc  => [ 'comments_loan_level_reserve_loc',
                                                                                                'loan_level_reserve_loc' ],
    ];
}