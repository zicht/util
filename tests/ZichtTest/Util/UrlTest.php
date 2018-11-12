<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Util;

use Zicht\Util\Url;

/**
 * @covers Zicht\Util\Url
 */
class UrlTest extends \PHPUnit_Framework_TestCase {
    function testConstruct() {
        $url = new Url();
        return $url;
    }


    /**
     * @depends testConstruct
     */
    function testComponentsAreAllEmptyInitially($url) {
        $this->assertTrue(empty($url[Url::SCHEME]));
        $this->assertTrue(empty($url[Url::USER]));
        $this->assertTrue(empty($url[Url::PASS]));
        $this->assertTrue(empty($url[Url::HOST]));
        $this->assertTrue(empty($url[Url::PORT]));
        $this->assertTrue(empty($url[Url::PATH]));
        $this->assertTrue(empty($url[Url::QUERY]));
        $this->assertTrue(empty($url[Url::FRAGMENT]));
        return $url;
    }


    /**
     * @depends testComponentsAreAllEmptyInitially
     */
    function testHashTransparency($url) {
        $parts = array(
            Url::SCHEME     => 'http',
            Url::USER       => 'user',
            Url::PASS       => 'pass',
            Url::HOST       => 'host',
            Url::PORT       => 'port',
            Url::PATH       => '/path',
            Url::QUERY      => 'a=b',
            Url::FRAGMENT   => 'fragment',
        );
        foreach ($parts as $part => $value) {
            $url[$part] = $value;
            $this->assertEquals($value, $url[$part]);
        }
        $this->assertEquals('http://user:pass@host:port/path?a=b#fragment', (string)$url);
        return $url;
    }

    /**
     * @depends testHashTransparency
     */
    function testHashTransparencyForUnset($url) {
        $this->assertNotEquals('', (string)$url);
        $parts = array(
            Url::SCHEME     => 'http',
            Url::USER       => 'user',
            Url::PASS       => 'pass',
            Url::HOST       => 'host',
            Url::PORT       => 'port',
            Url::PATH       => '/path',
            Url::QUERY      => 'a=b',
            Url::FRAGMENT   => 'fragment',
        );
        foreach ($parts as $part => $value) {
            unset($url[$part]);
        }
        $this->assertEquals('', (string)$url);
    }



    /**
     * @dataProvider urls
     * @param $url
     */
    function testUrlWillConstructSameUrlWithConstructorAsSetUrl($str) {
        $url = new Url($str);
        $other = new Url();
        $other->setUrl($str);
        $this->assertEquals((string)$other, (string)$url);
        $this->assertEquals($str, (string)$url);
    }
    /**
     * @dataProvider urls
     * @param $url
     */
    function testUrlWillConstructSameUrlBasedOnStringAsBasedOnStringCast($str) {
        $url = new Url();
        $url->setUrl($str);
        $other = new Url();
        $other->setUrl((string)$url);
        $this->assertEquals((string)$other, (string)$url);
        $this->assertEquals($str, (string)$url);
    }

    /**
     * @dataProvider urls
     * @param $url
     */
    function testComponentsWillContainExpectedValuesAfterParsing($str, $components) {
        $url = new Url();
        $url->setUrl($str);
        foreach ($components as $name => $value) {
            $this->assertEquals($url[$name], $value);
        }
    }

    /**
     * @dataProvider urls
     * @param $url
     */
    function testUrlWillBeExpectedFormatWhenCreatingFromComponents($str, $components) {
        $url = new Url();
        foreach ($components as $name => $value) {
            $url->set($name, $value);
        }
        $this->assertEquals($str, (string)$url);
    }



