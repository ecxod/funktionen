<?php

declare(strict_types=1);

namespace Ecxod\Funktionen;

use \Erusev\Parsedown;
use \Dotenv\Dotenv;
use function \realpath;

/** 
 * 1) Markdown is parsed into HTML.
 * 2) Any potentially harmful characters in the HTML output (like <, >, &, etc.) are encoded, 
 * which is especially important if you plan to display user-generated content on the web.
 * 
 * @param string $string 
 * @return string 
 * @author Christian Eichert <c@zp1.net>
 * @version 1.1.0
 */
function c(string $string): string
{
    //return preg_replace('/[^A-Za-z0-9\-_\.\;\& ]/', '', $string);
    $Parsedown = new \Parsedown();
    $string = $Parsedown->line($string);
    $string = htmlspecialchars(
        $string,
        flags: $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        encoding: $encoding = 'UTF-8',
        double_encode: $double_encode = false
    );
    return $string;
}

/**
 * @param string|array $t 
 * @param string|null $m 
 * @param int|null $l 
 * @return true
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function logg(string|array $t, string $m = null, int $l = null): bool
{
    if (!empty($m) and file_exists(strval($m))) {
        $m = basename(strval($m));
    }
    if (gettype($t) === 'array') {
        if (isset($_ENV['ERRORLOG'])) {
            error_log_array($t, $m, $l);
            write_mail($t, $m, $l);
        }
        return true;
    } elseif (gettype($t) === 'string') {
        if (!empty($_ENV['ERRORLOG']) and is_writeable($_ENV['ERRORLOG'])) {
            error_log(
                (strval($t) ?: 'ERROR') .
                    (strval($m) ? " in " . strval($m) : "") .
                    (strval($l) ? " #" . strval($l) : "")
            );
            write_mail($t, $m, $l);
        }
        return true;
    } else {
        return true;
    }
}

/**
 * write a email - not jet used
 * 
 * @param string|null $t 
 * @param string|null $m 
 * @param int|null $l 
 * @return void 
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function write_mail(string $t = null, string $m = null, int $l = null): void
{
    // TODO:. die methode muss noch geschrieben werden :)))
    return;
}

/** 
 * diese function gehoert zu logg() und soll helfen arrays loggen
 * 
 * @param array $arr 
 * @param string|null $m 
 * @param int|null $l 
 * @return void 
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function error_log_array(array $arr, string $m = null, int $l = null): void
{
    logg("array(" . json_encode(value: $arr) . ")", $m, $l);
    return;
}

/** wenn ich und $_ENV['DEBUGOPT'] wahr => wird genauer geloggd
 * @param string $m Method (__METHOD__)
 * @param string|null $f File (__FILE__)
 * @param string|null $l Line (__LINE__)
 * @param string|null $t Some text
 * @return void 
 */
function h(string $m = null, string $f = null, string $l = null, string $t = null): void
{
    if (empty($_ENV['DEBUGOPT']))
        // we do not want to debug
        return;
    // checking if display_errors is set


    if (
        !empty($_ENV['DEBUGOPT']) and
        !empty($_ENV['DEBUGLOG']) and
        explode(separator: ':', string: $m)[0] !== 'D' and
        explode(separator: ':', string: $m)[0] !== 'MENUE' and
        isMe()
    ) {
        if (is_writable(filename: strval(value: $_ENV['DEBUGLOG']))) {
            $kl = explode(separator: '::', string: $m)[0];
            $fu = explode(separator: '::', string: $m)[1];

            if (!empty($kl) and !empty($fu) and method_exists(object_or_class: $kl, method: $fu))
                error_log(message: "\n >>> $m (method)\r", message_type: 3, destination: $_ENV['DEBUGLOG']);

            if ((empty($kl) or empty($fu) or !method_exists(object_or_class: $kl, method: $fu)) and file_exists(filename: $f))
                error_log(message: "\n >>> " . basename(path: $f) . " (file) " . $l, message_type: 3, destination: $_ENV['DEBUGLOG']);

            if (empty($m) and empty($f) and empty($l) and !empty($t))
                error_log(message: "\n >>> $t", message_type: 3, destination: $_ENV['DEBUGLOG']);

            if (empty($m) and empty($f) and empty($l) and empty($t))
                return;
        } else {
            try {
                touch(filename: $_ENV['DEBUGLOG']);
            } catch (\Exception $exception) {
                error_log(message: "\n=======================================\n");
                error_log(message: "\nACHTUNG !!! Kann nicht schreiben in: " . $_ENV['DEBUGLOG'] . " in " . __METHOD__);
                error_log(message: "\n=======================================\n");
            }
        }
    } else {
        return;
    }
}

