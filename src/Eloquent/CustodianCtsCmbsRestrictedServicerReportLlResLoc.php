<?php


namespace DPRMC\RemitSpiderCTSLink\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class CustodianCtsCmbsRestrictedServicerReportLlResLoc extends AbstractCustodianCtsCmbsRestrictedServicerReportTab {

    public $table        = 'custodian_cts_cmbs_restricted_servicer_report_ll_res_locs';
    public $primaryKey   = self::id;
    public $keyType      = 'integer';
    public $incrementing = TRUE;

    const id                               = 'id';
//    const date                             = 'date';
//    const document_id                      = 'document_id';
    const created_at                       = 'created_at';
    const updated_at                       = 'updated_at';
    const trans_id                         = 'trans_id';
    const group_id                         = 'group_id';
    const loan_id                          = 'loan_id';
    const prospectus_loan_id               = 'prospectus_loan_id';
    const property_name                    = 'property_name';
    const paid_through_date                = 'paid_through_date';
    const current_ending_scheduled_balance = 'current_ending_scheduled_balance';
    const reserve_account_type             = 'reserve_account_type';
    const reserve_balance_at_contribution  = 'reserve_balance_at_contribution';
    const beginning_reserve_balance        = 'beginning_reserve_balance';
    const reserve_deposits                 = 'reserve_deposits';
    const reserve_disbursements            = 'reserve_disbursements';
    const ending_reserve_balance           = 'ending_reserve_balance';
    const loc_expiration_date              = 'loc_expiration_date';
    const comments_loan_level_reserve_loc  = 'comments_loan_level_reserve_loc';

    protected $casts = [
        self::date                             => 'date',
        self::document_id                      => 'integer',
        self::trans_id                         => 'string',
        self::group_id                         => 'string',
        self::loan_id                          => 'string',
        self::prospectus_loan_id               => 'string',
        self::property_name                    => 'string',
        self::paid_through_date                => 'string',
        self::current_ending_scheduled_balance => 'string',
        self::reserve_account_type             => 'string',
        self::reserve_balance_at_contribution  => 'string',
        self::beginning_reserve_balance        => 'string',
        self::reserve_deposits                 => 'string',
        self::reserve_disbursements            => 'string',
        self::ending_reserve_balance           => 'string',
        self::loc_expiration_date              => 'string',
        self::comments_loan_level_reserve_loc  => 'string',
    ];

    protected $fillable = [
        self::date,

        self::trans_id,
        self::group_id,
        self::loan_id,
        self::prospectus_loan_id,
        self::property_name,
        self::paid_through_date,
        self::current_ending_scheduled_balance,
        self::reserve_account_type,
        self::reserve_balance_at_contribution,
        self::beginning_reserve_balance,
        self::reserve_deposits,
        self::reserve_disbursements,
        self::ending_reserve_balance,
        self::loc_expiration_date,
        self::comments_loan_level_reserve_loc,
    ];


    public function __construct( array $attributes = [] ) {
        parent::__construct( $attributes );
        $this->connection = env( 'DB_CONNECTION_CUSTODIAN_CTS' );
    }
}