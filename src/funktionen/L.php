<?php
declare(strict_types=1);
namespace Ecxod\I18n;


class L
{

    /**
     * la gibt die ersten beiden Buchstaben des Coockie"la" xx_XX aus, oder wenn nicht existier dann "de"
     *
     * @return string
     */
    public static function la(): string
    {
        $la = "en";
        $cooLa = "";
        $cooLa = (!empty($_COOKIE['la'])) ? strval(value: $_COOKIE['la']) : strval(value: $la);

        // wenn die Länge nicht stimmt
        $la = (strlen(string: $cooLa) != 2) ? 'en' : $cooLa;

        return strval(value: $la);
    }


    /**
     * Summary of lang_old
     * this is not used right now
     * 
     * @return string
     */
    public static function lang_old(): string
    {
        $lang = "en_US";
        $la = (empty($_COOKIE['la']) ? 'en' : $_COOKIE['la']);
        $_SESSION['browserLang'] = $la; // en

        foreach(ISO6391::LANG as $v)
        {
            if($v[0] === $la)
            {
                $_SESSION['browserLANG'] = strtoupper(string: strval(value: $v[0])); // en
                $_SESSION['browserLOCALE'] = strval(value: $v[1]); // en_US
                if(!empty($_SESSION['browserLOCALE']))
                {
                    $lang = strval($v[1]);
                }
                $_SESSION['browserLanguage'] = strval(value: $v[2]); // Englesisch
            }
        }
        return $lang;
    }


    /** 
     * Thisfunction will execute locale -a command on your server and get the output into an array.
     * It then loops through the array and collects the locales that start with your desired language.
     * If there's only one, it returns that one.
     * If there's more than one, it tries to find a locale where the country part is the same as the language part (e.g., 'en_EN').
     * If it cannot find such a locale, it returns the first one it finds.
     * If there is no locales are found that match the desired language. the function will return 'en_US'.
     * 
     * example :  getLocale('en');
     * 
     * @param mixed $language 
     * @return mixed 
     */
    public static function lang(): mixed
    {

        if(empty($_COOKIE['la']))
        {
            $la = 'en';
            $_SESSION['browserLang'] = 'en';
            $_SESSION['browserLOCALE'] = 'en_US';
            return 'en_US';
        }
        else
        {
            $la = $_COOKIE['la'];
            $_SESSION['browserLang'] = $la;
        }


        // output is a array of locales that are installed on the machine
        $output = array();
        exec('locale -a', $output);

        $locales = array();
        // we filter in $locales[] all locales that start with the desired language "de_..."
        foreach($output as $locale)
        {
            if(strpos(haystack: $locale, needle: "{$la}_") === 0)
            {
                $locales[] = $locale;
            }
        }

        // if there is only one $locales[], we take it
        if(count($locales) == 1)
        {
            $_SESSION['browserLOCALE'] = strval(value: $locales[0]);
            return $locales[0];
        }
        // if there are more we take the the first that has the format xx_XX
        foreach($locales as $locale)
        {
            $parts = explode(separator: '_', string: $locale);
            if(count(value: $parts) == 2 && ($parts[0] == $parts[1] || strcasecmp(string1: $parts[0], string2: $parts[1]) == 0))
            {
                $_SESSION['browserLOCALE'] = strval($locale);
                return $locale;
            }
        }

        if(array_key_exists(0, array: $locales) and !empty($locales[0]))
        {
            $_SESSION['browserLOCALE'] = strval(value: $locales[0]);
        }
        else
        {
            $_SESSION['browserLOCALE'] = "en_US";
        }

        return $locales[0] ?? "en_US";
    }










    public static function httpAcceptLanguage($localedir): string
    {
        $lang = self::lang_getfrombrowser(allowed_languages: self::readLocaleDir2Array(localeDir: $localedir), default_language: 'en', lang_variable: null, strict_mode: false);

        /*
         *  SPRACHEN  HTTP_ACCEPT_LANGUAGE
         */
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {

            $_SESSION['browserLang'] = $lang; // en

            if(ISO6391::LANG !== null && is_array(ISO6391::LANG))
            {
                foreach(ISO6391::LANG as $v)
                {
                    if($v[0] === $lang)
                    {
                        $_SESSION['browserLANG'] = strtoupper(string: strval(value: $v[0])); // en
                        $_SESSION['browserLOCALE'] = strval(value: $v[1]); // en_US
                        $_SESSION['browserLanguage'] = strval(value: $v[2]); // Englesisch
                    }
                }
            }
        }
        return $lang;
    }




    /**
     * Browsersprache ermitteln
     * 
     * @param array   allowed languages
     * @param string  default language
     * @param mixed   requested language (null = server default)
     * @param bool    strict_mode (default true)
     * 
     * @return string result language
     */
    public static function lang_getfrombrowser(array $allowed_languages, string $default_language, string $lang_variable = null, bool $strict_mode = true): string
    {
        // $_SERVER['HTTP_ACCEPT_LANGUAGE'] verwenden, wenn keine Sprachvariable mitgegeben wurde
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $lang_variable === null)
        {
            $lang_variable = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }
        else
        {
            $lang_variable = "";
        }

