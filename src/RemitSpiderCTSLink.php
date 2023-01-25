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


    protected int $maxTimesToCheckForDownloadBeforeGivingUp = 10;



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

    public function setMaxTimesToCheckForDownloadBeforeGivingUp(int $value): void {
        $this->maxTimesToCheckForDownloadBeforeGivingUp = $value;
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

    public function enableDebug(): void {
        $this->debug = TRUE;
        $this->Debug->enableDebug();
    }

    public function disableDebug(): void {
        $this->debug = FALSE;
        $this->Debug->disableDebug();
    }

    public function downloadFile(string $hrefOfFileToDownload, string $tempDownloadPath, string $finalDownloadPath, string $newFileName = ''): string {
        $this->CTSLinkBrowser->page->setDownloadPath( $tempDownloadPath );



        // Since there is no *easy* way for Headless Chromium to let us know the name of the downloaded file...
        // My solution is to create a temporary unique directory to set as the download path for Headless Chromium.
        // After the download, there should be only one file in there.
        // Get the name of that file, and munge it as I see fit.
        $md5OfHREF                   = md5( $hrefOfFileToDownload ); // This should always be unique.
        $absolutePathToStoreTempFile = $tempDownloadPath . DIRECTORY_SEPARATOR . $md5OfHREF;    // This DIR will end up having one file.

        $this->_createTempDirectoryForDownloadedFile( $absolutePathToStoreTempFile );

        $this->CTSLinkBrowser->page->navigate( $hrefOfFileToDownload );

        $checkCount = 0;
        do {
            $checkCount++;

            $locale            = 'en_US';
            $nf                = new \NumberFormatter( $locale, \NumberFormatter::ORDINAL );
            $ordinalCheckCount = $nf->format( $checkCount );

            $this->Debug->_debug( "  Checking for the " . $ordinalCheckCount . " time." );
            sleep( 1 );
            $files = scandir( $absolutePathToStoreTempFile );

            if ( $checkCount >= $this->maxTimesToCheckForDownloadBeforeGivingUp ):
                $this->Debug->_debug( "  I have already checked " . $checkCount . " times. Enough is enough. Skipping it." );
                break;
            endif;
        } while ( ! $this->_downloadComplete( $files ) );


        $fileName = $this->_getFilenameFromFiles( $files );
        $this->Debug->_debug( "  Done checking. I found the file: " . $fileName );

        $contents = file_get_contents( $absolutePathToStoreTempFile . DIRECTORY_SEPARATOR . $fileName );


        if( $newFileName ):
            $finalReportName = $newFileName;
        else:
            $finalReportName = $fileName;
        endif;
        $absolutePathToStoreFinalFile = $finalDownloadPath . DIRECTORY_SEPARATOR . $finalReportName;
        $bytesWritten                 = file_put_contents( $absolutePathToStoreFinalFile, $contents );

        if ( FALSE === $bytesWritten ):
            throw new \Exception( "  Unable to write file to " . $absolutePathToStoreFinalFile );
        else:
            $this->Debug->_debug( "  " . $bytesWritten . " bytes written into " . $absolutePathToStoreFinalFile );
        endif;

        $this->Debug->_debug( "  Attempting to delete the TEMP directory and file at: " . $absolutePathToStoreTempFile );
        $this->_deleteTempDirectoryAndFile( $absolutePathToStoreTempFile );
    }


    /**
     * Headless Chromium creates a temp file ending with '.crdownload' that it streams the data into.
     * Don't count that file.
     * If the download is not complete, then set the $files var to an empty array to force
     * the code to stay in the DoWhile loop.
     *
     * @param array $files
     *
     * @return bool
     */
    private function _downloadComplete( array $files ): bool {
        array_shift( $files ); // Remove .
        array_shift( $files ); // Remove ..

        if ( ! isset( $files[ 0 ] ) ):
            return FALSE;
        endif;
        $fileName = $files[ 0 ];

        $needle = '.crdownload';
        if ( str_ends_with( $fileName, $needle ) ):
            return FALSE;
        endif;
        return TRUE;
    }

    /**
     * @param array $files An array of files from the scandir() call above.
     *
     * @return string The filename of the downloaded file from US Bank.
     * @throws \Exception This should never happen because of error checking above where this method is called.
     */
    protected function _getFilenameFromFiles( array $files ): string {
        array_shift( $files ); // Remove .
        array_shift( $files ); // Remove ..
        if ( ! isset( $files[ 0 ] ) ):
            throw new \Exception( "Unable to find the downloaded file in the files array." );
        endif;

        return $files[ 0 ];
    }


    protected function _deleteTempDirectoryAndFile( string $absolutePathToStoreTempFile ): void {
        if ( ! file_exists( $absolutePathToStoreTempFile ) ):
            return;
        endif;

        $filesInTempDir = scandir( $absolutePathToStoreTempFile );
        array_shift( $filesInTempDir ); // Remove .
        array_shift( $filesInTempDir ); // Remove ..
        foreach ( $filesInTempDir as $filename ):
            unlink( $absolutePathToStoreTempFile . DIRECTORY_SEPARATOR . $filename );
        endforeach;

        rmdir( $absolutePathToStoreTempFile );
    }

    /**
     * @param string $absolutePathToStoreTempFile
     *
     * @return void
     * @throws \Exception
     */
    protected function _createTempDirectoryForDownloadedFile( string $absolutePathToStoreTempFile ): void {

        if ( file_exists( $absolutePathToStoreTempFile ) ):
            $this->_deleteTempDirectoryAndFile( $absolutePathToStoreTempFile );
        endif;


        mkdir( $absolutePathToStoreTempFile,
               0777,
               TRUE );
    }

}