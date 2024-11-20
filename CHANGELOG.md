# CHANGELOG

## 1.5.10 (2024-11-20)

- Possibility to remove euro sufix on getDeductionPercentAndDonationsRecurrence results

## 1.5.9 (2024-03-14)

- Remove thousands separator

## 1.5.8 (2024-02-07)

- Library via composer
- PHPUnit tests
- Add range intervals for imports for tax calculator

## 1.5.7 (2024-01-18)

- getDeductionPercentAndDonationsRecurrence returns now '0€'... also by organizations
- Add functions to get total amount and contributors on 182

## 1.5.6 (2024-01-17)

- getDeductionPercentAndDonationsRecurrence returns now '0€' if the new 2024 regulation doesn't have
any effect on the donation amount (i.e The current donation is less than 150€) and returns the possible increase that the donor can make keeping constant the real cost (i.e Individual donations over 150€ and all societies donations)

## 1.5.5 (2024-01-16)

- Add function to check the validity of postal codes
- Add postal code function validator
- getDeductionPercentAndDonationsRecurrence function now return an estimation of the tax deductible amount for the donor according to the new 2024 regulation, an estimation of ther real donation cost amount for the donor according to the new 2024 regulation, and the increase in donation that the donor can make in 2024 compared to 2023 without incurring an extra cost
- Force normalization for declarant name on 993 outputfile
- PHP 8.0 compatibility

## 1.5.4 (2023-12-13)

- Add possibility to get teorical deducted amount and teorical donation cost for year donations

## 1.5.3 (2023-02-01)

- Warning message for 993 model and export buttons according to filters

## 1.5.1 (2023-01-20)

- Fix incorrect normalization for characters ñ and ç

## 1.5.0 (2023-01-19)

- Adds funcionality for 993 presentation model

## 1.4.2 (2022-01-22)

- Fix wrong code for Catalunya autonomous community

## 1.4.1 (2022-01-19)

- Add utilities related to provinces and autonomous comminities

## 1.4 (2020-01-09)

- Update new deduction percentages for individuals
