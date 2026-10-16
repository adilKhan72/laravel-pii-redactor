<?php

namespace AdilKhan\PiiRedactor;

use InvalidArgumentException;

/**
 * Replaces personal identifiers with typed placeholders.
 *
 * Pattern-based by design: predictable, fast and unit-testable. Order matters (most specific first), so a labelled
 * routing number isn't later re-matched as an account number.
 */
class PiiRedactor
{
    /**
     * @var array<string, list<string>>
     */
    public const BUILT_IN = [
        'SSN' => [
            '/\b\d{3}-\d{2}-\d{4}\b/',
            '/\b(?:SSN|SS#|Social Security(?: No\.?| Number)?)\s*[:#]?\s*\d{9}\b/i',
        ],
        'ROUTING_NUMBER' => [
            '/\b(?:Routing|RTN|ABA)(?: No\.?| Number| #)?\s*[:#]?\s*\d{9}\b/i',
        ],
        'ACCOUNT_NUMBER' => [
            '/\b(?:Account|Acct)(?: No\.?| Number| #)?\s*[:#]?\s*[X*]*\d[\d\- ]{2,16}\d\b/i',
        ],
        'CARD_NUMBER' => [
            '/\b(?:\d{4}[ -]){3}\d{4}\b/',
        ],
        'EMAIL' => [
            '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i',
        ],
        'PHONE' => [
            '/(?<!\d)(?:\+1[ .-]?)?\(?\d{3}\)?[ .-]\d{3}[ .-]\d{4}(?!\d)/',
        ],
        // Labelled names only. Name words are Capitalised-lowercase and stop at the next label ("Employee ID").
        'NAME' => [
            '/\b(?:Employee(?: Name)?|Account Holder|Pay to(?: the order of)?|Name)\s*:\s*[A-Z][a-z\'-]+(?:\s+(?!(?:Employee|Pay|Account|Date|Dept|Check|Period|SSN)\b)[A-Z][a-z\'-]+){1,3}/',
        ],
        'DATE_OF_BIRTH' => [
            '/\b(?:DOB|Date of Birth|Birth ?Date)\s*[:#]?\s*\d{1,2}[\/.-]\d{1,2}[\/.-]\d{2,4}\b/i',
        ],
    ];

    /**
     * @var array<string, list<string>>
     */
    private array $patterns = [];

    /**
     * @param  list<string>  $types  built-in types to enable, in order
     * @param  array<string, list<string>>  $custom  extra TYPE => patterns, run after the built-ins
     */
    public function __construct(array $types = [], array $custom = [])
    {
        foreach ($types ?: array_keys(self::BUILT_IN) as $type) {
            if (! isset(self::BUILT_IN[$type])) {
                throw new InvalidArgumentException("Unknown PII type [{$type}]. Known: ".implode(', ', array_keys(self::BUILT_IN)));
            }
            $this->patterns[$type] = self::BUILT_IN[$type];
        }

        foreach ($custom as $type => $patterns) {
            if (! preg_match('/^[A-Z][A-Z0-9_]*$/', $type)) {
                throw new InvalidArgumentException("Custom PII type [{$type}] must be UPPER_SNAKE_CASE.");
            }
            foreach ($patterns as $pattern) {
                if (@preg_match($pattern, '') === false) {
                    throw new InvalidArgumentException("Invalid regex for custom PII type [{$type}]: {$pattern}");
                }
            }
            $this->patterns[$type] = array_values($patterns);
        }
    }

    public function redact(string $text): RedactionResult
    {
        $counts = [];

        foreach ($this->patterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                $text = preg_replace_callback($pattern, function () use ($type, &$counts) {
                    $counts[$type] = ($counts[$type] ?? 0) + 1;

                    return "[{$type}]";
                }, $text);
            }
        }

        return new RedactionResult($text, $counts);
    }

    /**
     * Redacts every string in a (nested) array, e.g. a log context or an API payload. Keys are kept.
     *
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public function redactArray(array $data): array
    {
        return array_map(fn ($value) => match (true) {
            is_string($value) => $this->redact($value)->text,
            is_array($value) => $this->redactArray($value),
            default => $value,
        }, $data);
    }

    /**
     * @return list<string>
     */
    public function types(): array
    {
        return array_keys($this->patterns);
    }
}
