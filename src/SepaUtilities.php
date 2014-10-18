<?php

namespace AbcAeffchen\SepaUtilities;

/**
 * Useful methods to validate an sanitize input used in SEPA files
 */
class SepaUtilities
{
    // credit transfers version
    const SEPA_PAIN_001_002_03 = 100203;
    const SEPA_PAIN_001_003_03 = 100303;
    // direct debit versions
    const SEPA_PAIN_008_002_02 = 800202;
    const SEPA_PAIN_008_003_02 = 800302;

    const HTML_PATTERN_IBAN = '([a-zA-Z]\s*){2}([0-9]\s?){2}\s*([a-zA-Z0-9]\s*){1,30}';
    const HTML_PATTERN_BIC = '([a-zA-Z]\s*){6}[a-zA-Z2-9]\s*[a-nA-Np-zP-Z0-9]\s*(([A-Z0-9]\s*){3}){0,1}';

    const PATTERN_IBAN = '[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}';
    const PATTERN_BIC  = '[A-Z]{6}[A-Z2-9][A-NP-Z0-9]([A-Z0-9]{3}){0,1}';
    /**
     * equates to RestrictedPersonIdentifierSEPA
     */
    const PATTERN_CREDITOR_IDENTIFIER  = '[a-zA-Z]{2,2}[0-9]{2,2}([A-Za-z0-9]|[\+|\?|/|\-|:|\(|\)|\.|,|\']){3,3}([A-Za-z0-9]|[\+|\?|/|\-|:|\(|\)|\.|,|\']){1,28}';
    const PATTERN_SHORT_TEXT  = '[a-zA-Z0-9/\-?:().,\'+\s]{0,70}';
    const PATTERN_LONG_TEXT  = '[a-zA-Z0-9/\-?:().,\'+\s]{0,140}';
    /**
     * Used for Message-, Payment- and Transfer-IDs
     * equates to checkRestrictedIdentificationSEPA1
     */
    const PATTERN_FILE_IDS = '([A-Za-z0-9]|[\+|\?|/|\-|:|\(|\)|\.|,|\'|\s]){1,35}';
    /**
     * equates to checkRestrictedIdentificationSEPA2
     */
    const PATTERN_MANDATE_ID = '([A-Za-z0-9]|[\+|\?|/|\-|:|\(|\)|\.|,|\']){1,35}';

    const FLAG_ALT_REPLACEMENT_GERMAN = 1;

    const SEQUENCE_TYPE_FIRST     = 'FRST';
    const SEQUENCE_TYPE_RECURRING = 'RCUR';
    const SEQUENCE_TYPE_ONCE      = 'OOFF';
    const SEQUENCE_TYPE_FINAL     = 'FNAL';

    const LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT     = 'CORE';
    const LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT_D_1 = 'COR1';
    const LOCAL_INSTRUMENT_BUSINESS_2_BUSINESS   = 'B2B';
    /**
     * @type int BIC_REQUIRED_THRESHOLD Until 2016-01-31 (incl.) the BIC is required for international
     *           payment transactions
     */
    const BIC_REQUIRED_THRESHOLD = 20160131;
    /*
     * Checks if an creditor identifier (ci) is valid. Note that also if the ci is valid it does
     * not have to exist
     *
     * @param string $ci
     * @return string|false The valid iban or false if it is not valid
     */
    public static function checkCreditorIdentifier( $ci )
    {
        $alph =         array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
                              'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
                              'U', 'V', 'W', 'X', 'Y', 'Z');
        $alphValues =  array( 10,  11,  12,  13,  14,  15,  16,  17,  18,  19,
                              20,  21,  22,  23,  24,  25,  26,  27,  28,  29,
                              30,  31,  32,  33,  34,  35);

        $ci = preg_replace('/\s+/u', '', $ci);   // remove whitespaces
        $ci = strtoupper($ci);                  // todo does this breaks the ci?

        if(!self::checkRestrictedPersonIdentifierSEPA($ci))
            return false;

        $ciCopy = $ci;

