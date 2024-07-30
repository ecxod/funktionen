<?php

declare(strict_types=1);

namespace Ecxod\Funktionen;

/** Fügt (nur mir!) HTML Kommentare in den code ein.
 * Idealerweise ist Argument 1 die Methode. Das erscheint dann als KLASE::METHODE
 * @param string $m  
 * @return string 
 */
function m(string $m): string
{
    return isMe() ? PHP_EOL . '<!--' . strval($m) . '-->' . PHP_EOL : '';
}

/**
 * isMobile detects mobile devices
 * 
 * @return bool
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


/** Function is checking if a library is loaded.
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

