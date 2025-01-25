<?php

use PHPUnit\Framework\TestCase;
use function Ecxod\Funktionen\{
    load_locale
};

class LoadLocaleTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset superglobals
        $_SESSION = [];
        $_COOKIE = [];
        $_SERVER = [];
        $_ENV = [];
    }

    public function testDefaultLanguageIsSetToGerman()
    {
        // Call the function
        load_locale();

        // Assert default language
        $this->assertEquals('de', $_SESSION["lang"]);
        $this->assertEquals('de_DE', $_SESSION['locale_from_http']);
    }

    public function testCookieLanguageIsUsed()
    {
        // Simulate a 'lang' cookie
        $_COOKIE['lang'] = 'fr';

        // Call the function
        load_locale();

        // Assert that the language is taken from the cookie
        $this->assertEquals('fr', $_SESSION["lang"]);
    }

    public function testHttpAcceptLanguageIsProcessed()
    {
        // Simulate HTTP_ACCEPT_LANGUAGE
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';

        // Call the function
        load_locale();

        // Assert that locale from HTTP_ACCEPT_LANGUAGE is processed
        $this->assertEquals('en_US', $_SESSION['locale_from_http']);
        $this->assertEquals('en', $_SESSION['locale_display_language']);
        $this->assertEquals('United States', $_SESSION['locale_display_region']);
    }

    public function testDebugLoggingWhenDebugIsEnabled()
    {
        // Enable debugging via ENV
        $_ENV['DEBUGOPT'] = true;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es-ES';

        // Capture error log output
        $this->expectOutputRegex('/2_HTTP_ACCEPT_LANGUAGE=es-ES\/from_http=es_ES\/canonicalize=es_ES\/display_language=es\/display_region=Spain/');

        // Call the function
        load_locale();
    }

    public function testNoDebugLoggingWhenDebugIsDisabled()
    {
        // Ensure debugging is disabled
        $_ENV['DEBUGOPT'] = false;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'it-IT';

        // No output is expected
        $this->expectOutputString('');

        // Call the function
        load_locale();
    }
}
