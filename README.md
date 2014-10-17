SepaUtilities
===============

[![Build Status](https://travis-ci.org/AbcAeffchen/SepaUtilities.svg?branch=master)](https://travis-ci.org/AbcAeffchen/SepaUtilities)

###General###
SepaUtilities is a PHP class to check and sanitize inputs used in SEPA files
such as IBAN numbers, creditor identifiers, names and other text.

###Requirements###
SepaUtilities requires PHP >=5.3.

###Using the Utilities###
SepaUtilities contains the following checks:
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
- `getDate($date, $inputFormat)`: Returns $date in a Sepa-valid format. You can specify the
input format by using [the table on this site](http://de1.php.net/manual/en/function.date.php).
By default the german date format (DD.MM.YYYY) is used.
- `getDateWithOffset($workdayOffset, $today, $inputFormat)`: Computes the next workday (including today) 
with respect to a workday offset. If today is a sunday, the next day is returned.
- `getDateWithMinOffsetFromToday($target, $workdayMinOffset, $inputFormat, $today)`: Returns the 
target date, if it has at least the given offset of workdays form today. Else the earliest date 
that respects the offset is returned.

SepaUtilities also provides patterns that can be used as HTML5 input attribute "pattern"
to hint the user.

Also have a look at the the documentation and the example.

###Licence###
SepaUtilities is licensed under the MIT Licence.
