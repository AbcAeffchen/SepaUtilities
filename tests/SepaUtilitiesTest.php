<?php

use AbcAeffchen\SepaUtilities\SepaUtilities;

require __DIR__ . '/../src/SepaUtilities.php';


/**
 * Project: SepaUtilities
 * User:    AbcAeffchen
 * Date:    14.10.2014
 */

class SepaUtilitiesTest extends PHPUnit_Framework_TestCase
{

    public function testCheckCreditorIdentifier()
    {
        // Valid
        $this->assertSame('DE98ZZZ09999999999', SepaUtilities::checkCreditorIdentifier('DE98ZZZ09999999999'));

        // Invalid by wrong checksum
        $this->assertFalse(SepaUtilities::checkCreditorIdentifier('DE98ZZZ09999999998'));

        // Invalid by not allowed character
        $this->assertFalse(SepaUtilities::checkCreditorIdentifier('DE98ZZÄ09999999998'));

        // Valid but bad formatting
        $this->assertSame('DE98ZZZ09999999999', SepaUtilities::checkCreditorIdentifier('d e98 Z ZZ 09 99 9999999'));

        // Valid, ignoring the middlepart
        $this->assertSame('DE98ABC09999999999', SepaUtilities::checkCreditorIdentifier('DE98abc09999999999'));
    }

    public function testCheckIBAN()
    {
        // Valid
        $this->assertSame('DE21700519950000007229',SepaUtilities::checkIBAN('DE21700519950000007229'));

        // Valid, but bad formatting
        $this->assertSame('DE21700519950000007229',SepaUtilities::checkIBAN('d e2170051 99500 00007 229'));

        // Invalid by wrong character
        $this->assertFalse(SepaUtilities::checkIBAN('DE2170Ö519950000007229'));

        // Invalid by wrong checksum
        $this->assertFalse(SepaUtilities::checkIBAN('DE21700529950000007229'));
    }

    public function testCheckBIC()
    {
        // Valid
        $this->assertSame('ASDFGHJ0', SepaUtilities::checkBIC('ASDFGHJ0'));

        // Valid, but bad formatting
        $this->assertSame('ASDFGHJ0', SepaUtilities::checkBIC('A SdFG Hj0'));

        // Invalid (0 (zero) changed to O (oh)
        $this->assertFalse(SepaUtilities::checkBIC('ASDFGHJO'));
    }

    public function testFormatDate()
    {
        // Valid date (in german format)
        $this->assertSame('2014-10-14',SepaUtilities::getDate('14.10.2014'));

        // Valid date
        $this->assertSame('2014-10-14',SepaUtilities::getDate('10 14 2014', 'm d Y'));

        // Invalid date that can be adjusted
        $this->assertSame('2015-01-14',SepaUtilities::getDate('14.13.2014'));

        // Just invalid
        $this->assertFalse(SepaUtilities::getDate('some text'));
    }

    public function testCheck()
    {
        // invalid field
        $this->assertFalse(SepaUtilities::check('tetstfield','random input'));

        // valid field, valid value
        $this->assertSame('DE21700519950000007229',SepaUtilities::check('iban','DE21700519950000007229'));

        // valid field (but bad formatted), valid value
        $this->assertSame('DE21700519950000007229',SepaUtilities::check('IbAN','DE21700519950000007229'));
    }

    public function testContainsNotAllKeys()
    {
        $this->assertTrue(SepaUtilities::containsNotAllKeys(array('a' => 1, 'b' => 2, 'd' => 2),
                                                            array('a', 'b', 'c')));

        $this->assertFalse(SepaUtilities::containsNotAllKeys(array('a' => 1, 'b' => 2, 'd' => 2),
                                                             array('a', 'b', 'd')));
    }

    public function testContainsNotAnyKey()
    {
        $this->assertTrue(SepaUtilities::containsNotAnyKey(array('a' => 1, 'b' => 2, 'd' => 2),
                                                           array('e', 'f', 'g')));

        $this->assertFalse(SepaUtilities::containsNotAnyKey(array('a' => 1, 'b' => 2, 'd' => 2),
                                                            array('e', 'f', 'b')));
    }

