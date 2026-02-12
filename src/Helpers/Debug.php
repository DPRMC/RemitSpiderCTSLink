<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers;

use Carbon\Carbon;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Clip;
use HeadlessChromium\Page;

/**
 *
 */
class Debug {

    protected Page   $page;
    protected string $pathToScreenshots;
    protected bool   $debug;
    protected string $timezone;

    public function __construct( Page   &$page,
                                 string $pathToScreenshots = '',
                                 bool   $debug = FALSE,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        $this->page              = $page;
        $this->pathToScreenshots = $pathToScreenshots;
        $this->debug             = $debug;
        $this->timezone          = $timezone;
    }

    public function enableDebug(): void {
        $this->debug = TRUE;
        $this->ensureDebugDirectory();
        $this->_debug( "Debug now ENABLED. Screenshots are being saved to: " . $this->pathToScreenshots );
    }

    public function disableDebug(): void {
        $this->_debug( "Debug now DISABLED. Remember to delete screenshots from: " . $this->pathToScreenshots );
        $this->debug = FALSE;
    }


    /**
     * This is just a little helper function to clean up some debug code.
     *
     * @param string                      $suffix
     * @param \HeadlessChromium\Clip|NULL $clip
     *
     * @return void
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\FilesystemException
     * @throws \HeadlessChromium\Exception\ScreenshotFailed
     */
    public function _screenshot( string $suffix, Clip $clip = NULL, int $timeout = 5000 ) {

        $now   = Carbon::now( $this->timezone );
        $time  = $now->timestamp;
        $micro = $now->microsecond;

        if ( $this->debug ):
            $pathPrefix = $this->getDebugPathPrefix();
            if ( $clip ):
                $this->page->screenshot( [ 'clip' => $clip ] )
                           ->saveToFile( $pathPrefix . $time . '_' . $micro . '_' . $suffix . '.jpg', $timeout );
            else:
                $this->page->screenshot()
                           ->saveToFile( $pathPrefix . $time . '_' . $micro . '_' . $suffix . '.jpg', $timeout );
            endif;
        endif;
    }


    public function _html( string $filename ) {
        $now   = Carbon::now( $this->timezone );
        $time  = $now->timestamp;
        $micro = $now->microsecond;
        if ( $this->debug ):
            $html = $this->page->getHtml();
            $pathPrefix = $this->getDebugPathPrefix();
            file_put_contents( $pathPrefix . $time . '_' . $micro . '_' . $filename . '.html', $html );
        endif;
    }

    public function _debug( string $message, bool $die = FALSE ) {
        if ( $this->debug ):
            echo "\n" . $message;
            flush();
            if ( $die ):
                die();
            endif;
        endif;
    }

    private function ensureDebugDirectory(): void {
        if ($this->pathToScreenshots === '') {
            return;
        }

        if (!is_dir($this->pathToScreenshots)) {
            mkdir($this->pathToScreenshots, 0777, true);
        }
    }

    private function getDebugPathPrefix(): string {
        $this->ensureDebugDirectory();
        if ($this->pathToScreenshots === '') {
            return '';
        }

        return rtrim($this->pathToScreenshots, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}
