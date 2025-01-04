<?php

declare(strict_types=1);

namespace Ecxod\Funktionen;

use \Dotenv\Dotenv;
use \Sentry;
use \Sentry\State;
use \Sentry\Severity;

use Locale;
use Sentry\State\Scope;
use function array_merge;
use function basename;
use function class_exists;
use function error_log;
use function explode;
use function file_exists;
use function file_get_contents;
use function implode;
use function is_writable;
use function json_decode;
use function realpath;
use function \Sentry\captureException;
use function \Sentry\captureMessage;
use function \Sentry\withScope;
use function strval;



/**
 * @param string $string 
 * @return string 
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
function c(string $string): string
{
    //return preg_replace('/[^A-Za-z0-9\-_\.\;\& ]/', '', $string);
    $Parsedown = new \Parsedown();
    $string = htmlspecialchars($string, $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, $encoding = 'UTF-8', $double_encode = false);
    $string = $Parsedown->line($string);
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
    if(!empty($m) and \file_exists(filename: \strval(value: $m)))
    {
        $m = \basename(path: \strval(value: $m));
    }
    if(\gettype($t) === 'array')
    {
        if(isset($_ENV['ERRORLOG']))
        {
            \Ecxod\Funktionen\error_log_array(arr: $t, m: $m, l: $l);
            \Ecxod\Funktionen\write_mail(t: $t, m: $m, l: $l);
        }
        return true;
    }
    elseif(gettype($t) === 'string')
    {
        if($_ENV['ERRORLOG'])
        {
            \error_log(
                message: (strval($t) ?: 'ERROR') .
                (\strval($m) ? " in " . \strval($m) : "") .
                (\strval($l) ? " #" . \strval($l) : "")
            );
            \Ecxod\Funktionen\write_mail($t, $m, $l);
        }
        return true;
    }
    else
    {
        return true;
    }
}


function sentry_warning(string $warningtext)
{
    withScope(
        function (Scope $scope) use ($warningtext): void
        {
            $scope->setLevel(Severity::warning());
            captureMessage($warningtext);
        }
    );
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
    logg("array(" . json_encode($arr) . ")", $m, $l);
    return;
}

/** wenn ich und $_ENV['DEBUGOPT'] wahr => wird genauer geloggd
 * @param string $m Method (__METHOD__)
 * @param string|null $f File (__FILE__)
 * @param string|null $l Line (__LINE__)
 * @return void 
 */