/** 
 * Fügt (nur mir!) HTML Kommentare in den code ein.
 * Idealerweise ist Argument 1 die Methode. Das erscheint dann als KLASE::METHODE
 * 
 * @param string $m  
 * @return string 
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function m(string $m): string
{
    return isMe() ? PHP_EOL . '<!--' . strval($m) . '-->' . PHP_EOL : '';
}

/**
 * isMobile detects mobile devices
 * 
 * @return bool
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function isMobile(): bool
{
    if (isset($_SERVER["HTTP_USER_AGENT"])) {
        // preg_match(pattern, subject)
        return boolval(preg_match("/(" . mob_str() . ")/i", $_SERVER["HTTP_USER_AGENT"]));
    } else {
        return false;
    }
}

const mobile_geraete = ["android", "avantgo", "blackberry", "bolt", "boost", "cricket", "docomo", "fone", "hiptop", "mini", "mobi", "palm", "phone", "pie", "tablet", "up\.browser", "up\.link", "webos", "wos", "iphone", "ipad"];
function mob_str(): string
{
    return implode("|", mobile_geraete);
}

/** 
 * Detects me ;-)
 * 
 * @return bool
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function isMe(): bool
{
    $myIp = "";
    $BESUCHER = proxyDetection() ? strval(value: $_SERVER['HTTP_X_FORWARDED_FOR']) : strval(value: $_SERVER['REMOTE_ADDR']);

    if (empty($myIp))
        $myIp = strval(value: $_ENV['MYIP']);
    if (empty($myIp))
        $myIp = strval(value: getenv(name: 'MYIP'));
    if (empty($myIp)) {
        die("ERROR: " . __METHOD__);
    } else if (
        !empty($myIp)
        and
        isset($BESUCHER) and !empty($BESUCHER)
        and
        \in_array(needle: $BESUCHER, haystack: explode(separator: ",", string: $myIp))
    ) {
        return true;
    } else {
        return false;
    }
}

/** Detct if we are behind a proxy 
 * @return bool
 */
function proxyDetection(): bool
{
    // Check for common proxy headers
    $proxyHeaders = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED_SERVER',
        'HTTP_X_FORWARDED_HOST',
        'HTTP_CLIENT_IP',
        'HTTP_PROXY_CONNECTION'
    ];

    foreach ($proxyHeaders as $header) {
        if (!empty($_SERVER[$header])) {
            return true; // Detected a proxy
        }
    }

    return false; // No proxy detected
}



