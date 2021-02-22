<?php

declare(strict_types = 1);

/**
 * SepaUtilities
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2020 Alexander Schickedanz
 * @link      https://github.com/AbcAeffchen/SepaUtilities
 *
 * @author    Alexander Schickedanz <abcaeffchen@gmail.com>
 */

namespace AbcAeffchen\SepaUtilities;

use DateTime;
use Exception;

/**
 * Returns a DateTime object of easter sunday in the given year.
 * This is calculated with the Gaussian Algorithm.
 *
 * @param int $year The year written with four digits.
 * @return DateTime DateTime object pointing to easter sunday of the $year.
 */
function easterDate(int $year) : DateTime {
    $G = $year % 19;
    $C = (int)($year / 100);
    $H = (int)($C - (int)($C / 4) - (int)((8*$C+13) / 25) + 19*$G + 15) % 30;
    $I = (int)$H - (int)($H / 28)*(1 - (int)($H / 28)*(int)(29 / ($H + 1))*(int)((21 - $G) / 11));
    $J = ($year + (int)($year/4) + $I + 2 - $C + (int)($C/4)) % 7;
    $L = $I - $J;
    $m = 3 + (int)(($L + 40) / 44);
    $d = $L + 28 - 31 * ((int)($m / 4));

    return DateTime::createFromFormat('Y-n-j', $year . '-' . $m .'-' . $d);
}

/**
 * Useful methods to validate an sanitize input used in SEPA files
 */
class SepaUtilities
{
    // credit transfers version
    const SEPA_PAIN_001_002_03       = 100203;
    const SEPA_PAIN_001_003_03       = 100303;
    const SEPA_PAIN_001_001_03       = 100103;
    const SEPA_PAIN_001_001_03_GBIC  = 1001031;
	const SEPA_PAIN_001_001_03_CH_02 = 1001032;
    // direct debit versions
    const SEPA_PAIN_008_002_02              = 800202;
    const SEPA_PAIN_008_003_02              = 800302;
    const SEPA_PAIN_008_001_02              = 800102;
    const SEPA_PAIN_008_001_02_GBIC         = 8001021;
    const SEPA_PAIN_008_001_02_AUSTRIAN_003 = 8001022;
    const SEPA_PAIN_008_001_02_CH_03        = 8001023;

    const SEPA_TRANSACTION_TYPE_CT = 1;
    const SEPA_TRANSACTION_TYPE_DD = 8;

    const HTML_PATTERN_IBAN = '([a-zA-Z]\s*){2}([0-9]\s?){2}\s*([a-zA-Z0-9]\s*){1,30}';
    const HTML_PATTERN_BIC = '([a-zA-Z]\s*){6}[a-zA-Z2-9]\s*[a-nA-Np-zP-Z0-9]\s*(([A-Z0-9]\s*){3}){0,1}';

    const PATTERN_IBAN = '[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}';
    const PATTERN_BIC  = '[A-Z]{6}[A-Z2-9][A-NP-Z0-9]([A-Z0-9]{3}){0,1}';
    /**
     * equates to RestrictedPersonIdentifierSEPA
     */
    const PATTERN_CREDITOR_IDENTIFIER  = '[a-zA-Z]{2,2}[0-9]{2,2}([A-Za-z0-9]|[\+|\?|/|\-|:|\(|\)|\.|,|\']){3,3}([A-Za-z0-9]|[\+|\?|/|\-|:|\(|\)|\.|,|\']){1,28}';
    /**
     * used for Names, etc.
     */
    const PATTERN_SHORT_TEXT  = '[a-zA-Z0-9/\-?:().,\'+\s]{0,70}';
    /**
     * used for remittance information
     */
    const PATTERN_LONG_TEXT  = '[a-zA-Z0-9/\-?:().,\'+\s]{0,140}';
    /**
     * Used for Message-, Payment- and Transfer-IDs (since 2016 also for Mandate-ID)
     */
    const PATTERN_RESTRICTED_IDENTIFICATION_SEPA1 = '([A-Za-z0-9]|[\+|\?|/|\-|:|\(|\)|\.|,|\'|\s]){1,35}';
    /**
     * Used for Mandate-ID
     */
    const PATTERN_RESTRICTED_IDENTIFICATION_SEPA2 = '([A-Za-z0-9]|[\+|\?|/|\-|:|\(|\)|\.|,|\']){1,35}';
    /**
     * This is just for compatibility to v1.1.*
     */
    const PATTERN_MANDATE_ID = self::PATTERN_RESTRICTED_IDENTIFICATION_SEPA2;

    const FLAG_ALT_REPLACEMENT_GERMAN = 1;      // 1 << 0
    const FLAG_NO_REPLACEMENT_GERMAN  = 32768;  // 1 << 15

    /**
     * first direct debit
     */
    const SEQUENCE_TYPE_FIRST     = 'FRST';
    /**
     * recurring direct debit
     */
    const SEQUENCE_TYPE_RECURRING = 'RCUR';
    /**
     * one time direct debit
     */
    const SEQUENCE_TYPE_ONCE      = 'OOFF';
    /**
     * final direct debit
     */
    const SEQUENCE_TYPE_FINAL     = 'FNAL';
    /**
     * normal direct debit
     */
    const LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT     = 'CORE';
    /**
     * normal direct debit
     */
    const LOCAL_INSTRUMENT_LSV_DIRECT_DEBIT     = 'LSV+';
    /**
     * urgent direct debit
     */
    const LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT_D_1 = 'COR1';
    /**
     * business direct debit
     */
    const LOCAL_INSTRUMENT_BUSINESS_2_BUSINESS   = 'B2B';
    /**
     * @type int BIC_REQUIRED_THRESHOLD Until 2016-01-31 (incl.) the BIC is required for international
     *           payment transactions
     */
    const BIC_REQUIRED_THRESHOLD = 20160131;

    /**
     * Valid maximal text length
     */
    const TEXT_LENGTH_VERY_SHORT = 35;
    const TEXT_LENGTH_SHORT = 70;
    const TEXT_LENGTH_LONG = 140;

