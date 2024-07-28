<?php

declare(strict_types=1);

namespace \Ecxod\Funktionen {

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
}