/** 
 * Function is checking if a library is loaded.
 * If there is no composer.lock file, it will return false.
 * 
 * @param string $library 
 * @param string|null $document_root 
 * @return bool 
 * @package composer_utils
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function libraryLoaded(string $library, string $document_root = null): bool
{
    if ($document_root === null) {
        $document_root = $_SERVER['DOCUMENT_ROOT'];
    }

    if (file_exists($document_root . '/composer.lock')) {
        $composerLock = json_decode(file_get_contents('composer.lock'), true);
        $packages = array_merge($composerLock['packages'], $composerLock['packages-dev']);

        $libraryFound = false;
        foreach ($packages as $package) {
            if ($package['name'] === $library) {
                $libraryFound = true;
                break;
            }
        }

        if ($libraryFound) {
            // Library is required in this project.
            return true;
        } else {
            // Library is not required in this project.
            return false;
        }
    } else {
        // Couldn't find composer.lock file.
        return false;
    }
}

/**
 * check if an element is not in an array and add if 
 * it's missing using the in_array function in PHP.
 * 
 * $myArray = ['apple', 'banana'];
 * addIfNotExists($myArray, 'orange');
 * addIfNotExists($myArray, 'banana');
 * 
 * @param mixed $array 
 * @param mixed $element 
 * @return void
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function addIfNotExists(&$array, $element): void
{
    if (!in_array(needle: $element, haystack: $array)) {
        $array[] = $element;
    }
}

/** 
 * TODO: sollte in ::A gesteuert werden und soll wenn möglich keine Session sein.
 * 
 * @return void
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function userAgent(): void
{
    // Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:96.0) Gecko/20100101 Firefox/96.0
    // Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0
    // Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0) Gecko/20100101 Firefox/42.0
    // Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36 Edg/96.0.1054.62
    // Opera/9.80 (Macintosh; Intel Mac OS X; U; en) Presto/2.2.15 Version/10.00
    // Opera/9.60 (Windows NT 6.0; U; en) Presto/2.1.1
    // Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1
    // Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0)

    if (!empty($_SERVER['HTTP_USER_AGENT'])) {

        $userAgent = preg_split("/[\(\)]/", $_SERVER['HTTP_USER_AGENT'], 3);
        // Wenn alles mit Mozilla beginnt.
        $el = []; // oder besser unset ? to.do.

        foreach ($userAgent as $key => $val) {

            $varr = explode(separator: "/", string: preg_replace(pattern: '/\((.*)\)/', replacement: '', subject: trim(string: $val)));
            $parr = explode(separator: ";", string: preg_replace(pattern: '/\((.*)\)/', replacement: '', subject: trim(string: $val)));
            $barr = explode(separator: " ", string: preg_replace(pattern: '/\((.*)\)/', replacement: '', subject: trim(string: $val)));

            $el[$key] = [];

            if ($key === 0) {
                $el[$key]['generic'] = $varr[0];
                $el[$key]['htmlver'] = $varr[1] ?? null;
            }

            if ($key === 1) {
                foreach ($parr as $k => $v) {
                    if ($k == 0)
                        $el[$key]["platform"] = str_replace(search: ['(', ')'], replace: '', subject: trim(string: $parr[$k]));
                    if ($k == 1)
                        $el[$key]["version"] = str_replace(search: ['(', ')'], replace: '', subject: trim(string: $parr[$k]));
                    if ($k >= 2)
                        $el[$key]["val$k"] = str_replace(search: ['(', ')'], replace: '', subject: trim(string: $parr[$k]));
                }
            }

            if ($key === 2) {
                $n = 0;
                $bl = array();
                foreach ($barr as $i => $j) {
                    $jj = explode("/", preg_replace('/\((.*)\)/', '', trim($j)));
                    if (!empty(trim($jj[0]))) {
                        $el[$key + $n]['product'] = $jj[0];
                        $bl[$n] = $jj[0];
                        $el[$key + $n]['version'] = !empty($jj[1]) ? $jj[1] : null;
                        $n++;
                    }
                }
            }
        }
        unset($_SESSION['useragent']);
        $_SESSION['useragent'] = array();
        $_SESSION['useragent'] = $el;
        $_SESSION['useragent']['1']['browserlist'] = isset($bl) ? implode(',', $bl) : '';
        //F::logg($_SESSION['useragent'],__METHOD__,__LINE__);
        return;
    } else {

        if (!empty($_SESSION['useragent'])) {
            //F::logg("\$_SESSION['useragent']=" . $_SESSION['useragent'], __METHOD__, __LINE__);
            $_SESSION['useragent'] = "";
        } else {
            $_SESSION['useragent'] = "";
        }
        return;
    }
}

/** Logout
 *
 * @return void
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function sayonara(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(), '', 0, '/');
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => $_SERVER['SERVER_NAME'], // Optional: Domain angeben
            'secure'   => true, // Nur über HTTPS übertragen
            'httponly' => true, // Nicht per JavaScript zugänglich
            'samesite' => 'Strict' // 'None', 'Lax' oder 'Strict'
        ]);
        session_start();
    }
}

/** 
 * Hauptaufgabe den Cookie[la] setzen
 * Nebenaufgaben : die Session 'locale_canonicalize', 'locale_display_language' und 'locale_display_region' zu setzen
 * Wenn er keine HTTP_ACCEPT_LANGUAGE bekommt, setzte er alles auf englisch.
 * 
 * @return void
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function languageManagement(): void
{

    if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $locale_from_http = "en_US";
    } else {
        $locale_from_http = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $_SESSION['locale_canonicalize'] = strval(\Locale::canonicalize($locale_from_http));
        $_SESSION['locale_display_language'] = strval(locale_get_display_language(
            locale: $locale_from_http,
            displayLocale: $_COOKIE['la'] ?? 'en'
        ));
        $_SESSION['locale_display_region'] = strval(locale_get_display_region(
            locale: $locale_from_http,
            displayLocale: $_COOKIE['la'] ?? 'en'
        ));
    }
}

/** 
 * @param string $envfile 
 * @return void
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 * @legacy Use function load_dotenv
 * //TODO wir müssen hier festlegen bei welchen namespace was geladen wird
 */
