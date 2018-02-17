Sephpa - Change Log
===============

## 1.2.5 - Feb 21, '18
- added support for `OrgId` fields `ID` and `BicOrBei`. They can be used in check and sanitize
functions using the keys `orgid_id` and `orgid_bob`.
- added constants for max text lengths `SepaUtilities::TEXT_LENGTH_VERY_SHORT`, 
`SepaUtilities::TEXT_LENGTH_SHORT` and `SepaUtilities::TEXT_LENGTH_LONG`
- The functions `sanitizeShortText()` and `sanitizeLongText()` are now deprecated and will be
removed in the next major version. The replacemant is `sanitizeText()` using the `TEXT_LENGTH_*` 
constants.

## 1.2.4 - Oct 22, '17
- Added the function `version2string` to get a string representation
of a SEPA file version.
- Changed the long array syntax to the short syntax.
This breaks support for PHP <5.4 but this should not be used
and was never officially supported anyway.

## 1.2.3 - Nov 21, '16
- added support for pain.008.001.02.austria.003

## 1.2.2 - Oct 18, '16
- little bug fixes

## 1.2.1 - Oct 18, '16
- added function to validate if two IBANs belong to the EEA (European Economic Area).

## 1.2.0 - Oct 16, '16
- dropped PHP 5.5 support
- added support for HHVM
- added support for SEPA file formats pain.001.001.03 and pain.008.001.02.<br>
 There are two variants of this file format, one from 2009 witch is used e.g. in the netherlands
 and one from 2016 specified in the Appendix 3 V3.0 used in germany where it is valid from 
 November 2016. The two versions can be distinguished from each other via the new constants
 `SEPA_PAIN_001_001_03` and `SEPA_PAIN_001_001_03_GBIC` respectively `SEPA_PAIN_008_001_02` 
 and `SEPA_PAIN_008_001_02_GBIC`, where the constants with e `GBIC` suffix correspond to the new
 german file version.
- improved some functions robustness
- renamed `PATTERN_FILE_IDS` to `PATTERN_RESTRICTED_IDENTIFICATION_SEPA1`
- added more tests to increase the code coverage
- added new `sanitizeDate()` function


## 1.1.2 - Sep 21, '15
- added some doc comments
- fixed amount format check

## 1.1.1 - Feb 11, '15
- added flag for replaceSpecialChars(), to prevent replacing german umlauts

## 1.1.0 - Feb 5, '15
- made IBAN validation by checksum optional (defaults to true)
- added optional IBAN validation by format (defaults to true)
- added support for exceptional IBAN - BIC connections
- Licence changed to LGPL

## 1.0.7 - Dec 18, '14
- added `checkInput()`,`sanitizeInput()` and `checkAndSanitizeInput()` to validate user inputs.
With this functions it is not required to check first, if an array index like `$_POST['key1']['key2']` 
exists, before validating the values.

## 1.0.6 - Oct 24, '14
- Bugfix: If 'forceLongBic' options is used in `checkBic()` the BIC was always extended by three
characters without checking the length if the BIC is already long.

## 1.0.5 - Oct 20, '14
- date methods now support [TARGET2](http://en.wikipedia.org/wiki/TARGET2#TARGET2_holidays) days

## 1.0.4 - Oct 19, '14
- bugfix

## 1.0.3 - Oct 19, '14
- added `checkCreateDateTime()`

## 1.0.2 - Oct 18, '14
- added to `check()`:
  - `purp`: Purpose
  - `ctgypurp`: Category Purpose
  
## 1.0.1 - Oct 18, '14
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

## 1.0.0 - Oct 17, '14
- SepaUtilities is now a project on its own and available over composer
- added support for cyrillic characters
- added `getDate()` to get a date with an offset of workdays (all days but sundays)
- fixed some bugs in `replaceSpecialChars()`
