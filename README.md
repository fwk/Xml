# Fwk\Xml (Parsing and Validation for XML)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fwk/Xml/badges/quality-score.png?s=1f3757e2f99082fc035773f75b7b402e21c76b53)](https://scrutinizer-ci.com/g/fwk/Xml/)
[![Build Status](https://secure.travis-ci.org/fwk/Xml.png?branch=master)](http://travis-ci.org/fwk/Xml)
[![Code Coverage](https://scrutinizer-ci.com/g/fwk/Xml/badges/coverage.png?s=d253c01ad8cbfc4a83be2db5e49220e7f23761b4)](https://scrutinizer-ci.com/g/fwk/Xml/)
[![Latest Stable Version](https://poser.pugx.org/fwk/xml/v/stable.png)](https://packagist.org/packages/fwk/xml) 
[![Total Downloads](https://poser.pugx.org/fwk/xml/downloads.png)](https://packagist.org/packages/fwk/xml) 
[![Latest Unstable Version](https://poser.pugx.org/fwk/xml/v/unstable.png)](https://packagist.org/packages/fwk/xml) 
[![License](https://poser.pugx.org/fwk/xml/license.png)](https://packagist.org/packages/fwk/xml)

PHP utility to Parse and Validate XML files.

## Installation

Via [Composer](http://getcomposer.org):

```
{
    "require": {
        "fwk/xml": "dev-master",
    }
}
```

If you don't use Composer, you can still [download](https://github.com/fwk/Xml/zipball/master) this repository and add it
to your ```include_path``` [PSR-0 compatible](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)

## Documentation

### Parse a XML file

The classes ```Fwk\Xml\Map``` and ```Fwk\Xml\Path``` helps you define a parsing map to transform a XML file to a PHP array. 
Example XML file:

``` xml
<?xml version="1.0" encoding="UTF-8"?>
<test>
    <properties>
        <property name="test">test_value</property>
        <property name="test2">test_value2</property>
    </properties>
    
    <description>test description</description>
    
    <test-default />
</test>
```

We want to transform (i.e. parse) this file to obtain its data so we just have to create a Map:

``` php
use Fwk\Xml\Map;
use Fwk\Xml\Path;

$map = new Map();

// this path will fetch the <description /> tag
$map->add(Path::factory('/test/description', 'desc'));

// this path will fetch all the properties (loop) within the <properties /> tag
$map->add(
    Path::factory('/test/properties/property', 'props')
    ->loop(true)
    ->attribute('name')
    ->value('value')
);
```
Now we can execute the Map and exploit the results:

``` php
$results = $map->execute(new XmlFile('test.xml'));

var_dump($results);
// [
//      description: "test description", 
//      properties: [
//            (
//                name: "test", 
//                value: "test_value"
//            ),
//            (
//                name: "test2", 
//                value: "test_value2"
//            )
//      ]
// ]
``` 

A more complex exampl can be found [here](https://github.com/fwk/Xml/blob/master/Maps/Rss.php) (RSS feed)

### Loop with a keyed attribute

Sometimes, XML elements we loop throught are identified by an attribute (ex: ```<item id="42" />```).
We can tell our path to use this attribute as the array key!

``` php
// the loop() key attribute is an Xpath so any valid Xpath is allowed 
// (@ = attribute of the current element)
$map->add(
    Path::factory('/test/properties/property', 'props')
    ->loop(true, $key = '@name')
    ->value('value')
);

/* [...] */

var_dump($results);
// [
//      description: "test description", 
//      properties[test]: "test_value",
//      properties[test2]: "test_value2"
// ]
``` 

## Contributions / Community

- Issues on Github: https://github.com/fwk/Xml/issues
- Follow *Fwk* on Twitter: [@phpfwk](https://twitter.com/phpfwk)

## Legal 

Fwk is licensed under the 3-clauses BSD license. Please read LICENSE for full details.
