<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */


class SmartyPluginsTest extends PHPUnit_Framework_Testcase {
    function _loadPlugin($name, $type) {
        if(!function_exists(sprintf('smarty_%s_%s', $type, $name))) {
            require_once 'includes/helpers/smarty_plugins/' . $type . '.' . $name . '.php';
        }
    }


    /**
     * @dataProvider safe_escape
     * @return void
     */
    function testSafeEscape($ampOnly, $in, $expect) {
        $this->_loadPlugin('safe_escape', 'modifier');
        $this->assertEquals($expect, smarty_modifier_safe_escape($in, $ampOnly));
    }

    /**
     * @dataProvider repair_html
     * @param  $html
     * @return void
     */

    function testRepairHtml($in, $expect) {
        $this->_loadPlugin('repair_html', 'modifier');
        $this->assertEquals($expect, smarty_modifier_repair_html($in));
    }



    function safe_escape() {
        return array(
            array(true,     'lorem & ipsum',                'lorem &amp; ipsum'),
            array(true,     'lorem &quot ipsum',            'lorem &amp;quot ipsum'),
            array(true,     'lorem &amp; ipsum',            'lorem &amp; ipsum'),
            array(true,     'lorem &amp; " ipsum',          'lorem &amp; " ipsum'),
            array(true,     'lorem &amp; " <img> ipsum',    'lorem &amp; " <img> ipsum'),

            array(false,    'lorem & ipsum',                'lorem &amp; ipsum'),
            array(false,    'lorem &quot ipsum',            'lorem &amp;quot ipsum'),
            array(false,    'lorem &amp; ipsum',            'lorem &amp; ipsum'),
            array(false,    'lorem &amp; &quot; ipsum',     'lorem &amp; &quot; ipsum'),
            array(false,    'lorem &amp; " ipsum',          'lorem &amp; &quot; ipsum'),
            array(false,    'lorem &amp; " <img> ipsum',    'lorem &amp; &quot; &lt;img&gt; ipsum'),
        );
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
        $ret[]=array('<ul><li>Closing tag</pi></ul>',              '<ul><li>Closing tag</li></ul>');
        $ret[]=array('<ul><li>Closing tag</pi>',                   '<ul><li>Closing tag</li></ul>');
        $ret[]=array('<p>content<h5>header</h5></p>',              '<p>content</p><h5>header</h5>');
        $ret[]=array('<p>content<h5>header</h5>',                  '<p>content</p><h5>header</h5>');
        $ret[]=array('<p>content<h5>header</h5>asdf</p>',          '<p>content</p><h5>header</h5>asdf');

        $ret[]=array('<p><b>content<h5>header</h5></p>',           '<p><b>content</b></p><h5>header</h5>');
        $ret[]=array('<p><b><a href="">content<h5>header</h5></p>','<p><b><a href="">content</a></b></p><h5>header</h5>');

        return $ret;
    }
}