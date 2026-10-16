# Changelog

All notable changes to this package are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/)
and the project uses [Semantic Versioning](https://semver.org/).

## [0.1.0] - 2026-10-16

### Added
- `PiiRedactor` with 8 built-in detectors (SSN, routing, account, card, email, phone, labelled name, DOB), typed
  placeholders and counts-only `RedactionResult`.
- Configurable types and validated custom patterns; `redactArray()` for nested payloads.
- Auto-discovered service provider, `Pii` facade, publishable config.
- `Redacted` Eloquent cast (redacts on write).
- `RedactPiiProcessor` (Monolog 3) and `RedactPii` channel tap for log scrubbing.
- Laravel 12 and 13, PHP 8.3+.
