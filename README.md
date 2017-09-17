SepaUtilities
===============

[![Build Status](https://travis-ci.org/AbcAeffchen/SepaUtilities.svg?branch=master)](https://travis-ci.org/AbcAeffchen/SepaUtilities)
[![Latest Stable Version](https://poser.pugx.org/abcaeffchen/sepa-utilities/v/stable.svg)](https://packagist.org/packages/abcaeffchen/sepa-utilities) 
[![Total Downloads](https://poser.pugx.org/abcaeffchen/sepa-utilities/downloads.svg)](https://packagist.org/packages/abcaeffchen/sepa-utilities) 
[![License](https://poser.pugx.org/abcaeffchen/sepa-utilities/license.svg)](https://packagist.org/packages/abcaeffchen/sepa-utilities)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/AbcAeffchen/SepaUtilities?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)

## General
SepaUtilities is a PHP class to check and sanitize inputs used in SEPA files
such as IBAN numbers, creditor identifiers, names and other text.

## PHP Versions
SepaUtilities supports PHP >= 5.6 including 7.0, 7.1, 7.2 and HHVM. It should also work with PHP >=5.4, but since
this versions are _very_ old and do not get security updates any more you should not use them. 
It is also possible, that some future work on SepaUtilities will break the support of PHP < 5.6 
and I will not check if this is the case.

## Installation

### Composer
You can get SepaUtilities via Composer. Just add

```json
{
    "require": {
        "abcaeffchen/sepa-utilities": "~1.2.4"
    }
}
```

to your composer.json.

### Direct download
If you don't use Composer, you can download `SepaUtilities.php` and just include it into your
PHP files. Make sure you use the namespace `AbcAeffchen\SepaUtilities\`.

## The Utilities
Have a look at the [documentation](http://htmlpreview.github.io/?https://raw.githubusercontent.com/AbcAeffchen/SepaUtilities/master/docs/html/index.html).
### Checks
- `checkIBAN($iban)`: Checks if the IBAN is valid by checking the format and by calculating the checksum and also removes whitespaces and changes all letters to upper case.
- `checkBIC($bic)`: Checks if the BIC is valid by checking the format and also removes whitespaces
and changes all letters to upper case.
- `crossCheckIbanBic($iban, $bic)`: Checks if IBAN and BIC belong to the same country.
- `isNationalTransaction($iban1,$iban2)`: Checks if both IBANs are belong to the same country.
- `checkCharset($str)`: Checks if the string contains only allowed characters.
- `check($field, $input, $options, $version)`: Checks if the input fits the field. This function also does little
formatting changes, e.g. correcting letter case. Possible field values are:
  - `initgpty`: Initiating Party
  - `msgid`: Message ID
  - `pmtid`: Payment ID
  - `pmtinfid`: Payment Information ID
  - `cdtr`: Creditor Name
  - `ultmtcdrt`: Ultimate Creditor
  - `dbtr`: Debtor Name
  - `ultmtdebtr`: Ultimate Debtor
  - `iban`: IBAN
  - `bic`: BIC
  - `ccy`: Currency
  - `btchbookg`: Batch Booking (boolean as string)
  - `instdamt`: Instructed Amount
  - `rmtinf`: Remittance Information
  - `ci`: Creditor Identifier
  - `seqtp`: Sequence Type
  - `lclinstrm`: Local Instrument

The `$options` take an array 
### Sanitizing
- `sanitizeLength($input, $maxLen)`: Shortens the string if it is to long.
- `sanitizeShortText($input,$allowEmpty, $flags)`: Sanitizes the the charset and shortens the text if necessary.
- `sanitizeLongText($input,$allowEmpty, $flags)`: Sanitizes the the charset and shortens the text if necessary.
- `replaceSpecialChars($str)`: replaces all characters that are not allowed in sepa files by a
allowed one or removes them. Take a look at this [.xls file](http://www.europeanpaymentscouncil.eu/index.cfm/knowledge-bank/epc-documents/sepa-requirements-for-an-extended-character-set-unicode-subset-best-practices/) for more information
*Notice:* Cyrillic is not supported yet, but greek letters are.
- `sanitize($field, $input, $flags)`: tries to sanitize the input so it fits the field. Possible fields are
  - `cdtr`
  - `dbtr`
  - `rmtinf`
  - `ultmtcdrt`
  - `ultmtdebtr`

### Wrappers
- `checkAndSanitize($field, $input, $flags, $options)`: Checks the input and if it is not valid 
it tries to sanitize it.
- `checkAndSanitizeAll(&$inputs, $flags, $options)`: Takes an array of inputs (field => value)
and checks and sanitizes each of the fields. The input array is handed over as reference, so the
result will be direct effect the input array. The return value is true, if everything is ok and
else a string with problematic fields.

### Date functions
- `getDate($date, $inputFormat)`: Returns $date in a SEPA-valid format. You can specify the
input format by using [the table on this site](http://de1.php.net/manual/en/function.date.php).
By default the german date format (DD.MM.YYYY) is used.
- `getDateWithOffset($workdayOffset, $today, $inputFormat)`: Computes the next [TARGET2](http://en.wikipedia.org/wiki/TARGET2#TARGET2_holidays)
 day (including today) with respect to an offset.
- `getDateWithMinOffsetFromToday($target, $workdayMinOffset, $inputFormat, $today)`: Returns the 
target date, if it has at least the given offset of TARGET2 days form today. Else the earliest date 
that respects the offset is returned.

### Patterns
- `HTML_PATTERN_IBAN`
- `HTML_PATTERN_BIC`
- `PATTERN_IBAN`
- `PATTERN_BIC`
- `PATTERN_CREDITOR_IDENTIFIER`
- `PATTERN_SHORT_TEXT`
- `PATTERN_LONG_TEXT`
- `PATTERN_RESTRICTED_IDENTIFICATION_SEPA1`
- `PATTERN_MANDATE_ID`

The `HTML_PATTERN_*` constants can be used as HTML5 pattern attribute. It is user friendlier than 
the corresponding `PATTERN_*` as they allow lowercase characters and whitespaces. This is corrected 
by the `check` methods.

## Licence
SepaUtilities is licensed under the LGPL v3.0 License.
