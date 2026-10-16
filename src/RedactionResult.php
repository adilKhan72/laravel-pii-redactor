<?php

namespace AdilKhan\PiiRedactor;

final readonly class RedactionResult
{
    /**
     * @param  array<string, int>  $counts  placeholder type => number of replacements (never the values)
     */
    public function __construct(
        public string $text,
        public array $counts,
    ) {}

    public function total(): int
    {
        return array_sum($this->counts);
    }

    public function changed(): bool
    {
        return $this->counts !== [];
    }
}
