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

        // test valid IBANs from different countries by formatting
        $options = array('checkByCheckSum' => false, 'checkByFormat' => true);
        $this->assertSame('AD1200012030200359100100',SepaUtilities::checkIBAN('AD12 0001 2030 2003 5910 0100',$options));
        $this->assertSame('AL47212110090000000235698741',SepaUtilities::checkIBAN('AL47 2121 1009 0000 0002 3569 8741',$options));
        $this->assertSame('AT611904300234573201',SepaUtilities::checkIBAN('AT61 1904 3002 3457 3201',$options));
        $this->assertSame('BA391290079401028494',SepaUtilities::checkIBAN('BA39 1290 0794 0102 8494',$options));
        $this->assertSame('BE68539007547034',SepaUtilities::checkIBAN('BE68 5390 0754 7034',$options));
        $this->assertSame('BG80BNBG96611020345678',SepaUtilities::checkIBAN('BG80 BNBG 9661 1020 3456 78',$options));
        $this->assertSame('CH9300762011623852957',SepaUtilities::checkIBAN('CH93 0076 2011 6238 5295 7',$options));
        $this->assertSame('CY17002001280000001200527600',SepaUtilities::checkIBAN('CY17 0020 0128 0000 0012 0052 7600',$options));
        $this->assertSame('CZ6508000000192000145399',SepaUtilities::checkIBAN('CZ65 0800 0000 1920 0014 5399',$options));
        $this->assertSame('DE89370400440532013000',SepaUtilities::checkIBAN('DE89 3704 0044 0532 0130 00',$options));
        $this->assertSame('DK5000400440116243',SepaUtilities::checkIBAN('DK50 0040 0440 1162 43',$options));
        $this->assertSame('EE382200221020145685',SepaUtilities::checkIBAN('EE38 2200 2210 2014 5685',$options));
        $this->assertSame('ES9121000418450200051332',SepaUtilities::checkIBAN('ES91 2100 0418 4502 0005 1332',$options));
        $this->assertSame('FI2112345600000785',SepaUtilities::checkIBAN('FI21 1234 5600 0007 85',$options));
        $this->assertSame('FO6264600001631634',SepaUtilities::checkIBAN('FO62 6460 0001 6316 34',$options));
        $this->assertSame('FR1420041010050500013M02606',SepaUtilities::checkIBAN('FR14 2004 1010 0505 0001 3M02 606',$options));
        $this->assertSame('GB29NWBK60161331926819',SepaUtilities::checkIBAN('GB29 NWBK 6016 1331 9268 19',$options));
        $this->assertSame('GE29NB0000000101904917',SepaUtilities::checkIBAN('GE29 NB00 0000 0101 9049 17',$options));
        $this->assertSame('GI75NWBK000000007099453',SepaUtilities::checkIBAN('GI75 NWBK 0000 0000 7099 453',$options));
        $this->assertSame('GL8964710001000206',SepaUtilities::checkIBAN('GL89 6471 0001 0002 06',$options));
        $this->assertSame('GR1601101250000000012300695',SepaUtilities::checkIBAN('GR16 0110 1250 0000 0001 2300 695',$options));
        $this->assertSame('HR1210010051863000160',SepaUtilities::checkIBAN('HR12 1001 0051 8630 0016 0',$options));
        $this->assertSame('HU42117730161111101800000000',SepaUtilities::checkIBAN('HU42 1177 3016 1111 1018 0000 0000',$options));
        $this->assertSame('IE29AIBK93115212345678',SepaUtilities::checkIBAN('IE29 AIBK 9311 5212 3456 78',$options));
        $this->assertSame('IL620108000000099999999',SepaUtilities::checkIBAN('IL62 0108 0000 0009 9999 999',$options));
        $this->assertSame('IS140159260076545510730339',SepaUtilities::checkIBAN('IS14 0159 2600 7654 5510 7303 39',$options));
        $this->assertSame('IT60X0542811101000000123456',SepaUtilities::checkIBAN('IT60 X054 2811 1010 0000 0123 456',$options));
        $this->assertSame('KW81CBKU0000000000001234560101',SepaUtilities::checkIBAN('KW81 CBKU 0000 0000 0000 1234 5601 01',$options));
        $this->assertSame('KZ86125KZT5004100100',SepaUtilities::checkIBAN('KZ86 125K ZT50 0410 0100',$options));
        $this->assertSame('LB62099900000001001901229114',SepaUtilities::checkIBAN('LB62 0999 0000 0001 0019 0122 9114',$options));
        $this->assertSame('LI21088100002324013AA',SepaUtilities::checkIBAN('LI21 0881 0000 2324 013A A',$options));
        $this->assertSame('LT121000011101001000',SepaUtilities::checkIBAN('LT12 1000 0111 0100 1000',$options));
        $this->assertSame('LU280019400644750000',SepaUtilities::checkIBAN('LU28 0019 4006 4475 0000',$options));
        $this->assertSame('LV80BANK0000435195001',SepaUtilities::checkIBAN('LV80 BANK 0000 4351 9500 1',$options));
        $this->assertSame('MC1112739000700011111000H79',SepaUtilities::checkIBAN('MC11 1273 9000 7000 1111 1000 h79',$options));
        $this->assertSame('ME25505000012345678951',SepaUtilities::checkIBAN('ME25 5050 0001 2345 6789 51',$options));
        $this->assertSame('MK07250120000058984',SepaUtilities::checkIBAN('MK07 2501 2000 0058 984',$options));
        $this->assertSame('MR1300020001010000123456753',SepaUtilities::checkIBAN('MR13 0002 0001 0100 0012 3456 753',$options));
        $this->assertSame('MT84MALT011000012345MTLCAST001S',SepaUtilities::checkIBAN('MT84 MALT 0110 0001 2345 MTLC AST0 01S',$options));
        $this->assertSame('MU17BOMM0101101030300200000MUR',SepaUtilities::checkIBAN('MU17 BOMM 0101 1010 3030 0200 000M UR',$options));
        $this->assertSame('NL91ABNA0417164300',SepaUtilities::checkIBAN('NL91 ABNA 0417 1643 00',$options));
        $this->assertSame('NO9386011117947',SepaUtilities::checkIBAN('NO93 8601 1117 947',$options));
        $this->assertSame('PL61109010140000071219812874',SepaUtilities::checkIBAN('PL61 1090 1014 0000 0712 1981 2874',$options));
        $this->assertSame('PT50000201231234567890154',SepaUtilities::checkIBAN('PT50 0002 0123 1234 5678 9015 4',$options));
        $this->assertSame('RO49AAAA1B31007593840000',SepaUtilities::checkIBAN('RO49 AAAA 1B31 0075 9384 0000',$options));
        $this->assertSame('RS35260005601001611379',SepaUtilities::checkIBAN('RS35 2600 0560 1001 6113 79',$options));
        $this->assertSame('SA0380000000608010167519',SepaUtilities::checkIBAN('SA03 8000 0000 6080 1016 7519',$options));
        $this->assertSame('SE4550000000058398257466',SepaUtilities::checkIBAN('SE45 5000 0000 0583 9825 7466',$options));
        $this->assertSame('SI56191000000123438',SepaUtilities::checkIBAN('SI56 1910 0000 0123 438',$options));
        $this->assertSame('SK3112000000198742637541',SepaUtilities::checkIBAN('SK31 1200 0000 1987 4263 7541',$options));
        $this->assertSame('SM86U0322509800000000270100',SepaUtilities::checkIBAN('SM86 U032 2509 8000 0000 0270 100',$options));
        $this->assertSame('TN5910006035183598478831',SepaUtilities::checkIBAN('TN59 1000 6035 1835 9847 8831',$options));
        $this->assertSame('TR330006100519786457841326',SepaUtilities::checkIBAN('TR33 0006 1005 1978 6457 8413 26',$options));
    }

    public function testCheckBIC()
    {
        // Valid
        $this->assertSame('ASDFGHJ0', SepaUtilities::checkBIC('ASDFGHJ0'));

        // Valid, but bad formatting
        $this->assertSame('ASDFGHJ0', SepaUtilities::checkBIC('A SdFG Hj0'));

        // Invalid (0 (zero) changed to O (oh)
        $this->assertFalse(SepaUtilities::checkBIC('ASDFGHJO'));

        // options
        $this->assertSame('ASDFGHJ0XXX', SepaUtilities::checkBIC('ASDFGHJ0',array('forceLongBic' => true)));
        $this->assertSame('ASDFGHJ0ABC', SepaUtilities::checkBIC('ASDFGHJ0ABC',array('forceLongBic' => true)));
        $this->assertSame('ASDFGHJ0ABC', SepaUtilities::checkBIC('ASDFGHJ0',array('forceLongBic' => true, 'forceLongBicStr' => 'ABC')));
        $this->assertSame('ASDFGHJ0XXX', SepaUtilities::checkBIC('ASDFGHJ0XXX',array('forceLongBic' => true, 'forceLongBicStr' => 'ABC')));
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
        $this->assertFalse(SepaUtilities::containsAllKeys(array('a' => 1, 'b' => 2, 'd' => 2),
                                                            array('a', 'b', 'c')));

        $this->assertTrue(SepaUtilities::containsAllKeys(array('a' => 1, 'b' => 2, 'd' => 2),
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
        // fixed day
        $this->assertSame('2014-10-15',SepaUtilities::getDateWithOffset(0, '15.10.2014'));

        // fixed saturday
        $this->assertSame('2014-10-20',SepaUtilities::getDateWithOffset(0, '18.10.2014'));
        // fixed sunday
        $this->assertSame('2014-10-20',SepaUtilities::getDateWithOffset(0, '19.10.2014'));

        // offset to small to skip a sunday
        $this->assertSame('2014-10-17',SepaUtilities::getDateWithOffset(2, '15.10.2014'));

        // offset reaches weekend
        $this->assertSame('2014-10-20',SepaUtilities::getDateWithOffset(3, '15.10.2014'));

        // offset to skip one weekend
        $this->assertSame('2014-10-21',SepaUtilities::getDateWithOffset(4, '15.10.2014'));

        // offset big enough to skip a sunday + a day
        $this->assertSame('2014-10-22',SepaUtilities::getDateWithOffset(5, '15.10.2014'));

        // offset big enough to skip 4 weekends plus another sunday
        $this->assertSame('2014-11-24',SepaUtilities::getDateWithOffset(28, '15.10.2014'));
    }

    public function testGetDateWithMinOffsetFromToday()
    {
        // to small offset
        $this->assertSame('2014-10-23',SepaUtilities::getDateWithMinOffsetFromToday('23.10.2014',
                                                                                    3, 'd.m.Y',
                                                                                    '15.10.2014'));

        // to target and earliest date are equal
        $this->assertSame('2014-10-23',SepaUtilities::getDateWithMinOffsetFromToday('23.10.2014',
                                                                                    6, 'd.m.Y',
                                                                                    '15.10.2014'));

        // to target < earliest date
        $this->assertSame('2014-10-27',SepaUtilities::getDateWithMinOffsetFromToday('23.10.2014',
                                                                                    8, 'd.m.Y',
                                                                                    '15.10.2014'));

        // to target < earliest date
        $this->assertSame('2014-10-29',SepaUtilities::getDateWithMinOffsetFromToday('23.10.2014',
                                                                                    10, 'd.m.Y',
                                                                                    '15.10.2014'));
    }

    public function testCheckCcy()
    {
        $this->assertSame('EUR',SepaUtilities::check('ccy', 'EUR'));
        $this->assertSame('EUR',SepaUtilities::check('ccy', 'Eur'));
        $this->assertFalse(SepaUtilities::check('ccy', 'Eu'));
        $this->assertFalse(SepaUtilities::check('ccy', '€'));
        $this->assertFalse(SepaUtilities::check('ccy', 'euro'));
    }

    public function testCheckRequiredCollectionKeys()
    {
        $collectionInfo1 = array(
            'pmtInfId'      => 'PaymentID-1234',    // ID of the payment collection
            'dbtr'          => 'Name of Debtor2',   // (max 70 characters)
            'iban'          => 'DE21500500001234567897',// IBAN of the Debtor
            'bic'           => 'BELADEBEXXX',       // BIC of the Debtor
        );
        $collectionInfo2 = array(
            'pmtInfId'      => 'PaymentID-1234',    // ID of the payment collection
            'dbtr'          => 'Name of Debtor2',   // (max 70 characters)
            'iban'          => 'DE21500500001234567897',// IBAN of the Debtor
        );

        $this->assertTrue(SepaUtilities::checkRequiredCollectionKeys($collectionInfo1,SepaUtilities::SEPA_PAIN_001_002_03));
        $this->assertFalse(SepaUtilities::checkRequiredCollectionKeys($collectionInfo2,SepaUtilities::SEPA_PAIN_001_002_03));
        $this->assertTrue(SepaUtilities::checkRequiredCollectionKeys($collectionInfo2,SepaUtilities::SEPA_PAIN_001_003_03));

    }

    public function testCheckRequiredPaymentKeys()
    {
        $directDebitPayment = array(
            // needed information about the
            'pmtId'         => 'TransferID-1235-1',     // ID of the payment (EndToEndId)
            'instdAmt'      => 2.34,                    // amount
            'mndtId'        => 'Mandate-Id',            // Mandate ID
            'dtOfSgntr'     => '2010-04-12',            // Date of signature
            'bic'           => 'BELADEBEXXX',           // BIC of the Debtor
            'dbtr'          => 'Name of Debtor',        // (max 70 characters)
            'iban'          => 'DE87200500001234567890',// IBAN of the Debtor
            // optional
            'amdmntInd'     => 'false',                 // Did the mandate change
            'elctrncSgntr'  => 'test',                  // do not use this if there is a paper-based mandate
            'ultmtDbtr'     => 'Ultimate Debtor Name',  // just an information, this do not affect the payment (max 70 characters)
            //'purp'        => ,                        // Do not use this if you not know how. For further information read the SEPA documentation
            'rmtInf'        => 'Remittance Information',// unstructured information about the remittance (max 140 characters)
            // only use this if 'amdmntInd' is 'true'. at least one must be used
            'orgnlMndtId'           => 'Original-Mandat-ID',
            'orgnlCdtrSchmeId_nm'   => 'Creditor-Identifier Name',
            'orgnlCdtrSchmeId_id'   => 'DE98AAA09999999999',
            'orgnlDbtrAcct_iban'    => 'DE87200500001234567890',// Original Debtor Account
            'orgnlDbtrAgt'          => 'SMNDA'          // only 'SMNDA' allowed if used

        );

        $this->assertTrue(SepaUtilities::checkRequiredPaymentKeys($directDebitPayment,SepaUtilities::SEPA_PAIN_008_002_02));
    }

    public function testCheckAndSanitizeAll()
    {
        $collectionInfo = array(
            // needed information about the payer
            'pmtInfId'      => 'PaymentID-1234',    // ID of the payment collection
            'dbtr'          => 'Name of Debtor2',   // (max 70 characters)
            'iban'          => 'DE21500500001234567897',// IBAN of the Debtor
            'bic'           => 'BELADEBEXXX',       // BIC of the Debtor
            // optional
            'ccy'           => 'EUR',               // Currency. Default is 'EUR'
            'btchBookg'     => 'true',              // BatchBooking, only 'true' or 'false'
            //'ctgyPurp'      => ,                  // Do not use this if you do not know how. For further information read the SEPA documentation
            'reqdExctnDt'   => '2013-11-25',        // Date: YYYY-MM-DD
            'ultmtDebtr'    => 'Ultimate Debtor Name'   // just an information, this do not affect the payment (max 70 characters)
        );

        $directDebitPaymentInformation = array(
            // needed information about the
            'pmtId'         => 'TransferID-1235-1',     // ID of the payment (EndToEndId)
            'instdAmt'      => 2.34,                    // amount
            'mndtId'        => 'Mandate-Id',            // Mandate ID
            'dtOfSgntr'     => '2010-04-12',            // Date of signature
            'bic'           => 'BELADEBEXXX',           // BIC of the Debtor
            'dbtr'          => 'Name of Debtor',        // (max 70 characters)
            'iban'          => 'DE87200500001234567890',// IBAN of the Debtor
            // optional
            'amdmntInd'     => 'false',                 // Did the mandate change
            'elctrncSgntr'  => 'test',                  // do not use this if there is a paper-based mandate
            'ultmtDbtr'     => 'Ultimate Debtor Name',  // just an information, this do not affect the payment (max 70 characters)
            //'purp'        => ,                        // Do not use this if you not know how. For further information read the SEPA documentation
            'rmtInf'        => 'Remittance Information',// unstructured information about the remittance (max 140 characters)
            // only use this if 'amdmntInd' is 'true'. at least one must be used
            'orgnlMndtId'           => 'Original-Mandat-ID',
            'orgnlCdtrSchmeId_nm'   => 'Creditor-Identifier Name',
            'orgnlCdtrSchmeId_id'   => 'DE98AAA09999999999',
            'orgnlDbtrAcct_iban'    => 'DE87200500001234567890',// Original Debtor Account
            'orgnlDbtrAgt'          => 'SMNDA'          // only 'SMNDA' allowed if used
        );

        $this->assertTrue(SepaUtilities::checkAndSanitizeAll($collectionInfo));
        $this->assertTrue(SepaUtilities::checkAndSanitizeAll($directDebitPaymentInformation));
    }

    public function testCheckCreateDateTime()
    {
        $this->assertSame('2014-10-19T00:36:11',SepaUtilities::checkCreateDateTime('2014-10-19T00:36:11'));
    }

    public function testCrossIbanBicCheck()
    {
        // IBAN and BIC is not validated. It is only checked if the country codes belong together.

        // valid
        $this->assertTrue(SepaUtilities::crossCheckIbanBic('FR','1234FR12XXX'));
        $this->assertTrue(SepaUtilities::crossCheckIbanBic('FR','1234TF12XXX'));
        $this->assertTrue(SepaUtilities::crossCheckIbanBic('GB','1234GG12XXX'));

        // invalid
        $this->assertFalse(SepaUtilities::crossCheckIbanBic('DE','1234ED12XXX'));
        $this->assertFalse(SepaUtilities::crossCheckIbanBic('FR','1234AD12XXX'));
    }
}
 