Sephpa - Change Log
===============

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
