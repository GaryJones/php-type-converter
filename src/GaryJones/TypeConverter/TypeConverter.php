<?php
/**
 * PHP Type Converter
 *
 * @package   GaryJones\TypeConverter
 * @copyright Copyright 2006-2012, Miles Johnson - http://milesj.me
 *            Additions, Copyright 2013 Gary Jones
 * @author    Miles Johnson
 * @author    Gary Jones <gary@garyjones.co.uk>
 * @license	  http://opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/GaryJones/php-type-converter
 */

namespace GaryJones\TypeConverter;

/**
 * Handles the detection and conversion of certain resource formats / content
 * types into other formats.
 *
 * The current formats are supported: XML, JSON, Array, Object, Serialized.
 *
 * @package GaryJones\TypeConverter
 * @author  Miles Johnson
 * @author  Gary Jones <gary@garyjones.co.uk>
 */
class TypeConverter
{
    /**
     * Disregard XML attributes and only return the value.
     *
     * @type int
     */
    const XML_NONE = 0;

    /**
     * Merge attributes and the value into a single dimension; the values key
     * will be "value".
     *
     * @type int
     */
    const XML_MERGE = 1;

    /**
     * Group the attributes into a key "attributes" and the value into a key of
     * "value".
     *
     * @type int
     */
    const XML_GROUP = 2;

    /**
     * Attributes will only be returned.
     *
     * @type int
     */
    const XML_OVERWRITE = 3;

    /**
     * Tracks the recursion level of buildXML();
     *
     * @type int
     */
    private $xml_depth = 0;

    /**
     * Returns a string for the detected type.
     *
     * @param mixed $data
     *
     * @return string
     */
    public function is($data)
    {
        if ($this->isArray($data)) {
            return 'array';
        } elseif ($this->isObject($data)) {
            return 'object';
        } elseif ($this->isJson($data)) {
            return 'json';
        } elseif ($this->isSerialized($data)) {
            return 'serialized';
        } elseif ($this->isXml($data)) {
            return 'xml';
        }

        return 'other';
    }

    /**
     * Check to see if data passed is an array.
     *
     * @param mixed $data
     *
     * @return boolean
     */
    public function isArray($data)
    {
        return is_array($data);
    }

    /**
     * Check to see if data passed is a JSON object.
     *
     * @param mixed $data
     *
     * @return boolean
     */
    public function isJson($data)
    {
        return (@json_decode($data) !== null);
    }

    /**
     * Check to see if data passed is an object.
     *
     * @param mixed $data
     *
     * @return boolean
     */
    public function isObject($data)
    {
        return is_object($data);
    }

    /**
     * Check to see if data passed has been serialized.
     *
     * @param mixed $data
     *
     * @return boolean
     */
    public function isSerialized($data)
    {
        $ser = @unserialize($data);

        return $ser !== false;
    }

    /**
     * Check to see if data passed is an XML document.
     *
     * @param mixed $data
     *
     * @return boolean
     */
    public function isXml($data)
    {
        return is_a($data, 'SimpleXMLElement');
    }

    /**
     * Transforms a resource into an array.
     *
     * @param mixed $resource
     *
     * @return array
     */
    public function toArray($resource)
    {
        if ($this->isArray($resource)) {
            return $resource;

        } elseif ($this->isObject($resource)) {
            return $this->buildArray($resource);

        } elseif ($this->isJson($resource)) {
            return json_decode($resource, true);

        } elseif ($this->isSerialized($resource)) {
            return $this->toArray(@unserialize($resource));

        } elseif ($this->isXml($resource)) {
            return $this->xmlToArray($resource);
        }

        return $resource;
    }

    /**
     * Transforms a resource into a JSON object.
     *
     * @param mixed $resource
     *
     * @return string (json)
     */
    public function toJson($resource)
    {
        if ($this->isJson($resource)) {
            return $resource;
        }

        if ($xml = $this->isXml($resource)) {
            $resource = $this->xmlToArray($xml);

        } elseif ($ser = $this->isSerialized($resource)) {
            $resource = $ser;
        }

        return json_encode($resource);
    }

    /**
     * Transforms a resource into an object.
     *
     * @param mixed $resource
     *
     * @return object
     */
    public function toObject($resource)
    {
        if ($this->isObject($resource)) {
            return $resource;

        } elseif ($this->isArray($resource)) {
            return $this->buildObject($resource);

        } elseif ($this->isJson($resource)) {
            return json_decode($resource);

        } elseif ($ser = $this->isSerialized($resource)) {
            return $this->toObject($ser);

        } elseif ($xml = $this->isXml($resource)) {
            return $xml;
        }

        return $resource;
    }

    /**
     * Transforms a resource into a serialized form.
     *
     * @param mixed $resource
     *
     * @return string
     */
    public function toSerialize($resource)
    {
        if (!$this->isArray($resource)) {
            $resource = $this->toArray($resource);
        }

        return serialize($resource);
    }

    /**
     * Transforms a resource into an XML document.
     *
     * @param mixed        $resource
     * @param string       $root
     * @param string|array $tags     String or array of wrapping tags when
     *                           converting indexed array of object to XML.
     *
     * @return string XML
     */
    public function toXml($resource, $root = 'root', $tags = 'item')
    {
        if ($this->isXml($resource)) {
            return $resource->asXML();
        }

        $array = $this->toArray($resource);

        if (!empty($array)) {
            $xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><'. $root .'></'. $root .'>');
            $response = $this->buildXml($xml, $array, $tags);

            return $response->asXML();
        }

        return $resource;
    }