function dotEnv(string $envfile = '.env', string $namespace = null): void
{
    if (empty($envpath) and !empty($_ENV["DOTENV"])) {
        $envpath = realpath(path: strval(value: $_ENV["DOTENV"]));
    }
    if (empty($envpath) and !empty(getenv(name: "DOTENV"))) {
        $envpath = realpath(path: strval(value: getenv(name: "DOTENV")));
    }
    if (empty($envpath) and empty(getenv(name: "DOTENV")) and empty($_ENV["DOTENV"])) {
        $envpath = strval(value: realpath(path: $_SERVER['DOCUMENT_ROOT'] . '/../'));
    }
    if (empty($envpath)) {
        die("ERROR: Initialisation problem");
    }
    if (class_exists(class: 'Dotenv\Dotenv')) {
        if ($envpath) {
            $dotenv = Dotenv::createImmutable($envpath);
            $dotenv->load();
            $dotenv->required('CHARSET')->notEmpty();
            $dotenv->required('CHARSET')->allowedValues(['ISO-8859-1', 'ISO-8859-2', 'UTF8', 'UTF-8']);
            $dotenv->required('DSNMAPPING')->notEmpty();
            $dotenv->required('NAMESPACE')->notEmpty();
            $dotenv->required('LOCALEDIR')->notEmpty();
            $dotenv->required('AUTOLOAD')->notEmpty();
            $dotenv->required('PHPCLS')->notEmpty();
            $dotenv->required('TERM')->notEmpty();
        }
    } else {
        if (function_exists(function: 'Sentry\captureMessage')) {
            \Sentry\captureMessage("Class Dotenv\Dotenv is not loaded");
        }
        die("ERROR: Starting problem, please put oil.");
    }
}

