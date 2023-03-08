<?php


namespace DPRMC\RemitSpiderCTSLink\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class CustodianCtsCmbsRestrictedServicerReportTotalLoan extends Model {

    public $table        = 'custodian_cts_cmbs_restricted_servicer_report_total_loans';
    public $primaryKey   = self::id;
    public $keyType      = 'integer';
    public $incrementing = TRUE;

    const id                                              = 'id';
    const created_at                                      = 'created_at';
    const updated_at                                      = 'updated_at';
    const transaction_id                                  = 'transaction_id';
    const group_id                                        = 'group_id';
    const loan_id                                         = 'loan_id';
    const split_loan_id                                   = 'split_loan_id';
    const original_split_loan_amount                      = 'original_split_loan_amount';
    const prospectus_loan_id                              = 'prospectus_loan_id';
    const loan_contributor_to_securitization              = 'loan_contributor_to_securitization';
    const prospectus_loan_name                            = 'prospectus_loan_name';
    const original_shadow_rating_m_s_f_d                  = 'original_shadow_rating_m_s_f_d';
    const total_loan_amount_at_origination                = 'total_loan_amount_at_origination';
    const scheduled_principal_balance_at_contribution     = 'scheduled_principal_balance_at_contribution';
    const current_ending_scheduled_balance                = 'current_ending_scheduled_balance';
    const total_scheduled_p_and_i_due                     = 'total_scheduled_p_and_i_due';
    const current_note_rate                               = 'current_note_rate';
    const paid_through_date                               = 'paid_through_date';
    const sequential_pay_order                            = 'sequential_pay_order';
    const trustee                                         = 'trustee';
    const master_servicer                                 = 'master_servicer';
    const advancing_servicer                              = 'advancing_servicer';
    const special_servicer                                = 'special_servicer';
    const special_servicer_workout_control_type           = 'special_servicer_workout_control_type';
    const current_controlling_holder_or_operating_advisor = 'current_controlling_holder_or_operating_advisor';
    const controlling_class_rights                        = 'controlling_class_rights';
    const current_lockbox_status                          = 'current_lockbox_status';

    protected $casts = [
        self:: transaction_id                                  => 'string',
        self:: group_id                                        => 'string',
        self:: loan_id                                         => 'string',
        self:: split_loan_id                                   => 'string',
        self:: original_split_loan_amount                      => 'string',
        self:: prospectus_loan_id                              => 'string',
        self:: loan_contributor_to_securitization              => 'string',
        self:: prospectus_loan_name                            => 'string',
        self:: original_shadow_rating_m_s_f_d                  => 'string',
        self:: total_loan_amount_at_origination                => 'string',
        self:: scheduled_principal_balance_at_contribution     => 'string',
        self:: current_ending_scheduled_balance                => 'string',
        self:: total_scheduled_p_and_i_due                     => 'string',
        self:: current_note_rate                               => 'string',
        self:: paid_through_date                               => 'string',
        self:: sequential_pay_order                            => 'string',
        self:: trustee                                         => 'string',
        self:: master_servicer                                 => 'string',
        self:: advancing_servicer                              => 'string',
        self:: special_servicer                                => 'string',
        self:: special_servicer_workout_control_type           => 'string',
        self:: current_controlling_holder_or_operating_advisor => 'string',
        self:: controlling_class_rights                        => 'string',
        self:: current_lockbox_status                          => 'string',
    ];

    protected $fillable = [
        self:: transaction_id,
        self:: group_id,
        self:: loan_id,
        self:: split_loan_id,
        self:: original_split_loan_amount,
        self:: prospectus_loan_id,
        self:: loan_contributor_to_securitization,
        self:: prospectus_loan_name,
        self:: original_shadow_rating_m_s_f_d,
        self:: total_loan_amount_at_origination,
        self:: scheduled_principal_balance_at_contribution,
        self:: current_ending_scheduled_balance,
        self:: total_scheduled_p_and_i_due,
        self:: current_note_rate,
        self:: paid_through_date,
        self:: sequential_pay_order,
        self:: trustee,
        self:: master_servicer,
        self:: advancing_servicer,
        self:: special_servicer,
        self:: special_servicer_workout_control_type,
        self:: current_controlling_holder_or_operating_advisor,
        self:: controlling_class_rights,
        self:: current_lockbox_status,
    ];


    public function __construct( array $attributes = [] ) {
        parent::__construct( $attributes );
        $this->connection = env( 'DB_CONNECTION_CUSTODIAN_CTS' );
    }
}