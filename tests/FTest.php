<?php

namespace Ecxod\Tests;

use Ecxod\Funktionen;
use PHPUnit\Framework\TestCase;

class FTest extends TestCase
{
    private $tempFile;
    private $testDir;

    private $apache;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(directory: sys_get_temp_dir(), prefix: 'apache_config_');
        $this->testDir = sys_get_temp_dir() . '/test_apache_conf';
        mkdir($this->testDir);
        $this->apache = new Apache;
    }
    protected function tearDown(): void
    {
        $this->removeDirectory($this->testDir);

        if(file_exists($this->tempFile))
        {
            unlink($this->tempFile);
        }
    }
    /** removint Test Directory and everything inside.
     * @param mixed $dir 
     * @return void 
     */
    private function removeDirectory($dir)
    {
        if(is_dir(filename: $dir))
        {
            $objects = scandir(directory: $dir);
            foreach($objects as $object)
            {
                if($object != "." && $object != "..")
                {
                    if(is_dir(filename: $dir . "/" . $object))
                    {
                        $this->removeDirectory($dir . "/" . $object);
                    }
                    else
                    {
                        unlink(filename: $dir . "/" . $object);
                    }
                }
            }
            rmdir(directory: $dir);
        }
    }
}
