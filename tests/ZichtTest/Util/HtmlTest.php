<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Util;

use Zicht\Util\Html;

class HtmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider repair_html
     * @param  $html
     * @return void
     */
    function testRepairHtml($in, $expect, $allowed = null) {
        $this->assertEquals($expect, Html::repair($in, $allowed));
    }

    function repair_html() {
        $tests = array(
            array('<a href=">bla</a>',                          '<a href="">bla</a>'),
            array('<a href=bla>bla</a>',                        '<a href="bla">bla</a>'),
            array('<a href="blabla target="_blank">bla</a>',    '<a href="blabla target=" _blank="_blank">bla</a>'),
        );

        $ret = array();
        foreach($tests as $test) {
            $ret[]= $test;
            $ret[]= array($test[0] . ' content after', $test[1] . ' content after');
            $ret[]= array('content before ' . $test[0] . ' content after', 'content before ' . $test[1] . ' content after');
            $ret[]= array('content before ' . $test[0], 'content before ' . $test[1]);
        }

        $ret[]=array('<a href=">bla</a',                           '<a href="">bla&lt;/a</a>');
        $ret[]=array('<ul><li>Closing tag</ul>',                   '<ul><li>Closing tag</li></ul>');
        $ret[]=array('<ul><li>1<li>2</ul>',                        '<ul><li>1</li><li>2</li></ul>');
        $ret[]=array('<ul><li>1<li>2</li></ul>',                   '<ul><li>1</li><li>2</li></ul>');
        $ret[]=array('<ul><li><span>1<li>2</li></ul>',             '<ul><li><span>1</span></li><li>2</li></ul>');
        $ret[]=array('<ul><li>Closing tag</pi></ul>',              '<ul><li>Closing tag</li></ul>');
        $ret[]=array('<ul><li>Closing tag</pi>',                   '<ul><li>Closing tag</li></ul>');
        $ret[]=array('<p>content<h5>header</h5></p>',              '<p>content</p><h5>header</h5>');
        $ret[]=array('<p>content<h5>header</h5>',                  '<p>content</p><h5>header</h5>');
        $ret[]=array('<p>content<h5>header</h5>asdf</p>',          '<p>content</p><h5>header</h5>asdf');

        $ret[]=array('<p><b>content<h5>header</h5></p>',           '<p><b>content</b></p><h5>header</h5>');
        $ret[]=array('<p><b><a href="some link">content<h5>header</h5></p>','<p><b><a href="some link">content</a></b></p><h5>header</h5>');
        $ret[]=array('<p><b><a href=\'some link\'>content<h5>header</h5></p>','<p><b><a href="some link">content</a></b></p><h5>header</h5>');
        $ret[]=array('<p><b><a href=some link>content<h5>header</h5></p>','<p><b><a href="some" link="link">content</a></b></p><h5>header</h5>');

        $ret[]=array('<p><b>content<h5>header<br /><br /></h5></p>',           '<p><b>content</b></p><h5>header<br /><br /></h5>');
        $ret[]=array('<p><b>content<h5>header<br\><br\></h5></p>',           '<p><b>content</b></p><h5>header<br /><br /></h5>');
        $ret[]=array('<p><b>content</b></p>',           '<p><!-- b -->content<!-- /b --></p>', array('p' => array()));
        $ret[]=array('<span 12=13>', '<span></span>');
        $ret[]=array('<span a12=13>', '<span a12="13"></span>');
        $ret[]=array('<span a12=13>', '<span></span>', array('span' => function() { return false; })); // false means "no attributes"
        $ret[]=array(
            '<span style="foo" class="sugob">',
            '<span class="bogus"></span>',
            array(
                'span' => function($tagName, $attr = null, $value = null) {
                    if ($attr == 'style') {
                        return false;
                    }
                    return strrev($value);
                }
            )
        );
        $ret[]=array(
            '<span style="foo" class="sugob">',
            '<span class="bogus"></span>',
            array(
                'span' => function($tagName, $attr = null, $value = null) {
                    if ($attr == 'style') {
                        return false;
                    }
                    return strrev($value);
                }
            )
        );
        $ret[]=array(
            '<span style="foo" class="sugob">',
            '<span style="foo" class="bogus"></span>',
            array(
                'span' => array(
                    'class' => function($tagName, $attr = null, $value = null) {
                        return strrev($value);
                    }
                )
            )
        );

        $ret[]=array(
            '<span style="foo" class="sugob">',
            '<span style="oof" class="bogus"></span>',
            function($tagName, $attr = null, $value = null) {
                if (func_num_args() === 1) {
                    return true;
                }
                return strrev($value);
            }
        );
        $ret[]=array('< span>', '&lt; span>');
        $ret[]=array('<span    >', '<span></span>');
        $ret[]=array('<li>boo', '<ul><li>boo</li></ul>');
        $ret[]=array('<ol><li>boo', '<ol><li>boo</li></ol>');
        $ret[]=array('<p><li>boo', '<p></p><ul><li>boo</li></ul>');

        return $ret;
    }


    /**
     * @dataProvider entity_data
     */
    function testSanitizeEntityReferences($in, $expect)
    {
        $this->assertEquals($expect, Html::sanitizeEntityReferences($in));
    }
    function entity_data()
    {
        return array(
            array('bla &amp; bla', 'bla &amp; bla'),
            array('bla & bla', 'bla &amp; bla')
        );
    }


    /**
     * @dataProvider is_empty_node_data
     */
    function testIsEmptyNode($contents, $expectEmpty)
    {
        $doc = new \DOMDocument();
        $doc->loadHTML('<html><body><div>' . $contents . '</div></body></html>');

        $node = $doc->getElementsByTagName('div')->item(0);
        foreach ($node->childNodes as $node) {
            $this->assertEquals($expectEmpty, Html::isEmptyNode($node));
        }
    }
    function is_empty_node_data()
    {
        return array(
            array(' ', true),
            array('<p></p>', true),
            array('<p> </p>', true),
            array('<p><span></span></p>', true),
            array('<p><span>  </span></p>', true),
            array('<p><span>a</span></p>', false),
        );
    }


    /**
     * @dataProvider createLinks
     */
    function testCreateLinks($in, $out)
    {
        $this->assertEquals($out, Html::createLinks($in));
    }


    public function createLinks()
    {
        return array(
            array('a https://nl.wikipedia.org/wiki/Mineur_(leger)', 'a <a href="https://nl.wikipedia.org/wiki/Mineur_(leger)">https://nl.wikipedia.org/wiki/Mineur_(leger)</a>'),
            array('a https://nl.wikipedia.org/wiki/Mineur_(leger) n', 'a <a href="https://nl.wikipedia.org/wiki/Mineur_(leger)">https://nl.wikipedia.org/wiki/Mineur_(leger)</a> n'),
            array('a www.example.org', 'a <a href="http://www.example.org">www.example.org</a>'),
            array('a www.example.org b', 'a <a href="http://www.example.org">www.example.org</a> b'),
            array('a http://www.example.org b', 'a <a href="http://www.example.org">http://www.example.org</a> b'),
            array('a http://www.example.org/foo.html b', 'a <a href="http://www.example.org/foo.html">http://www.example.org/foo.html</a> b'),
            array('Over http://www.example.org/foo.html, het volgende', 'Over <a href="http://www.example.org/foo.html">http://www.example.org/foo.html</a>, het volgende'),
            array('Kijk hier: http://www.example.org/foo.html.', 'Kijk hier: <a href="http://www.example.org/foo.html">http://www.example.org/foo.html</a>.'),
            array('Kijk hier: http://www.example.org/foo.html. ', 'Kijk hier: <a href="http://www.example.org/foo.html">http://www.example.org/foo.html</a>. '),
            array('Kijk hier: http://www.example.org/foo.html.twig ', 'Kijk hier: <a href="http://www.example.org/foo.html.twig">http://www.example.org/foo.html.twig</a> '),
            array('Kijk hier: http://www.example.org/foo.html; en anderen', 'Kijk hier: <a href="http://www.example.org/foo.html">http://www.example.org/foo.html</a>; en anderen'),
            array('Kijk hier: http://www.example.org.<', 'Kijk hier: <a href="http://www.example.org">http://www.example.org</a>.<'),
            array('Kijk hier: http://www.example.org. <', 'Kijk hier: <a href="http://www.example.org">http://www.example.org</a>. <'),
        );
    }
}
