SepaUtilities - A PHP class to check and sanitize you SEPA inputs
===============

###General###
SepaUtilities can be used by any php class or script to check nearly all inputs used in SEPA files
such as IBAN numbers, creditor identifiers and sanitize names and other text.

###Requirements###
Sephpa was tested on PHP version 5.3.

###Using the Utilities###
The SepaUtilities class depends not on Sephpa, so it can also used in any other project.

Since you want to know if the input is valid at input time and not at the moment you create the
file, Sephpa does not check the input itself. So the best would be, you check the inputs at input time
and later just make a file out of the data.

SepaUtilities contains the following checks:
- `checkIBAN($iban)`: Checks if the IBAN is valid by checking the format and by calculating the checksum.
It also removes whitespaces and changes all letters to upper case.
- `checkBIC($bic)`: Checks if the BIC is valid by checking the format. It also removes whitespaces
and changes all letters to upper case.
- `checkCharset($str)`: Checks if the string contains only allowed characters.
- `check($field, $input)`: Checks if the input fits in the field. This function also does little
formatting changes, e.g. correcting letter case. Possible field values are:
  - 'pmtinfid': Payment-Information-ID
  - 'dbtr': Debtor Name
  - 'iban'
  - 'bic'
  - 'ccy': Currency
  - 'btchbookg': Batch Booking (boolean as string)
  - 'ultmtdebtr': Ultimate Debtor
  - 'pmtid': Payment ID
  - 'instdamt': Instructed Amount
  - 'cdtr': Creditor Name
  - 'ultmtcdrt': Ultimate Creditor
  - 'rmtinf': Remittance Information
  - 'ci': Creditor Identifier
- `sanitizeLength($input, $maxLen)`: Shortens the string if it is to long.
- `replaceSpecialChars($str)`: replaces all characters that are not allowed in sepa files by a
allowed one or removes them. Take a look at this [.xls file](http://www.europeanpaymentscouncil.eu/index.cfm/knowledge-bank/epc-documents/sepa-requirements-for-an-extended-character-set-unicode-subset-best-practices/) for more information
*Notice:* Cyrillic is not supported yet, but greek letters are.
- `sanitize($field, $input)`: tries to sanitize the input so it fits in the field. Possible fields are
  - 'cdtr'
  - 'dbtr'
  - 'rmtInf'
  - 'ultmtCdrt'
  - 'ultmtDebtr'
- `formatDate($date, $inputFormat)`: Returns $date in a Sepa-valid format. You can specify the
input format by using [the table on this site](http://de1.php.net/manual/en/function.date.php).
By default the german date format (DD.MM.YYYY) is used.

Have also a look at utilitiesExample.php

The SepaUtilities provides also patterns that can be used in the HTML5 input-attribute "pattern"
to hint the user

###Licence###
Published under MIT-Licence
