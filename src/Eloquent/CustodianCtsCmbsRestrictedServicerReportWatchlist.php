<?php


namespace DPRMC\RemitSpiderCTSLink\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class CustodianCtsCmbsRestrictedServicerReportWatchlist extends Model {

    public $table        = 'custodian_cts_cmbs_restricted_servicer_report_watchlists';
    public $primaryKey   = self::id;
    public $keyType      = 'integer';
    public $incrementing = TRUE;

    const id                                         = 'id';
    const created_at                                 = 'created_at';
    const updated_at                                 = 'updated_at';
    const trans_id                                   = 'trans_id';
    const group_id                                   = 'group_id';
    const loan_id                                    = 'loan_id';
    const prospectus_loan_id                         = 'prospectus_loan_id';
    const property_name                              = 'property_name';
    const property_type                              = 'property_type';
    const property_city                              = 'property_city';
    const property_state                             = 'property_state';
    const date_added_to_servicer_watchlist           = 'date_added_to_servicer_watchlist';
    const current_ending_scheduled_balance           = 'current_ending_scheduled_balance';
    const paid_thru_date                             = 'paid_thru_date';
    const maturity_date                              = 'maturity_date';
    const preceding_fy_dscr_noi_ncf                  = 'preceding_fy_dscr_noi_ncf';
    const preceding_fiscal_year_financial_as_of_date = 'preceding_fiscal_year_financial_as_of_date';
    const most_recent_dscr_noi_ncf                   = 'most_recent_dscr_noi_ncf';
    const most_recent_financial_as_of_start_date     = 'most_recent_financial_as_of_start_date';
    const most_recent_financial_as_of_end_date       = 'most_recent_financial_as_of_end_date';
    const servicer_watch_list_code                   = 'servicer_watch_list_code';
    const comments_servicer_watchlist                = 'comments_servicer_watchlist';
    const informational_or_credit                    = 'informational_or_credit';
    const most_recent_physical_occupancy             = 'most_recent_physical_occupancy';
    const most_recent_occupancy_as_of_date           = 'most_recent_occupancy_as_of_date';

    protected $casts = [
        self:: trans_id                                   => 'string',
        self:: group_id                                   => 'string',
        self:: loan_id                                    => 'string',
        self:: prospectus_loan_id                         => 'string',
        self:: property_name                              => 'string',
        self:: property_type                              => 'string',
        self:: property_city                              => 'string',
        self:: property_state                             => 'string',
        self:: date_added_to_servicer_watchlist           => 'string',
        self:: current_ending_scheduled_balance           => 'string',
        self:: paid_thru_date                             => 'string',
        self:: maturity_date                              => 'string',
        self:: preceding_fy_dscr_noi_ncf                  => 'string',
        self:: preceding_fiscal_year_financial_as_of_date => 'string',
        self:: most_recent_dscr_noi_ncf                   => 'string',
        self:: most_recent_financial_as_of_start_date     => 'string',
        self:: most_recent_financial_as_of_end_date       => 'string',
        self:: servicer_watch_list_code                   => 'string',
        self:: comments_servicer_watchlist                => 'string',
        self:: informational_or_credit                    => 'string',
        self:: most_recent_physical_occupancy             => 'string',
        self:: most_recent_occupancy_as_of_date           => 'string',
    ];

    protected $fillable = [
        self:: trans_id,
        self:: group_id,
        self:: loan_id,
        self:: prospectus_loan_id,
        self:: property_name,
        self:: property_type,
        self:: property_city,
        self:: property_state,
        self:: date_added_to_servicer_watchlist,
        self:: current_ending_scheduled_balance,
        self:: paid_thru_date,
        self:: maturity_date,
        self:: preceding_fy_dscr_noi_ncf,
        self:: preceding_fiscal_year_financial_as_of_date,
        self:: most_recent_dscr_noi_ncf,
        self:: most_recent_financial_as_of_start_date,
        self:: most_recent_financial_as_of_end_date,
        self:: servicer_watch_list_code,
        self:: comments_servicer_watchlist,
        self:: informational_or_credit,
        self:: most_recent_physical_occupancy,
        self:: most_recent_occupancy_as_of_date,
    ];


    public function __construct( array $attributes = [] ) {
        parent::__construct( $attributes );
        $this->connection = env( '' );
    }
}