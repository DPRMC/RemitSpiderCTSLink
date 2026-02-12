<?php

use DPRMC\RemitSpiderCTSLink\Eloquent\CustodianCtsLink;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use PHPUnit\Framework\TestCase;

/**
 * To run tests call:
 * php ./vendor/phpunit/phpunit/phpunit --group=first
 * Class BusinessDateTest
 */
class RemitSpiderCTSLinkTest extends TestCase {

    protected static RemitSpiderCTSLink $spider;

    protected static bool $debug = TRUE;

    const TIMEZONE = 'America/New_York';


    private function _getNewSpider() {
//        $this->spider = new USBankSpider\( $this->debug,
//                                          $storagePath,
//                                          $this->timezone );
    }


    private function _getSpider(): RemitSpiderCTSLink {
        return new RemitSpiderCTSLink( $_ENV[ 'CHROME_PATH' ],
                                       $_ENV[ 'CTS_USER' ],
                                       $_ENV[ 'CTS_PASS' ],
                                       self::$debug,
                                       $_ENV[ 'PATH_TO_DEBUG_SCREENSHOTS' ],
                                       $_ENV[ 'PATH_TO_FILE_DOWNLOADS' ],
                                       self::TIMEZONE );
    }

    private function hasIntegrationEnv(): bool {
        $required = [
            'CHROME_PATH',
            'CTS_USER',
            'CTS_PASS',
            'PATH_TO_DEBUG_SCREENSHOTS',
            'PATH_TO_FILE_DOWNLOADS',
        ];

        foreach ($required as $key) {
            $value = $_ENV[$key] ?? getenv($key);
            if ($value === false || $value === null || $value === '') {
                return false;
            }
        }

        return true;
    }

    private function requireIntegrationEnv(): void {
        if (!$this->hasIntegrationEnv()) {
            $this->markTestSkipped('Integration env not set: CHROME_PATH, CTS_USER, CTS_PASS, PATH_TO_DEBUG_SCREENSHOTS, PATH_TO_FILE_DOWNLOADS.');
        }
    }

    public static function setUpBeforeClass(): void {


    }


    public static function tearDownAfterClass(): void {

    }

    protected function _getCUSIPList(): array {
        return file( getcwd() . '/tests/test_input/cusips.csv' );
    }


    /**
     * @group cusip
     * @group unit
     */
    public function testGetCusipList() {
        $filePath = getcwd() . '/tests/test_input/fun_1999c01_20190516_4245037.pdf';

        $factory = new \DPRMC\RemitSpiderCTSLink\Factories\CMBSDistributionFileFactory();
        $cusips  = $factory->getCUSIPList( $filePath );

        //dump( $cusips );

    }

    /**
     * @test
     * @group unit
     */
    public function testConstructor() {
        $spider = $this->_getSpider();
        $this->assertInstanceOf( RemitSpiderCTSLink::class,
                                 $spider );
    }


    /**
     * @test
     * @group generic
     * @group integration
     */
    public function testGenericHelper() {
        $this->requireIntegrationEnv();
        $spider = $this->_getSpider();

        $spider->enableDebug();
        $spider->Login->login();

        // This page has all the links to the shelves.
        //$html = file_get_contents( '/Users/michaeldrennen/PhpstormProjects/RemitSpiderCTSLink/deleteme.html' );
        $html               = NULL;
        $ctsLinkShelfModels = $spider->GenericHelper->getCtsLinkShelfModels( $html );
        dump( "I found " . count( $ctsLinkShelfModels ) . " shelves to look at." );
        //dump( end( $ctsLinkShelfModels ) );

        /**
         * @var \DPRMC\RemitSpiderCTSLink\Models\CTSLinkShelf $lastModel
         */
        //$lastModel = end( $ctsLinkShelfModels );

        //$debugSeriesHtml = file_get_contents('/Users/michaeldrennen/PhpstormProjects/RemitSpiderCTSLink/delete-series.html');
        //$debugSeriesHtml = file_get_contents('/Users/michaeldrennen/PhpstormProjects/RemitSpiderCTSLink/delete-series-2.html');
        $debugSeriesHtml = NULL;

        $seriesLinks = [];

        /**
         * @var \DPRMC\RemitSpiderCTSLink\Models\CTSLinkShelf $ctsLinkShelfModel
         */
        foreach ( $ctsLinkShelfModels as $ctsLinkShelfModel ):
            dump( " Getting series links for: " . $ctsLinkShelfModel->issuerName . " " . $ctsLinkShelfModel->shelf );
            $newLinks = $spider->GenericHelper->SeriesLinkHelper->getLinks( $ctsLinkShelfModel->seriesListHref,
                                                                            $debugSeriesHtml );

            foreach ( $newLinks as $seriesDocsPageUrl ):
                $parts = parse_url( $seriesDocsPageUrl, PHP_URL_QUERY );
                parse_str( $parts, $output );
                $shelf  = $output[ 'shelfId' ];
                $series = $output[ 'seriesId' ];

                //$debugDocsHtml = file_get_contents( '/Users/michaeldrennen/PhpstormProjects/RemitSpiderCTSLink/delete-docs.html' );
                $debugDocsHtml = NULL;
                try {
                    $docLinks = $spider->GenericHelper->ShelfDocsHelper->getLinks( $seriesDocsPageUrl,
                                                                                   $debugDocsHtml );

                    //
                    dump( "  Here are the doc links I found: " );
                    dump( $docLinks );
                    //dd( 'short stop' );
                } catch ( \DPRMC\RemitSpiderCTSLink\Helpers\Generic\Exceptions\NoAccessToDealException $exception ) {
                    dump( $exception->getMessage() );
                    // This is where I would report to bugsnag perhaps.
                    // Or record that I do not have access to this deal.
                }


            endforeach;
        endforeach;

        dump( $seriesLinks );
    }

