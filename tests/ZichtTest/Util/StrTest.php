<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Util;
use PHPUnit\Framework\TestCase;
use Zicht\Util\Str;

class StrTest extends TestCase
{
    /**
     * @dataProvider cases
     */
    function test($method, $in, $expect)
    {
        $args = func_get_args();
        $method = array_shift($args);
        $expect = array_pop($args);
        $this->assertEquals(
            $expect,
            call_user_func_array(array('\Zicht\Util\Str', $method), $args)
        );
    }

    function cases()
    {
        return array(
            array('camel',      '', ''),
            array('camel',      'me', 'me'),
            array('camel',      'me-', 'me'),
            array('camel',      '-me', 'Me'),
            array('camel',      'me-myself', 'meMyself'),
            array('camel',      '-me-myself', 'MeMyself'),
            array('camel',      'me-myself-and-i', 'meMyselfAndI'),
            array('camel',      'me-myself-and-i-', 'meMyselfAndI'),
            array('camel',      'me_myself_and_i', 'meMyselfAndI'),
            array('camel',      'me_myself_and_i_', 'meMyselfAndI'),
            array('camel',      '_me_myself_and_i_', 'MeMyselfAndI'),
            array('uscore',     '', ''),
            array('uscore',     'MeMyself', 'me_myself'),
            array('uscore',     'MeMyselfAndI', 'me_myself_and_i'),
            array('dash',       'me', 'me'),
            array('dash',       'meMyself', 'me-myself'),
            array('dash',       'MeMyself', '-me-myself'),
            array('dash',       'meMyselfAndI', 'me-myself-and-i'),
            array('dash',       'MeMyselfAndI', '-me-myself-and-i'),
            array('rstrip',     '', '', ''),
            array('rstrip',     '', 'foo', ''),
            array('rstrip',     'foo bar', 'bar', 'foo '),
            array('rstrip',     'foo bar', 'baz', 'foo bar'),
            array('lstrip',     '', '', ''),
            array('lstrip',     '', 'foo', ''),
            array('lstrip',     'foo bar', 'bar', 'foo bar'),
            array('lstrip',     'foo bar', 'foo', ' bar'),
            array('strip',      '', '', '', ''),
            array('lstrip',     '', 'foo', '', ''),
            array('lstrip',     '', '', 'foo', ''),
            array('strip',      'foo bar', 'foo', 'bar', ' '),
            array('strip',      'foo bar', 'bar', 'foo', 'foo bar'),
            array('classname',   '', ''),
            array('classname',   'A', 'A'),
            array('classname',   'A\B', 'B'),
            array('classname',   '\A\B', 'B'),
            array('humanize',    'one_flew_over_theCuckoos-nest', 'One flew over the cuckoos nest'),
            array('systemize',   'w00t', 'w00t'),
            array('systemize',   'foo bar', 'foo-bar'),
            array('systemize',   'foo  bar', 'foo-bar'),
            array('systemize',   html_entity_decode('foo&shy;bar', null, 'UTF-8'), 'foobar'),
            array('systemize',   'Foo  Bar', 'foo-bar'),
            array('systemize',   '\'Foo  Bar', 'foo-bar'),
            array('ascii',       'Hééél gek', 'Heeel gek'),
            array('ascii',       'ЁЂЃЄЅІ和製漢語 １２３ﬁ', 'EDGEZI he zhi han yu 123fi'),
            array('systemize',   'Hééél gek', 'heeel-gek'),
            array('systemize',   'ЁЂЃЄЅІ和製漢語 １２３ﬁ', 'edgezi-he-zhi-han-yu-123fi'),
            array('truncate',   'Kort', 10, 'Kort'),
            array('truncate',   'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 10, 'ABCDEFG...'),
            array('truncate',   'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 10, '--', 'ABCDEFGH--'),
            array('truncate',   'ABCDEFGHIJ', 10, 'ABCDEFGHIJ'),
            array('commonPrefix', array(), ''),
            array('commonPrefix', array(''), ''),
            array('commonPrefix', array('', ''), ''),
            array('commonPrefix', array('foo'), 'foo'),
            array('commonPrefix', array('foo', 'bar'), ''),
            array('commonPrefix', array('foo', 'f'), 'f'),
            array('commonPrefix', array('foo', 'fa', 'foo'), 'f'),
        );
    }


    function testRandom()
    {
        // how to test randomness. Well..... something like this should do the trick.
        $nTimes = 20;

        for ($i = 0; $i < $nTimes; $i ++) {
            $this->assertEquals('a', Str::random(1, 'a'));
        }
        for ($i = 0; $i < $nTimes; $i ++) {
            $this->assertNotEquals('a', Str::random(1, 'b'));
        }
        for ($i = 0; $i < $nTimes; $i ++) {
            $this->assertEquals(2, strlen(Str::random(2, '2130978213507')));
        }
        for ($i = 0; $i < $nTimes; $i ++) {
            foreach (array(Str::ALNUM, Str::ALNUM_SAFE, Str::ALPHA, Str::NUMERIC, Str::HEX) as $src) {
                $str = Str::random(10, $src);
                for ($j = 0; $j < strlen($str); $j ++) {
                    $this->assertTrue(false !== strpos($src, $str[$j]));
                }
            }
        }
    }
}
