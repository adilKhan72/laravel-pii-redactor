<?php

namespace AdilKhan\PiiRedactor\Facades;

use AdilKhan\PiiRedactor\PiiRedactor;
use AdilKhan\PiiRedactor\RedactionResult;
use Illuminate\Support\Facades\Facade;

/**
 * @method static RedactionResult redact(string $text)
 * @method static array<array-key, mixed> redactArray(array<array-key, mixed> $data)
 * @method static list<string> types()
 *
 * @see PiiRedactor
 */
class Pii extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PiiRedactor::class;
    }
}