        // wurde irgendwelche Information mitgeschickt?
        if(empty($lang_variable))
        {
            // Nein? => Standardsprache zurückgeben
            return $default_language;
        }

        // Den Header auftrennen
        $accepted_languages = preg_split(pattern: '/,\s*/', subject: $lang_variable);

        // Die Standardwerte einstellen
        $current_lang = $default_language;
        $current_q = 0;

        // Nun alle mitgegebenen Sprachen abarbeiten
        foreach($accepted_languages as $accepted_language)
        {
            // Alle Infos über diese Sprache rausholen
            $res = preg_match(
                pattern: '/^([a-z]{1,8}(?:-[a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i',
                subject: $accepted_language,
                matches: $matches
            );

            // war die Syntax gültig?
            if(!$res)
            {
                // Nein? Dann ignorieren
                continue;
            }

            // Sprachcode holen und dann sofort in die Einzelteile trennen
            $lang_code = explode(separator: '-', string: $matches[1]);

            // Wurde eine Qualität mitgegeben?
            if(isset($matches[2]))
            {
                // die Qualität benutzen
                $lang_quality = (float) $matches[2];
            }
            else
            {
                // Kompabilitätsmodus: Qualität 1 annehmen
                $lang_quality = 1.0;
            }

            // Bis der Sprachcode leer ist...
            while(count($lang_code))
            {
                // mal sehen, ob der Sprachcode angeboten wird
                if(in_array(needle: strtolower(string: join(separator: '-', array: $lang_code)), haystack: $allowed_languages))
                {
                    // Qualität anschauen
                    if($lang_quality > $current_q)
                    {
                        // diese Sprache verwenden
                        $current_lang = strtolower(string: join(separator: '-', array: $lang_code));
                        $current_q = $lang_quality;
                        // Hier die innere while-Schleife verlassen
                        break;
                    }
                }
                // Wenn wir im strengen Modus sind, die Sprache nicht versuchen zu minimalisieren
                if($strict_mode)
                {
                    // innere While-Schleife aufbrechen
                    break;
                }
                // den rechtesten Teil des Sprachcodes abschneiden
                array_pop($lang_code);
            }
        }

        // suchen den langen namen auf englisch 
        foreach(ISO6391::LANG as $l)
        {
            if($l[0] === $current_lang)
            {
                $_SESSION['browserLang'] = $l[2];
            }
        }
        $_SESSION['browserLang'] = \Locale::getDisplayName(locale: $current_lang, displayLocale: 'en');


        // die gefundene Sprache zurückgeben
        return $current_lang;

    }






    public const LANGARRAY = [];

    /**
     * Method readLocaleDir2Array  
     * reads the locale folder  
     * ex: array("ar","as","az","bg","br","brx","ca",...)
     * 
     * @param string $localedir localedir surl [default:null]
     * @param string $LOCALEDIRENV name of the dotenv variable that stores the localedir surl
     *
     * @return array
     */
    public static function readLocaleDir2Array(string $localeDir): array
    {

        $subDirArray = array();

        if(is_dir($localeDir))
        {

            // einlesen mit DirectoryIterator
            $dir = new \DirectoryIterator($localeDir);
            foreach($dir as $fileinfo)
            {
                if($fileinfo->isDir() and !$fileinfo->isDot())
                {
                    array_push($subDirArray, $fileinfo->getFilename());
                }
            }

            // wir sortieren die array neu
            $subDirArray = array_values($subDirArray);
            // doubletten ud sortieren
            $subDirArray = array_unique($subDirArray, SORT_STRING);
            // sortieren
            sort($subDirArray);

            return $subDirArray;
        }
        else
        {
            return [];
        }

    }





    public static function get_web_page(string $url, $curl_data)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            // return web page
            CURLOPT_HEADER         => false,
            // don't return headers
            CURLOPT_FOLLOWLOCATION => true,
            // follow redirects
            CURLOPT_ENCODING       => "",
            // handle all encodings
            CURLOPT_USERAGENT      => "elvis",
            // who am i
            CURLOPT_AUTOREFERER    => true,
            // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,
            // timeout on connect
            CURLOPT_TIMEOUT        => 120,
            // timeout on response
            CURLOPT_MAXREDIRS      => 10,
            // stop after 10 redirects
            CURLOPT_POST           => 1,
            // i am sending post data
            CURLOPT_POSTFIELDS     => $curl_data,
            // this are my post vars
            CURLOPT_SSL_VERIFYHOST => 0,
            // don't verify ssl
            CURLOPT_SSL_VERIFYPEER => false,
            //
            CURLOPT_VERBOSE        => 1 //
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        unset($_SESSION['trans']);
        $_SESSION['trans'] = array();
        $_SESSION['trans']['errno'] = $err;
        $_SESSION['trans']['errmsg'] = $errmsg;
        $_SESSION['trans']['content'] = $content;

        return $content;
    }






}
