<?php

use PHPUnit\Framework\TestCase;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;

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

    public static function setUpBeforeClass(): void {


    }


    public static function tearDownAfterClass(): void {

    }

    protected function _getCUSIPList(): array {
        return file( getcwd() . '/tests/test_input/cusips.csv' );
    }


    /**
     * @test
     */
    public function testConstructor() {
        $spider = $this->_getSpider();
        $this->assertInstanceOf( RemitSpiderCTSLink::class,
                                 $spider );
    }


    /**
     * @test
     * @group login
     */
    public function testLogin() {
        $spider = $this->_getSpider();
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
     * @group pdf
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
     */
    public function testCMBSDistributionFileFactory() {
        ini_set( 'memory_limit', -1 );
        $filePath = getcwd() . '/tests/test_input/BAMLC_2018BNK12_DDST.pdf';
        $filePath = getcwd() . '/tests/test_input/CCM_2022B35_DDST.pdf';

        $factory              = new \DPRMC\RemitSpiderCTSLink\Factories\CMBSDistributionFileFactory();
        $cmbsDistributionFile = $factory->make( $filePath );

        print_r($cmbsDistributionFile);

        $this->assertInstanceOf( \DPRMC\RemitSpiderCTSLink\Models\CMBSDistributionFile::class,
                                 $cmbsDistributionFile );
    }


    /**
     * @test
     * @group dl
     */
    public function testDownload() {
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
     */
    public function testGetShelfLinks() {
        $spider = $this->_getSpider();
        $spider->disableDebug();
        $spider->Login->login();
        $shelfLinks = $spider->CMBSDistributionFilesHelper->getShelfLinks();
        $this->assertNotEmpty( $shelfLinks );
    }


    /**
     * @test
     * @group cmbs-dist-data
     */
    public function testGetCMBSDistributionFiles() {
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

}