function h(string $m, string $f = null, string $l = null): void
{
    if(
        !empty($_ENV['DEBUGOPT']) and
        !empty($_ENV['DEBUGLOG']) and
        explode(separator: ':', string: $m)[0] !== 'D' and
        explode(separator: ':', string: $m)[0] !== 'MENUE' and
        isMe()
    )
    {
        if(\is_writable(filename: \strval(value: $_ENV['DEBUGLOG'])))
        {
            $kl = explode(separator: '::', string: $m)[0];
            $fu = explode(separator: '::', string: $m)[1];
            if(!empty($kl) and !empty($fu) and method_exists(object_or_class: $kl, method: $fu))
                error_log(message: "\n >>> $m (method)\r", message_type: 3, destination: $_ENV['DEBUGLOG']);
            if((empty($kl) or empty($fu) or !method_exists(object_or_class: $kl, method: $fu)) and file_exists(filename: $f))
                error_log(message: "\n >>> " . basename(path: $f) . " (file) " . $l, message_type: 3, destination: $_ENV['DEBUGLOG']);
        }
        else
        {
            try
            {
                touch(filename: $_ENV['DEBUGLOG']);
            }
            catch (\Throwable $exception)
            {
                \Sentry\captureException($exception);
                \error_log(message: "\n=======================================\n");
                \error_log(message: "\nACHTUNG !!! Kann nicht schreiben in: " . $_ENV['DEBUGLOG'] . " in " . __METHOD__);
                \error_log(message: "\n=======================================\n");
            }
        }
    }
    else
    {
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
    if(isset($_SERVER["HTTP_USER_AGENT"]))
    {
        // preg_match(pattern, subject)
        return boolval(preg_match("/(" . mob_str() . ")/i", $_SERVER["HTTP_USER_AGENT"]));
    }
    else
    {
        return false;
    }
}

const mobile_geraete = [ "android", "avantgo", "blackberry", "bolt", "boost", "cricket", "docomo", "fone", "hiptop", "mini", "mobi", "palm", "phone", "pie", "tablet", "up\.browser", "up\.link", "webos", "wos", "iphone", "ipad" ];
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
    if(empty($myIp))
        $myIp = strval(value: $_ENV['MYIP']);
    if(empty($myIp))
        $myIp = strval(value: getenv(name: 'MYIP'));
    if(empty($myIp))
    {
        die("ERROR: " . __METHOD__);
    }
    else if(
        !empty($myIp) and
        isset($_SERVER['REMOTE_ADDR']) and !empty($_SERVER['REMOTE_ADDR']) and
        \in_array(needle: $_SERVER['REMOTE_ADDR'], haystack: explode(separator: ",", string: $myIp))
    )
    {
        return true;
    }
    else
    {
        return false;
    }
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
    if($document_root === null)
    {
        $document_root = $_SERVER['DOCUMENT_ROOT'];
    }

    if(\file_exists($document_root . '/composer.lock'))
    {
        $composerLock = \json_decode(\file_get_contents('composer.lock'), true);
        $packages = \array_merge($composerLock['packages'], $composerLock['packages-dev']);

        $libraryFound = false;
        foreach($packages as $package)
        {
            if($package['name'] === $library)
            {
                $libraryFound = true;
                break;
            }
        }

        if($libraryFound)
        {
            // Library is required in this project.
            return true;
        }
        else
        {
            // Library is not required in this project.
            return false;
        }
    }
    else
    {
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
    if(!in_array(needle: $element, haystack: $array))
    {
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

    if(!empty($_SERVER['HTTP_USER_AGENT']))
    {

        $userAgent = preg_split("/[\(\)]/", $_SERVER['HTTP_USER_AGENT'], 3);
        // Wenn alles mit Mozilla beginnt.
        $el = []; // oder besser unset ? to.do.

        foreach($userAgent as $key => $val)
        {

            $varr = explode(separator: "/", string: preg_replace(pattern: '/\((.*)\)/', replacement: '', subject: trim(string: $val)));
            $parr = explode(separator: ";", string: preg_replace(pattern: '/\((.*)\)/', replacement: '', subject: trim(string: $val)));
            $barr = explode(separator: " ", string: preg_replace(pattern: '/\((.*)\)/', replacement: '', subject: trim(string: $val)));

            $el[$key] = [];

            if($key === 0)
            {
                $el[$key]['generic'] = $varr[0];
                $el[$key]['htmlver'] = $varr[1] ?? null;
            }

            if($key === 1)
            {
                foreach($parr as $k => $v)
                {
                    if($k == 0)
                        $el[$key]["platform"] = str_replace(search: [ '(', ')' ], replace: '', subject: trim(string: $parr[$k]));
                    if($k == 1)
                        $el[$key]["version"] = str_replace(search: [ '(', ')' ], replace: '', subject: trim(string: $parr[$k]));
                    if($k >= 2)
                        $el[$key]["val$k"] = str_replace(search: [ '(', ')' ], replace: '', subject: trim(string: $parr[$k]));
                }
            }

            if($key === 2)
            {
                $n = 0;
                $bl = array();
                foreach($barr as $i => $j)
                {
                    $jj = explode("/", preg_replace('/\((.*)\)/', '', trim($j)));
                    if(!empty(trim($jj[0])))
                    {
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
    }
    else
    {

        if(!empty($_SESSION['useragent']))
        {
            //F::logg("\$_SESSION['useragent']=" . $_SESSION['useragent'], __METHOD__, __LINE__);
            $_SESSION['useragent'] = "";
        }
        else
        {
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
    if(\session_status() === PHP_SESSION_ACTIVE)
    {
        \session_unset();
        \session_destroy();
        \session_write_close();
        \setcookie(name: \session_name(), value: '', expires_or_options: 0, path: '/');
        if(\session_status() === PHP_SESSION_ACTIVE)
        {
            \session_regenerate_id(delete_old_session: true);
        }
        \session_start();
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

    if(empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
    {
        $locale_from_http = "en_US";
    }
    else
    {
        $locale_from_http = locale_accept_from_http(header: $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $_SESSION['locale_canonicalize'] = strval(value: Locale::canonicalize($locale_from_http));
        $_SESSION['locale_display_language'] = strval(value: locale_get_display_language(
            locale: $locale_from_http,
            displayLocale: isset($_COOKIE['la']) ? $_COOKIE['la'] : 'en'
        ));
        $_SESSION['locale_display_region'] = strval(value: locale_get_display_region(
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
    if(empty($envpath) and !empty($_ENV["DOTENV"]))
    {
        $envpath = realpath(path: strval(value: $_ENV["DOTENV"]));
    }
    if(empty($envpath) and !empty(getenv(name: "DOTENV")))
    {
        $envpath = realpath(path: strval(value: getenv(name: "DOTENV")));
    }
    if(empty($envpath) and empty(getenv(name: "DOTENV")) and empty($_ENV["DOTENV"]))
    {
        $envpath = strval(value: realpath(path: $_SERVER['DOCUMENT_ROOT'] . '/../'));
    }
    if(empty($envpath))
    {
        die("ERROR: Initialisation problem");
    }
    if(class_exists(class: 'Dotenv\Dotenv'))
    {
        if($envpath)
        {
            $dotenv = Dotenv::createImmutable($envpath);
            $dotenv->load();
            $dotenv->required('CHARSET')->notEmpty();
            $dotenv->required('CHARSET')->allowedValues([ 'ISO-8859-1', 'ISO-8859-2', 'UTF8', 'UTF-8' ]);
            $dotenv->required('DSNMAPPING')->notEmpty();
            $dotenv->required('NAMESPACE')->notEmpty();
            $dotenv->required('LOCALEDIR')->notEmpty();
            $dotenv->required('AUTOLOAD')->notEmpty();
            $dotenv->required('PHPCLS')->notEmpty();
            $dotenv->required('TERM')->notEmpty();
        }
    }
    else
    {
        if(function_exists(function: 'Sentry\captureMessage'))
        {
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
function load_dotenv(string $envfile = '.env'): void
{
    if(empty($envpath) and !empty($_ENV["DOTENV"]))
    {
        $envpath = realpath(path: strval(value: $_ENV["DOTENV"]));
    }
    if(empty($envpath) and !empty(getenv(name: "DOTENV")))
    {
        $envpath = \realpath(path: \strval(value: \getenv(name: "DOTENV")));
    }
    if(empty($envpath) and empty(\getenv("DOTENV")) and empty($_ENV["DOTENV"]))
    {
        $envpath = \strval(\realpath($_SERVER['DOCUMENT_ROOT'] . '/../'));
    }
    if(empty($envpath))
    {
        die("ERROR: Initialisation problem");
    }
    if(class_exists(class: 'Dotenv\Dotenv'))
    {
        if($envpath)
        {
            $dotenv = Dotenv::createImmutable($envpath, $envfile);
            $dotenv->load();
            $dotenv->required('CHARSET')->allowedValues([ 'UTF-8', 'utf8', 'utf-8' ]);
            $dotenv->required('AUTOLOAD')->notEmpty();
            $dotenv->required('PHPCLS')->notEmpty();
            $dotenv->required('TERM')->notEmpty();
        }
    }
    else
    {
        if(function_exists('Sentry\captureMessage'))
        {
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
    if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
    {
        $_SESSION['locale_from_http'] = strval(value: Locale::acceptFromHttp(header: strval(value: $_SERVER['HTTP_ACCEPT_LANGUAGE'])));
        $_SESSION['locale_canonicalize'] = strval(value: Locale::canonicalize(locale: strval(value: $_SESSION['locale_from_http'])));
        $_SESSION['locale_display_language'] = strval(value: Locale::getDisplayLanguage(locale: $_SESSION['locale_from_http'], displayLocale: $_SESSION["lang"]));
        $_SESSION['locale_display_region'] = strval(value: Locale::getDisplayRegion(locale: $_SESSION['locale_from_http'], displayLocale: $_SESSION["lang"]));
        if((\class_exists("Ecxod\\Funktionen\\K") and K::DEBUGOPT) or $_ENV['DEBUGOPT'])
        {
            \error_log(
                message: "2_HTTP_ACCEPT_LANGUAGE=" . $_SERVER['HTTP_ACCEPT_LANGUAGE'] .
                "/from_http=" . $_SESSION['locale_from_http'] .
                "/canonicalize=" . $_SESSION['locale_canonicalize'] .
                "/display_language=" . $_SESSION['locale_display_language'] .
                "/display_region=" . $_SESSION['locale_display_region']
            );
        }
    }

    return;
}