    /**
     * @test
     * @group login
     * @group integration
     */
    public function testLogin() {
        $this->requireIntegrationEnv();
        $spider = $this->_getSpider();

        $spider->enableDebug();
        $spider->Login->login();

        //$html = $spider->FilesByCUSIP->getFilePathsByCUSIP($_ENV['CMBS_CUSIP']);

        $numFound   = 0;
        $numMissing = 0;

        $cusips = $this->_getCUSIPList();
        foreach ( $cusips as $cusip ):
            try {
                $html = $spider->FilesByCUSIP->getFilePathsByCUSIP( $cusip );
                $numFound++;
            } catch ( \DPRMC\RemitSpiderCTSLink\Exceptions\CUSIPNotFoundException $exception ) {
                echo $exception->cusip;
                $numMissing++;
            }
        endforeach;
        //

        echo "\nNum found: " . $numFound;
        echo "\nNum missing: " . $numMissing;


        $spider->Login->logout();
    }


    /**
     * @test
     * @group cmbs-crefc
     * @group integration
     */
    public function testGetCREFCLoanSetUpFile() {
        $this->requireIntegrationEnv();
        $spider = $this->_getSpider();
        $spider->disableDebug();
        $spider->Login->login();
        $shelf                    = 'GSM';
        $series                   = '2014GC24';
        $linkToCREFCLoanSetUpFile = $spider->CMBSCREFCLoanSetUpFilesHelper->getCREFCLoanSetUpFileLink( $shelf, $series );

        var_dump( $linkToCREFCLoanSetUpFile );
        $this->assertNotEmpty( $linkToCREFCLoanSetUpFile );
    }


    /**
     * @test
     * @group pdf
     * @group unit
     */
    public function testParsePDF() {
        ini_set( 'memory_limit', -1 );
        $filePath     = getcwd() . '/tests/test_input/BAMLC_2018BNK12_DDST.pdf';
        $fileContents = file_get_contents( $filePath );

        $parser = new \Smalot\PdfParser\Parser();
        $pdf    = $parser->parseContent( $fileContents );

        $pages = $pdf->getPages();

        echo "\nThere are " . count( $pages ) . " pages.";

        /**
         * @var \Smalot\PdfParser\Page $page
         */
        $page = $pages[ 0 ];
        print_r( $page->getTextArray( $page ) );

        $page = $pages[ 1 ];
        print_r( $page->getTextArray( $page ) );


    }


    /**
     * @test
     * @group fac
     * @group unit
     */
    public function testCMBSDistributionFileFactory() {
        ini_set( 'memory_limit', -1 );
        //$filePath = getcwd() . '/tests/test_input/BAMLC_2018BNK12_DDST.pdf';
        //$filePath = getcwd() . '/tests/test_input/CCM_2022B35_DDST.pdf';
        $filePath = getcwd() . '/tests/test_input/fun_1999c01_20190516_4245037.pdf';

        $factory              = new \DPRMC\RemitSpiderCTSLink\Factories\CMBSDistributionFileFactory();
        $cmbsDistributionFile = $factory->make( $filePath );

        dump( $cmbsDistributionFile );

        $this->assertInstanceOf( \DPRMC\RemitSpiderCTSLink\Models\CMBSDistributionFile::class,
                                 $cmbsDistributionFile );
    }


