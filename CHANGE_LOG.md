Sephpa - Change Log
===============

##1.0.1 - not yet released##
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
- added constants for SEPA version
- added constants for the fields `lclInst` and `seqTp`
- added constant `BIC_REQUIRED_THRESHOLD`

##1.0.0 - Oct 17, '14##
- SepaUtilities is now a project on its own and available over composer
- added support for cyrillic characters
- added `getDate()` to get a date with an offset of workdays (all days but sundays)
- fixed some bugs in `replaceSpecialChars()`