/**
 * @param string $envfile 
 * @return void
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function load_dotenv(string $envfile = '.env')
{
    if (empty($envpath) and !empty($_ENV["DOTENV"])) {
        $envpath = realpath(strval($_ENV["DOTENV"]));
    }
    if (empty($envpath) and !empty(getenv("DOTENV"))) {
        $envpath = realpath(strval(getenv("DOTENV")));
    }
    if (empty($envpath) and empty(getenv("DOTENV")) and empty($_ENV["DOTENV"])) {
        $envpath = strval(realpath($_SERVER['DOCUMENT_ROOT'] . '/../'));
    }
    if (empty($envpath)) {
        die("ERROR: Initialisation problem");
    }
    if (class_exists('Dotenv\Dotenv')) {
        if ($envpath) {
            $dotenv = Dotenv::createImmutable($envpath, $envfile);
            $dotenv->load();
            $dotenv->required('CHARSET')->allowedValues(['UTF-8', 'utf8', 'utf-8']);
            $dotenv->required('AUTOLOAD')->notEmpty();
            $dotenv->required('PHPCLS')->notEmpty();
            $dotenv->required('TERM')->notEmpty();
        }
    } else {
        if (function_exists('Sentry\captureMessage')) {
            \Sentry\captureMessage("Class Dotenv\Dotenv is not loaded");
        }
        die("ERROR: Starting problem, please put oil.");
    }

    return;
}

function load_locale()
{
    // https://www.php.net/manual/en/locale.getdisplayregion.php#119895

    $_SESSION["lang"] = isset($_COOKIE['lang']) ? strval($_COOKIE['lang']) : 'de'; // wird vom User im Dropdown gesetzt
    $_SESSION['locale_from_http'] ?? "de_DE";
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $_SESSION['locale_from_http'] = strval(value: \Locale::acceptFromHttp(header: strval(value: $_SERVER['HTTP_ACCEPT_LANGUAGE'])));
        $_SESSION['locale_canonicalize'] = strval(value: \Locale::canonicalize(locale: strval(value: $_SESSION['locale_from_http'])));
        $_SESSION['locale_display_language'] = strval(value: \Locale::getDisplayLanguage(locale: $_SESSION['locale_from_http'], displayLocale: $_SESSION["lang"]));
        $_SESSION['locale_display_region'] = strval(value: \Locale::getDisplayRegion(locale: $_SESSION['locale_from_http'], displayLocale: $_SESSION["lang"]));
        if (!empty($_ENV['DEBUGOPT']) and is_writable($_ENV['DEBUGOPT'])) {
            error_log(
                "2_HTTP_ACCEPT_LANGUAGE=" . $_SERVER['HTTP_ACCEPT_LANGUAGE'] .
                    "/from_http=" . $_SESSION['locale_from_http'] .
                    "/canonicalize=" . $_SESSION['locale_canonicalize'] .
                    "/display_language=" . $_SESSION['locale_display_language'] .
                    "/display_region=" . $_SESSION['locale_display_region']
            );
        }
    }

    return;
}

// /** 
//  * @param string $json 
//  * @return bool ex. JSON_ERROR_NONE, JSON_ERROR_SYNTAX  etc ...
//  * 











/**   
 * if $verbose is not set ot false - it returns "true" or "false"  
 * if $verbose is true - it returns an error constant, such as JSON_ERROR_NONE (no error) or others like JSON_ERROR_SYNTAX (invalid JSON).  
 * https://www.php.net/manual/function.json-decode.php
 * //TODO: etwas ist da faul
 * @param string $json 
 * @param null|bool $associative true, false, null
 * @param int $depth 512
 * @param int $flags 0
 * @param bool|null $verbose true, false, null 
 * @return bool|int
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function is_json(string $json, bool|null $associative = null, int $depth = 512, int $flags = 0, bool|null $verbose = true): bool|int|string
{
    json_decode(json: $json, associative: $associative, depth: $depth, flags: $flags);
    if (empty($verbose)) {
        return json_last_error() === JSON_ERROR_NONE;
    } else {
        return json_last_error_msg();
    }
}



/**
 * @param mixed $needle 
 * @param array $haystack 
 * @param bool $strict 
 * @return bool 
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function in_array_recursive(mixed $needle, array $haystack, bool $strict = false): bool
{
    foreach ($haystack as $element) {
        if (
            ($strict ? $element === $needle : $element == $needle) ||
            (is_array(value: $element) && in_array_recursive($needle, haystack: $element, strict: $strict))
        ) {
            return true;
        }
    }
    return false;
}


/** //TODO warum ist diese funktion anders ?
 * @param mixed $needle 
 * @param array $haystack 
 * @param bool $strict 
 * @param bool $recursive 
 * @param int $depth 
 * @return bool 
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function in_array(
    mixed $needle,
    array $haystack,
    bool $strict = false,
    bool $recursive = false,
    int $depth = -1
): bool {
    if ($depth === 0)
        return false;
    foreach ($haystack as $element) {
        if (($strict ? $element === $needle : $element == $needle))
            return true;
        if ($recursive && is_array(value: $element))
            if (in_array(needle: $needle, haystack: $element, strict: $strict, recursive: $recursive, depth: $depth - 1))
                return true;
    }
    return false;
}



// TODO brauch noch jemand das ab hier ?

function cleanString($text)
{
    $utf8 = array(
        '/[áàâãª]/u' => 'a',
        '/[ÁÀÂÃ]/u'  => 'A',
        '/[ÍÌÎÏ]/u'  => 'I',
        '/[íìîï]/u'  => 'i',
        '/[éèêë]/u'  => 'e',
        '/[ÉÈÊË]/u'  => 'E',
        '/[óòôõº]/u' => 'o',
        '/[ÓÒÔÕ]/u'  => 'O',
        '/[úùû]/u'   => 'u',
        '/[ÚÙÛ]/u'   => 'U',
        '/ç/'        => 'c',
        '/Ç/'        => 'C',
        '/ñ/'        => 'n',
        '/Ñ/'        => 'N',
        '/–/'        => '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/u' => ' ', // Literally a single quote
        '/[“”«»„]/u' => ' ', // Double quote
        '/ /'        => ' ', // nonbreaking space (equiv. to 0x160)
    );
    return preg_replace(array_keys($utf8), array_values($utf8), $text);
}



/**
 * Sends a message to Sentry
 * 
 * @param string $message
 * @param string $level [warning, error, fatal]
 * @return void
 */