    /**
     * Turn an object into an array. Alternative to array_map magic.
     *
     * @param object $object
     *
     * @return array
     */
    public function buildArray($object)
    {
        $array = array();

        foreach ($object as $key => $value) {
            if (is_object($value)) {
                $array[$key] = $this->buildArray($value);
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * Turn an array into an object. Alternative to array_map magic.
     *
     * @param array $array
     *
     * @return object
     */
    public function buildObject($array)
    {
        $obj = new \stdClass();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $obj->{$key} = $this->buildObject($value);
            } else {
                $obj->{$key} = $value;
            }
        }

        return $obj;
    }

    /**
     * Turn an array into an XML document. Alternative to array_map magic.
     *
     * @param \SimpleXMLElement $xml
     * @param mixed             $data
     * @param string|array      $tags String or array of wrapping tags when
     *                                converting indexed array of object to XML.
     *
     * @return object
     */
    public function buildXml(\SimpleXMLElement $xml, $data, $tags = 'item')
    {
        $this->xml_depth++;
        if (is_array($tags)) {
            // Stay within the bounds of the $tags array
            $index = min(array($this->xml_depth, count($tags))) - 1;
            $tag = $tags[$index];
        } else {
            $tag = $tags;
        }

        foreach ($data as $key => $value) {
            if (is_int($key)) {
                $key = $tag;
            }
            if (is_object($value)) {
                $value = $this->toArray($value);
            } elseif (!is_array($value)) {
                $xml->addChild($key, htmlentities($value));
                continue;
            }

            // At this point, value is an array - special keys are "attributes"
            // and "value", so we need to handle them.

            // Set value if it explicitely exists
            if (isset($value['value'])) {
                if (is_array($value['value'])) {
                    $node = $xml->addChild($key);
                    $this->buildXml($node, $value['value'], $tags);
                } else {
                    $node = $xml->addChild($key, htmlentities($value['value']));
                }
                unset($value['value']);
            } else {
                // Add a node, if there was no explicit value
                $node = $xml->addChild($key);
            }

            // Set attributes, if they explictely exist
            if (isset($value['attributes'])) {
                foreach ($value['attributes'] as $aKey => $aValue) {
                    $node->addAttribute($aKey, $aValue);
                }
                unset($value['attributes']);
            }

            // Handle standard value and recursion
            foreach ($value as $aKey => $aValue) {
                if (is_array($aValue) || is_object($aValue)) {
                    $this->buildXml($node, array($aKey => $aValue), $tags);
                } else {
                    $node->addChild($aKey, htmlentities($aValue));
                }
            }
        }
        $this->xml_depth--;

        return $xml;
    }

    /**
     * Convert a SimpleXML object into an array.
     *
     * @param object $xml
     * @param int    $format
     *
     * @return array
     */
    public function xmlToArray($xml, $format = self::XML_GROUP)
    {
        if (is_string($xml)) {
            $xml = @simplexml_load_string($xml);
        }

        if (count($xml->children()) <= 0) {
            return (string) $xml;
        }

        $array = array();

        foreach ($xml->children() as $element => $node) {
            $data = array();

            if (!isset($array[$element])) {
                $array[$element] = "";
            }

            if (!$node->attributes() || $format === $this->XML_NONE) {
                $data = $this->xmlToArray($node, $format);

            } else {
                switch ($format) {
                    case $this->XML_GROUP:
                        $data = array(
                            'attributes' => array(),
                            'value' => (string) $node
                        );

                        if (count($node->children()) > 0) {
                            $data['value'] = $this->xmlToArray($node, $format);
                        }

                        foreach ($node->attributes() as $attr => $value) {
                            $data['attributes'][$attr] = (string) $value;
                        }
                        break;
                    case $this->XML_MERGE:
                    case $this->XML_OVERWRITE:
                        if ($format === $this->XML_MERGE) {
                            if (count($node->children()) > 0) {
                                $data = $data + $this->xmlToArray($node, $format);
                            } else {
                                $data['value'] = (string) $node;
                            }
                        }

                        foreach ($node->attributes() as $attr => $value) {
                            $data[$attr] = (string) $value;
                        }
                        break;
                }
            }

            if (count($xml->{$element}) > 1) {
                $array[$element][] = $data;
            } else {
                $array[$element] = $data;
            }
        }

        return $array;
    }

    /**
     * Encode a resource object for UTF-8.
     *
     * @param mixed $data
     *
     * @return array|string
     */
    public function utf8Encode($data)
    {
        if (is_string($data)) {
            return utf8_encode($data);

        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[utf8_encode($key)] = $this->utf8Encode($value);
            }

        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->{$key} = $this->utf8Encode($value);
            }
        }

        return $data;
    }

    /**
     * Decode a resource object for UTF-8.
     *
     * @param mixed $data
     *
     * @return array|string
     */
    public function utf8Decode($data)
    {
        if (is_string($data)) {
            return utf8_decode($data);

        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[utf8_decode($key)] = $this->utf8Decode($value);
            }

        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->{$key} = $this->utf8Decode($value);
            }
        }

        return $data;
    }
}