        // remove creditor business code
        $nationalIdentifier = substr($ci, 7);
        $check = substr($ci, 0,4);
        $concat = $nationalIdentifier . $check;

        $concat = preg_replace('#[^a-zA-Z0-9]#u','',$concat);      // remove all non-alpha-numeric characters

        $concat = $check = str_replace($alph, $alphValues, $concat);

        if(self::iso7064Mod97m10ChecksumCheck($concat))
            return $ciCopy;
        else
            return false;
    }

    /**
     * Checks if an iban is valid. Note that also if the iban is valid it does not have to exist
     * @param string $iban
     * @return string|false The valid iban or false if it is not valid
     */
    public static function checkIBAN( $iban )
    {
        $alph =         array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
                              'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
                              'U', 'V', 'W', 'X', 'Y', 'Z');
        $alphValues =  array( 10,  11,  12,  13,  14,  15,  16,  17,  18,  19,
                              20,  21,  22,  23,  24,  25,  26,  27,  28,  29,
                              30,  31,  32,  33,  34,  35);

        $iban = preg_replace('/\s+/u', '' , $iban );     // remove whitespaces
        $iban = strtoupper($iban);

        if(!preg_match('/^' . self::PATTERN_IBAN . '$/',$iban))
            return false;

        $ibanCopy = $iban;
        $iban = $check = str_replace($alph, $alphValues, $iban);

        $bban = substr($iban, 6);
        $check = substr($iban, 0,6);

        $concat = $bban . $check;

        if(self::iso7064Mod97m10ChecksumCheck($concat))
            return $ibanCopy;
        else
            return false;
    }

    private static function iso7064Mod97m10ChecksumCheck($input)
    {
        $mod97 = array(1, 10, 3, 30, 9, 90, 27, 76, 81, 34, 49, 5, 50, 15, 53, 45, 62, 38,
                       89, 17, 73, 51, 25, 56, 75, 71, 31, 19, 93, 57, 85, 74, 61, 28, 86,
                       84, 64, 58, 95, 77, 91, 37, 79, 14, 43, 42, 32, 29, 96, 87, 94, 67,
                       88, 7, 70, 21, 16, 63, 48, 92, 47, 82, 44, 52, 35, 59, 8, 80, 24);

        $checksum = 0;
        $len = strlen($input);
        for($i = 1; $i  <= $len; $i++)
        {
            $checksum = (($checksum + $mod97[$i-1]*$input[$len-$i]) % 97);
        }

        return ($checksum == 1);
    }

    /**
     * Checks if a bic is valid. Note that also if the bic is valid it does not have to exist
     *
     * @param string $bic
     * @param array  $options Takes the following keys:
     *                        - `allowEmptyBic`: (bool) The BIC can be empty.
     *                        - `forceLongBic`: (bool) If the BIC has exact 8 characters, `forceLongBicStr`
     *                        is added. (default false)
     *                        - `forceLongBicStr`: string (default 'XXX')
     * @internal param bool $forceLongBic If true all 8 character BIC's will extended with 'XXX'
     * @return string|false the valid bic or false if it is not valid
     */
    public static function checkBIC($bic, array $options = null)
    {
        $bic = preg_replace('/\s+/u', '' , $bic );   // remove whitespaces

        if(!empty($options['forceLongBic']))
            $bic .= empty($options['forceLongBicStr']) ? 'XXX' : $options['forceLongBicStr'];

        if(empty($bic) && !empty($options['allowEmptyBic']))
            return '';

        $bic = strtoupper($bic);                    // use only capital letters

        if(preg_match('/^' . self::PATTERN_BIC . '$/', $bic))
            return $bic;
        else
            return false;
    }

    /**
     * Checks if both IBANs are belong to the same country.
     * @param string $iban1
     * @param string $iban2
     * @return bool
     */
    public static function isNationalTransaction($iban1, $iban2)
    {
        // remove whitespaces
        $iban1 = preg_replace('#\s+#','',$iban1);
        $iban2 = preg_replace('#\s+#','',$iban2);

        // check the country code
        if(strtoupper(substr($iban1,0,2)) === strtoupper(substr($iban2,0,2)))
            return true;
        else
            return false;
    }

    /**
     * Checks if IBAN and BIC belong to the same country. If not, the also can not belong to
     * each other.
     *
     * @param string $iban
     * @param string $bic
     * @return bool
     */
    public static function crossCheckIbanBic($iban, $bic)
    {
        // remove whitespaces
        $iban = preg_replace('#\s+#','',$iban);
        $bic  = preg_replace('#\s+#','',$bic);

        // check the country code
        if(strtoupper(substr($iban,0,2)) === strtoupper(substr($bic,4,2)))
            return true;
        else
            return false;
    }

    private static function checkDateFormat($input)
    {
        if($input === \DateTime::createFromFormat('Y-m-d', $input)->format('Y-m-d'))
            return $input;
        else
            return false;
    }

    /**
     * Reformat a date string from a given format to the ISODate format. Notice: 20.13.2014 is
     * valid and becomes 2015-01-20.
     *
     * @param string $date A date string of the given input format
     * @param string $inputFormat default is the german format DD.MM.YYYY
     * @return string|false date as YYYY-MM-DD or false, if the input is not a date.
     */
    public static function getDate($date = null, $inputFormat = 'd.m.Y')
    {
        if(empty($date))
            $dateTimeObj = new \DateTime();
        else
            $dateTimeObj = \DateTime::createFromFormat($inputFormat, $date);

        if($dateTimeObj === false)
            return false;

        return $dateTimeObj->format('Y-m-d');
    }

    /**
     * Computes the next workday (including today) with respect to a workday offset. If today is
     * a sunday, the next day is returned.
     *
     * @param int    $workdayOffset a positive number of workdays to skip.
     * @param string $today         if set, this date is used as today
     * @param string $inputFormat
     * @return string|false YYYY-MM-DD
     */
    public static function getDateWithOffset($workdayOffset, $today = null, $inputFormat = 'd.m.Y')
    {
        if(empty($today))
            $dateTimeObj = new \DateTime();
        else
            $dateTimeObj = \DateTime::createFromFormat($inputFormat, $today);

        if($dateTimeObj === false)
            return false;

        // if today is sunday
        if($dateTimeObj->format('w') === '0')
            $dateTimeObj->modify('+1 day');

        while($workdayOffset > 0)      // todo: this runs in O(offsetDays)... could be faster
        {
            $dateTimeObj->modify('+1 day');

            if($dateTimeObj->format('w') !== '0')
                $workdayOffset--;
        }

        return $dateTimeObj->format('Y-m-d');
    }

    /**
     * Returns the target date, if it has at least the given offset of workdays form today. Else
     * the earliest date that respects the offset is returned.
     *
     * @param string $target
     * @param int    $workdayMinOffset
     * @param string $inputFormat
     * @param string $today
     * @return string
     */
    public static function getDateWithMinOffsetFromToday($target, $workdayMinOffset, $inputFormat = 'd.m.Y', $today = null)
    {
        $targetDateObj = \DateTime::createFromFormat($inputFormat,$target);

        $earliestDate = self::getDateWithOffset($workdayMinOffset, $today, $inputFormat);

        if($targetDateObj === false || $earliestDate === false)
            return false;

        $earliestDateObj = new \DateTime($earliestDate);

        if($targetDateObj->format('w') === '0')
            $targetDateObj->modify('+1 day');

        $diff = $targetDateObj->diff($earliestDateObj);
        if($diff->invert === 1)      // target > earliest
            return $targetDateObj->format('Y-m-d');
        else
            return $earliestDateObj->format('Y-m-d');
    }

    /**
     * Checks if the input holds for the field.
     *
     * @param string $field   Valid fields are: 'orgnlcdtrschmeid_id','ci','msgid','pmtid','pmtinfid',
     *                        'orgnlmndtid','mndtid','initgpty','cdtr','dbtr','orgnlcdtrschmeid_nm',
     *                        'ultmtcdrt','ultmtdebtr','rmtinf','orgnldbtracct_iban','iban','bic',
     *                        'ccy','amendment', 'btchbookg','instdamt','seqtp','lclinstrm',
     *                        'elctrncsgntr','reqdexctndt','purp','ctgypurp','orgnldbtragt'
     * @param mixed  $input
     * @param array  $options see `checkBic()` and `checkLocalInstrument()` for details
     * @return mixed|false The checked input or false, if it is not valid
     */
    public static function check($field, $input, array $options = null)
    {
        $field = strtolower($field);
        switch($field)      // fall-through's are on purpose
        {
            case 'orgnlcdtrschmeid_id':
            case 'ci': return self::checkCreditorIdentifier($input);
            case 'msgid':
            case 'pmtid':   // next line
            case 'pmtinfid': return self::checkRestrictedIdentificationSEPA1($input);
            case 'orgnlmndtid':
            case 'mndtid': return self::checkRestrictedIdentificationSEPA2($input);
            case 'initgpty':                                // cannot be empty (and the following things also)
            case 'cdtr':                                    // cannot be empty (and the following things also)
            case 'dbtr': if(empty($input)) return false;    // cannot be empty
            case 'orgnlcdtrschmeid_nm':
            case 'ultmtcdtr':
            case 'ultmtdbtr': return (self::checkLength($input, 70) && self::checkCharset($input)) ? $input : false;
            case 'rmtinf': return (self::checkLength($input, 140) && self::checkCharset($input)) ? $input : false;
            case 'orgnldbtracct_iban':
            case 'iban': return self::checkIBAN($input);
            case 'bic': return self::checkBIC($input,$options);
            case 'ccy': return self::checkActiveOrHistoricCurrencyCode($input);
            case 'amdmntind':
            case 'btchbookg': return self::checkBoolean($input);
            case 'instdamt': return self::checkAmountFormat($input);
            case 'seqtp': return self::checkSeqType($input);
            case 'lclinstrm': return self::checkLocalInstrument($input, $options);
            case 'elctrncsgntr': return (self::checkLength($input, 1025) && self::checkCharset($input)) ? $input : false;
            case 'dtofsgntr':
            case 'reqdcolltndt':
            case 'reqdexctndt': return self::checkDateFormat($input);
            case 'purp': return self::checkPurpose($input);
            case 'ctgypurp': return self::checkCategoryPurpose($input);
            case 'orgnldbtragt': return $input;     // nothing to check here
            default: return false;
        }
    }

    /**
     * Tries to sanitize the the input so it fits in the field.
     *
     * @param string $field Valid fields are: 'ultmtcdrt', 'ultmtdebtr',
     *                      'orgnlcdtrschmeid_nm', 'initgpty', 'cdtr', 'dbtr', 'rmtinf'
     * @param mixed  $input
     * @param int    $flags Flags used in replaceSpecialChars()
     * @return mixed|false The sanitized input or false if the input is not sanitizeable or
     *                      invalid also after sanitizing.
     */
    public static function sanitize($field, $input, $flags = 0)
    {
        $field = strtolower($field);
        switch($field)          // fall-through's are on purpose
        {
            case 'ultmtcdrt':
            case 'ultmtdebtr': return self::sanitizeShortText($input,true,$flags);
            case 'orgnlcdtrschmeid_nm':
            case 'initgpty':
            case 'cdtr':
            case 'dbtr':
                return self::sanitizeShortText($input,false,$flags);
            case 'rmtinf': return self::sanitizeLongText($input,true,$flags);
            default: return false;
        }
    }

    /**
     * Checks the input and if it is not valid it tries to sanitize it.
     *
     * @param string $field all fields check and/or sanitize supports
     * @param mixed  $input
     * @param int    $flags
     * @param array  $options see `checkBic` for details
     * @return mixed|false
     */
    public static function checkAndSanitize($field, $input, $flags = 0, array $options = null)
    {
        $checkedInput = self::check($field, $input, $options);
        if($checkedInput !== false)
            return $checkedInput;

        return self::sanitize($field,$input,$flags);
    }

    /**
     * @param array $inputs A reference to an input array (field => value)
     * @param int   $flags  Flags for sanitizing
     * @param array $options Options for checking
     * @return true|string returns true, if everything is ok or could be sanitized. Otherwise a
     *                     string with fields, that could not be sanitized is returned.
     */
    public static function checkAndSanitizeAll(array &$inputs, $flags = 0, array $options = null)
    {
        $fieldsWithErrors = array();
        foreach($inputs as $field => &$input)
        {
            $input = self::checkAndSanitize($field, $input, $flags, $options);
            if($input === false)
                $fieldsWithErrors[] = $field;
        }

        if(empty($fieldsWithErrors))
            return true;
        else
            return implode(', ', $fieldsWithErrors);
    }

    public static function sanitizeShortText($input,$allowEmpty = false, $flags = 0)
    {
        $res = self::sanitizeLength(self::replaceSpecialChars($input, $flags), 70);

        if($allowEmpty || !empty($res))
            return $res;

        return false;
    }

    public static function sanitizeLongText($input,$allowEmpty = false, $flags = 0)
    {
        $res = self::sanitizeLength(self::replaceSpecialChars($input, $flags), 140);

        if($allowEmpty || !empty($res))
            return $res;

        return false;
    }

    public static function checkRequiredCollectionKeys(array $inputs, $version)
    {
        switch($version)
        {
            case self::SEPA_PAIN_001_002_03:
                $requiredKeys = array('pmtInfId', 'dbtr', 'iban', 'bic');
                break;
            case self::SEPA_PAIN_001_003_03:
                $requiredKeys = array('pmtInfId', 'dbtr', 'iban');
                break;
            case self::SEPA_PAIN_008_002_02:
                $requiredKeys = array('pmtInfId', 'lclInstrm', 'seqTp', 'cdtr', 'iban', 'bic',
                                      'ci');
                break;
            case self::SEPA_PAIN_008_003_02:
                $requiredKeys = array('pmtInfId', 'lclInstrm', 'seqTp', 'cdtr', 'iban', 'ci');
                break;
            default:
                return false;
        }

        return self::containsAllKeys($inputs,$requiredKeys);
    }

    public static function checkRequiredPaymentKeys(array $inputs, $version)
    {
        switch($version)
        {
            case self::SEPA_PAIN_001_002_03:
                $requiredKeys = array('pmtId', 'instdAmt', 'iban', 'bic', 'cdtr');
                break;
            case self::SEPA_PAIN_001_003_03:
                $requiredKeys = array('pmtId', 'instdAmt', 'iban', 'cdtr');
                break;
            case self::SEPA_PAIN_008_002_02:
                $requiredKeys = array('pmtId', 'instdAmt', 'mndtId', 'dtOfSgntr', 'dbtr', 'iban','bic');
                break;
            case self::SEPA_PAIN_008_003_02:
                $requiredKeys = array('pmtId', 'instdAmt', 'mndtId', 'dtOfSgntr', 'dbtr', 'iban');
                break;
            default: return false;
        }

        return self::containsAllKeys($inputs,$requiredKeys);
    }

    /**
     * Checks if $arr misses one of the given $keys
     * @param array $arr
     * @param array $keys
     * @return bool false, if at least one key is missing, else true
     */
    public static function containsAllKeys(array $arr, array $keys)
    {
        foreach($keys as $key)
        {
            if( !isset( $arr[$key] ) )
                return false;
        }

        return true;
    }

    /**
     * Checks if $arr not contains any key of $keys
     * @param array $arr
     * @param array $keys
     * @return bool true, if $arr contains not even on the the keys, else false
     */
    public static function containsNotAnyKey(array $arr, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($arr[$key]))
                return false;
        }

        return true;
    }

    /**
     * Checks if the currency code has a valid format. Also if it has a valid format it has not to exist.
     * If it has a valid format it will also be changed to upper case only.
     * @param string $ccy
     * @return string|false The valid input (in upper case only) or false if it is not valid.
     */
    private static function checkActiveOrHistoricCurrencyCode( $ccy )
    {
        $ccy = strtoupper($ccy);

        if(preg_match('/^[A-Z]{3}$/', $ccy))
            return $ccy;
        else
            return false;
    }

    /**
     * Checks if $bbi is a valid batch booking indicator. Returns 'true' for "1", "true", "on"
     * and "yes", returns 'false' for "0", "false", "off", "no", and ""
     *
     * @param mixed $input
     * @return string|false The batch booking indicator (in lower case only) or false if not
     *                      valid
     */
    private static function checkBoolean($input )
    {
        $bbi = filter_var($input,FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE);

        if($bbi === true)
            return 'true';

        if($bbi === false)
            return 'false';

        return false;
    }

    /**
     * @param string $input
     * @return string|bool
     */
    private static function checkRestrictedIdentificationSEPA1($input)
    {
        if(preg_match('#^' . self::PATTERN_FILE_IDS . '$#',$input))
            return $input;
        else
            return false;
    }

    /**
     * @param string $input
     * @return string|bool
     */
    private static function checkRestrictedIdentificationSEPA2($input)
    {
        if(preg_match('#^' . self::PATTERN_MANDATE_ID . '$#',$input))
            return $input;
        else
            return false;
    }

    /**
     * @param string $input
     * @return string|bool
     */
    private static function checkRestrictedPersonIdentifierSEPA($input)
    {
        if(preg_match('#^' . self::PATTERN_CREDITOR_IDENTIFIER . '$#',$input))
            return $input;
        else
            return false;
    }

    /**
     * Checks if the length of the input string not longer than the entered length
     *
     * @param string $input
     * @param int $maxLen
     * @return bool
     */
    private static function checkLength( $input, $maxLen )
    {
        return !isset($input[$maxLen]);     // takes the string as char array
    }

    /**
     * Shortens the input string to the max length if it is to long.
     * @param string $input
     * @param int $maxLen
     * @return string sanitized string
     */
    public static function sanitizeLength($input, $maxLen)
    {
        if(isset($input[$maxLen]))     // take string as array of chars
            return substr($input,0,$maxLen);
        else
            return $input;
    }

    /**
     * Replaces all special chars like á, ä, â, à, å, ã, æ, Ç, Ø, Š, ", ’ and & with a latin char.
     * All special characters that can not be replaced with a latin char (such like quotes) will
     * be removed as long as they can not converted. See http://www.europeanpaymentscouncil.eu/index.cfm/knowledge-bank/epc-documents/sepa-requirements-for-an-extended-character-set-unicode-subset-best-practices/
     * for more information about converting characters.
     *
     * @param string $str
     * @param int    $flags Use the SepaUtilities::FLAG_ALT_REPLACEMENT_* constants. This will
     *                      ignore the best practice replacement and use a more common one.
     *                      You can use more than one flag by using the | (bitwise or) operator.
     * @return string
     */
    public static function replaceSpecialChars($str, $flags = 0)
    {
        if($flags & self::FLAG_ALT_REPLACEMENT_GERMAN)
            $str = str_replace(array('Ä','ä','Ö','ö','Ü','ü','ß'),
                               array('Ae','ae','Oe','oe','Ue','ue','ss'),
                               $str);

        // remove characters
        $str = str_replace(array('"','&','<','>'),'',$str);

        // replace all kinds of whitespaces by a space
        $str = preg_replace('#\s+#u',' ',$str);

        // special replacement for some characters (incl. greek and cyrillic)
        $search  = array(';','[','\\',']','^','_','`', '{','|','}','~','¿','À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','Þ','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ð','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','þ','ÿ','Ā','ā','Ă','ă','Ą','ą','Ć','ć','Ĉ','ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę','Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī','ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','ĸ','Ĺ','ĺ','Ļ','ļ','Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š','š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű','Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','Ș','ș','Ț','ț','Ά','Έ','Ή','Ί','Ό','Ύ','Ώ','ΐ','Α','Β','Γ','Δ','Ε','Ζ','Η','Θ' ,'Ι','Κ','Λ','Μ','Ν','Ξ','Ο','Π','Ρ','Σ','Τ','Υ','Φ','Χ', 'Ψ', 'Ω','Ϊ','Ϋ','ά','έ','ή','ί','ΰ','α','β','γ','δ','ε','ζ','η','θ', 'ι','κ','λ','μ','ν','ξ','ο','π','ρ','ς','σ','τ','υ','φ','χ', 'ψ', 'ω','ϊ','ϋ','ό','ύ','ώ','А','Б','В','Г','Д','Е','Ж', 'З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц', 'Ч', 'Ш', 'Щ',  'Ъ','Ь','Ю', 'Я', 'а','б','в','г','д','е','ж', 'з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц', 'ч', 'ш', 'щ',  'ъ','ь','ю', 'я', '€');
        $replace = array(',','(','/', ')','.','-','\'','(','/',')','-','?','A','A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','T','s','a','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','d','n','o','o','o','o','o','o','u','u','u','u','y','t','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','I','i','J','j','K','k','.','L','l','L','l','L','l','L','l','L','l','N','n','N','n','N','n','O','o','O','o','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','S','s','T','t','A','E','I','I','O','Y','O','i','A','V','G','D','E','Z','I','TH','I','K','L','M','N','X','O','P','R','S','T','Y','F','CH','PS','O','I','Y','a','e','i','i','y','a','v','g','d','e','z','i','th','i','k','l','m','n','x','o','p','r','s','s','t','y','f','ch','ps','o','i','y','o','y','o','A','B','V','G','D','E','ZH','Z','I','Y','K','L','M','N','O','P','R','S','T','U','F','H','TS','CH','SH','SHT','A','Y','YU','YA','a','b','v','g','d','e','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','ts','ch','sh','sht','a','y','yu','ya','E');
        $str = str_replace($search,$replace,$str);

        // replace everything not allowed in sepa files by . (a dot)
        $str = preg_replace('#[^a-zA-Z0-9/\-?:().,\'+ ]#u','.',$str);

        // remove leading and closing whitespaces
        return trim($str);
    }

    private static function checkCharset($str)
    {
        return (boolean) preg_match('#^[a-zA-Z0-9/\-?:().,\'+ ]*$#', $str);
    }

    /**
     * Checks if the amount fits the format: A float with only two decimals, not lower than 0.01,
     * not greater than 999,999,999.99.
     *
     * @param mixed $amount float or string with or without thousand separator (use , or .). You
     *                      can use '.' or ',' as decimal point, but not one sign as thousand separator
     *                      and decimal point. So 1234.56; 1,234.56; 1.234,56; 1234,56 ar valid
     *                      inputs.
     * @return float|false
     */
    private static function checkAmountFormat( $amount )
    {
        // $amount is a string -> check for '1,234.56'
        $amount = filter_var($amount, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);

        if($amount === false)
            $amount = filter_var(strtr($amount,array(',' => '.', '.' => ',')), FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);

        if($amount === false || $amount < 0.01 || $amount > 999999999.99 || round($amount,2) != $amount)
            return false;

        return $amount;
    }

    /**
     * Checks if the sequence type is valid.
     *
     * @param string $seqTp
     * @return string|false
     */
    private function checkSeqType($seqTp)
    {
        $seqTp = strtoupper($seqTp);

        if( in_array($seqTp, array(self::SEQUENCE_TYPE_FIRST, self::SEQUENCE_TYPE_RECURRING,
                                   self::SEQUENCE_TYPE_ONCE, self::SEQUENCE_TYPE_FINAL)) )
            return $seqTp;

        return false;
    }

    /**
     * @param string $input
     * @param array $options
     * @return bool|string
     */
    private static function checkLocalInstrument($input, array $options = null)
    {
        $version = empty($options['version']) ? self::SEPA_PAIN_008_002_02 : $options['version'];

        $input = strtoupper($input);

        switch($version)
        {
            case self::SEPA_PAIN_008_002_02:
                $validCases = array(self::LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT,
                                    self::LOCAL_INSTRUMENT_BUSINESS_2_BUSINESS);
                break;
            case self::SEPA_PAIN_008_003_02:
                $validCases = array(self::LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT,
                                    self::LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT_D_1,
                                    self::LOCAL_INSTRUMENT_BUSINESS_2_BUSINESS);
                break;
            default:
                return false;
        }

        if( in_array($input, $validCases) )
            return $input;

        return false;
    }

    private static function checkCategoryPurpose($input)
    {
        $validValues = array('BONU', 'CASH', 'CBLK', 'CCRD', 'CORT', 'DCRD', 'DIVI', 'EPAY',
                             'FCOL', 'GOVT', 'HEDG', 'ICCP', 'IDCP', 'INTC', 'INTE', 'LOAN',
                             'OTHR', 'PENS', 'SALA', 'SECU', 'SSBE', 'SUPP', 'TAXS', 'TRAD',
                             'TREA', 'VATX', 'WHLD');

        $input = strtoupper($input);

        if(in_array($input,$validValues))
            return $input;

        return false;
    }

    private static function checkPurpose($input)
    {
        $validValues = array('CBLK', 'CDCB', 'CDCD', 'CDCS', 'CDDP', 'CDOC', 'CDQC', 'ETUP',
                             'FCOL', 'MTUP', 'ACCT', 'CASH', 'COLL', 'CSDB', 'DEPT', 'INTC',
                             'LIMA', 'NETT', 'AGRT', 'AREN', 'BEXP', 'BOCE', 'COMC', 'CPYR',
                             'GDDS', 'GDSV', 'GSCB', 'LICF', 'POPE', 'ROYA', 'SCVE', 'SUBS',
                             'SUPP', 'TRAD', 'CHAR', 'COMT', 'CLPR', 'DBTC', 'GOVI', 'HLRP',
                             'INPC', 'INSU', 'INTE', 'LBRI', 'LIFI', 'LOAN', 'LOAR', 'PENO',
                             'PPTI', 'RINP', 'TRFD', 'ADMG', 'ADVA', 'BLDM', 'CBFF', 'CBFR',
                             'CCRD', 'CDBL', 'CFEE', 'CGDD', 'COST', 'CPKC', 'DCRD', 'EDUC',
                             'FAND', 'FCPM', 'GOVT', 'ICCP', 'IDCP', 'IHRP', 'INSM', 'IVPT',
                             'MSVC', 'NOWS', 'OFEE', 'OTHR', 'PADD', 'PTSP', 'RCKE', 'RCPT',
                             'REBT', 'REFU', 'RENT', 'RIMB', 'STDY', 'TBIL', 'TCSC', 'TELI',
                             'WEBI', 'ANNI', 'CAFI', 'CFDI', 'CMDT', 'DERI', 'DIVD', 'FREX',
                             'HEDG', 'INVS', 'PRME', 'SAVG', 'SECU', 'SEPI', 'TREA', 'ANTS',
                             'CVCF', 'DMEQ', 'DNTS', 'HLTC', 'HLTI', 'HSPC', 'ICRF', 'LTCF',
                             'MDCS', 'VIEW', 'ALLW', 'ALMY', 'BBSC', 'BECH', 'BENE', 'BONU',
                             'COMM', 'CSLP', 'GVEA', 'GVEB', 'GVEC', 'GVED', 'PAYR', 'PENS',
                             'PRCP', 'SALA', 'SSBE', 'AEMP', 'GFRP', 'GWLT', 'RHBS', 'ESTX',
                             'FWLV', 'GSTX', 'HSTX', 'INTX', 'NITX', 'PTXP', 'RDTX', 'TAXS',
                             'VATX', 'WHLD', 'TAXR', 'AIRB', 'BUSB', 'FERB', 'RLWY', 'TRPT',
                             'CBTV', 'ELEC', 'ENRG', 'GASB', 'NWCH', 'NWCM', 'OTLC', 'PHON',
                             'UBIL', 'WTER');

        $input = strtoupper($input);

        if( in_array($input, $validValues) )
            return $input;

        return false;
    }

}