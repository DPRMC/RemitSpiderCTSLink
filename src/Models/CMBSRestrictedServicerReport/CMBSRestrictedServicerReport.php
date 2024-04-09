<?php

namespace DPRMC\RemitSpiderCTSLink\Models\CMBSRestrictedServicerReport;

use Carbon\Carbon;

class CMBSRestrictedServicerReport {


    // Properties defined as constants here, so I can easily acess them
    // through the inspector.
    const watchlist       = 'watchlist';
    const dlsr            = 'dlsr';
    const reosr           = 'reosr';
    const hlmfclr         = 'hlmfclr';
    const csfr            = 'csfr';
    const llResLOC        = 'llResLOC';
    const totalLoan       = 'totalLoan';
    const advanceRecovery = 'advanceRecovery';

    const dateOfFile = 'dateOfFile';
    const documentId = 'documentId';

    public function __construct(

        public readonly array   $watchlist,
        public readonly array   $dlsr,
        public readonly array   $reosr,
        public readonly array   $hlmfclr,
        public readonly array   $csfr,
        public readonly array   $llResLOC,
        public readonly array   $totalLoan,
        public readonly array   $advanceRecovery,
        public readonly array   $cleanHeadersByProperty,
        public readonly array   $alerts,
        public readonly array   $exceptions,
        public readonly ?Carbon $dateOfFile = NULL,
        public readonly ?int    $documentId = NULL
    ) {
    }

}