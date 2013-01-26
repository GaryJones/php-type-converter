# PHP Type Converter

A class that handles the detection and conversion of certain resource formats / content types into other formats.

The current formats are supported: XML, JSON, Array, Object, Serialized

## Requirements

* PHP 5.3.0+
* [SimpleXML](http://php.net/manual/book.simplexml.php) 

## Documentation

The class is pretty straight forward. If you want to convert something to another format, use the "to" methods.

~~~php
$object = TypeConverter::toObject($resource);
$array = TypeConverter::toArray($resource);
$json = TypeConverter::toJson($resource);
$xml = TypeConverter::toXml($resource);
$ser = TypeConverter::toSerialize($resource);
~~~

If you want to detect what resource type it is, use the "is" methods.
If you use the "to" methods above, it does automatic "is" detection.

~~~php
TypeConverter::isObject($resource);
TypeConverter::isArray($resource);
TypeConverter::isJson($resource);
TypeConverter::isXml($resource);
TypeConverter::isSerialized($resource);
~~~

If you want a string representation of what a resource is, use the default is() method.

~~~php
$resource = array();
TypeConverter::is($resource); // array
~~~

You can convert an XML document into an array (must have SimpleXML).

~~~php
$array = TypeConverter::xmlToArray($xml, TypeConverter::XML_MERGE);
~~~

When using xmlToArray(), you can define the format in which the node attributes and values are presented. The following constants are available.

* `XML_NONE`  - Disregard XML attributes and only return the value.
* `XML_MERGE` - Merge attributes and the value into a single dimension; the values key will be "value".
* `XML_GROUP` - Group the attributes into a key of "attributes" and the value into a key of "value".
* `XML_OVERWRITE` - Attributes will only be returned.

## Status
[![Build Status](https://travis-ci.org/GaryJones/php-type-converter.png)](https://travis-ci.org/GaryJones/php-type-converter)

## Changelog
Originally forked from Type Converter v2.0.0 https://github.com/milesj/php-type_converter by [Miles Johnson](https://twitter.com/gearvOsh).

This fork adds support for conversion of nested combinations of arrays / objects and some unit tests.