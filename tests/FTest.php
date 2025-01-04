<?php

namespace Ecxod\Tests;

use Ecxod\Funktionen;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;

class FTest extends TestCase
{
    private $tempFile;
    private $testDir;


    protected function setUp(): void
    {
        $this->tempFile = tempnam(directory: sys_get_temp_dir(), prefix: 'functionen_');
        $this->testDir = sys_get_temp_dir() . '/functionen_';
        mkdir($this->testDir);
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


    /** 
     * @return void
     * @doesNotPerformAssertions
     */
    public function testLogg()
    {
        $this->expectNotToPerformAssertions();
    }



}

