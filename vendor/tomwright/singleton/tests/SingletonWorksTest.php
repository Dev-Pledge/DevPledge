<?php

class SingletonTestClass
{
    use TomWright\Singleton\SingletonTrait;

    protected $testingValue = null;


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

class SingletonWorksTest extends PHPUnit_Framework_TestCase
{

    public function testSingletonWorks()
    {
        $obj = SingletonTestClass::getInstance();
        $obj->setTestingValue(123);
        $this->assertEquals(123, $obj->getTestingValue());

        $objTwo = SingletonTestClass::getInstance();
        $this->assertEquals(123, $objTwo->getTestingValue());
        $objTwo->setTestingValue(456);
        $this->assertEquals(456, $objTwo->getTestingValue());

        $this->assertEquals($obj->getTestingValue(), $objTwo->getTestingValue());
    }

}