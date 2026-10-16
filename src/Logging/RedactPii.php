<?php

namespace AdilKhan\PiiRedactor\Logging;

use AdilKhan\PiiRedactor\PiiRedactor;
use Illuminate\Log\Logger;

/**
 * Channel tap: add to any channel in config/logging.php
 *
 *     'stack' => ['driver' => 'stack', 'channels' => ['single'], 'tap' => [RedactPii::class]],
 */
class RedactPii
{
    public function __construct(private readonly PiiRedactor $redactor) {}

    public function __invoke(Logger $logger): void
    {
        $logger->pushProcessor(new RedactPiiProcessor($this->redactor));
    }
}