    public function testReplaceSpecialChars()
    {
        // All valid chars are accepted
        $allValidChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 /-?:().,\'+';
        $this->assertSame($allValidChars, SepaUtilities::replaceSpecialChars($allValidChars));

        // All replaced characters (contains greek an cyrillic characters)
        $input = ';[\]^_`{|}~¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿĀāĂăĄąĆćĈĉĊċČčĎďĐđĒēĔĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħĨĩĪīĬĭĮįİıĲĳĴĵĶķĸĹĺĻļĽľĿŀŁłŃńŅņŇňŐőŒœŔŕŖŗŘřŚśŜŝŞşŠšŢţŤťŦŧŨũŪūŬŭŮůŰűŲųŴŵŶŷŸŹźŻżŽžȘșȚțΆΈΉΊΌΎΏΐΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩΪΫάέήίΰαβγδεζηθικλμνξοπρςστυφχψωϊϋόύώАБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЬЮЯабвгдежзийклмнопрстуфхцчшщъьюя€';
        $result = ',(/).-\'(/)-?AAAAAAACEEEEIIIIDNOOOOOOUUUUYTsaaaaaaaceeeeiiiidnoooooouuuuytyAaAaAaCcCcCcCcDdDdEeEeEeEeEeGgGgGgGgHhHhIiIiIiIiIiIiJjKk.LlLlLlLlLlNnNnNnOoOoRrRrRrSsSsSsSsTtTtTtUuUuUuUuUuUuWwYyYZzZzZzSsTtAEIIOYOiAVGDEZITHIKLMNXOPRSTYFCHPSOIYaeiiyavgdezithiklmnxoprsstyfchpsoiyoyoABVGDEZHZIYKLMNOPRSTUFHTSCHSHSHTAYYUYAabvgdezhziyklmnoprstufhtschshshtayyuyaE';
        $this->assertSame($result,SepaUtilities::replaceSpecialChars($input));

        // mixed Test
        $input = '[\]^_`{|}~¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿apjmjasdsfkjh2920dsafoKLJSGFOALKJÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿĀāĂăĄąĆćĈĉĊċČčĎďĐđĒēĔĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħĨĩĪīĬĭĮįİıĲĳĴĵĶķĸĹĺĻļĽľĿŀŁłŃńŅņŇňŉŊŋŌōŎŏŐőŒœŔŕŖŗŘřŚśŜŝŞşŠšŢţŤťŦŧŨũŪūŬŭŮůŰűŲųŴŵŶŷŸŹźŻżŽžſƀƁƂƃƄƅƆƇƈƉƊƋƌƍƎƏƐƑƒƓƔƕƖƗƘƙƚƛƜƝƞƟƠơƢƣƤƥƦƧƨƩƪƫƬƭƮƯưƱƲƳƴƵƶƷƸƹƺƻƼƽƾƿǀǁǂǃǄǅǆǇǈǉǊǋǌǍǎǏǐǑǒǓǔǕǖǗǘǙǚǛǜǝǞǟǠǡǢǣǤǥǦǧǨǩǪǫǬǭǮǯǰǱǲǳǴǵǶǷǸǹǺǻǼǽǾǿȀȁȂȃȄȅȆȇȈȉȊȋȌȍȎȏȐȑȒȓȔȕȖȗȘșȚțȜȝȞȟȢȣȤȥȦȧȨȩȪȫȬȭȮȯȰȱȲȳ';
        $result = '(/).-\'(/)-..............................?apjmjasdsfkjh2920dsafoKLJSGFOALKJAAAAAAACEEEEIIIIDNOOOOO.OUUUUYTsaaaaaaaceeeeiiiidnooooo.ouuuuytyAaAaAaCcCcCcCcDdDdEeEeEeEeEeGgGgGgGgHhHhIiIiIiIiIiIiJjKk.LlLlLlLlLlNnNnNn.......OoOoRrRrRrSsSsSsSsTtTtTtUuUuUuUuUuUuWwYyYZzZzZz.........................................................................................................................................................SsTt......................';
        $this->assertSame($result,SepaUtilities::replaceSpecialChars($input));

        // Test german characters flag
        $this->assertSame('AaOoUus',SepaUtilities::replaceSpecialChars('ÄäÖöÜüß'));
        $this->assertSame('AeaeOeoeUeuess',SepaUtilities::replaceSpecialChars('ÄäÖöÜüß',SepaUtilities::FLAG_ALT_REPLACEMENT_GERMAN));

    }

    public function testGetDateWithOffset()
    {
        // today
        $this->assertSame(date('Y-m-d'),SepaUtilities::getDateWithOffset(0));

        // fixed day
        $this->assertSame('2014-10-15',SepaUtilities::getDateWithOffset(0, '15.10.2014'));

        // fixed sunday
        $this->assertSame('2014-10-20',SepaUtilities::getDateWithOffset(0, '19.10.2014'));

        // offset to small to skip a sunday
        $this->assertSame('2014-10-17',SepaUtilities::getDateWithOffset(2, '15.10.2014'));

        // offset little to small to skip a sunday
        $this->assertSame('2014-10-18',SepaUtilities::getDateWithOffset(3, '15.10.2014'));

        // offset to skip one sunday
        $this->assertSame('2014-10-20',SepaUtilities::getDateWithOffset(4, '15.10.2014'));

        // offset big enough to skip a sunday + a day
        $this->assertSame('2014-10-21',SepaUtilities::getDateWithOffset(5, '15.10.2014'));

        // offset big enough to skip 4 sundays plus another sunday
        $this->assertSame('2014-11-17',SepaUtilities::getDateWithOffset(28, '15.10.2014'));
    }

    public function testGetDateWithMinOffsetFromToday()
    {
        // to small offset
        $this->assertSame('2014-10-23',SepaUtilities::getDateWithMinOffsetFromToday('23.10.2014',
                                                                                    3, 'd.m.Y',
                                                                                    '15.10.2014'));

        // to target and earliest date are equal
        $this->assertSame('2014-10-23',SepaUtilities::getDateWithMinOffsetFromToday('23.10.2014',
                                                                                    7, 'd.m.Y',
                                                                                    '15.10.2014'));

        // to target < earliest date
        $this->assertSame('2014-10-24',SepaUtilities::getDateWithMinOffsetFromToday('23.10.2014',
                                                                                    8, 'd.m.Y',
                                                                                    '15.10.2014'));

        // to target < earliest date
        $this->assertSame('2014-10-27',SepaUtilities::getDateWithMinOffsetFromToday('23.10.2014',
                                                                                    10, 'd.m.Y',
                                                                                    '15.10.2014'));
    }




}
 