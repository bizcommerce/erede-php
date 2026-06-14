# Enums

[← Back to README](../README.md)

All enums live under the `Rede\Enum\` namespace. Backed enums serialize to their backing value
automatically (`json_encode(TransactionKind::Credit)` → `"credit"`), and response data is parsed
back into the matching case.

## `TransactionKind` (string)

| Case | Value |
| --- | --- |
| `Credit` | `credit` |
| `Debit` | `debit` |
| `Pix` | `pix` |

## `TransactionOrigin` (int)

| Case | Value |
| --- | --- |
| `Erede` | `1` |
| `VisaCheckout` | `4` |
| `Masterpass` | `6` |

## `ItemType` (int)

| Case | Value |
| --- | --- |
| `Physical` | `1` |
| `Digital` | `2` |
| `Service` | `3` |
| `Airline` | `4` |

## `PhoneType` (int)

| Case | Value |
| --- | --- |
| `Cellphone` | `1` |
| `Home` | `2` |
| `Work` | `3` |
| `Other` | `4` |

## `Gender` (string)

| Case | Value |
| --- | --- |
| `Male` | `M` |
| `Female` | `F` |

## `UrlKind` (string)

| Case | Value |
| --- | --- |
| `Callback` | `callback` |
| `ThreeDSecureFailure` | `threeDSecureFailure` |
| `ThreeDSecureSuccess` | `threeDSecureSuccess` |

## `Mpi` (string)

| Case | Value |
| --- | --- |
| `Rede` | `mpi_rede` |
| `ThirdParty` | `mpi_third_party` |

## `OnFailure` (string)

| Case | Value |
| --- | --- |
| `Continue` | `continue` |
| `Decline` | `decline` |

## `ResidenceType` (int)

Used by `Address::setType()`.

| Case | Value |
| --- | --- |
| `Apartment` | `1` |
| `House` | `2` |
| `Commercial` | `3` |
| `Other` | `4` |

## `AddressTarget` (pure enum)

Used by `Cart::address()` to choose which slot(s) to fill — `Billing`, `Shipping`, `Both`.
This replaces the former bit-flag integer constants.