    function testConstructingArrayFromStringWillYieldExpectedUrl() {
        $url = new Url();
        $url[Url::SCHEME] = 'http';
        $url[Url::HOST]= 'example.org';
        $url[Url::PATH]= '/';
        $url->setParam('a', 'b');
        $url->addParam('a', 'c', true);

        $this->assertEquals('http://example.org/?a[]=b&a[]=c', (string)$url);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testConstructingArrayFromStringWillThrowExceptionIfStrict() {
        $url = new Url();
        $url->setParam('a', 'b');
        $url->addParam('a', 'c');
    }

    /**
     */
    function testConstructingArrayByDefault() {
        $url = new Url();
        $url->addParam('a', 'c');
        $this->assertEquals('a[]=c', $url[Url::QUERY]);
    }


    function urls() {
        return array(
            array('http://example.org', array(Url::SCHEME => 'http', Url::HOST => 'example.org', Url::PATH => '')),
            array('http://example.org/', array(Url::SCHEME => 'http', Url::HOST => 'example.org', Url::PATH => '/')),
            array('http://example.org/?a=b', array(Url::SCHEME => 'http', Url::HOST => 'example.org', Url::PATH => '/', Url::QUERY => 'a=b')),
            array('/a/b/c?a=b', array(Url::SCHEME => '', Url::HOST => '', Url::PATH => '/a/b/c', Url::QUERY => 'a=b')),
            array('/a/b/c?a=b&c=d', array(Url::SCHEME => '', Url::HOST => '', Url::PATH => '/a/b/c', Url::QUERY => 'a=b&c=d')),
            array('?a=b', array(Url::QUERY => 'a=b')),
            array('http://username@example.org/index/', array(Url::SCHEME => 'http', Url::HOST => 'example.org', Url::USER => 'username', Url::PATH => '/index/')),
            array('http://username@example.org/index/?c=d', array(Url::SCHEME => 'http', Url::HOST => 'example.org', Url::PATH => '/index/', Url::USER => 'username', Url::QUERY => 'c=d')),
            array('http://username:password@example.org/path?arg=value#anchor', array(Url::SCHEME => 'http', Url::HOST => 'example.org', Url::PATH => '/path', Url::USER => 'username', Url::PASS => 'password', Url::QUERY => 'arg=value', Url::FRAGMENT => 'anchor')),
            array('http://username:password@example.org:443/path?arg=value#anchor', array(Url::PORT => 443, Url::SCHEME => 'http', Url::HOST => 'example.org', Url::PATH => '/path', Url::USER => 'username', Url::PASS => 'password', Url::QUERY => 'arg=value', Url::FRAGMENT => 'anchor')),
            array('/a', array(Url::PATH => '/a')),
        );
    }


    function testParameterValueEncoding() {
        $url = new Url();
        $url->setParam('a', 'contains spaces');
        $this->assertEquals('?a=contains%20spaces', (string)$url);
    }


    /**
     * @dataProvider queryStrings
     * @param $expected
     * @param $input
     */
    function testSerializingQueryStringWillRenderExpectedResults($expected, $input) {
        $this->assertEquals($expected, Url::queryString($input));
    }


    function queryStrings() {
        return array(
            array('', array()),
            array('a=b', array('a' => 'b')),
            array('a=b&c=d', array('a' => 'b', 'c' => 'd')),
            array('a[]=b', array('a' => array('b'))),
            array('a[]=b&a[]=c', array('a' => array('b', 'c'))),
            array('a[]=b&a[]=c', array('a' => array(0 => 'b', 1 => 'c'))),
            array('a[foo]=b&a[bar]=c', array('a' => array('foo' => 'b', 'bar' => 'c'))),
            array('a[foo][]=b&a[foo][]=c', array('a' => array('foo' => array('b', 'c')))),
            array(
                'a[foo][foo]=b&a[foo][bar]=c',
                array(
                    'a' => array(
                        'foo' => array(
                            'foo' => 'b',
                            'bar' => 'c'
                        )
                    )
                )
            ),
            array(
                '',
                array(
                    'a' => array(
                        'foo' => array(
                            'foo' => '',
                            'bar' => ''
                        )
                    )
                )
            )
        );
    }



    /**
     * @expectedException \OutOfBoundsException
     */
    function testOffsetSetWillThrowExceptionIfInvalidPropertyIsRequested() {
        $url = new Url();
        $url['a'] = 'b';
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    function testOffsetGetWillThrowExceptionIfInvalidPropertyIsRequested() {
        $url = new Url();
        echo $url['a'];
    }


    /**
     * @dataProvider getParamData
     */
    function testGetParamWillReturnParameterFromQueryString($queryString, $name, $expectedValue) {
        $url = new Url();
        $url[Url::QUERY]= $queryString;
        $this->assertEquals($expectedValue, $url->getParam($name));
    }
    function getParamData() {
        return array(
            array('a=b', 'a', 'b'),
            array('a[b][c]=foo', array('a', 'b', 'c'), 'foo'),
        );
    }


    function testGetParamWillReturnDefaultValueIfNotSet()
    {
        $url = new Url();
        $url[Url::QUERY] = 'a[b]=0';
        $this->assertEquals(0, $url->getParam(array('a', 'b')));
        $this->assertEquals('foo', $url->getParam(array('a', 'c'), 'foo'));
        $this->assertEquals('foo', $url->getParam('b', 'foo'));
    }


    /**
     * @dataProvider getParamData
     */
    function testSetParamWillSetQueryStringAccordingly($queryString, $name, $expectedValue) {
        $url = new Url();
        $url->setParam($name, $expectedValue);
        $this->assertEquals($queryString, $url[Url::QUERY]);
    }


    function testSetParamWillNotOverwriteIfReplaceIsFalse()
    {
        $url = new Url();
        $url->setParam('a', 'b');
        $this->assertEquals('a=b', $url[Url::QUERY]);
        $url->setParam('a', 'c', false);
        $this->assertEquals('a=b', $url[Url::QUERY]);
    }
}