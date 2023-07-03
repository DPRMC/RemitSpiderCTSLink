<?php


namespace DPRMC\RemitSpiderCTSLink\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class CustodianCtsLink extends Model {

    public $table        = 'custodian_cts_links';
    public $primaryKey   = self::id;
    public $keyType      = 'integer';
    public $incrementing = TRUE;

    const id              = 'id';
    const created_at      = 'created_at';
    const updated_at      = 'updated_at';
    const shelf           = 'shelf';
    const series          = 'series';
    const url             = 'url';
    const name            = 'name';
    const downloaded_at   = 'downloaded_at';
    const downloaded_name = 'downloaded_name';
    const date_of_file    = 'date_of_file';
    const revised_date    = 'revised_date';
    const key             = 'key';

    protected $casts = [
        self::shelf           => 'string',
        self::series          => 'string',
        self::url             => 'string',
        self::name            => 'string',
        self::downloaded_at   => 'datetime',
        self::downloaded_name => 'string',
        self::date_of_file    => 'date',
        self::revised_date    => 'date',
        self::key             => 'string', // Could probably be an int, but I don't know if they zero pad lower IDs.
    ];

    protected $guarded = [];


    public function __construct( array $attributes = [] ) {
        parent::__construct( $attributes );
        $this->connection = env( 'DB_CONNECTION_CUSTODIAN_CTS' );
    }


}