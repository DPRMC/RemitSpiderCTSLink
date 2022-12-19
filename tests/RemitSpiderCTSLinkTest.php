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


    private function _getNewSpider(){
//        $this->spider = new USBankSpider\( $this->debug,
//                                          $storagePath,
//                                          $this->timezone );
    }



    private function _getSpider(): RemitSpiderCTSLink {
        return new RemitSpiderCTSLink( $_ENV[ 'CHROME_PATH' ],
                                                              $_ENV[ 'CTS_USER' ],
                                                              $_ENV[ 'CTS_PASS' ],
                                                              self::$debug,
                                                              '',
                                                              '',
                                                              '',
                                                              '',
                                                              '',
                                                              '/Users/michaeldrennen/Desktop/files',
                                                              self::TIMEZONE );
    }

    public static function setUpBeforeClass(): void {

    }


    public static function tearDownAfterClass(): void {

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