<?php

class MultipleSingletonTestClass
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

class MultipleSingletonWorksTest extends PHPUnit_Framework_TestCase
{

    public function testMultipleSingletonWorks()
    {
        $objOne = MultipleSingletonTestClass::getInstance('version1');
        $objTwo = MultipleSingletonTestClass::getInstance('version2');
        $objThree = MultipleSingletonTestClass::getInstance('version2');

        $objOne->setTestingValue(123);
        $objTwo->setTestingValue(123);
        $this->assertEquals($objOne->getTestingValue(), $objTwo->getTestingValue());

        $objOne->setTestingValue(456);
        $objTwo->setTestingValue(789);
        $this->assertNotEquals($objOne->getTestingValue(), $objTwo->getTestingValue());

        $this->assertEquals($objThree->getTestingValue(), $objTwo->getTestingValue());
    }

}