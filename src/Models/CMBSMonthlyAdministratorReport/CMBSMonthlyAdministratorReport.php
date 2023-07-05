<?php

namespace DPRMC\RemitSpiderCTSLink\Models\CMBSMonthlyAdministratorReport;

class CMBSMonthlyAdministratorReport {


    public function __construct(
        public readonly array $lpu,
        public readonly array $cleanHeadersByProperty,
        public readonly array $alerts,
        public readonly array $exceptions ) {
    }

}