    /**
     * @test
     * @group dl
     * @group integration
     */
    public function testDownload() {
        $this->requireIntegrationEnv();
        $spider = $this->_getSpider();
        //$spider->disableDebug();
        $spider->Login->login();

        //$href = 'https://i.imgur.com/PmhVTiH.jpeg';
        //$href = 'https://google.com';
        $href  = 'https://www.ctslink.com/a/document.html?key=5900425';
        $temp  = 'tests/temp';
        $final = 'tests/final';
        $name  = 'test.pdf';
        //$finalFilePath = $spider->FileDownloader->downloadFile($href, $temp, $final, $name);

        $spider->FileDownloader->fileGetContents( $href, $final, $name );
    }

    /**
     * @test
     * @group cmbs
     */
//    public function testGetCMBSDistributionFiles(){
//        $link = 'https://www.ctslink.com/a/shelflist.html?shelfType=CMBS';
//        $spider = $this->_getSpider();
//        $spider->Login->login();
//        $allRecentCMBSDistributionFiles = $spider->CMBSDistributionFiles->getAllRecentCMBSDistributionFiles();
//        $this->assertNotEmpty($allRecentCMBSDistributionFiles);
//    }


    /**
     * @test
     * @group cmbs-shelf-links
     * @group integration
     */
    public function testGetShelfLinks() {
        $this->requireIntegrationEnv();
        $spider = $this->_getSpider();
        $spider->disableDebug();
        $spider->Login->login();
        $shelfLinks = $spider->CMBSDistributionFilesHelper->getShelfLinks();

        print_r( $shelfLinks );
        $this->assertNotEmpty( $shelfLinks );
    }


    /**
     * @test
     * @group cmbs-dist-data
     * @group integration
     */
    public function testGetCMBSDistributionFiles() {
        $this->requireIntegrationEnv();
        $spider = $this->_getSpider();
        $spider->disableDebug();
        $spider->Login->login();
        $shelfLinks = $spider->CMBSDistributionFilesHelper->getShelfLinks();

        $subsetOfShelfLinks = array_slice( $shelfLinks, 0, 10 );
        foreach ( $subsetOfShelfLinks as $href ):
            $data = $spider->CMBSDistributionFilesHelper->getDistributionDateStatementDataFromLink( $href );
            echo "\n\n\n" . $href . "\n";
            print_r( $data );
            $this->assertNotEmpty( $data );
        endforeach;
    }

    /**
     * @test
     * @group cmbs-help
     * @group integration
     */
    public function testCMBSHelper() {
        $this->requireIntegrationEnv();
        $spider = $this->_getSpider();
        $spider->disableDebug();
        $spider->Login->login();
        $shelfLinks = $spider->CMBSDistributionFilesHelper->getShelfLinks();
//        $shelfLinks = [
//            'https://www.ctslink.com/a/serieslist.html?shelfId=JPC',
//        ];

        $allSeriesLinks = [];

        foreach ( $shelfLinks as $shelfLink ):
//            echo "\nSHELF LINK IS: " . $shelfLink . " ";
            try {
                $seriesLinks = $spider->CMBSRestrictedServicerReportHelper->getSeriesLinks( $shelfLink );
                foreach ( $seriesLinks as $i => $seriesLink ):
                    echo "\n   " . $i + 1 . " of " . count( $seriesLinks ) . ": " . $seriesLink;
                    $allSeriesLinks[] = $seriesLink;
                endforeach;
            } catch ( \HeadlessChromium\Exception\OperationTimedOut $exception ) {
                echo "\nOperationTimedOut: " . $exception->getMessage();
            } catch ( \HeadlessChromium\Exception\CommunicationException $exception ) {
                echo "\nCommunicationException: " . $exception->getMessage();
            }
        endforeach;

        echo "\n\nThere are " . count( $allSeriesLinks ) . " total series links.";

// DEBUG
//        $allSeriesLinks = [
//            \DPRMC\RemitSpiderCTSLink\Helpers\CMBSHelper::HISTORY_URL . 'shelfId=JPC&seriesId=2022B32&doc=JPC_2022B32_RSRV',
//        ];

        foreach ( $allSeriesLinks as $seriesLink ):
            $parts = $spider->CMBSRestrictedServicerReportHelper->getPartsFromSeriesLink( $seriesLink );

            try {
                $historyLinks = $spider->CMBSRestrictedServicerReportHelper->getAllRestrictedServicerReportLinksFromSeriesPage( $parts[ \DPRMC\RemitSpiderCTSLink\Helpers\CMBSHelper::shelf ],
                                                                                                                                $parts[ \DPRMC\RemitSpiderCTSLink\Helpers\CMBSHelper::series ] );
            } catch ( \DPRMC\RemitSpiderCTSLink\Exceptions\NoAccessToRestrictedServicerReportException $exception ) {
                echo "\n " . $exception->getMessage();
            }
        endforeach;
    }


