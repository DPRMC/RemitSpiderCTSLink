<?php

namespace DPRMC\RemitSpiderCTSLink\Models\CMBSRestrictedServicerReport;


class CMBSRestrictedServicerReport {


    public function __construct( public readonly array $watchlist,
                                 public readonly array $dlsr,
                                 public readonly array $reosr,
                                 public readonly array $hlmfclr,
                                 public readonly array $csfr,
                                 public readonly array $llResLOC,
                                 public readonly array $totalLoan,
                                 public readonly array $advanceRecovery,
                                 public readonly array $alerts,
                                 public readonly array $exceptions ) {
    }

}