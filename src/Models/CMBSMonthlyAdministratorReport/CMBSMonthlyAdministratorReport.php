<?php

namespace DPRMC\RemitSpiderCTSLink\Models\CMBSMonthlyAdministratorReport;

class CMBSMonthlyAdministratorReport {


    public function __construct(
        public readonly array $lpu,
        public readonly array $exceptions,
        public readonly array $alerts ) {
    }

}