    /**
     * @test
     * @group rest
     * @group integration
     */
    public function testRestrictedServicerReport() {
        $this->requireIntegrationEnv();
        $seriesLink = 'https://www.ctslink.com/a/seriesdocs.html?shelfId=JPC&seriesId=2015C31';

        // CMBSRestrictedServicerReportHelper

        $spider = $this->_getSpider();
        $spider->disableDebug();
        $spider->Login->login();

        $spider->CMBSRestrictedServicerReportHelper->getAllRestrictedServicerReportLinks( $seriesLink );


//        $shelfLinks = $spider->CMBSRestrictedServicerReportHelper->getShelfLinks();
//        print_r($shelfLinks);
//
//        foreach ( $shelfLinks as $shelfLink ):
//            echo "\nSHELF LINK IS: " . $shelfLink . " ";
//            try {
//                $seriesLinks = $spider->CMBSRestrictedServicerReportHelper->getSeriesLinks( $shelfLink );
//                foreach ( $seriesLinks as $i => $seriesLink ):
//                    echo "\n   " . $i + 1 . " of " . count( $seriesLinks ) . ": " . $seriesLink;
//
//
//
//
//                endforeach;
//            } catch ( \HeadlessChromium\Exception\OperationTimedOut $exception ) {
//                echo "\nEXCEPTION: " . $exception->getMessage();
//            }
//
//        endforeach;


    }


    /**
     * @test
     * @group mike
     * @group unit
     */
    public function testRestrictedServicerReportFactory() {
        $filePath = getcwd() . '/tests/test_input/JPC_2022B32_RSRV.xls';
        //$filePath = getcwd() . '/tests/test_input/BOAMLLL_2012CKSV_RSRV.xls';
        $filePath = getcwd() . '/tests/test_input/JMACFM_2015R1_6231196_JMACFM_2015R1_MADR.xlsx';
        $filePath = getcwd() . '/tests/test_input/WFCM_2020BNK28_6216732_WFCM_2020BNK28_RSRV.xls';


        $factory = new \DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\CMBSRestrictedServicerReportFactory( self::TIMEZONE );
        //$factory = new \DPRMC\RemitSpiderCTSLink\Factories\CMBSMonthlyAdministratorReport\CMBSMonthlyAdministratorReportFactory( self::TIMEZONE );
        //$mar     = $factory->make( $filePath );

        $ctsLink    = new CustodianCtsLink();
        $dateOfFile = \Carbon\Carbon::today( 'America/New_York' );
        $mar        = $factory->make( $filePath, $ctsLink, $dateOfFile );

        dd( $mar );

        dump( $restrictedServicerReport->csfr );
        dd( 'done.dasdfasd' );

//        if ( $restrictedServicerReport->alerts ):
//            dump( "ALERTS" );
//            foreach ( $restrictedServicerReport->alerts as $alert ):
//                dump( $alert->getMessage() );
//            endforeach;
//        endif;
//
//        if ( $restrictedServicerReport->exceptions ):
//            dump( "EXCEPTIONS" );
//            foreach ( $restrictedServicerReport->exceptions as $exception ):
//
//
//                switch ( get_class( $exception ) ):
//                    case \DPRMC\RemitSpiderCTSLink\Exceptions\HeadersTooLongForMySQLException::class:
//                        dump( $exception->getTraceAsString() );
//                        dump( $exception->getMessage() );
//                        dump( $exception->headersThatAreTooLong );
//                        break;
//
//                    default:
//                        dump( get_class( $exception ) . ': ' . $exception->getMessage() );
//                endswitch;
//            endforeach;
//
//            die( "Done." );
//        endif;
//
////
////
////        dd($restrictedServicerReport);
//
//        dump( $filePath );
//        dump( "Showing the toSQL output" );
//
//        $sqlGenerator = new \DPRMC\RemitSpiderCTSLink\SQL\GenerateSqlFromCMBSRestrictedServicerReport( $restrictedServicerReport );
//
//        $sql = $sqlGenerator->generateSQL();
//
//        dump( $sql );
//
//        $modelGenerator = new \DPRMC\RemitSpiderCTSLink\Laravel\GenerateEloquentModelsFromCMBSRestrictedServicerReport( $restrictedServicerReport );
//        $modelGenerator->generateModels();


        //dump($restrictedServicerReport->failedTables);
    }


