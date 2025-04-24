<?php

declare(strict_types=1);

namespace Ecxod\Funktionen;

use Erusev\Parsedown;
use Dotenv\Dotenv;
use Throwable;
use function \realpath;

class mydotenv
{
    protected string $envfile;
    protected string $envfileabsolute;
    protected string $envpath;
    protected string $namespace;

    protected string $document_root;

    public function __construct(string $envfile = '.env', string $namespace = null)
    {
        $this->envfile   = $envfile;
        $this->namespace = $namespace;

        // document_root and envpath 
        if(!empty($_SERVER['DOCUMENT_ROOT']))
        {
            // $pathinf_dirname = pathinfo(path: $_SERVER['DOCUMENT_ROOT'],flags: PATHINFO_DIRNAME);
            if(pathinfo(path: $_SERVER['DOCUMENT_ROOT'], flags: PATHINFO_DIRNAME) === $_SERVER['DOCUMENT_ROOT'])
            {
                $this->document_root = $_SERVER['DOCUMENT_ROOT'];
                $this->envpath       = strval(value: realpath(path: "{$this->document_root}/../"));
            }
        }
        else
        {
            die("Please dset the DOCUMENT_ROOT");
        }

        //envfileabsolute
        if(realpath(path: "{$this->envpath}$envfile"))
        {
            $this->envfileabsolute = "{$this->envpath}$envfile";
        }
        else
        {
            die("Enviroment File Parth does not exist!");
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
    public function dotEnv(): void
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








}
