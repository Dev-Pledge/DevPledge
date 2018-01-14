<?php

class CreateSingletonTestClassOne
{
    use TomWright\Singleton\SingletonTrait;

    protected $testingValue = null;

    public function __construct($testVal = null)
    {
        $this->setTestingValue($testVal);
    }


    /**
     * @return mixed
     */
    public function getTestingValue()
    {
        return $this->testingValue;
    }


    /**
     * @param mixed $testingValue
     */
    public function setTestingValue($testingValue)
    {
        $this->testingValue = $testingValue;
    }
}

class CreateSingletonTestClassTwo extends CreateSingletonTestClassOne
{
    
    public static function createSingleton($instanceId)
    {
        return new static($instanceId);
    }
    
}

class CreateSingletonWorksTest extends PHPUnit_Framework_TestCase
{

    public function testCreateSingletonIsUsed()
    {
        $objTwo = CreateSingletonTestClassTwo::getInstance('version2');

        $this->assertEquals($objTwo->getTestingValue(), 'version2');
    }


    public function testCreateSingletonIsNotUsed()
    {
        $objOne = CreateSingletonTestClassOne::getInstance('version1');

        $this->assertNull($objOne->getTestingValue());
    }
    
}