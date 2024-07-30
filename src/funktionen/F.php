<?php

declare(strict_types=1);

namespace Ecxod\Funktionen;

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

