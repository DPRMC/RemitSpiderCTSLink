<?php


namespace DPRMC\RemitSpiderCTSLink\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class CustodianCtsCmbsRestrictedServicerReportAdvanceRecovery extends Model {

    public $table        = 'custodian_cts_cmbs_restricted_servicer_report_advance_recoveries';
    public $primaryKey   = self::id;
    public $keyType      = 'integer';
    public $incrementing = TRUE;

    const id                                                            = 'id';
    const created_at                                                    = 'created_at';
    const updated_at                                                    = 'updated_at';
    const trans_id                                                      = 'trans_id';
    const group_id                                                      = 'group_id';
    const prospectus_loan_id                                            = 'prospectus_loan_id';
    const loan_id                                                       = 'loan_id';
    const date_of_last_modification                                     = 'date_of_last_modification';
    const wodra_deemed_non_recoverable_date                             = 'wodra_deemed_non_recoverable_date';
    const servicer_info_unreimbursed_adv_initial_amount                 = 'servicer_info_unreimbursed_adv_initial_amount';
    const servicer_info_unreimbursed_adv_advance_interest               = 'servicer_info_unreimbursed_adv_advance_interest';
    const servicer_info_initial_reimbursement_date_date                 = 'servicer_info_initial_reimbursement_date_date';
    const servicer_info_reimbursed_adv_principal_collections_current    = 'servicer_info_reimbursed_adv_principal_collections_current';
    const servicer_info_reimbursed_adv_principal_collections_cumulative = 'servicer_info_reimbursed_adv_principal_collections_cumulative';
    const servicer_info_reimbursed_adv_interest_collections_current     = 'servicer_info_reimbursed_adv_interest_collections_current';
    const servicer_info_reimbursed_adv_interest_collections_cumulative  = 'servicer_info_reimbursed_adv_interest_collections_cumulative';
    const servicer_info_amounts_outstanding                             = 'servicer_info_amounts_outstanding';
    const borrower_info_unliquidated_advances_beginning_balances        = 'borrower_info_unliquidated_advances_beginning_balances';
    const borrower_info_current_principal_amounts_paid_by_borrower      = 'borrower_info_current_principal_amounts_paid_by_borrower';
    const borrower_info_unliquidated_advances_ending_balance            = 'borrower_info_unliquidated_advances_ending_balance';
    const is_it_still_recoverable_or_nonrecoverable                     = 'is_it_still_recoverable_or_nonrecoverable';
    const if_nonrec_adv_reimb_from_prin_realized_loss_amount            = 'if_nonrec_adv_reimb_from_prin_realized_loss_amount';
    const comments_advance_recovery                                     = 'comments_advance_recovery';
    const actual_balance                                                = 'actual_balance';
    const current_hyper_amortizing_date                                 = 'current_hyper_amortizing_date';
    const maturity_date                                                 = 'maturity_date';

    protected $casts = [
        self:: trans_id                                                      => 'string',
        self:: group_id                                                      => 'string',
        self:: prospectus_loan_id                                            => 'string',
        self:: loan_id                                                       => 'string',
        self:: date_of_last_modification                                     => 'string',
        self:: wodra_deemed_non_recoverable_date                             => 'string',
        self:: servicer_info_unreimbursed_adv_initial_amount                 => 'string',
        self:: servicer_info_unreimbursed_adv_advance_interest               => 'string',
        self:: servicer_info_initial_reimbursement_date_date                 => 'string',
        self:: servicer_info_reimbursed_adv_principal_collections_current    => 'string',
        self:: servicer_info_reimbursed_adv_principal_collections_cumulative => 'string',
        self:: servicer_info_reimbursed_adv_interest_collections_current     => 'string',
        self:: servicer_info_reimbursed_adv_interest_collections_cumulative  => 'string',
        self:: servicer_info_amounts_outstanding                             => 'string',
        self:: borrower_info_unliquidated_advances_beginning_balances        => 'string',
        self:: borrower_info_current_principal_amounts_paid_by_borrower      => 'string',
        self:: borrower_info_unliquidated_advances_ending_balance            => 'string',
        self:: is_it_still_recoverable_or_nonrecoverable                     => 'string',
        self:: if_nonrec_adv_reimb_from_prin_realized_loss_amount            => 'string',
        self:: comments_advance_recovery                                     => 'string',
        self:: actual_balance                                                => 'string',
        self:: current_hyper_amortizing_date                                 => 'string',
        self:: maturity_date                                                 => 'string',
    ];

    protected $fillable = [
        self:: trans_id,
        self:: group_id,
        self:: prospectus_loan_id,
        self:: loan_id,
        self:: date_of_last_modification,
        self:: wodra_deemed_non_recoverable_date,
        self:: servicer_info_unreimbursed_adv_initial_amount,
        self:: servicer_info_unreimbursed_adv_advance_interest,
        self:: servicer_info_initial_reimbursement_date_date,
        self:: servicer_info_reimbursed_adv_principal_collections_current,
        self:: servicer_info_reimbursed_adv_principal_collections_cumulative,
        self:: servicer_info_reimbursed_adv_interest_collections_current,
        self:: servicer_info_reimbursed_adv_interest_collections_cumulative,
        self:: servicer_info_amounts_outstanding,
        self:: borrower_info_unliquidated_advances_beginning_balances,
        self:: borrower_info_current_principal_amounts_paid_by_borrower,
        self:: borrower_info_unliquidated_advances_ending_balance,
        self:: is_it_still_recoverable_or_nonrecoverable,
        self:: if_nonrec_adv_reimb_from_prin_realized_loss_amount,
        self:: comments_advance_recovery,
        self:: actual_balance,
        self:: current_hyper_amortizing_date,
        self:: maturity_date,
    ];


    public function __construct( array $attributes = [] ) {
        parent::__construct( $attributes );
        $this->connection = env( '' );
    }
}