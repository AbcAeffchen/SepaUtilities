Sephpa - Change Log
===============

##1.1.0 - Feb 5, '15##
- made IBAN validation by checksum optional (defaults to true)
- added optional IBAN validation by format (defaults to true)
- added support for exceptional IBAN - BIC connections
- Licence changed to LGPL

##1.0.7 - Dec 18, '14##
- added `checkInput()`,`sanitizeInput()` and `checkAndSanitizeInput()` to validate user inputs.
With this functions it is not required to check first, if an array index like `$_POST['key1']['key2']` 
exists, before validating the values.

##1.0.6 - Oct 24, '14##
- Bugfix: If 'forceLongBic' options is used in `checkBic()` the BIC was always extended by three
characters without checking the length if the BIC is already long.

##1.0.5 - Oct 20, '14##
- date methods now support [TARGET2](http://en.wikipedia.org/wiki/TARGET2#TARGET2_holidays) days

##1.0.4 - Oct 19, '14##
- bugfix

##1.0.3 - Oct 19, '14##
- added `checkCreateDateTime()`

##1.0.2 - Oct 18, '14##
- added to `check()`:
  - `purp`: Purpose
  - `ctgypurp`: Category Purpose
  
##1.0.1 - Oct 18, '14##
- added `sanitizeShortText()` and `sanitizeLongText()`
- added `checkAndSanitizeAll()`
- added `crossCheckIbanBic()`
- added `isNationalTransaction()`
- added `checkRequiredCollectionKeys()` and `checkRequiredPaymentKeys()`
- added `$options` parameter to `checkBic()`, `check()` and `checkAndSanitize()`
- added to `check()`:
  - `seqtp`: sequence type
  - `msgid`: message identifier
  - `amdmntind`: amendment indicator
  - `elctrncsgntr`: electronic signature
  - `reqdexctndt`: requested execution date
  - `reqdcolltndt`: requested collection date
  - `dtofsgntr`: date of signature
- added constants for SEPA version
- added constants for the fields `lclInst` and `seqTp`
- added constant `BIC_REQUIRED_THRESHOLD`

##1.0.0 - Oct 17, '14##
- SepaUtilities is now a project on its own and available over composer
- added support for cyrillic characters
- added `getDate()` to get a date with an offset of workdays (all days but sundays)
- fixed some bugs in `replaceSpecialChars()`