function captureMessage(string $message = null, string $level = "warning"): void
{
    $level_arr = ["warning", "error", "fatal"];
    if (
        in_array($level, $level_arr) and
        method_exists(object_or_class: "\\Sentry\\Sentry", method: "captureMessage") and
        method_exists(object_or_class: "\\Sentry\\Sentry", method: "withScope")
    ) {
        \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($message, $level): void {
            if ($level == "error")
                $scope->setLevel(\Sentry\Severity::error());
            if ($level == "warning")
                $scope->setLevel(\Sentry\Severity::warning());
            if ($level == "fatal")
                $scope->setLevel(\Sentry\Severity::fatal());

            if (isset($message)) {
                // Use the $message variable instead of hardcoded string
                \Sentry\captureMessage($message);
            }
        });
    }
}

/**
 * Log something to the log location or send to sentry
 * 
 * @param string|null $logfile 
 * @param string|null $logstring 
 * 
 * @return void 
 */
function log(string $logfile = null, string $logstring, string $sentryLevel = "warning"): void
{
    $level_arr = ["warning", "error", "fatal"];
    if (in_array(needle: $sentryLevel, haystack: $level_arr) == false) {
        $sentryLevel = "warning";
    }


    if (!empty($logfile)) {
        // Wenn kein Pfad existiert erstellen wir ihn
        if (!empty($logfile) and !is_file(filename: $logfile)) {
            touch(filename: $logfile);
        }

        // da wir jetzt ein logPath haben , nutzen wir ihn
        try {

            $realLogpath = realpath(path: $logfile);
            if ($realLogpath) {
                error_log(message: $logstring, message_type: 3, destination: $realLogpath);
            } else {
                error_log(message: $logstring);
            }
        } catch (Exception $e) {

            // \Sentry\withScope( function (\Sentry\State\Scope $scope, string $realLogpath,string  $e): void 
            // {
            //     $scope->setLevel(\Sentry\Severity::warning());
            //     \Sentry\captureMessage(
            //         message: "Can not create $realLogpath. The location is not writeable. $e " . __NAMESPACE__ . ":::" . __METHOD__ . ":" . __LINE__
            //     );
            // }, $realLogpath, $e);

            captureMessage(message: "Can not create $realLogpath. The location is not writeable. $e " . __NAMESPACE__ . ":::" . __METHOD__ . ":" . __LINE__, level: $sentryLevel);
        }
    } else {

        // \Sentry\withScope( function (\Sentry\State\Scope $scope,string $logstring): void {
        //     $scope->setLevel(\Sentry\Severity::warning());
        //     \Sentry\captureMessage(
        //         message: $logstring ." ::::". __NAMESPACE__ . ":::" . __METHOD__ . ":" . __LINE__
        //     );
        // });
        captureMessage(message: $logstring . " ::::" . __NAMESPACE__ . ":::" . __METHOD__ . ":" . __LINE__, level: $sentryLevel);
        error_log(message: $logstring);
    }
}







































