<?php

namespace AdilKhan\PiiRedactor\Logging;

use AdilKhan\PiiRedactor\PiiRedactor;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Scrubs PII from the message, context and extra of every log record before any handler sees it.
 */
class RedactPiiProcessor implements ProcessorInterface
{
    public function __construct(private readonly PiiRedactor $redactor) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with(
            message: $this->redactor->redact($record->message)->text,
            context: $this->redactor->redactArray($record->context),
            extra: $this->redactor->redactArray($record->extra),
        );
    }
}
