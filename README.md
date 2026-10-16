# laravel-pii-redactor

Redact personal data from text **before** it leaves your application: prompts sent to LLMs, log lines, payloads to
third-party APIs, support tickets.

```php
use AdilKhan\PiiRedactor\Facades\Pii;

$result = Pii::redact('Employee: Jordan Avery  SSN: 900-41-2217  Email: jordan@example.com');

$result->text;    // "[NAME]  SSN: [SSN]  Email: [EMAIL]"
$result->counts;  // ['SSN' => 1, 'EMAIL' => 1, 'NAME' => 1] (what was removed, never the values)
```

Extracted from [loan-underwriting-ai](https://github.com/adilKhan72/loan-underwriting-ai), where redaction runs on every
document before it's sent to a model (that project's ADR-0004).

## What it detects

| Placeholder | Examples |
|---|---|
| `[SSN]` | `900-12-3456`, `SSN: 900123456` |
| `[ROUTING_NUMBER]` | `Routing #: 121000358` |
| `[ACCOUNT_NUMBER]` | `Account No. 0012345678`, `Acct: XXXX1234` |
| `[CARD_NUMBER]` | `4111 1111 1111 1111` |
| `[EMAIL]` | `jane.doe@example.com` |
| `[PHONE]` | `(555) 010-2000`, `+1 555-010-2000` |
| `[NAME]` | labelled only: `Employee: Jordan Avery`, `Account Holder: Maria Ruiz` |
| `[DATE_OF_BIRTH]` | `DOB: 04/12/1990` |

Patterns are deliberately conservative and ordered so the most specific match wins (a routing number is never also
counted as an account number). Free-text names are **not** detected: without a label they're indistinguishable from
employer and merchant names. That limit is stated rather than papered over.

## Roadmap

| # | Milestone | Status |
|---|---|---|
| 1 | Core redactor, service provider, facade, publishable config (disable types, add custom patterns) | in progress |
| 2 | `Redacted` Eloquent cast, Monolog processor for log scrubbing, v0.1.0 | planned |

## Licence

MIT
