<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/type-converter
 */

// Turn on errors
error_reporting(E_ALL);

function debug($var) {
	echo '<pre>' . print_r($var, true) . '</pre>';
}

function dump($key, $value) {
	echo $key . ' (' . ($value ? 'true' : 'false') . ') ';
}

// Include class
include_once '../TypeConverter.php';

use \mjohnson\utility\TypeConverter;

// Create variables
$array	= array('is' => 'array');
$json	= json_encode(array('is' => 'json'));
$ser	= serialize(array('is' => 'serialize'));
$xml	= '<?xml version="1.0" encoding="utf-8"?><root><is>xml</is></root>';
$object	= new \stdClass();
?>

<!DOCTYPE html>
<head>
	<title>TypeConverter</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>

<?php
$object->is = 'object';

// Determine the type
$typeConverter = new TypeConverter;
debug('$typeConverter->is()');
debug($typeConverter->is($array));
debug($typeConverter->is($object));
debug($typeConverter->is($json));
debug($typeConverter->is($ser));
debug($typeConverter->is($xml));

// Validate against all types
foreach (array('isArray', 'isObject', 'isJson', 'isSerialized', 'isXml') as $method) {
	debug('$typeConverter->'. $method .'()');

	dump('array', $typeConverter->$method($array));
	dump('object', $typeConverter->$method($object));
	dump('json', $typeConverter->$method($json));
	dump('serialize', $typeConverter->$method($ser));
	dump('xml', $typeConverter->$method($xml));
}

// Convert all the types
foreach (array('toArray', 'toObject', 'toJson', 'toSerialize', 'toXml') as $method) {
	debug('$typeConverter->'. $method .'()');

	if ($method == 'toXml') {
		debug(htmlentities($typeConverter->toXml($array)));
		debug(htmlentities($typeConverter->toXml($object)));
		debug(htmlentities($typeConverter->toXml($json)));
		debug(htmlentities($typeConverter->toXml($ser)));
		debug(htmlentities($typeConverter->toXml($xml)));
	} else {
		debug($typeConverter->$method($array));
		debug($typeConverter->$method($object));
		debug($typeConverter->$method($json));
		debug($typeConverter->$method($ser));
		debug($typeConverter->$method($xml));
	}
}

// Convert a complicated XML file to an array
$xml = file_get_contents('test.xml');

foreach (array('none', 'merge', 'group', 'overwrite') as $format) {
	debug('$typeConverter->xmlToArray('. $format .')');

	switch ($format) {
		case 'none':
			debug($typeConverter->xmlToArray($xml, TypeConverter::XML_NONE));
		break;
		case 'merge':
			debug($typeConverter->xmlToArray($xml, TypeConverter::XML_MERGE));
		break;
		case 'group':
			debug($typeConverter->xmlToArray($xml, TypeConverter::XML_GROUP));
		break;
		case 'overwrite':
			debug($typeConverter->xmlToArray($xml, TypeConverter::XML_OVERWRITE));
		break;
	}
}

// Convert UTF-8
$json = array('j\'étais', 'joué', '中文', 'éáíúűóüöäÍÓ');

debug($json);
debug($typeConverter->utf8Encode($json));
debug($typeConverter->utf8Decode($typeConverter->utf8Encode($json))); ?>

</body>
</html>