<?php

namespace DPRMC\RemitSpiderCTSLink;

use DPRMC\RemitSpiderCTSLink\Helpers\CTSLinkBrowser;
use DPRMC\RemitSpiderCTSLink\Helpers\Debug;
use DPRMC\RemitSpiderCTSLink\Helpers\FileDownloader;
use DPRMC\RemitSpiderCTSLink\Helpers\FilesByCUSIP;
use DPRMC\RemitSpiderCTSLink\Helpers\Login;
use DPRMC\RemitSpiderCTSLink\Helpers\CMBSDistributionFilesHelper;
use HeadlessChromium\Cookies\CookiesCollection;


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
    public CTSLinkBrowser              $CTSLinkBrowser;
    public Debug                       $Debug;
    public Login                       $Login;
    public FileDownloader              $FileDownloader;
    public FilesByCUSIP                $FilesByCUSIP;
    public CMBSDistributionFilesHelper $CMBSDistributionFilesHelper;

    const  BASE_URL = 'https://www.ctslink.com';


    /**
     * TESTING, not sure if this will work.
     *
     * @var CookiesCollection Saving the cookies post login. When the connection dies for no reason, I can restart the session.
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

        $this->FileDownloader = new FileDownloader( $this->CTSLinkBrowser->page,
                                                    $this->Debug,
                                                    $this->timezone );

        $this->FilesByCUSIP = new FilesByCUSIP( $this->CTSLinkBrowser->page,
                                                $this->Debug,
                                                $this->timezone );

        $this->CMBSDistributionFilesHelper = new CMBSDistributionFilesHelper( $this->CTSLinkBrowser->page,
                                                                              $this->Debug,
                                                                              $this->timezone );
    }


    /**
     * A little helper function to turn on debugging from the top level object.
     * @return void
     */
    public function enableDebug(): void {
        $this->debug = TRUE;
        $this->Debug->enableDebug();
    }


    /**
     * @return void
     */
    public function disableDebug(): void {
        $this->debug = FALSE;
        $this->Debug->disableDebug();
    }
}