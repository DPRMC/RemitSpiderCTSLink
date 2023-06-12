<?php


namespace DPRMC\RemitSpiderCTSLink\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class CustodianCtsSeries extends Model {

    public $table        = 'custodian_cts_serieses';
    public $primaryKey   = self::id;
    public $keyType      = 'integer';
    public $incrementing = TRUE;

    const id         = 'id';
    const created_at = 'created_at';
    const updated_at = 'updated_at';
    const shelf      = 'shelf';
    const series     = 'series';
    const url        = 'url';
    const has_access = 'has_access';

    const terminated                                      = 'terminated';
    const current_cycle                                   = 'current_cycle';
    const next_cycle                                      = 'next_cycle';
    const next_available                                  = 'next_available';
    const revised_date                                    = 'revised_date';
    const link_to_most_recent_distribution_date_statement = 'link_to_most_recent_distribution_date_statement';
    const link_to_historical_distribution_date_statements = 'link_to_historical_distribution_date_statements';

    const product_type = 'product_type';

    // This is the last time this shelf/series was checked for documents.
    // This is an important field to look at when determining the health of the system.
    // And more specifically, how up to date our data is.
    const last_checked = 'last_checked';

    protected $casts = [
        self::shelf      => 'string',
        self::series     => 'string',
        self::url        => 'string',
        self::has_access => 'boolean',

        self::terminated     => 'boolean',
        self::current_cycle  => 'date',
        self::next_cycle     => 'date',
        self::next_available => 'date',
        self::revised_date   => 'date',

        self::link_to_most_recent_distribution_date_statement => 'string',
        self::link_to_historical_distribution_date_statements => 'string',

        self::product_type => 'string',

        self::last_checked => 'date'
    ];

    protected $guarded = [];


    public function __construct( array $attributes = [] ) {
        parent::__construct( $attributes );
        $this->connection = env( 'DB_CONNECTION_CUSTODIAN_CTS' );
    }


}