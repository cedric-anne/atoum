<?php

namespace atoum\atoum\tests\units\test\data\providers;

use atoum\atoum;

require_once __DIR__ . '/../../../runner.php';

class dummy
{
    private function __construct()
    {
    }
}

class phpObject extends atoum\test
{
    public function testClass()
    {
        $this
            ->testedClass->implements(atoum\test\data\provider::class);
    }

    public function testGenerate()
    {
        $this
            ->given($this->newTestedInstance)
            ->then
                ->exception(function () {
                    $this->testedInstance->generate();
                })
                    ->isInstanceOf(atoum\exceptions\logic::class)
                    ->hasMessage('Class is undefined')
            ->given($class = 'stdClass')
            ->if($this->testedInstance->setClass($class))
            ->then
                ->object($this->testedInstance->generate())->isInstanceOf($class)
            ->assert('Fail to instanciate an object from a class with mandatory arguments')
            ->given($class = 'splFileObject')
            ->if($this->testedInstance->setClass($class))
            ->then
                ->exception(function () {
                    $this->testedInstance->generate();
                })
                    ->isInstanceOf(atoum\exceptions\runtime::class)
                    ->hasMessage('Could not instanciate an object from ' . $class . ' because ' . $class . '::__construct() has at least one mandatory argument')
            ->assert('Fail to instanciate an object from a class with a private constructor')
            ->given($class = __NAMESPACE__ . '\\dummy')
            ->if($this->testedInstance->setClass($class))
            ->then
                ->exception(function () {
                    $this->testedInstance->generate();
                })
                    ->isInstanceOf(atoum\exceptions\runtime::class)
                    ->hasMessage('Could not instanciate an object from ' . $class . ' because ' . $class . '::__construct() is private')
        ;
    }

    public function testGetSetClass()
    {
        $this
            ->given($this->newTestedInstance)
            ->then
                ->variable($this->testedInstance->getClass())->isNull
                ->exception(function () {
                    $this->testedInstance->setClass(uniqid());
                })
                    ->isInstanceOf(atoum\exceptions\logic\invalidArgument::class)
                    ->hasMessage('Argument must be a class name')
            ->given($class = 'stdClass')
            ->then
                ->object($this->testedInstance->setClass($class))->istestedInstance
                ->string($this->testedInstance->getClass())->isEqualTo($class)
        ;
    }
}
