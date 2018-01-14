<?php

class InitSingletonTestClass
{
    use TomWright\Singleton\SingletonTrait;

    protected $testingValue = null;

    public function initSingleton($instanceId)
    {
        $this->setTestingValue($instanceId);
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

class InitSingletonWorksTest extends PHPUnit_Framework_TestCase
{

    public function testCreateSingletonIsUsed()
    {
        $obj = InitSingletonTestClass::getInstance('version2');

        $this->assertEquals($obj->getTestingValue(), 'version2');
    }

}