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
        $this->setPhpIniErrorLog(ini_get(option: 'error_log'));


    }

    protected function machen()
    {
        if(
            // id0100
            $this->check_env_debugopt() === true
        )
        {

            // id0200
            $this->set_inis();

            // id0300
            if( $this->getPhpIniErrorLog() and $this->getPhpIniErrorLog() !== "syslog" )
            {
                // id0400

                if( 
                    // id0500
                    $this->check_env_debugLog() === true
                )
                {
                    $_ENV['DEBUGLOG'] = ini_get(option: 'error_log');
                    $this->setEnvDebugOpt(env_debug_opt: $_ENV['DEBUGOPT']);
                }else{

                    // id0600
                    $this->check_server_error_log();

                }

            }
            else
            {
                // id0310
                if($this->check_error_log_exists_writable())
                {
                    // id0990

                }else{
                    // id0600
                }
            }


            return true;

        }
        else
        {
            // id0210 + id0220
            return false;
        }
    }


    /**
     * Prüfen, ob DEBUGOPT aktiviert ist (als bool oder String "true").
     * // id0100
     * @return bool
     */
    protected function check_env_debugopt(): bool
    {
        return (isset($_ENV['DEBUGOPT']) && ($_ENV['DEBUGOPT'] === true || $_ENV['DEBUGOPT'] === 'true')) ||
            (getenv(name: 'DEBUGOPT') === 'true');
    }

    /**
     * Prüfen, ob DEBUGLOG vorhanden ist und ein String ist.
     * // id0400
     * @return bool
     */
    protected function check_env_debugLog(): bool
    {
        return (isset($_ENV['DEBUGLOG']) && is_string($_ENV['DEBUGLOG'])) ||
            (getenv(name: 'DEBUGLOG') !== false && is_string(value: getenv('DEBUGLOG')));
    }

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
    protected function check_error_log_exists_writable(): bool
    {
        // id0310
        return file_exists(filename: $this->getPhpIniErrorLog()) && 
            is_writable(filename: $this->getPhpIniErrorLog());

    }




















    protected function set_inis(): void
    {
        ini_set(option: 'display_errors', value: '0');
        ini_set(option: 'display_starup_errors', value: '1');
        ini_set(option: 'log_errors', value: '1');
        return;
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

