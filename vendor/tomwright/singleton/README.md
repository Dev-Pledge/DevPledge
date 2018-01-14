# Singleton

[![Build Status](https://travis-ci.org/TomWright/Singleton.svg?branch=master)](https://travis-ci.org/TomWright/Singleton)
[![Total Downloads](https://poser.pugx.org/tomwright/singleton/d/total.svg)](https://packagist.org/packages/tomwright/singleton)
[![Latest Stable Version](https://poser.pugx.org/tomwright/singleton/v/stable.svg)](https://packagist.org/packages/tomwright/singleton)
[![Latest Unstable Version](https://poser.pugx.org/tomwright/singleton/v/unstable.svg)](https://packagist.org/packages/tomwright/singleton)
[![License](https://poser.pugx.org/tomwright/singleton/license.svg)](https://packagist.org/packages/tomwright/singleton)

Singleton is a simple package that can be used to implement the Singleton Design Pattern.

## Installation

```
composer require tomwright/singleton
```

## Basic Usage

```php
use TomWright\Singleton\SingletonTrait

class DatabaseConnection
{
    use SingletonTrait;
    
    public $connectionId;
    
    public function __construct()
    {
        $this->connectionId = rand();
    }
}

$db = DatabaseConnection::getInstance();
echo $db->connectionId; // 1231

$db = DatabaseConnection::getInstance();
echo $db->connectionId; // 1231

$db = DatabaseConnection::getInstance('another_connection');
echo $db->connectionId; // 1543

$db = DatabaseConnection::getInstance('another_connection');
echo $db->connectionId; // 1543

// The argument passed in here is defaulted to "default"
$db = DatabaseConnection::getInstance('default');
echo $db->connectionId; // 1231
```

## Setting Your Own Instances

You can specifically set an instance yourself if required.

```php
$db = new DatabaseConnection();
echo $db->connectionId; // 9373
DatabaseConnection::setInstance('some_connection', $db);

$db = DatabaseConnection::getInstance('some_connection');
echo $db->connectionId; // 9373
```

## Additional Functionality

There are some more methods you can use to customise your experience with this package.

### static::createSingleton($instanceId)

If this method exists in your class, it will be used rather than the default `__construct()` method in your class.

Please note that it must `return` the new instance.

```php
use TomWright\Singleton\SingletonTrait

class DatabaseConnection
{
    use SingletonTrait;
    
    public $connectionId;
    
    public function __construct()
    {
        $this->connectionId = rand();
    }
    
    public static function createSingleton($instanceId)
    {
        $instance = new static();
        if ($instanceId == 'my-specific-connection') {
            $instance->connectionId = 1;
        }
        return $instance;
    }
}

$db = DatabaseConnection::getInstance();
echo $db->connectionId; // 2313

$db = DatabaseConnection::getInstance('some-general-connection');
echo $db->connectionId; // 5432

$db = DatabaseConnection::getInstance('my-specific-connection');
echo $db->connectionId; // 1
```

### initSingleton($instanceId)

If this method exists in your class, it will be executed once, immediately after the instance is created.

```php
use TomWright\Singleton\SingletonTrait

class DatabaseConnection
{
    use SingletonTrait;
    
    public $connectionId;
    
    public function __construct()
    {
        $this->connectionId = rand();
    }
    
    public static function initSingleton($instanceId)
    {
        if ($instanceId == 'my-specific-connection') {
            $this->connectionId = 1;
        }
    }
}

$db = DatabaseConnection::getInstance();
echo $db->connectionId; // 2313

$db = DatabaseConnection::getInstance('some-general-connection');
echo $db->connectionId; // 5432

$db = DatabaseConnection::getInstance('my-specific-connection');
echo $db->connectionId; // 1
```
