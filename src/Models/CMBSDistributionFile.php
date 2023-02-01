<?php

namespace DPRMC\RemitSpiderCTSLink\Models;

use DPRMC\RemitSpiderCTSLink\Exceptions\CUSIPNotFoundException;
use DPRMC\RemitSpiderCTSLink\Exceptions\LoginTimedOutException;
use DPRMC\RemitSpiderCTSLink\Exceptions\WrongNumberOfTitleElementsException;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Clip;
use HeadlessChromium\Cookies\CookiesCollection;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Exception\CommunicationException\CannotReadResponse;
use HeadlessChromium\Exception\CommunicationException\InvalidResponse;
use HeadlessChromium\Exception\CommunicationException\ResponseHasError;
use HeadlessChromium\Exception\NavigationExpired;
use HeadlessChromium\Exception\NoResponseAvailable;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Page;


class CMBSDistributionFile {

    public string $pathToDistributionFilePdf;
    public int    $numberOfPages;

    public array $dates = [];

    public array $certificateDistributionDetail           = [];
    public array $certificateFactorDetail                 = [];
    public array $certificateInterestReconciliationDetail = [];

    public array $modifiedLoanDetail    = [];
    public array $delinquencyLoanDetail = [];
    public array $historicalDetail      = [];

    public array $mortgageLoanDetailPart1 = [];
    public array $mortgageLoanDetailPart2 = [];

    public function __construct() {
    }

}