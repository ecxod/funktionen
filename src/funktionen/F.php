<?php

declare(strict_types=1);

namespace Ecxod\Funktionen;

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
function logg(string|array $t, string $m = NULL, int $l = NULL): bool
{
    if (!empty($m) and file_exists(strval($m))) {
        $m = basename(strval($m));
    }
    if (gettype($t) === 'array') {
        if (isset($_ENV['ERRORLOG'])) {
            error_log_array($t, $m, $l);
            write_mail($t, $m, $l);
        }
        return TRUE;
    } elseif (gettype($t) === 'string') {
        if ($_ENV['ERRORLOG']) {
            error_log(
                (strval($t) ? strval($t) : 'ERROR') .
                    (strval($m) ? " in " . strval($m) : "") .
                    (strval($l) ? " #" . strval($l) : "")
            );
            write_mail($t, $m, $l);
        }
        return TRUE;
    } else {
        return TRUE;
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
{
    logg("array(" . json_encode($arr) . ")", $m, $l);
    return;
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

const mobile_geraete = array("android", "avantgo", "blackberry", "bolt", "boost", "cricket", "docomo", "fone", "hiptop", "mini", "mobi", "palm", "phone", "pie", "tablet", "up\.browser", "up\.link", "webos", "wos", "iphone", "ipad");
function mob_str()
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
    if (empty($myIp)) $myIp = strval(value: $_ENV['MYIP']);
    if (empty($myIp)) $myIp = strval(value: getenv(name: 'MYIP'));
    if (empty($myIp)) {
        die("ERROR: " . __METHOD__);
    } else if (
        !empty($myIp) and
        isset($_SERVER['REMOTE_ADDR']) and !empty($_SERVER['REMOTE_ADDR']) and
        \in_array(needle: $_SERVER['REMOTE_ADDR'], haystack: explode(separator: ",", string: $myIp))
    ) {
        return true;
    } else {
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
 * check if an element is not in an array and add it if it's missing using the in_array function in PHP.
 * 
 * @param mixed $array 
 * @param mixed $element 
 * @return void
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */

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
        $el = array(); // oder besser unset ? to.do.

        foreach ($userAgent as $key => $val) {

            $varr = explode("/", preg_replace('/\((.*)\)/', '', trim($val)));
            $parr = explode(";", preg_replace('/\((.*)\)/', '', trim($val)));
            $barr = explode(" ", preg_replace('/\((.*)\)/', '', trim($val)));

            $el[$key] = array();

            if ($key === 0) {
                $el[$key]['generic'] = $varr[0];
                $el[$key]['htmlver'] = (isset($varr[1]) ? $varr[1] : null);
            }

            if ($key === 1) {
                foreach ($parr as $k => $v) {
                    if ($k == 0)
                        $el[$key]['platform'] = str_replace(['(', ')'], '', trim($parr[$k]));
                    if ($k == 1)
                        $el[$key]['version'] = str_replace(['(', ')'], '', trim($parr[$k]));
                    if ($k >= 2)
                        $el[$key]['val' . $k] = str_replace(['(', ')'], '', trim($parr[$k]));
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
        if (session_status() === PHP_SESSION_ACTIVE) {session_regenerate_id(true);}
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
            $locale_from_http,
            isset($_COOKIE['la']) ? $_COOKIE['la'] : 'en'
        ));
        $_SESSION['locale_display_region'] = strval(locale_get_display_region(
            $locale_from_http,
            isset($_COOKIE['la']) ? $_COOKIE['la'] : 'en'
        ));
    }
}

/**
 * @param string $envfile 
 * @return void
 * @author Christian Eichert <c@zp1.net>
 * @version 1.0.0
 */
{
    $envpath = realpath($_SERVER['DOCUMENT_ROOT'] . '/../');
    if ($envpath) {
        $dotenv = \Dotenv\Dotenv::createImmutable($envpath, $envfile);
        $dotenv->load();
        $dotenv->required('CHARSET')->notEmpty();
        $dotenv->required('CHARSET')->allowedValues(['ISO-8859-1', 'ISO-8859-2', 'UTF8', 'UTF-8']);
        $dotenv->required('LOCALEDIR')->notEmpty();
        $dotenv->required('DSNMAPPING')->notEmpty();
    }
}
