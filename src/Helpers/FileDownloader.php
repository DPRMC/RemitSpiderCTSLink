<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers;

use DPRMC\RemitSpiderCTSLink\Exceptions\ExceededMaxAttemptsToDownloadFileException;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Cookies\Cookie;
use HeadlessChromium\Cookies\CookiesCollection;
use HeadlessChromium\Page;

/**
 * Ok... So here's how this object is meant to be used.
 * This guy is meant to download files that require some kind of authentiction
 * to get access to. ( Otherwise I would just use file_get_contents() )
 * You need to specify 2 local paths when downloading a file.
 * A temp directory where this object will create a temp subdir inside of.
 * The Chrome browser is not good at telling us what the name of the downloaded file is.
 * Makes it kinda hard to know when it's done downloading.
 * So the temp subdir will ONLY receive that one downloaded file.
 * I name the subdir the md5() of the URL being downloaded. Should be unique.
 * I will check 10 times ( after a small delay each time ) to see if the file has
 * finished downloading.
 * ( After 10 tries, I assume it timed out and throw an Exception )
 * After the file has been downloaded, I copy it to a user-specified final destination.
 * The user can supply a desired name for the final copy of the file.
 * Or the original file name will be used.
 * After all that is done, I delete the temp subdir.
 * Ta Da!
 */
class FileDownloader extends AbstractHelper {

    protected Page   $Page;
    protected Debug  $Debug;
    protected string $timezone;

    public CookiesCollection $cookies;

    protected int $maxTimesToCheckForDownloadBeforeGivingUp = 10;


    /**
     * @param Page $Page
     * @param Debug $Debug
     * @param string $timezone
     */
    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        parent::__construct( $Page, $Debug, $timezone );
    }


    public function fileGetContents(string $href, string $pathToSaveFile, string $fileName) {
        $cookies = $this->Page->getAllCookies();

        $cookieParts = [];
        /**
         * @var Cookie $cookie
         */
        foreach($cookies as $cookie):
            $cookieParts[] = $cookie->getName() . '=' . $cookie->getValue() . ';';
        endforeach;

        $cookieString = implode(' ', $cookieParts);

        // Create a stream
        $opts = [
            'http' => [
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                            "Cookie: " . $cookieString . "\r\n",
            ],
        ];

        $context = stream_context_create( $opts );

        // Open the file using the HTTP headers set above
        $fileContents = file_get_contents( $href, FALSE, $context );

        file_put_contents($pathToSaveFile . DIRECTORY_SEPARATOR . $fileName, $fileContents);
    }


    /**
     * @param string $hrefOfFileToDownload
     * @param string $tempDownloadPath
     * @param string $finalDownloadPath
     * @param string|NULL $newFileName
     * @return string
     * @throws ExceededMaxAttemptsToDownloadFileException
     * @throws \HeadlessChromium\Exception\CommunicationException
     */
    public function downloadFile( string $hrefOfFileToDownload,
                                  string $tempDownloadPath,
                                  string $finalDownloadPath,
                                  string $newFileName = NULL ): string {
        $this->Debug->_debug( "Setting temp download path to: " . $tempDownloadPath );
        $this->Page->setDownloadPath( $tempDownloadPath );


        // Since there is no *easy* way for Headless Chromium to let us know the name of the downloaded file...
        // My solution is to create a temporary unique directory to set as the download path for Headless Chromium.
        // After the download, there should be only one file in there.
        // Get the name of that file, and munge it as I see fit.
        $md5OfHREF                   = md5( $hrefOfFileToDownload );                            // This should always be unique.
        $absolutePathToStoreTempFile = $tempDownloadPath . DIRECTORY_SEPARATOR . $md5OfHREF;    // This DIR will end up having one file.

        $this->Debug->_debug( "Creating absolute path to store temp file set to: " . $absolutePathToStoreTempFile );

        $this->_createTempDirectoryForDownloadedFile( $absolutePathToStoreTempFile );

        $this->Debug->_debug( "Attempting to download: " . $hrefOfFileToDownload );

        $this->Page->navigate( $hrefOfFileToDownload );

        $this->Debug->_screenshot( 'est' );
        $this->Debug->_html( 'est' );
        $this->Page->pdf( [ 'printBackground' => FALSE ] )->saveToFile( 'tests/final/bar.pdf' );

        die( 'dead' );

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
                $message = "I have already checked " . $checkCount . " times. Enough is enough. Skipping it.";
                $this->Debug->_debug( "  " . $message );
                $this->_deleteTempDirectoryAndFile( $absolutePathToStoreTempFile );
                throw new ExceededMaxAttemptsToDownloadFileException( $message,
                                                                      0,
                                                                      NULL,
                                                                      $this->maxTimesToCheckForDownloadBeforeGivingUp,
                                                                      $hrefOfFileToDownload );
            endif;
        } while ( ! $this->_downloadComplete( $files ) );

        $fileName = $this->_getFilenameFromFiles( $files );
        $this->Debug->_debug( "  Done checking. I found the file: " . $fileName );

        $contents = file_get_contents( $absolutePathToStoreTempFile . DIRECTORY_SEPARATOR . $fileName );

        if ( $newFileName ):
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

        return $absolutePathToStoreFinalFile;
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
     * @return string The filename of the downloaded file from US Bank.
     * @throws \Exception
     */
    protected function _getFilenameFromFiles( array $files ): string {
        array_shift( $files ); // Remove .
        array_shift( $files ); // Remove ..
        if ( ! isset( $files[ 0 ] ) ):
            throw new \Exception( "Unable to find the downloaded file in the files array." );
        endif;

        return $files[ 0 ];
    }


    /**
     * @param string $absolutePathToStoreTempFile
     * @return void
     */
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