    private const IBAN_PATTERNS = ['EG' => 'EG[0-9]{2}[0-9A-Z]{23}',
                                   'AL' => 'AL[0-9]{10}[0-9A-Z]{16}',
                                   'DZ' => 'DZ[0-9]{2}[0-9A-Z]{20}',
                                   'AD' => 'AD[0-9]{10}[0-9A-Z]{12}',
                                   'AO' => 'AL[0-9]{2}[0-9A-Z]{21}',
                                   'AZ' => 'AZ[0-9]{2}[0-9A-Z]{24}',
                                   'BH' => 'AL[0-9]{2}[0-9A-Z]{18}',
                                   'BE' => 'BE[0-9]{14}',
                                   'BJ' => 'BJ[0-9]{2}[0-9A-Z]{24}',
                                   'BA' => 'BA[0-9]{18}',
                                   'BR' => 'BR[0-9]{2}[0-9A-Z]{25}',
                                   'VG' => 'VG[0-9]{2}[0-9A-Z]{20}',
                                   'BG' => 'BG[0-9]{2}[A-Z]{4}[0-9]{6}[0-9A-Z]{8}',
                                   'BF' => 'BF[0-9]{2}[0-9A-Z]{23}',
                                   'BI' => 'BI[0-9]{2}[0-9A-Z]{12}',
                                   'CR' => 'CR[0-9]{2}[0-9A-Z]{17}',
                                   'CI' => 'CI[0-9]{2}[0-9A-Z]{24}',
                                   'DK' => 'DK[0-9]{16}',
                                   'DE' => 'DE[0-9]{20}',
                                   'DO' => 'DO[0-9]{2}[0-9A-Z]{24}',
                                   'EE' => 'EE[0-9]{18}',
                                   'FO' => 'FO[0-9]{16}',
                                   'FI' => 'FI[0-9]{16}',
                                   'FR' => 'FR[0-9]{2}[0-9A-Z]{23}',
                                   'GA' => 'GA[0-9]{2}[0-9A-Z]{23}',
                                   'GE' => 'GE[0-9]{2}[A-Z]{2}[0-9A-Z]{16}',
                                   'GI' => 'GI[0-9]{2}[A-Z]{4}[0-9]{15}',
                                   'GR' => 'GR[0-9]{9}[0-9A-Z]{16}',
                                   'GL' => 'GL[0-9]{16}',
                                   'GT' => 'GT[0-9]{2}[0-9A-Z]{24}',
                                   'IR' => 'IR[0-9]{2}[0-9A-Z]{22}',
                                   'IE' => 'IE[0-9]{2}[A-Z]{4}[0-9]{14}',
                                   'IS' => 'IS[0-9]{24}',
                                   'IL' => 'IL[0-9]{21}',
                                   'IT' => 'IT[0-9]{2}[A-Z]{1}[0-9]{10}[0-9A-Z]{12}',
                                   'JO' => 'JO[0-9]{2}[0-9A-Z]{26}',
                                   'CM' => 'CM[0-9]{2}[0-9A-Z]{23}',
                                   'CV' => 'CV[0-9]{2}[0-9A-Z]{21}',
                                   'KZ' => 'KZ[0-9]{5}[0-9A-Z]{13}',
                                   'QA' => 'QA[0-9]{2}[0-9A-Z]{25}',
                                   'CG' => 'CG[0-9]{2}[0-9A-Z]{23}',
                                   'KS' => 'KS[0-9]{2}[0-9A-Z]{16}',    // todo: This should be the IBAN format for Kosovo. Is this correct?
                                   'HR' => 'HR[0-9]{19}',
                                   'KW' => 'KW[0-9]{2}[A-Z]{4}[0-9A-Z]{22}',
                                   'LV' => 'LV[0-9]{2}[A-Z]{4}[0-9A-Z]{13}',
                                   'LB' => 'LB[0-9]{6}[0-9A-Z]{20}',
                                   'LI' => 'LI[0-9]{7}[0-9A-Z]{12}',
                                   'LT' => 'LT[0-9]{18}',
                                   'LU' => 'LU[0-9]{5}[0-9A-Z]{13}',
                                   'MG' => 'MG[0-9]{2}[0-9A-Z]{23}',
                                   'ML' => 'ML[0-9]{2}[0-9A-Z]{24}',
                                   'MT' => 'MT[0-9]{2}[A-Z]{4}[0-9]{5}[0-9A-Z]{18}',
                                   'MR' => 'MR[0-9]{25}',
                                   'MU' => 'MU[0-9]{2}[0-9A-Z]{23}[A-Z]{3}',
                                   'MK' => 'MK[0-9]{5}[0-9A-Z]{10}[0-9]{2}',
                                   'MD' => 'MD[0-9]{2}[0-9A-Z]{20}',
                                   'MC' => 'MC[0-9]{12}[0-9A-Z]{11}[0-9]{2}',
                                   'ME' => 'ME[0-9]{20}',
                                   'MZ' => 'MZ[0-9]{2}[0-9A-Z]{21}',
                                   'NL' => 'NL[0-9]{2}[A-Z]{4}[0-9]{10}',
                                   'NO' => 'NO[0-9]{13}',
                                   'AT' => 'AT[0-9]{18}',
                                   'TL' => 'TL[0-9]{2}[0-9A-Z]{16}',
                                   'PK' => 'PK[0-9]{2}[0-9A-Z]{20}',
                                   'PS' => 'PS[0-9]{2}[0-9A-Z]{25}',
                                   'PL' => 'PL[0-9]{26}',
                                   'PT' => 'PT[0-9]{23}',
                                   'RO' => 'RO[0-9]{2}[A-Z]{4}[0-9A-Z]{16}',
                                   'SM' => 'SM[0-9]{2}[A-Z]{1}[0-9]{10}[0-9A-Z]{12}',
                                   'ST' => 'ST[0-9]{2}[0-9A-Z]{21}',
                                   'SA' => 'SA[0-9]{4}[0-9A-Z]{18}',
                                   'SE' => 'SE[0-9]{22}',
                                   'CH' => 'CH[0-9]{2}[0-9]{5}[0-9A-Z]{12}',
                                   'SN' => 'SN[0-9]{2}[0-9A-Z]{24}',
                                   'RS' => 'RS[0-9]{20}',
                                   'SK' => 'SK[0-9]{22}',
                                   'SI' => 'SI[0-9]{17}',
                                   'ES' => 'ES[0-9]{22}',
                                   'CZ' => 'CZ[0-9]{22}',
                                   'TN' => 'TN[0-9]{22}',
                                   'TR' => 'TR[0-9]{7}[0-9A-Z]{17}',
                                   'HU' => 'HU[0-9]{26}',
                                   'AE' => 'AE[0-9]{2}[0-9A-Z]{19}',
                                   'GB' => 'GB[0-9]{2}[A-Z]{4}[0-9]{14}',
                                   'CY' => 'CY[0-9]{10}[0-9A-Z]{16}',
                                   'CF' => 'CF[0-9]{2}[0-9A-Z]{23}'];

