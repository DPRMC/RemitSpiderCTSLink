<?php

namespace DPRMC\RemitSpiderCTSLink;

use DPRMC\RemitSpiderCTSLink\Helpers\CTSLinkBrowser;
use DPRMC\RemitSpiderCTSLink\Helpers\Debug;
use DPRMC\RemitSpiderCTSLink\Helpers\FilesByCUSIP;
use DPRMC\RemitSpiderCTSLink\Helpers\Login;
use DPRMC\RemitSpiderCTSLink\Helpers\CMBSDistributionFiles;
use HeadlessChromium\Cookies\CookiesCollection;
use HeadlessChromium\Page;


/**
 *
 */
class RemitSpiderCTSLink {
    const DEFAULT_TIMEZONE = 'America/New_York';

    protected bool   $debug;
    protected string $pathToScreenshots;
    protected string $timezone;

    /**
     * @var CTSLinkBrowser
     */
    public CTSLinkBrowser        $CTSLinkBrowser;
    public Debug                 $Debug;
    public Login                 $Login;
    public FilesByCUSIP          $FilesByCUSIP;
    public CMBSDistributionFiles $CMBSDistributionFiles;
//    public Portfolios                  $Portfolios;
//    public Deals                       $Deals;
//    public HistoryLinks                $HistoryLinks;
//    public FileIndex                   $FileIndex;
//    public PrincipalAndInterestFactors $PrincipalAndInterestFactors;
//    public PeriodicReportsSecured      $PeriodicReportsSecured;


//    protected string $pathToPortfolioIds;
//    protected string $pathToDealLinkSuffixes;
//    protected string $pathToHistoryLinks;
//    protected string $pathToFileIndex;
//    protected string $timezone;
//
//    protected array $portfolioIds;
//    protected array $dealIds;
//
//    protected Page $page;


    const  BASE_URL = 'https://www.ctslink.com';
//    const  PORTFOLIO_IDS_FILENAME                  = '_portfolio_ids.json';
//    const  DEAL_LINK_SUFFIXES_FILENAME             = '_deal_link_suffixes.json';
//    const  HISTORY_LINKS_FILENAME                  = '_history_links.json';
//    const  FILE_INDEX_FILENAME                     = '_file_index.json';


    /**
     * TESTING, not sure if this will work.
     *
     * @var CookiesCollection Saving the cookies post login. When the connection dies for no reason, I can restart the
     *      session.
     */
    public CookiesCollection $cookies;


    public function __construct( string $chromePath,
                                 string $user,
                                 string $pass,
                                 bool   $debug = FALSE,
                                 string $pathToScreenshots = '',
                                 string $pathToFileDownloads = '',
                                 string $timezone = self::DEFAULT_TIMEZONE
    ) {

        $this->debug             = $debug;
        $this->pathToScreenshots = $pathToScreenshots;
//        $this->pathToPortfolioIds                = $pathToPortfolioIds . self::PORTFOLIO_IDS_FILENAME;
//        $this->pathToDealLinkSuffixes            = $pathToDealLinkSuffixes . self::DEAL_LINK_SUFFIXES_FILENAME;
//        $this->pathToHistoryLinks                = $pathToHistoryLinks . self::HISTORY_LINKS_FILENAME;
//        $this->pathToFileIndex                   = $pathToFileIndex . self::FILE_INDEX_FILENAME;

        $this->timezone = $timezone;

        $this->CTSLinkBrowser = new CTSLinkBrowser( $chromePath );
        $this->CTSLinkBrowser->page->setDownloadPath( $pathToFileDownloads );

        $this->Debug = new Debug( $this->CTSLinkBrowser->page,
                                  $pathToScreenshots,
                                  $debug,
                                  $this->timezone );

        $this->Login = new Login( $this->CTSLinkBrowser->page,
                                  $this->Debug,
                                  $user,
                                  $pass,
                                  $this->timezone );

        $this->FilesByCUSIP = new FilesByCUSIP( $this->CTSLinkBrowser->page,
                                                $this->Debug,
                                                $this->timezone );

        $this->CMBSDistributionFiles = new CMBSDistributionFiles( $this->CTSLinkBrowser->page,
                                                                  $this->Debug,
                                                                  $this->timezone );

//        $this->Portfolios = new Portfolios( $this->USBankBrowser->page,
//                                            $this->Debug,
//                                            $this->pathToPortfolioIds,
//                                            $this->timezone );
//
//        $this->Deals = new Deals( $this->USBankBrowser->page,
//                                  $this->Debug,
//                                  $this->pathToDealLinkSuffixes,
//                                  $this->timezone );
//
//        $this->HistoryLinks = new HistoryLinks( $this->USBankBrowser->page,
//                                                $this->Debug,
//                                                $this->pathToHistoryLinks,
//                                                $this->timezone );
//
//        $this->FileIndex = new FileIndex( $this->USBankBrowser->page,
//                                          $this->Debug,
//                                          $this->pathToFileIndex,
//                                          $this->timezone );
//
//        $this->PrincipalAndInterestFactors = new PrincipalAndInterestFactors( $this->USBankBrowser->page,
//                                                                              $this->Debug,
//                                                                              $this->timezone );
//        $this->PeriodicReportsSecured      = new PeriodicReportsSecured( $this->USBankBrowser->page,
//                                                                         $this->Debug,
//                                                                         $this->timezone );
    }


    /**
     *
     */
//    private function _loadIds() {
//        if ( file_exists( $this->pathToPortfolioIds ) ):
//            $this->portfolioIds = file( $this->pathToPortfolioIds );
//        else:
//            file_put_contents( $this->pathToPortfolioIds, NULL );
//        endif;
//
//        if ( file_exists( $this->pathToDealLinkSuffixes ) ):
//            $this->dealIds = file( $this->pathToDealLinkSuffixes );
//        else:
//            file_put_contents( $this->pathToDealLinkSuffixes, NULL );
//        endif;
//    }


}