<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Util;

/**
 * URL Helper functions
 */
class Url implements \ArrayAccess
{
    /** Constant to identify the 'scheme' part of an url */
    const SCHEME = 'scheme';

    /** Constant to identify the 'user' part of an url */
    const USER = 'user';

    /** Constant to identify the 'pass' part of an url */
    const PASS = 'pass';

    /** Constant to identify the 'host' part of an url */
    const HOST = 'host';

    /** Constant to identify the 'port' part of an url */
    const PORT = 'port';

    /** Constant to identify the 'path' part of an url */
    const PATH = 'path';

    /** Constant to identify the 'query' part of an url */
    const QUERY = 'query';

    /** Constant to identify the 'fragment' part of an url */
    const FRAGMENT = 'fragment';

    /**
     * Contains all available url parts.
     *
     * @var array
     */
    protected static $parts = [
        self::SCHEME,
        self::USER,
        self::PASS,
        self::HOST,
        self::PORT,
        self::PATH,
        self::QUERY,
        self::FRAGMENT
    ];

    /**
     * Contains a hash of the components mapped to their values
     *
     * @var array
     */
    private $components = [];

    /**
     * Setup the class with empty defaults
     *
     * @param string $url
     */
    public function __construct($url = null)
    {
        $this->reset();
        if (null !== $url) {
            $this->setUrl($url);
        }
    }


    /**
     * Set and parse the url.
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->reset();

        foreach (parse_url($url) as $part => $value) {
            $this[$part] = $value;
        }
    }


    /**
     * Serialize all properties into an URL string
     *
     * @return string
     */
    public function __toString()
    {
        $ret = '';
        if (!empty($this[self::SCHEME])) {
            $ret .= $this[self::SCHEME];
            $ret .= ':';
        }
        if (!empty($this[self::HOST])) {
            $ret .= '//';
            if (!empty($this[self::USER])) {
                $ret .= $this[self::USER];
                if (!empty($this[self::PASS])) {
                    $ret .= ':';
                    $ret .= $this[self::PASS];
                }
                $ret .= '@';
            }
            $ret .= $this[self::HOST];
        }
        if (!empty($this[self::PORT])) {
            $ret .= ':';
            $ret .= $this[self::PORT];
        }
        if (!empty($this[self::PATH])) {
            $ret .= $this[self::PATH];
        }
        if (!empty($this[self::QUERY])) {
            $ret .= '?';
            $ret .= $this[self::QUERY];
        }
        if (!empty($this[self::FRAGMENT])) {
            $ret .= '#';
            $ret .= $this[self::FRAGMENT];
        }
        return $ret;
    }


    /**
     * Checks if the defined Url part exists.
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->components[$offset]);
    }


    /**
     * Returns the string value of the specified url part.
     * Does not allow getting properties other than the specified class constants.
     *
     * @param string $offset
     * @return string
     * @throws \OutOfBoundsException
     */
    public function offsetGet($offset)
    {
        if (!in_array($offset, self::$parts)) {
            throw new \OutOfBoundsException("$offset is not a valid url part");
        }
        if (isset($this->components[$offset])) {
            $ret = $this->components[$offset];
        } else {
            $ret = '';
        }
        if (is_array($ret) && $offset == self::QUERY) {
            $ret = self::queryString($ret);
        }
        return $ret;
    }


    /**
     * Set the specified URL part.
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     *
     * @throws \OutOfBoundsException If the specified part is not a valid URL part.
     */
    public function offsetSet($offset, $value)
    {
        if ($offset == self::QUERY) {
            $parameters = [];
            parse_str($value, $parameters);
            $value = $parameters;
        }
        $this->set($offset, $value);
    }


    /**
     * Set a URL part.
     *
     * @param string $part
     * @param mixed $value
     * @return self
     * @throws \OutOfBoundsException
     */
    public function set($part, $value)
    {
        if (!in_array($part, self::$parts)) {
            throw new \OutOfBoundsException("$part is not a valid url part");
        }

        $this->components[$part] = $value;
        return $this;
    }


