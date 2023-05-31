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


    protected $casts = [
        self::shelf  => 'string',
        self::series => 'string',
        self::url    => 'string',

    ];

    protected $guarded = [];


    public function __construct( array $attributes = [] ) {
        parent::__construct( $attributes );
        $this->connection = env( 'DB_CONNECTION_CUSTODIAN_CTS' );
    }
}