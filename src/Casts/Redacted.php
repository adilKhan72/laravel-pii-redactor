<?php

namespace AdilKhan\PiiRedactor\Casts;

use AdilKhan\PiiRedactor\PiiRedactor;
use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Redacts on write: the original text never reaches the database. For free-text columns (support notes, chat
 * transcripts, feedback) that must be kept but must not hold personal data.
 *
 *     protected function casts(): array { return ['notes' => Redacted::class]; }
 */
class Redacted implements CastsInboundAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return is_string($value) ? app(PiiRedactor::class)->redact($value)->text : $value;
    }
}
