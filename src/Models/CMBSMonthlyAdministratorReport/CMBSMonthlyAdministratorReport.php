<?php

namespace DPRMC\RemitSpiderCTSLink\Models\CMBSMonthlyAdministratorReport;

class CMBSMonthlyAdministratorReport {

    public array $exceptions = [];
    public array $alerts     = [];

    public function __construct(
        public readonly array $lpu ) {
    }

}