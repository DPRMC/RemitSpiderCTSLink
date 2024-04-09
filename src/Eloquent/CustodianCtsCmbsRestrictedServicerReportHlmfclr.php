<?php


namespace DPRMC\RemitSpiderCTSLink\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class CustodianCtsCmbsRestrictedServicerReportHlmfclr extends AbstractCustodianCtsCmbsRestrictedServicerReportTab {

    public $table        = 'custodian_cts_cmbs_restricted_servicer_report_hlmfclrs';
    public $primaryKey   = self::id;
    public $keyType      = 'integer';
    public $incrementing = TRUE;

    const id                                                     = 'id';
//    const date                                                   = 'date';
//    const document_id                                            = 'document_id';
    const created_at                                             = 'created_at';
    const updated_at                                             = 'updated_at';
    const category                                               = 'category';
    const trans_id                                               = 'trans_id';
    const group_id                                               = 'group_id';
    const loan_id                                                = 'loan_id';
    const prospectus_loan_id                                     = 'prospectus_loan_id';
    const property_city                                          = 'property_city';
    const property_state                                         = 'property_state';
    const modification_code                                      = 'modification_code';
    const most_recent_master_servicer_return_date                = 'most_recent_master_servicer_return_date';
    const date_of_last_modification                              = 'date_of_last_modification';
    const balance_when_sent_to_special_servicer                  = 'balance_when_sent_to_special_servicer';
    const balance_at_the_effective_date_of_modification          = 'balance_at_the_effective_date_of_modification';
    const old_note_rate                                          = 'old_note_rate';
    const number_of_months_for_rate_change                       = 'number_of_months_for_rate_change';
    const modified_note_rate                                     = 'modified_note_rate';
    const old_p_and_i                                            = 'old_p_and_i';
    const modified_payment_amount                                = 'modified_payment_amount';
    const old_maturity_date                                      = 'old_maturity_date';
    const maturity_date                                          = 'maturity_date';
    const total_months_for_change_of_modification                = 'total_months_for_change_of_modification';
    const realized_loss_to_trust                                 = 'realized_loss_to_trust';
    const estimated_future_interest_loss_to_trust_rate_reduction = 'estimated_future_interest_loss_to_trust_rate_reduction';
    const comments_hlmr_cml                                      = 'comments_hlmr_cml';
    const modification_execution_date                            = 'modification_execution_date';
    const modification_booking_date                              = 'modification_booking_date';

    protected $casts = [
        self::date                                                   => 'date',
        self::document_id                                            => 'integer',
        self::trans_id                                               => 'string',
        self::group_id                                               => 'string',
        self::loan_id                                                => 'string',
        self::category                                               => 'string',
        self::prospectus_loan_id                                     => 'string',
        self::property_city                                          => 'string',
        self::property_state                                         => 'string',
        self::modification_code                                      => 'string',
        self::most_recent_master_servicer_return_date                => 'string',
        self::date_of_last_modification                              => 'string',
        self::balance_when_sent_to_special_servicer                  => 'string',
        self::balance_at_the_effective_date_of_modification          => 'string',
        self::old_note_rate                                          => 'string',
        self::number_of_months_for_rate_change                       => 'string',
        self::modified_note_rate                                     => 'string',
        self::old_p_and_i                                            => 'string',
        self::modified_payment_amount                                => 'string',
        self::old_maturity_date                                      => 'string',
        self::maturity_date                                          => 'string',
        self::total_months_for_change_of_modification                => 'string',
        self::realized_loss_to_trust                                 => 'string',
        self::estimated_future_interest_loss_to_trust_rate_reduction => 'string',
        self::comments_hlmr_cml                                      => 'string',
        self::modification_execution_date                            => 'string',
        self::modification_booking_date                              => 'string',
    ];

    protected $guarded = [ 'id' ];


    public function __construct( array $attributes = [] ) {
        parent::__construct( $attributes );
        $this->connection = env( 'DB_CONNECTION_CUSTODIAN_CTS' );
    }
}