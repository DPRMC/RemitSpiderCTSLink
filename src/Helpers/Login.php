<?php

namespace DPRMC\RemitSpiderCTSLink\Helpers;

use DPRMC\RemitSpiderCTSLink\Exceptions\LoginTimedOutException;
use DPRMC\RemitSpiderCTSLink\Exceptions\NeedToResetPasswordException;
use DPRMC\RemitSpiderCTSLink\Exceptions\WrongNumberOfTitleElementsException;
use DPRMC\RemitSpiderCTSLink\RemitSpiderCTSLink;
use HeadlessChromium\Clip;
use HeadlessChromium\Cookies\CookiesCollection;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Page;

/**
 *
 */
class Login {

    const URL_LOGIN = 'https://www.ctslink.com/';

    //https://wca.www.ctslink.com/wcaapi/login/auth/logout?appId=appcts&brandId=CTSLink&isWidget=true&sId=0e85f55c-1276-4aba-abe3-6e60578e7059
    const URL_LOGOUT = 'https://wca.www.ctslink.com/wcaapi/login/auth/logout?appId=appcts&brandId=CTSLink&isWidget=true&sId=';

    // 2024-10-22:mdd They added a button you need to click to display the user/pass form
    const SIGN_IN_BUTTON_X = 930;
    const SIGN_IN_BUTTON_Y = 25;

    const LOGIN_BUTTON_X = 50;
    //const LOGIN_BUTTON_Y = 370;
    const LOGIN_BUTTON_Y = 396; // 2024-10-22:mdd They moved it down a few pixels because of the new header.

    const LOGOUT_BUTTON_X = 570;
    const LOGOUT_BUTTON_Y = 25;


    //const URL_INTERFACE  = RemitSpiderUSBank::BASE_URL . '/TIR/portfolios';

    protected Page   $Page;
    protected Debug  $Debug;
    protected string $user;
    protected string $pass;
    protected string $timezone;

    public ?string           $csrf = NULL;
    public CookiesCollection $cookies;


    public function __construct( Page   &$Page,
                                 Debug  &$Debug,
                                 string $user,
                                 string $pass,
                                 string $timezone = RemitSpiderCTSLink::DEFAULT_TIMEZONE ) {
        $this->Page     = $Page;
        $this->Debug    = $Debug;
        $this->user     = $user;
        $this->pass     = $pass;
        $this->timezone = $timezone;
    }