    /**
     * Returns a parameter from the query string. Allows an array to traverse a tree of key names.
     *
     * @param string|array $name
     * @param mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (is_array($name)) {
            return TreeTools::getByPath($this->components[self::QUERY], $name, $default);
        }
        if (isset($this->components[self::QUERY][$name])) {
            return $this->components[self::QUERY][$name];
        }

        return $default;
    }


    /**
     * Sets a parameter in the query string. Allows an array to traverse a tree of key names.
     *
     * @param string|array $name
     * @param mixed $value
     * @param bool $replace
     * @return Url
     */
    public function setParam($name, $value, $replace = true)
    {
        if (is_array($name)) {
            if (!isset($this->components[self::QUERY])) {
                $this->components[self::QUERY] = [];
            }
            TreeTools::setByPath($this->components[self::QUERY], $name, $value);
        } else {
            if (isset($this->components[self::QUERY][$name]) && !$replace) {
                return $this;
            }
            $this->components[self::QUERY][$name] = $value;
        }
        return $this;
    }


    /**
     * Adds a parameter at the specified path.
     *
     * @param string|array $name
     * @param mixed $value
     * @param bool $convertToArrayIfExists
     * @return Url
     * @throws \InvalidArgumentException
     */
    public function addParam($name, $value, $convertToArrayIfExists = false)
    {
        if (isset($this->components[self::QUERY][$name])) {
            if (!is_array($this->components[self::QUERY][$name])) {
                if ($convertToArrayIfExists) {
                    $this->components[self::QUERY][$name] = [$this->components[self::QUERY][$name]];
                } else {
                    throw new \InvalidArgumentException("$name is not an array");
                }
            }
        } else {
            $this->components[self::QUERY][$name] = [];
        }
        $this->components[self::QUERY][$name][] = $value;
        return $this;
    }


    /**
     * Unsets the specified array component.
     *
     * @param string|array $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->components[$offset]);
    }


    /**
     * Returns pairs of keys and values based on a (nested) array, e.g.:
     * <code>
     * Url::flattenRequestVars ( array (
     *   'a' =>
     *   array (
     *     'a1' => '1',
     *     'a2' => '2'
     *   ),
     *   'b' => '3'
     * ) );
     * </code>
     *
     * would yield
     *
     * <code>
     * array (
     *   array('a[a1]', '1'),
     *   array('a[a1]', '2'),
     *   array('b', '3')
     * )
     * </code>
     *
     * @param array $vars
     * @param string $parentName Used internally for recursion
     * @return array
     */
    protected static function flattenRequestVars($vars, $parentName = '')
    {
        $ret = [];
        $ignoreName = (($parentName != '') && array_keys($vars) === range(0, count($vars) - 1));

        foreach ($vars as $name => $value) {
            if ($ignoreName) {
                $name = null;
            }
            if (strlen($parentName)) {
                $name = sprintf('%s[%s]', $parentName, $name);
            }
            if (is_array($value)) {
                $ret = array_merge($ret, self::flattenRequestVars($value, $name));
            } else {
                $ret[] = [$name, $value];
            }
        }

        return $ret;
    }


    /**
     * Construct a query string from the parameters
     *
     * @param array $params
     * @param null $parent
     * @param string $callback
     * @param bool $ignoreNonValues
     * @return string
     */
    public static function queryString($params, $parent = null, $callback = 'rawurlencode', $ignoreNonValues = true)
    {
        $params = self::flattenRequestVars($params, $parent);
        $ret = [];
        foreach ($params as $pair) {
            list($name, $value) = $pair;
            if (!$value && $ignoreNonValues) {
                continue;
            }
            if ($callback) {
                $value = call_user_func($callback, $value);
            }
            $ret[] = sprintf('%s=%s', $name, $value);
        }
        return implode('&', $ret);
    }


    /**
     * Reset the components
     *
     * @return void
     */
    private function reset()
    {
        $this->components = [];
    }
}