    private const ALPHABET = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
                              'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
                              'U', 'V', 'W', 'X', 'Y', 'Z'];

    private const ALPHABET_VALUES = [10, 11, 12, 13, 14, 15, 16, 17, 18, 19,
                                     20, 21, 22, 23, 24, 25, 26, 27, 28, 29,
                                     30, 31, 32, 33, 34, 35];

    private const MOD_97_VALUES = [1, 10, 3, 30, 9, 90, 27, 76, 81, 34, 49, 5, 50, 15, 53, 45, 62, 38,
                                   89, 17, 73, 51, 25, 56, 75, 71, 31, 19, 93, 57, 85, 74, 61, 28, 86,
                                   84, 64, 58, 95, 77, 91, 37, 79, 14, 43, 42, 32, 29, 96, 87, 94, 67,
                                   88, 7, 70, 21, 16, 63, 48, 92, 47, 82, 44, 52, 35, 59, 8, 80, 24];

    private const SPECIAL_CHARS_REPLACEMENT = [';' => ',', '[' => '(', '\\' => '/', ']' => ')', '^' => '.', '_' => '-', '`' => '\'', '{' => '(', '|' => '/', '}' => ')', '~' => '-', '¿' => '?', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'T', 'ß' => 's', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 't', 'ÿ' => 'y', 'Ā' => 'A', 'ā' => 'a', 'Ă' => 'A', 'ă' => 'a', 'Ą' => 'A', 'ą' => 'a', 'Ć' => 'C', 'ć' => 'c', 'Ĉ' => 'C', 'ĉ' => 'c', 'Ċ' => 'C', 'ċ' => 'c', 'Č' => 'C', 'č' => 'c', 'Ď' => 'D', 'ď' => 'd', 'Đ' => 'D', 'đ' => 'd', 'Ē' => 'E', 'ē' => 'e', 'Ĕ' => 'E', 'ĕ' => 'e', 'Ė' => 'E', 'ė' => 'e', 'Ę' => 'E', 'ę' => 'e', 'Ě' => 'E', 'ě' => 'e', 'Ĝ' => 'G', 'ĝ' => 'g', 'Ğ' => 'G', 'ğ' => 'g', 'Ġ' => 'G', 'ġ' => 'g', 'Ģ' => 'G', 'ģ' => 'g', 'Ĥ' => 'H', 'ĥ' => 'h', 'Ħ' => 'H', 'ħ' => 'h', 'Ĩ' => 'I', 'ĩ' => 'i', 'Ī' => 'I', 'ī' => 'i', 'Ĭ' => 'I', 'ĭ' => 'i', 'Į' => 'I', 'į' => 'i', 'İ' => 'I', 'ı' => 'i', 'Ĳ' => 'I', 'ĳ' => 'i', 'Ĵ' => 'J', 'ĵ' => 'j', 'Ķ' => 'K', 'ķ' => 'k', 'ĸ' => '.', 'Ĺ' => 'L', 'ĺ' => 'l', 'Ļ' => 'L', 'ļ' => 'l', 'Ľ' => 'L', 'ľ' => 'l', 'Ŀ' => 'L', 'ŀ' => 'l', 'Ł' => 'L', 'ł' => 'l', 'Ń' => 'N', 'ń' => 'n', 'Ņ' => 'N', 'ņ' => 'n', 'Ň' => 'N', 'ň' => 'n', 'Ő' => 'O', 'ő' => 'o', 'Œ' => 'O', 'œ' => 'o', 'Ŕ' => 'R', 'ŕ' => 'r', 'Ŗ' => 'R', 'ŗ' => 'r', 'Ř' => 'R', 'ř' => 'r', 'Ś' => 'S', 'ś' => 's', 'Ŝ' => 'S', 'ŝ' => 's', 'Ş' => 'S', 'ş' => 's', 'Š' => 'S', 'š' => 's', 'Ţ' => 'T', 'ţ' => 't', 'Ť' => 'T', 'ť' => 't', 'Ŧ' => 'T', 'ŧ' => 't', 'Ũ' => 'U', 'ũ' => 'u', 'Ū' => 'U', 'ū' => 'u', 'Ŭ' => 'U', 'ŭ' => 'u', 'Ů' => 'U', 'ů' => 'u', 'Ű' => 'U', 'ű' => 'u', 'Ų' => 'U', 'ų' => 'u', 'Ŵ' => 'W', 'ŵ' => 'w', 'Ŷ' => 'Y', 'ŷ' => 'y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'ź' => 'z', 'Ż' => 'Z', 'ż' => 'z', 'Ž' => 'Z', 'ž' => 'z', 'Ș' => 'S', 'ș' => 's', 'Ț' => 'T', 'ț' => 't', 'Ά' => 'A', 'Έ' => 'E', 'Ή' => 'I', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ώ' => 'O', 'ΐ' => 'i', 'Α' => 'A', 'Β' => 'V', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'I', 'Θ' => 'TH', 'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => 'X', 'Ο' => 'O', 'Π' => 'P', 'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'CH', 'Ψ' => 'PS', 'Ω' => 'O', 'Ϊ' => 'I', 'Ϋ' => 'Y', 'ά' => 'a', 'έ' => 'e', 'ή' => 'i', 'ί' => 'i', 'ΰ' => 'y', 'α' => 'a', 'β' => 'v', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'i', 'θ' => 'th', 'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'x', 'ο' => 'o', 'π' => 'p', 'ρ' => 'r', 'ς' => 's', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'ch', 'ψ' => 'ps', 'ω' => 'o', 'ϊ' => 'i', 'ϋ' => 'y', 'ό' => 'o', 'ύ' => 'y', 'ώ' => 'o', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'TS', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SHT', 'Ъ' => 'A', 'Ь' => 'Y', 'Ю' => 'YU', 'Я' => 'YA', 'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sht', 'ъ' => 'a', 'ь' => 'y', 'ю' => 'yu', 'я' => 'ya', '€' => 'E'];

    /**
     * @type array IBAN country code => array of valid BIC country codes
     */
    private const BIC_IBAN_COUNTRY_CODE_EXCEPTIONS = ['FR' => ['GF', 'GP', 'MQ', 'RE',
                                                               'PF', 'TF', 'YT', 'NC',
                                                               'BL', 'MF', 'PM', 'WF'],
                                                      'GB' => ['IM', 'GG', 'JE']];

    private const EXCEPTIONAL_BICS = [
                                        'NOTAVAIL'  // used in Austria to mark that no BIC is provided
    ];

    /*
     * Checks if an creditor identifier (ci) is valid. Note that also if the ci is valid it does
     * not have to exist
     *
     * @param string $ci
     * @return string|false The valid IBAN or false if it is not valid
     */
    public static function checkCreditorIdentifier(string $ci)
    {
        $ci = preg_replace('/\s+/u', '', $ci);   // remove whitespaces
        $ci = strtoupper($ci);

        if(!self::checkRestrictedPersonIdentifierSEPA($ci))
            return false;

        $ciCopy = $ci;

        // remove creditor business code
        $nationalIdentifier = substr($ci, 7);
        $check = substr($ci, 0, 4);
        $concat = $nationalIdentifier . $check;

        $concat = preg_replace('#[^a-zA-Z0-9]#u', '', $concat);      // remove all non-alpha-numeric characters

        $concat = $check = str_replace(self::ALPHABET, self::ALPHABET_VALUES, $concat);

        if(self::iso7064Mod97m10ChecksumCheck($concat))
            return $ciCopy;

        return false;
    }

    /**
     * Checks if an iban is valid. Note that also if the iban is valid it does not have to exist
     *
     * @param string $iban
     * @param array  $options valid keys:
     *                        - checkByCheckSum (boolean): If true, the IBAN checksum is
     *                        calculated (default:true)
     *                        - checkByFormat (boolean): If true, the format is checked by
     *                        regular expression (default: true)
     * @return string|false The valid iban or false if it is not valid
     */
    public static function checkIBAN(string $iban, ?array $options = null)
    {
        $iban = preg_replace('/\s+/u', '' , $iban );     // remove whitespaces
        $iban = strtoupper($iban);

        if(!preg_match('/^' . self::PATTERN_IBAN . '$/',$iban))
            return false;

        $ibanCopy = $iban;

        if(!isset($options['checkByFormat']) || $options['checkByFormat'])
        {
            $countryCode = substr($iban, 0, 2);
            if(isset(self::IBAN_PATTERNS[$countryCode])
                && !preg_match('/^' . self::IBAN_PATTERNS[$countryCode] . '$/',$iban))
                return false;
        }

        if(!isset($options['checkByCheckSum']) || $options['checkByCheckSum'])
        {
            $iban = $check = str_replace(self::ALPHABET, self::ALPHABET_VALUES, $iban);

            $bban  = substr($iban, 6);
            $check = substr($iban, 0, 6);

            $concat = $bban . $check;

            if( !self::iso7064Mod97m10ChecksumCheck($concat) )
                return false;
        }

        return $ibanCopy;
    }

    private static function iso7064Mod97m10ChecksumCheck(string $input) : bool
    {
        $checksum = 0;
        $len = strlen($input);
        for($i = 1; $i <= $len; $i++)
        {
            $checksum = (($checksum + self::MOD_97_VALUES[$i - 1] * $input[$len - $i]) % 97);
        }

        return $checksum === 1;
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
     * @return string|false the valid bic or false if it is not valid
     */
    public static function checkBIC(string $bic, ?array $options = null)
    {
        $bic = preg_replace('/\s+/u', '' , $bic );   // remove whitespaces

        if(!empty($options['forceLongBic']) && strlen($bic) === 8)
            $bic .= empty($options['forceLongBicStr']) ? 'XXX' : $options['forceLongBicStr'];

        if(empty($bic) && !empty($options['allowEmptyBic']))
            return '';

        $bic = strtoupper($bic);                    // use only capital letters

        if(preg_match('/^' . self::PATTERN_BIC . '$/', $bic))
            return $bic;

        return false;
    }

    /**
     * Checks if both IBANs do belong to the same country.
     * This function does not check if the IBANs are valid.
     *
     * @param string $iban1
     * @param string $iban2
     * @return bool
     */
    public static function isNationalTransaction(string $iban1, string $iban2) : bool
    {
        // remove whitespaces
        $iban1 = preg_replace('#\s+#', '', $iban1);
        $iban2 = preg_replace('#\s+#', '', $iban2);

        // check the country code
        return stripos($iban1, substr($iban2, 0, 2)) === 0;
    }

    /**
     * Checks if both IBANs belong to the EEA (European Economic Area)
     * This function does not check if the IBANs are valid.
     *
     * @param string $iban1
     * @param string $iban2
     * @return bool
     */
    public static function isEEATransaction(string $iban1, string $iban2) : bool
    {
        // remove whitespaces
        $iban1 = preg_replace('#\s+#','',$iban1);
        $iban2 = preg_replace('#\s+#','',$iban2);

        // check if both county codes belong to the EEA
        $EEA = ['IS' => 1, 'LI' => 1, 'NO' => 1, 'BE' => 1, 'BG' => 1, 'DK' => 1, 'DE' => 1,
                'EE' => 1, 'FI' => 1, 'FR' => 1, 'GR' => 1, 'IE' => 1, 'IT' => 1, 'HR' => 1,
                'LV' => 1, 'LT' => 1, 'LU' => 1, 'MT' => 1, 'NL' => 1, 'AT' => 1, 'PL' => 1,
                'PT' => 1, 'RO' => 1, 'SE' => 1, 'SK' => 1, 'SI' => 1, 'ES' => 1, 'CZ' => 1,
                'HU' => 1, 'GB' => 1, 'CY' => 1];

        return isset($EEA[strtoupper(substr($iban1, 0, 2))], $EEA[strtoupper(substr($iban2, 0, 2))]);
    }

    /**
     * Checks if IBAN and BIC belong to the same country. If not, they also can not belong to
     * each other.
     *
     * @param string $iban
     * @param string $bic
     * @return bool
     */
    public static function crossCheckIbanBic(string $iban, string $bic) : bool
    {
        // check for special cases
        if(in_array(strtoupper($bic), self::EXCEPTIONAL_BICS))
            return true;

        // remove whitespaces
        $iban = preg_replace('#\s+#','',$iban);
        $bic  = preg_replace('#\s+#','',$bic);

        // check the country code
        $ibanCountryCode = strtoupper(substr($iban, 0, 2));
        $bicCountryCode  = strtoupper(substr($bic, 4, 2));

        return $ibanCountryCode === $bicCountryCode
            || (isset(self::BIC_IBAN_COUNTRY_CODE_EXCEPTIONS[$ibanCountryCode])
                && in_array($bicCountryCode,self::BIC_IBAN_COUNTRY_CODE_EXCEPTIONS[$ibanCountryCode]));
    }

    private static function checkDateFormat(string $input)
    {
        $dateObj = DateTime::createFromFormat('Y-m-d', $input);
        if($dateObj !== false && $input === $dateObj->format('Y-m-d'))
            return $input;

        return false;
    }

    /**
     * Tries to convert the given date into the format YYYY-MM-DD (Y-m-d). Therefor it tries the
     * following input formats in the order of appearance: d.m.Y, d.m.y, j.n.Y, j.n.y, m.d.Y,
     * m.d.y, n.j.Y, n.j.y, Y/m/d, y/m/d, Y/n/j, y/n/j, Y.m.d, y.m.d, Y.n.j, y.n.j.
     * Notice that this method tries to interpret the first number as day-of-month. This can
     * lead to wrong dates if you have something like the 1st of April 2016 written as 04.01.2016.
     * This will be interpreted as the 4th of January 2016. This is why you have to call this
     * method on your owen risk and it is not included in the sanitize() method.
     *
     * @param string $input The date that should be reformatted
     * @param array  $preferredFormats An array of formats that will be checked first.
     * @return string|false The sanitized date or false, if it is not sanitizable.
     */
    public static function sanitizeDateFormat(string $input, array $preferredFormats = [])
    {
        $dateFormats = ['d.m.Y', 'd.m.y', 'j.n.Y', 'j.n.y', 'm.d.Y', 'm.d.y', 'n.j.Y', 'n.j.y',
                        'Y/m/d', 'y/m/d', 'Y/n/j', 'y/n/j', 'Y.m.d', 'y.m.d', 'Y.n.j', 'y.n.j'];

        // input is already in the correct format?
        $dateObj = DateTime::createFromFormat('Y-m-d',$input);
        if($dateObj !== false)
            return $input;

        foreach($preferredFormats as $format)
        {
            $dateObj = DateTime::createFromFormat($format, $input);
            if($dateObj !== false)
                return $dateObj->format('Y-m-d');
        }

        foreach($dateFormats as $format)
        {
            $dateObj = DateTime::createFromFormat($format, $input);
            if($dateObj !== false)
                return $dateObj->format('Y-m-d');
        }

        return false;
    }

    /**
     * Checks if the input has the format 'Y-m-d\TH:i:s'
     * @param string $input
     * @return string|false Returns $input if it is valid and false else.
     */
    public static function checkCreateDateTime(string $input)
    {
        $dateObj = DateTime::createFromFormat('Y-m-d\TH:i:s', $input);
        if($dateObj !== false && $input === $dateObj->format('Y-m-d\TH:i:s'))
            return $input;

        return false;
    }

    /**
     * Reformat a date string from a given format to the ISODate format. Notice: 20.13.2014 is
     * valid and becomes 2015-01-20.
     *
     * @param string $date        A date string of the given input format
     * @param string $inputFormat default is the german format DD.MM.YYYY
     * @return string|false date as YYYY-MM-DD or false, if the input is not a date.
     * @throws Exception If $date is provided but does not match the given $inputFormat.
     */
    public static function getDate(?string $date = null, string $inputFormat = 'd.m.Y')
    {
        $dateTimeObj = empty($date) ? new DateTime() : DateTime::createFromFormat($inputFormat, $date);

        if($dateTimeObj === false)
            return false;

        return $dateTimeObj->format('Y-m-d');
    }

    /**
     * Computes the next TARGET2 day (including today) with respect to a TARGET2 offset.
     *
     * @param int    $workdayOffset a positive number of workdays to skip.
     * @param string $today         if set, this date is used as today
     * @param string $inputFormat
     * @return string|false YYYY-MM-DD
     * @throws Exception If $date is provided but does not match the given $inputFormat.
     */
    public static function getDateWithOffset(int $workdayOffset, ?string $today = null, string $inputFormat = 'd.m.Y')
    {
        $dateTimeObj = empty($today) ? new DateTime() : DateTime::createFromFormat($inputFormat, $today);

        if($dateTimeObj === false)
            return false;

        $isTargetDay = self::dateIsTargetDay($dateTimeObj);

        while( !$isTargetDay || $workdayOffset > 0 )
        {
            $dateTimeObj->modify('+1 day');

            if($isTargetDay)
                $workdayOffset--;

            $isTargetDay = self::dateIsTargetDay($dateTimeObj);
        }

        return $dateTimeObj->format('Y-m-d');
    }

    /**
     * Returns the target date, if it has at least the given offset of TARGET2 days form
     * today. Else the earliest date that respects the offset is returned.
     *
     * @param string $target
     * @param int    $workdayMinOffset
     * @param string $inputFormat
     * @param string $today
     * @return string|false
     * @throws Exception If $today is provided but does not match the given $inputFormat.
     */
    public static function getDateWithMinOffsetFromToday(string $target, int $workdayMinOffset, string $inputFormat = 'd.m.Y', ?string $today = null)
    {
        $targetDateObj = DateTime::createFromFormat($inputFormat,$target);

        $earliestDate = self::getDateWithOffset($workdayMinOffset, $today, $inputFormat);

        if($targetDateObj === false || $earliestDate === false)
            return false;

        $earliestDateObj = new DateTime($earliestDate);

        $isTargetDay = self::dateIsTargetDay($targetDateObj);
        while( !$isTargetDay )
        {
            $targetDateObj->modify('+1 day');
            $isTargetDay = self::dateIsTargetDay($targetDateObj);
        }

        if($targetDateObj > $earliestDateObj)
            return $targetDateObj->format('Y-m-d');

        return $earliestDateObj->format('Y-m-d');
    }

    /**
     * Checks if $date is a SEPA TARGET day. Every day is a TARGET day except for saturdays, sundays
     * new year's day, good friday, easter monday, the may holiday, first and second christmas holiday.
     * @param DateTime $date
     * @return bool
     */
    private static function dateIsTargetDay(DateTime $date) : bool
    {
        // $date is a saturday or sunday?
        if($date->format('N') === '6' || $date->format('N') === '7')
            return false;

        $day = $date->format('m-d');
        if($day === '01-01'             // new year's day
            || $day === '05-01'         // labour day
            || $day === '12-25'         // first christmas day
            || $day === '12-26')        // second christmas day
            return false;

        $year = $date->format('Y');
        $easter = easterDate((int) $year);      // contains easter sunday
        $goodFriday =   $easter->modify('-2 days')->format('m-d');      // $easter contains now good friday
        $easterMonday = $easter->modify('+3 days')->format('m-d');      // $easter contains now easter monday

        if($day === $goodFriday || $day === $easterMonday)
            return false;

        return true;
    }

    /**
     * @param mixed[]             $input Reference to an array
     * @param int|string|string[] $keys  The keys of the multidimensional array in order of
     *                                   appearance. e.g. `['key1','key2']` checks
     *                                   `$arr['key1']['key2']`
     * @return mixed|false Returns the value of the field or null if the field does not exist.
     */
    private static function getValFromMultiDimInput(array &$input, $keys)
    {
        $key = is_array($keys) ? array_shift($keys) : $keys;
        if( !isset( $input[$key] ) )
            return false;

        if( is_array($keys) && !empty( $keys ) ) // another dimension
            return self::getValFromMultiDimInput($input[$key], $keys);

        return $input[$key];
    }

    /**
     * Checks if the input holds for the field.
     *
     * @param string $field   Valid fields are: 'orgnlcdtrschmeid_id','ci','msgid','pmtid','pmtinfid',
     *                        'orgnlmndtid','mndtid','initgpty','cdtr','dbtr','orgnlcdtrschmeid_nm',
     *                        'ultmtcdrt','ultmtdbtr','rmtinf','orgnldbtracct_iban','iban','bic',
     *                        'ccy','amendment', 'btchbookg','instdamt','seqtp','lclinstrm',
     *                        'elctrncsgntr','reqdexctndt','purp','ctgypurp','orgnldbtragt', 'adrline'
     *                        'ctry', 'dbtrpstladr', 'cdtrpstladr', 'pstladr'
     * @param mixed  $input
     * @param array  $options See `checkBIC()`, `checkIBAN()` and `checkLocalInstrument()` for
     *                        details. In addition one can use the key `version`, which is relevant
     *                        for validation 'mndtid'.
     * @return false|mixed The checked input or false, if it is not valid
     * @noinspection PhpMissingBreakStatementInspection On purpose here.
     */
    public static function check(string $field, $input, array $options = null)
    {
        $field = strtolower($field);
        $version = $options['version'] ?? null;
        switch($field)      // fall-through's are on purpose
        {
            case 'orgnlcdtrschmeid_id':
            case 'ci': return self::checkCreditorIdentifier($input);
            case 'msgid':
            case 'instrid':
            case 'pmtid':   // next line
            case 'esr':
            case 'mmbid':
            case 'lsv':
            case 'pmtinfid': return self::checkRestrictedIdentificationSEPA1($input);
            case 'orgnlmndtid':
            case 'mndtid':
                return $version === self::SEPA_PAIN_008_001_02
                            || $version === self::SEPA_PAIN_008_001_02_GBIC
                    ? self::checkRestrictedIdentificationSEPA1($input)
                    : self::checkRestrictedIdentificationSEPA2($input);
            case 'initgptyid':
                if($version === self::SEPA_PAIN_008_001_02_AUSTRIAN_003)
                    return false;   // not supported on this version
            case 'initgpty':                                // cannot be empty (and the following things also)
            case 'cdtr':                                    // cannot be empty (and the following things also)
            case 'dbtr':
                if(empty($input))
                    return false;    // cannot be empty
            case 'ultmtdbtrid':
            case 'orgid_id':
                return ( self::checkLength($input, self::TEXT_LENGTH_VERY_SHORT)
                    && self::checkCharset($input) )
                    ? $input : false;
            case 'adrline': if(is_array($input))
                {
                    if(count($input) === 0 || count($input) > 2)
                        return false;

                    foreach($input as &$value)
                    {
                        $value = self::check('adrline', $value, $options);
                    }

                    return in_array(false, $input, true) ? false : array_values($input);
                } // if not => fall through
            case 'orgnlcdtrschmeid_nm':
            case 'ultmtcdtr':
            case 'ultmtdbtr':
                return ( self::checkLength($input, self::TEXT_LENGTH_SHORT)
                    && self::checkCharset($input) )
                    ? $input : false;
            case 'rmtinf':
                return ( self::checkLength($input, self::TEXT_LENGTH_LONG)
                    && self::checkCharset($input) )
                    ? $input : false;
            case 'orgnldbtracct_iban':
            case 'iban': return self::checkIBAN($input, $options);
            case 'orgnldbtragt_bic':
            case 'orgid_bob':
            case 'bic': return self::checkBIC($input, $options);
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
            case 'ref':
            case 'orgnldbtragt': return $input;     // nothing to check here
            case 'ctry': return self::checkCountryCode($input);
            case 'pstladr':
            case 'cdtrpstladr':
            case 'dbtrpstladr': if(is_array($input) && count($input) > 0 && count($input) <= 2)
                {
                    foreach($input as $key => &$value)
                    {
                        if(!in_array(strtolower($key), ['ctry', 'adrline'], true))
                            return false;

                        $value = self::check($key, $value, $options);
                    }
                    return in_array(false, $input, true) ? false : $input;
                } // if not => fall through
            default: return false;
        }
    }

    /**
     * This function checks if the index of the inputArray exists and if the input is valid. The
     * function can be called as `checkInput($fieldName,$_POST,['input',$fieldName],$options)`
     * and equals `check($fieldName,$_POST['input'][$fieldName],$options)`, but checks first, if
     * the index exists.
     *
     * @param string             $field   see `check()` for valid values.
     * @param array              $inputArray
     * @param string|int|mixed[] $inputKeys
     * @param array              $options see `check()` for valid values.
     * @param int|null           $version
     * @return mixed|false
     */
    public static function checkInput(string $field, array &$inputArray, $inputKeys, array $options = null)
    {
        $value = self::getValFromMultiDimInput($inputArray, $inputKeys);

        if($value === false)
            return false;

        return self::check($field, $value, $options);
    }

    /**
     * This function checks if the index of the inputArray exists and if the input is valid. The
     * function can be called as `sanitizeInput($fieldName,$_POST,['input',$fieldName],$flags)`
     * and equals `sanitize($fieldName,$_POST['input'][$fieldName],$flags)`, but checks first, if
     * the index exists.
     * @param string $field     see `sanitize()` for valid values.
     * @param array $inputArray
     * @param string|int|mixed[] $inputKeys
     * @param int   $flags    see `sanitize()` for valid values.
     * @return mixed|false
     */
    public static function sanitizeInput(string $field, array &$inputArray, $inputKeys, $flags = 0)
    {
        $value = self::getValFromMultiDimInput($inputArray,$inputKeys);

        if($value === false)
            return false;

        return self::sanitize($field, $value, $flags);
    }

    /**
     * Checks the input and if it is not valid it tries to sanitize it.
     *
     * @param string $field   all fields check and/or sanitize supports
     * @param mixed  $input
     * @param int    $flags   see `sanitize()` for details
     * @param array  $options see `check()` for details
     * @return mixed|false
     */
    public static function checkAndSanitize(string $field, $input, $flags = 0, array $options = null)
    {
        $checkedInput = self::check($field, $input, $options);
        if($checkedInput !== false)
            return $checkedInput;

        return self::sanitize($field, $input, $flags);
    }

    /**
     * This function checks if the index of the inputArray exists and if the input is valid. The
     * function can be called as `checkAndSanitizeInput($fieldName,$_POST,['input',$fieldName],$flags,$options)`
     * and equals `checkAndSanitize($fieldName,$_POST['input'][$fieldName],$flags,$options)`, but checks first, if
     * the index exists.
     *
     * @param string             $field   see `checkAndSanitize()` for valid values.
     * @param array              $inputArray
     * @param string|int|mixed[] $inputKeys
     * @param int                $flags   see `checkAndSanitize()` for valid values.
     * @param array|null         $options see `checkAndSanitize()` for valid values.
     * @return false|mixed
     */
    public static function checkAndSanitizeInput(string $field, array &$inputArray, $inputKeys, int $flags = 0, array $options = null)
    {
        $value = self::getValFromMultiDimInput($inputArray, $inputKeys);

        if($value === false)
            return false;

        return self::checkAndSanitize($field, $value, $flags, $options);
    }

    /**
     * @param array      $inputs A reference to an input array (field => value)
     * @param int        $flags  Flags for sanitizing
     * @param array|null $options Options for checking
     * @return true|string returns true, if everything is ok or could be sanitized. Otherwise a
     *                     string with fields, that could not be sanitized is returned.
     */
    public static function checkAndSanitizeAll(array &$inputs, int $flags = 0, array $options = null)
    {
        $fieldsWithErrors = [];
        foreach($inputs as $field => &$input)
        {
            $input = self::checkAndSanitize($field, $input, $flags, $options);
            if($input === false)
                $fieldsWithErrors[] = $field;
        }

        if(empty($fieldsWithErrors))
            return true;

        return implode(', ', $fieldsWithErrors);
    }

    public static function sanitizeText(int $length, string $input, bool $allowEmpty = false, int $flags = 0)
    {
        $res = self::sanitizeLength(self::replaceSpecialChars($input, $flags), $length);

        if($allowEmpty || !empty($res))
            return $res;

        return false;
    }

    /**
     * Tries to sanitize the the input so it fits in the field.
     *
     * @param string $field Valid fields are: 'ultmtcdrt', 'ultmtdbtr',
     *                      'orgnlcdtrschmeid_nm', 'initgpty', 'cdtr', 'dbtr', 'rmtinf', 'adrline'
     * @param mixed  $input
     * @param int    $flags Flags used in replaceSpecialChars()
     * @return mixed|false  The sanitized input or false if the input is not sanitizeable or
     *                      invalid also after sanitizing.
     */
    public static function sanitize(string $field, $input, int $flags = 0)
    {
        $field = strtolower($field);
        switch($field)          // fall-through's are on purpose
        {
            case 'orgid_id':
                return self::sanitizeText(self::TEXT_LENGTH_VERY_SHORT, $input, true, $flags);
            case 'adrline':
                if(is_array($input))
                {
                    foreach($input as &$value)
                        $value = self::sanitize($field, $value, $flags);

                    return in_array(false, $input, true) ? false : $input;
                }
            case 'ultmtcdrt':
            case 'ultmtdbtr':
            case 'ultmtdebtr':  // deprecated, just here for backwards compatibility
                return self::sanitizeText(self::TEXT_LENGTH_SHORT, $input, true, $flags);
            case 'orgnlcdtrschmeid_nm':
            case 'initgpty':
            case 'cdtr':
            case 'dbtr':
                return self::sanitizeText(self::TEXT_LENGTH_SHORT, $input, false, $flags);
            case 'rmtinf':
                return self::sanitizeText(self::TEXT_LENGTH_LONG, $input, true, $flags);
            default:
                return false;
        }
    }

    public static function checkRequiredCollectionKeys(array $inputs, int $version)
    {
        switch($version)    // fall-through's are on purpose
        {
            case self::SEPA_PAIN_001_002_03:
                $requiredKeys = ['pmtInfId', 'dbtr', 'iban', 'bic'];
                break;
            case self::SEPA_PAIN_001_001_03:
            case self::SEPA_PAIN_001_001_03_GBIC:
            case self::SEPA_PAIN_001_001_03_CH_02:
            case self::SEPA_PAIN_001_003_03:
                $requiredKeys = ['pmtInfId', 'dbtr', 'iban'];
                break;
            case self::SEPA_PAIN_008_002_02:
                $requiredKeys = ['pmtInfId', 'lclInstrm', 'seqTp', 'cdtr', 'iban', 'bic', 'ci'];
                break;
            case self::SEPA_PAIN_008_001_02:
            case self::SEPA_PAIN_008_001_02_GBIC:
            case self::SEPA_PAIN_008_003_02:
                $requiredKeys = ['pmtInfId', 'lclInstrm', 'seqTp', 'cdtr', 'iban', 'ci'];
                break;
            case self::SEPA_PAIN_008_001_02_CH_03:
                $requiredKeys = ['pmtInfId', 'lclInstrm', 'seqTp', 'cdtr', 'iban','lsv'];
                break;
            default:
                return false;
        }

        return self::containsAllKeys($inputs, $requiredKeys);
    }

    public static function checkRequiredPaymentKeys(array $inputs, int $version)
    {
        switch($version)
        {
            case self::SEPA_PAIN_001_002_03:
                $requiredKeys = ['pmtId', 'instdAmt', 'iban', 'bic', 'cdtr'];
                break;
            case self::SEPA_PAIN_001_001_03:
            case self::SEPA_PAIN_001_001_03_GBIC:
            case self::SEPA_PAIN_001_001_03_CH_02:
            case self::SEPA_PAIN_001_003_03:
                $requiredKeys = ['pmtId', 'instdAmt', 'iban', 'cdtr'];
                break;
            case self::SEPA_PAIN_008_002_02:
                $requiredKeys = ['pmtId', 'instdAmt', 'mndtId', 'dtOfSgntr', 'dbtr', 'iban', 'bic'];
                break;
            case self::SEPA_PAIN_008_001_02:
            case self::SEPA_PAIN_008_001_02_GBIC:
            case self::SEPA_PAIN_008_003_02:
                $requiredKeys = ['pmtId', 'instdAmt', 'mndtId', 'dtOfSgntr', 'dbtr', 'iban'];
                break;
            case self::SEPA_PAIN_008_001_02_CH_03:
                $requiredKeys = ['pmtId', 'instdAmt', 'dbtr', 'iban'];
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
    public static function containsAllKeys(array $arr, array $keys) : bool
    {
        foreach($keys as $key)
        {
            if(!isset($arr[$key]))
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
    public static function containsNotAnyKey(array $arr, array $keys) : bool
    {
        foreach($keys as $key)
        {
            if(isset($arr[$key]))
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
    private static function checkActiveOrHistoricCurrencyCode(string $ccy)
    {
        $ccy = strtoupper($ccy);

        if(preg_match('/^[A-Z]{3}$/', $ccy))
            return $ccy;

        return false;
    }

    private static function checkBoolean($input)
    {
        $bbi = filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

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
    private static function checkRestrictedIdentificationSEPA1(string $input)
    {
        if(preg_match('#^' . self::PATTERN_RESTRICTED_IDENTIFICATION_SEPA1 . '$#', $input))
            return $input;

        return false;
    }

    /**
     * @param string $input
     * @return string|bool
     */
    private static function checkRestrictedIdentificationSEPA2(string $input)
    {
        if(preg_match('#^' . self::PATTERN_RESTRICTED_IDENTIFICATION_SEPA2 . '$#', $input))
            return $input;

        return false;
    }

    /**
     * @param string $input
     * @return string|bool
     */
    private static function checkRestrictedPersonIdentifierSEPA(string $input)
    {
        if(preg_match('#^' . self::PATTERN_CREDITOR_IDENTIFIER . '$#',$input))
            return $input;

        return false;
    }

    /**
     * Checks if the length of the input string not longer than the entered length
     *
     * @param string $input
     * @param int $maxLen
     * @return bool
     */
    private static function checkLength(string $input, int $maxLen) : bool
    {
        return !isset($input[$maxLen]);     // takes the string as char array
    }

    /**
     * Shortens the input string to the max length if it is to long.
     * @param string $input
     * @param int $maxLen
     * @return string sanitized string
     */
    public static function sanitizeLength(string $input, int $maxLen) : string
    {
        if(isset($input[$maxLen]))     // take string as array of chars
            return substr($input,0,$maxLen);

        return $input;
    }

    /**
     * Replaces all special chars like á, ä, â, à, å, ã, æ, Ç, Ø, Š, ", ’ and & by a latin character.
     * All special characters that cannot be replaced by a latin char (such like quotes) will
     * be removed as long as they cannot be converted. See http://www.europeanpaymentscouncil.eu/index.cfm/knowledge-bank/epc-documents/sepa-requirements-for-an-extended-character-set-unicode-subset-best-practices/
     * for more information about converting characters.
     *
     * @param string $str
     * @param int    $flags Use the SepaUtilities::FLAG_ALT_REPLACEMENT_* and SepaUtilities::FLAG_NO_REPLACEMENT_*
     *                      constants. FLAG_ALT_REPLACEMENT_* will ignore the best practice replacement
     *                      and use a more common one. You can use more than one flag by using
     *                      the | (bitwise or) operator. FLAG_NO_REPLACEMENT_* tells the function
     *                      not to replace the character group.
     * @return string
     */
    public static function replaceSpecialChars(string $str, int $flags = 0) : string
    {
        $specialCharsReplacement = self::SPECIAL_CHARS_REPLACEMENT;
        $charExceptions = '';

        if($flags & self::FLAG_ALT_REPLACEMENT_GERMAN)
            $specialCharsReplacement = array_merge($specialCharsReplacement, ['Ä' => 'Ae', 'ä' => 'ae', 'Ö' => 'Oe', 'ö' => 'oe', 'Ü' => 'Ue', 'ü' => 'ue', 'ß' => 'ss']);

        if($flags & self::FLAG_NO_REPLACEMENT_GERMAN)
        {
            $specialCharsReplacement = array_diff_key($specialCharsReplacement, array_flip(['Ä', 'ä', 'Ö', 'ö', 'Ü', 'ü', 'ß']));
            $charExceptions .= 'ÄäÖöÜüß';
        }

        // remove characters
        $str = str_replace(['"', '&', '<', '>'], '', $str);

        // replace all kinds of whitespaces by a space
        $str = preg_replace('#\s+#u',' ',$str);

        // special replacement for some characters (incl. greek and cyrillic)
        $str = strtr($str,$specialCharsReplacement);

        // replace everything not allowed in sepa files by . (a dot)
        $str = preg_replace('#[^a-zA-Z0-9/\-?:().,\'+ ' . $charExceptions . ']#u','.',$str);

        // remove leading and closing whitespaces
        return trim($str);
    }

    private static function checkCharset(string $str) : bool
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
    private static function checkAmountFormat($amount)
    {
        // $amount is a string -> check for '1,234.56'
        $result = filter_var($amount, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);

        if($result === false)
            $result = filter_var(strtr($amount, [',' => '.', '.' => ',']), FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);

        if($result === false || $result < 0.01 || $result > 999999999.99 || round($result,2) != $result)
            return false;

        return $result;
    }

    /**
     * Checks if the sequence type is valid.
     *
     * @param string $seqTp
     * @return string|false
     */
    private static function checkSeqType(string $seqTp)
    {
        $seqTp = strtoupper($seqTp);

        if(in_array($seqTp, [self::SEQUENCE_TYPE_FIRST, self::SEQUENCE_TYPE_RECURRING,
                             self::SEQUENCE_TYPE_ONCE, self::SEQUENCE_TYPE_FINAL]))
            return $seqTp;

        return false;
    }

    /**
     * @param string $input
     * @param array $options Can contain the key `version` with values `SepaUtilities::SEPA_PAIN_008_*`
     * @return bool|string
     */
    private static function checkLocalInstrument(string $input, ?array $options = null)
    {
        $version = empty($options['version']) ? self::SEPA_PAIN_008_002_02 : $options['version'];

        $input = strtoupper($input);

        switch($version)    // fall-through's are on purpose
        {
            case self::SEPA_PAIN_008_001_02:
            case self::SEPA_PAIN_008_001_02_GBIC:
            case self::SEPA_PAIN_008_002_02:
                $validCases = [self::LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT,
                               self::LOCAL_INSTRUMENT_BUSINESS_2_BUSINESS];
                break;
            case self::SEPA_PAIN_008_003_02:
                $validCases = [self::LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT,
                               self::LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT_D_1,
                               self::LOCAL_INSTRUMENT_BUSINESS_2_BUSINESS];
                break;
            case self::SEPA_PAIN_008_001_02_CH_03:
                $validCases = [self::LOCAL_INSTRUMENT_LSV_DIRECT_DEBIT];
                break;
            default:
                return false;
        }

        if(in_array($input, $validCases))
            return $input;

        return false;
    }

    private static function checkCategoryPurpose(string $input)
    {
        $validValues = ['BONU', 'CASH', 'CBLK', 'CCRD', 'CORT', 'DCRD', 'DIVI', 'EPAY',
                        'FCOL', 'GOVT', 'HEDG', 'ICCP', 'IDCP', 'INTC', 'INTE', 'LOAN',
                        'OTHR', 'PENS', 'SALA', 'SECU', 'SSBE', 'SUPP', 'TAXS', 'TRAD',
                        'TREA', 'VATX', 'WHLD'];

        $input = strtoupper($input);

        if(in_array($input, $validValues))
            return $input;

        return false;
    }

    private static function checkCountryCode(string $input)
    {
        $input = strtoupper($input);
        if(isset(self::IBAN_PATTERNS[$input]))
            return $input;

        return false;
    }

    private static function checkPurpose(string $input)
    {
        $validValues = ['CBLK', 'CDCB', 'CDCD', 'CDCS', 'CDDP', 'CDOC', 'CDQC', 'ETUP',
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
                        'UBIL', 'WTER'];

        $input = strtoupper($input);

        if( in_array($input, $validValues) )
            return $input;

        return false;
    }

    /**
     * Returns the SEPA file version as a string.
     * @param int $version Use the SEPA_PAIN_* constants.
     * @return string|false SEPA file version as a string or false if the version is invalid.
     */
    public static function version2string(int $version)
    {
        switch($version)
        {   // fall-through's are on purpose
            case self::SEPA_PAIN_001_001_03_GBIC:
			case self::SEPA_PAIN_001_001_03_CH_02:
            case self::SEPA_PAIN_001_001_03: return 'pain.001.001.03';
            case self::SEPA_PAIN_001_002_03: return 'pain.001.002.03';
            case self::SEPA_PAIN_001_003_03: return 'pain.001.003.03';
            case self::SEPA_PAIN_008_001_02_GBIC:
            case self::SEPA_PAIN_008_001_02_AUSTRIAN_003:
            case self::SEPA_PAIN_008_001_02_CH_03:
            case self::SEPA_PAIN_008_001_02: return 'pain.008.001.02';
            case self::SEPA_PAIN_008_002_02: return 'pain.008.002.02';
            case self::SEPA_PAIN_008_003_02: return 'pain.008.003.02';
            default: return false;
        }
    }

    /**
     * Returns a SEPA_TRANSACTION_TYPE_* constant
     *
     * @param int $version
     * @return int|false
     */
    public static function version2transactionType(int $version)
    {
        $type = (int) ((string) $version)[0];

        if($type === self::SEPA_TRANSACTION_TYPE_CT)
            return self::SEPA_TRANSACTION_TYPE_CT;

        if($type === self::SEPA_TRANSACTION_TYPE_DD)
            return self::SEPA_TRANSACTION_TYPE_DD;

        return false;
    }
}