    /**
     * @return string
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\ResponseHasError
     * @throws \HeadlessChromium\Exception\FilesystemException
     * @throws \HeadlessChromium\Exception\NavigationExpired
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     * @throws \HeadlessChromium\Exception\ScreenshotFailed
     * @throws \Exception
     */
    public function login(): string {
        $this->Debug->_debug( "Navigating to login screen." );
        $this->Page->navigate( self::URL_LOGIN )->waitForNavigation();
        $this->Debug->_screenshot( 'first_page' );

        $this->Debug->_screenshot( 'where_i_clicked_to_display_user_pass', new Clip( 0,
                                                                         0,
                                                                         self::SIGN_IN_BUTTON_X,
                                                                         self::SIGN_IN_BUTTON_Y ) );
        $this->Page->mouse()
                   ->move( self::SIGN_IN_BUTTON_X, self::SIGN_IN_BUTTON_Y )
                   ->click();
        //$this->Page->waitUntilContainsElement('#loginButton',100);
        sleep(2);
        $this->Debug->_screenshot( 'should_see_user_pass_form' );


        $this->Debug->_debug( "Filling out user and pass." );
        $user = json_encode($this->user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $pass = json_encode($this->pass, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $this->Page->evaluate("document.querySelector('#userid').value = " . $user . ";" );
        $this->Page->evaluate("document.querySelector('#password').value = " . $pass . ";" );

        // DEBUG
        $this->Debug->_screenshot( 'filled_in_user_pass' );
        $this->Debug->_screenshot( 'where_i_clicked_to_login', new Clip( 0,
                                                                         0,
                                                                         self::LOGIN_BUTTON_X,
                                                                         self::LOGIN_BUTTON_Y ) );


        // Click the login button, and wait for the page to reload.
        $this->Debug->_debug( "Clicking the login button." );

        try {
            $this->Page->mouse()
                       ->move( self::LOGIN_BUTTON_X, self::LOGIN_BUTTON_Y )
                       ->click();
            $this->Page->waitForReload();
        } catch ( OperationTimedOut $exception ) {
            $this->Debug->_screenshot( 'i_am_not_logged_in' );
            $this->Debug->_html( 'i_am_not_logged_in' );

            $html = $this->Page->getHtml();
            if ( $this->_needToResetPassword( $html ) ):
                throw new NeedToResetPasswordException();
            endif;

            throw new LoginTimedOutException( $exception->getMessage(),
                                              $exception->getCode(),
                                              $exception );
        }


        $this->Debug->_screenshot( 'am_i_logged_in' );

        $this->cookies = $this->Page->getAllCookies();
        $postLoginHTML = $this->Page->getHtml();

        $this->Debug->_html( "post_login" );

        return $postLoginHTML;
    }


    public function logout(): bool {

        $this->Page->navigate( self::URL_LOGOUT )->waitForNavigation();
        $this->Debug->_screenshot( 'loggedout' );
        return TRUE;

        $this->Debug->_screenshot( 'about_to_logout' );
        $this->Debug->_screenshot( 'where_i_click_to_logout', new Clip( 0, 0, self::LOGOUT_BUTTON_X, self::LOGOUT_BUTTON_Y ) );
        $this->Debug->_debug( "Clicking the sign out button." );
        $this->Page->mouse()
                   ->move( self::LOGOUT_BUTTON_X, self::LOGOUT_BUTTON_Y )
                   ->click();
        //$this->Page->waitForReload();
        sleep( 3 );
        $this->Debug->_screenshot( 'am_i_logged_out' );
        return TRUE;
    }


    /**
     * @param string $html
     * @return bool
     * @throws WrongNumberOfTitleElementsException
     */
    protected function _needToResetPassword( string $html ): bool {
        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );

        /**
         * @var \DOMNodeList $elements
         */
        $elements = $dom->getElementsByTagName( 'title' );

        if ( 1 != $elements->count() ):
            throw new WrongNumberOfTitleElementsException( $elements->count() . " title elements were found. Should only see 1.",
                                                           0,
                                                           NULL,
                                                           $elements,
                                                           $html );
        endif;


        $titleText = trim( $elements->item( 0 )->textContent );

        if ( 'Reset Password' == $titleText ):
            return TRUE;
        endif;

        return FALSE;
    }


    /**
     * @param string $html
     *
     * @return string
     * @throws \Exception
     */
    protected function getCSRF( string $html ): string {
        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );
        $inputs = $dom->getElementsByTagName( 'input' );
        foreach ( $inputs as $input ):
            $id = $input->getAttribute( 'id' );

            // This is the one we want!
            if ( 'OWASP_CSRFTOKEN' == $id ):
                return $input->getAttribute( 'value' );
            endif;
        endforeach;

        // Secondary Search if first was unfruitful. I have been getting some errors.
        // This regex search is looing for:
        // xhr.setRequestHeader('OWASP_CSRFTOKEN', 'AAAA-BBBB-CCCC-DDDD-EEEE-FFFF-GGGG-HHHH');
        //$pattern = "/'OWASP_CSRFTOKEN', '(.*)'\);/";
        $pattern = '/([A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4})/';
        $matches = [];
        $success = preg_match( $pattern, $html, $matches );
        if ( 1 === $success ):
            return $matches[ 1 ];
        endif;

        throw new \Exception( "Unable to find the CSRF value in the HTML." );
    }

}
