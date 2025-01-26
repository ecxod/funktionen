<?php

declare(strict_types=1);
namespace Ecxod\Funktionen;

class DEBUGLOG
{
    protected bool   $env_debug_opt;
    protected string $env_debug_log;
    protected string $php_get_ini_error_log;
    protected string $server_error_log;



    /**
     */
    public function __construct()
    {
        $this->setEnvDebugOpt( env_debug_opt: $_ENV['DEBUGOPT'] ?? getenv(name: 'DEBUGOPT') ?? false);
        $this->setEnvDebugLog( env_debug_log: $_ENV['DEBUGLOG'] ?? getenv(name: 'DEBUGLOG') ?? "" );
        $this->setServerErrorLog(server_error_log: $_SERVER['ERROR_LOG'] ?? getenv(name: 'ERROR_LOG') ?? "");
        $this->setPhpIniErrorLog(php_get_ini_error_log: ini_get(option: 'error_log'));


    }

    protected function id0100_function()
    {
        if(
            $this->getEnvDebugOpt() === true
        )
        {
            // if true
            $this->id0200_set_inis();
        }
        elseif($this->getEnvDebugOpt() === false)
        {
            // if false 
            return false;
        }
        else
        {
            // if empty
            die("Please set DEBUGOPT.");
        }
    }

    protected function id0200_set_inis(): void
    {
        ini_set(option: 'display_errors', value: '0');
        ini_set(option: 'display_starup_errors', value: '1');
        ini_set(option: 'log_errors', value: '1');
        $this->id0300_function();
        return;
    }

    protected function id0300_function()
    {
        if(
            // empty or not empty or syslog
            $this->getEnvDebugLog() === strtolower('syslog') or empty($this->getEnvDebugLog())
        )
        {
            // id0400 
            $this->id0400_function();
        }else{
            // id0310
            $this->id0310_check_error_log_exists_writable();
        }
    }



    protected function id0400_function()
    {
        if(empty($this->getEnvDebugLog())){
            //id0600
        }else{
            //id0500
        }
    }







    /**
     * Prüfen, ob DEBUGOPT aktiviert ist (als bool oder String "true").
     * // id0100
     * @return bool
     */
    // protected function id0100_check_env_debugopt(): bool
    // {
    //     return (isset($_ENV['DEBUGOPT']) && ($_ENV['DEBUGOPT'] === true || $_ENV['DEBUGOPT'] === 'true')) ||
    //         (getenv(name: 'DEBUGOPT') === 'true');
    // }



    /**
     * Prüfen, ob ERROR_LOG vorhanden ist und ein String ist.
     * // id0600
     * @return bool
     */
    protected function check_server_error_log(): bool
    {
        return isset($_SERVER['ERROR_LOG']) && is_string(value: $_SERVER['ERROR_LOG']);
    }

    /** 
     * 
     * @return bool 
     */
    protected function id0310_check_error_log_exists_writable(): bool
    {
        // id0310
        return file_exists(filename: $this->getPhpIniErrorLog()) &&
            is_writable(filename: $this->getPhpIniErrorLog());

    }

























    /**
     * @return string
     */
    public function getPhpIniErrorLog(): string
    {
        return $this->php_get_ini_error_log;
    }

    /**
     * @param string $php_get_ini_error_log 
     * @return self
     */
    public function setPhpIniErrorLog(string $php_get_ini_error_log): self
    {
        $this->php_get_ini_error_log = $php_get_ini_error_log;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnvDebugOpt(): bool
    {
        return $this->env_debug_opt;
    }

    /**
     * @param bool $env_debug_opt 
     * @return self
     */
    public function setEnvDebugOpt(bool $env_debug_opt): self
    {
        $this->env_debug_opt = $env_debug_opt;
        return $this;
    }



    /**
     * @return string
     */
    public function getServerErrorLog(): string
    {
        return $this->server_error_log;
    }

    /**
     * @param string $server_error_log 
     * @return self
     */
    public function setServerErrorLog(string $server_error_log): self
    {
        $this->server_error_log = $server_error_log;
        return $this;
    }

    /**
     * @return string
     */
    public function getEnvDebugLog(): string
    {
        return $this->env_debug_log;
    }

    /**
     * @param string $env_debug_log 
     * @return self
     */
    public function setEnvDebugLog(string $env_debug_log): self
    {
        $this->env_debug_log = $env_debug_log;
        return $this;
    }
}