    /**
     * @test
     * @group rest2
     * @group integration
     */
    public function testGetRestrictedServicerReportObjects() {
        $this->requireIntegrationEnv();

        $shelf  = 'BAMLC';
        $series = '2013C11';
        $spider = $this->_getSpider();
        $spider->disableDebug();
        $spider->Login->login();
        try {
            $historyLinks = $spider->CMBSRestrictedServicerReportHelper->getAllRestrictedServicerReportLinkDataFromSeriesPage( $shelf,
                                                                                                                               $series );
            dump( $historyLinks );
        } catch ( \DPRMC\RemitSpiderCTSLink\Exceptions\NoAccessToRestrictedServicerReportException $exception ) {
            echo "\n " . $exception->getMessage();
        }
    }


    /**
     * @test
     * @group revised
     * @group unit
     */
    public function testDocLinkShouldParseRevisedDates() {
        $nameOfFile   = 'testfile.xls';
        $currentCycle = '11/15/2023


										 REVISED 11/16/2023';
        $docLink      = new \DPRMC\RemitSpiderCTSLink\Helpers\Generic\DocLink( $nameOfFile, $currentCycle );

        $this->assertNotEmpty( $docLink->currentCycle );
        $this->assertNotEmpty( $docLink->revisedCurrentCycle );

        var_dump( $docLink );
    }


    /**
     * @test
     * @group prop
     * @group unit
     */
    public function testParserShouldReturnPropertyDataIfFound() {
        $nameOfFile     = 'test.xls';
        $testPath       = 'tests' . DIRECTORY_SEPARATOR . 'test_input' . DIRECTORY_SEPARATOR;
        $filePath       = $testPath . $nameOfFile;
        $dateOfFile     = \Carbon\Carbon::create( 2024, 10, 8, 0, 0, 0, 'America/New_York' );
        $key            = '6777762';
        $json           = file_get_contents( $testPath . 'header_key_words.json' );
        $headerKeywords = json_decode( $json );

        $factory    = new \DPRMC\RemitSpiderCTSLink\Factories\CMBSRestrictedServicerReport\CMBSRestrictedServicerReportFactory( self::TIMEZONE,
                                                                                                                                $dateOfFile,
                                                                                                                                $key,
                                                                                                                                $headerKeywords );
        $linkObject = new CustodianCtsLink();

        $restrictedServicerReport = $factory->make( $filePath,
                                                    $linkObject,                                  //$ctsLinkObject,
                                                    $linkObject->{CustodianCtsLink::date_of_file} //$ctsLinkObject->{CustodianCtsLink::date_of_file}
        );

        //dump( $restrictedServicerReport );
        //dd('$restrictedServicerReport');

        $this->assertInstanceOf( \DPRMC\RemitSpiderCTSLink\Models\CMBSRestrictedServicerReport\CMBSRestrictedServicerReport::class, $restrictedServicerReport );
    }


    /**
     * @test
     * @group xxx
     */
//    public function testClickLinkFromRegexLinkText(){
//        $spider = $this->_getSpider();
//
//        $spider->enableDebug();
//        $spider->Login->login();
//
////        $html = $spider->CTSLinkBrowser->page->getHtml();
////        $this->assertEquals( 'testfile.xls', $html );
//
//        $regex = '/^Commercial Securities \(CMBS\)$/';
//        $regex = '/MyCTSLink(?=\()/';
//
////        $regex = '/Commercial Securities.*/';
//        $linkFound = $spider->GenericHelper->clickLinkWithMatchingLinkText($regex);
//
//        $this->assertTrue( $linkFound );
////        $this->assertFalse( $linkFound );
//
//
//        $html = $spider->CTSLinkBrowser->page->getHtml();
//        $this->assertEquals( 'xxx.xls', $html );
//
//
////        $nextHtml = $spider->CTSLinkBrowser->page->getHtml();
////        $this->assertEquals( 'yyy.xls', $html );
//
//        fwrite(STDERR, print_r($html, TRUE));
//    }


    /**
     * @test
     * @group upper
     * @group unit
     */
public function teststrToUpperQueryParamsShouldUppercaseTheParams(){
    $href = 'https://example.com?foo=bar';
    $expectedHref = 'https://example.com?foo=BAR';
    $uppered = \DPRMC\RemitSpiderCTSLink\Helpers\AbstractHelper::strToUpperQueryParams($href);
    $this->assertEquals($expectedHref, $uppered);
}


}
