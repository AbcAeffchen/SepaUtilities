SepaUtilities
===============

[![Build Status](https://travis-ci.org/AbcAeffchen/SepaUtilities.svg?branch=master)](https://travis-ci.org/AbcAeffchen/SepaUtilities)

##General##
SepaUtilities is a PHP class to check and sanitize inputs used in SEPA files
such as IBAN numbers, creditor identifiers, names and other text.

##Requirements##
SepaUtilities requires PHP >=5.3.

##Installation##

###Composer###
You can get SepaUtilities via Composer. Just add

    {
        "require": {
            "abcaeffchen/sepa-utilities": "1.0.*"
        }
    }
to your composer.json.

###Direct download###
If you don't use Composer, you also can download `SepaUtilities.php` and just include it into your
PHP files. Make sure you use the namespace `AbcAeffchen\SepaUtilities\`.

##The utilities##

###Checks###
- `checkIBAN($iban)`: Checks if the IBAN is valid by checking the format and by calculating the checksum and also removes whitespaces and changes all letters to upper case.
- `checkBIC($bic)`: Checks if the BIC is valid by checking the format and also removes whitespaces
and changes all letters to upper case.
- `checkCharset($str)`: Checks if the string contains only allowed characters.
- `check($field, $input)`: Checks if the input fits the field. This function also does little
formatting changes, e.g. correcting letter case. Possible field values are:
  - `pmtinfid`: Payment-Information-ID
  - `dbtr`: Debtor Name
  - `iban`
  - `bic`
  - `ccy`: Currency
  - `btchbookg`: Batch Booking (boolean as string)
  - `ultmtdebtr`: Ultimate Debtor
  - `pmtid`: Payment ID
  - `instdamt`: Instructed Amount
  - `cdtr`: Creditor Name
  - `ultmtcdrt`: Ultimate Creditor
  - `rmtinf`: Remittance Information
  - `ci`: Creditor Identifier
  
###Sanitizing###
- `sanitizeLength($input, $maxLen)`: Shortens the string if it is to long.
- `replaceSpecialChars($str)`: replaces all characters that are not allowed in sepa files by a
allowed one or removes them. Take a look at this [.xls file](http://www.europeanpaymentscouncil.eu/index.cfm/knowledge-bank/epc-documents/sepa-requirements-for-an-extended-character-set-unicode-subset-best-practices/) for more information
*Notice:* Cyrillic is not supported yet, but greek letters are.
- `sanitize($field, $input)`: tries to sanitize the input so it fits the field. Possible fields are
  - `cdtr`
  - `dbtr`
  - `rmtInf`
  - `ultmtCdrt`
  - `ultmtDebtr`
- `checkAndSanitize($field, $input)`: Checks the input and if it is not valid it tries to sanitize it.

###Date functions###
- `getDate($date, $inputFormat)`: Returns $date in a Sepa-valid format. You can specify the
input format by using [the table on this site](http://de1.php.net/manual/en/function.date.php).
By default the german date format (DD.MM.YYYY) is used.
- `getDateWithOffset($workdayOffset, $today, $inputFormat)`: Computes the next workday (including today) 
with respect to a workday offset. If today is a sunday, the next day is returned.
- `getDateWithMinOffsetFromToday($target, $workdayMinOffset, $inputFormat, $today)`: Returns the 
target date, if it has at least the given offset of workdays form today. Else the earliest date 
that respects the offset is returned.

###Patterns###
- `HTML_PATTERN_IBAN`
- `HTML_PATTERN_BIC`
- `PATTERN_IBAN`
- `PATTERN_BIC`
- `PATTERN_CREDITOR_IDENTIFIER`
- `PATTERN_SHORT_TEXT`
- `PATTERN_LONG_TEXT`
- `PATTERN_FILE_IDS`
- `PATTERN_MANDATE_ID`

The `HTML_PATTERN` constants can be used as HTML5 pattern attribute. It is user friendlier than 
the corresponding `PATTERN` as they allows lowercase characters and whitespaces. This is corrected 
by the `check` methods.

##Licence##
SepaUtilities is licensed under the MIT Licence.
