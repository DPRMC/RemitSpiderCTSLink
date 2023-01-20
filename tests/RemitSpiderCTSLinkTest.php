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

        $numFound = 0;
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
        ini_set('memory_limit', -1);
        $filePath = getcwd() . '/tests/test_input/BAMLC_2018BNK12_DDST.pdf';
        $fileContents = file_get_contents($filePath);

        $parser      = new \Smalot\PdfParser\Parser();
        $pdf         = $parser->parseContent( $fileContents );

        $pages        = $pdf->getPages();

        echo "\nThere are " . count($pages) . " pages.";

        /**
         * @var \Smalot\PdfParser\Page $page
         */
        $page = $pages[0];
        print_r($page->getTextArray($page));

        $page = $pages[1];
        print_r($page->getTextArray($page));



    }


    /**
     * @test
     * @group cmbs
     */
    public function testGetCMBSDistributionFiles(){
        $link = 'https://www.ctslink.com/a/shelflist.html?shelfType=CMBS';
        $spider = $this->_getSpider();
        $spider->Login->login();
        $allRecentCMBSDistributionFiles = $spider->CMBSDistributionFiles->getAllRecentCMBSDistributionFiles();
        $this->assertNotEmpty($allRecentCMBSDistributionFiles);
    }



        /**
     * @test
     * @group all
     */
//    public function testAll() {
//        $spider = $this->_getSpider();
//        $spider->Login->login();
//        $portfolioIds = $spider->Portfolios->getAll( $spider->Login->csrf );
//
//        $dealLinkSuffixesByPortfolioId = [];
//        foreach ( $portfolioIds as $portfolioId ):
//            $dealLinkSuffixesByPortfolioId[ $portfolioId ] = $spider->Deals->getAllByPortfolioId( $portfolioId );
//        endforeach;
//
//
//        $historyLinksByPortfolioId = [];
//        $dealIdToDealName          = [];
//        foreach ( $dealLinkSuffixesByPortfolioId as $portfolioId => $dealLinkSuffixes ):
//            $historyLinksByPortfolioId[$portfolioId] = [];
//            foreach ( $dealLinkSuffixes as $dealLinkSuffix ):
//                $historyLinks                         = $spider->HistoryLinks->getAllByDeal( $dealLinkSuffix );
//                $dealId                               = $spider->HistoryLinks->getDealId();
//                $dealName                             = $spider->HistoryLinks->getDealName();
//                $dealIdToDealName[ $dealId ]          = $dealName;
//                $historyLinksByPortfolioId[$portfolioId][ $dealId ] = $historyLinks;
//            endforeach;
//        endforeach;
//
//
//
//        $fileIndexes = [];
//        foreach ( $historyLinksByPortfolioId as $portfolioId => $dealIds ):
//            $fileIndexes[$portfolioId] = [];
//            foreach ( $dealIds as $dealId => $historyLinks ):
//                $fileIndexes[$portfolioId][$dealId] = [];
//                foreach($historyLinks as $historyLinkSuffix):
//                    $tempFileIndexes = $spider->FileIndex->getAllFromHistoryLink( $historyLinkSuffix);
//                    $fileIndexes[$portfolioId][$dealId] = array_merge($fileIndexes[$portfolioId][$dealId], $tempFileIndexes);
//                endforeach;
//            endforeach;
//        endforeach;
//
//
//        print_r($fileIndexes);
//    }


}