/** 
 * returns a list of keys of the method in witch it was called like :
 * echo F::v(get_defined_vars());
 * 
 * // TODO: umziehen in globale F 
 * 
 * @param array $get_defined_vars 
 * @param string $outputformat 
 * @return array|string 
 */
function vv(array $get_defined_vars, array $func_get_args = null, string $method = null, string $outputformat = "string")
{

    $outputformatArray = ["string", "array"];
    $outputformat = in_array(needle: $outputformat, haystack: $outputformatArray) ? $outputformat : "string";

    $method_keys_array = empty($get_defined_vars) ? [] : array_keys(array: $get_defined_vars);
    $method_keys_csv = implode(separator: ',', array: $method_keys_array);

    $param_keys_array = empty($func_get_args) ? [] : array_keys(array: $func_get_args);
    $param_keys_csv = implode(separator: ',', array: $param_keys_array);

    $method_with_keys = $method ? "$method($method_keys_csv) " .
        (
            empty($param_keys_csv) ? "" : "SELF($param_keys_csv)"
        ) :
        (string) $method_keys_csv . ",(self::)" . $param_keys_csv;

    return $outputformat === "array" ? (array) [$method_keys_array, $param_keys_array] : (string) $method_with_keys;
}

function v(array $get_defined_vars = null, array $func_get_args = null, string $m = null)
{

    $debuglog_aus_const = (class_exists("Ecxod\\Funktionen\\K") and defined("K::DEBUGLOG")) ?
        strval(value: realpath(path: strval(value: K::DEBUGLOG))) : '';
    $debuglog_aus_server = empty($_SERVER['error_log']) ? '' : strval(value: realpath(path: strval(value: $_SERVER['error_log'])));
    $error_message_text = "\n >>> " . strval(value: vv(get_defined_vars: $get_defined_vars, func_get_args: $func_get_args, method: $m, outputformat: "string")) . "\n";


    if (!empty($debuglog_aus_const)) {
        $debuglog = $debuglog_aus_const;
    } elseif (!empty($debuglog_aus_server)) {
        $debuglog = $debuglog_aus_server;
    } else {
        $debuglog = null;
    }

    if (
        (!empty($_ENV['DEBUGOPT']) or
            (class_exists(class: "Ecxod\\Funktionen\\K") and
                defined(constant_name: "K::DEBUGLOG"))) and
        explode(separator: ':', string: $m)[0] !== 'D' and
        explode(separator: ':', string: $m)[0] !== 'MENUE' and
        isMe()
    ) {
        if (
            !empty($debuglog) and
            file_exists(filename: $debuglog) and
            is_writable(filename: $debuglog)
        ) {
            error_log(message: $error_message_text, message_type: 3, destination: $debuglog);
        } elseif (
            !empty($debuglog) and
            file_exists(filename: $debuglog) and
            !is_writable(filename: $debuglog)
        ) {
            try {
                touch($debuglog);
            } catch (\Exception $e) {
                error_log("\n=======================================\n");
                error_log("\nACHTUNG !!! Kann nicht schreiben in ENV[DEBUGLOG] $debuglog in " . __METHOD__ . ":" . __LINE__);
                error_log("\n=======================================\n");
            }
            error_log(message: $error_message_text);
        } else {
            error_log(message: $error_message_text);
        }
    } else {
        return;
    }
}
