<?php

namespace AdilKhan\PiiRedactor\Tests;

use AdilKhan\PiiRedactor\Casts\Redacted;
use AdilKhan\PiiRedactor\Logging\RedactPii;
use AdilKhan\PiiRedactor\Logging\RedactPiiProcessor;
use AdilKhan\PiiRedactor\PiiRedactor;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Monolog\Level;
use Monolog\LogRecord;

class SupportTicket extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['notes' => Redacted::class];
    }
}

class CastAndLoggingTest extends TestCase
{
    public function test_the_cast_redacts_before_the_value_is_stored(): void
    {
        $ticket = new SupportTicket(['notes' => 'Customer jane@example.com, SSN 900-12-3456, wants a refund']);

        $this->assertSame('Customer [EMAIL], SSN [SSN], wants a refund', $ticket->getAttributes()['notes']);
    }

    public function test_the_cast_leaves_non_strings_alone(): void
    {
        $this->assertNull((new SupportTicket(['notes' => null]))->getAttributes()['notes']);
    }

    public function test_the_processor_scrubs_message_context_and_extra(): void
    {
        $record = new LogRecord(
            datetime: new DateTimeImmutable,
            channel: 'app',
            level: Level::Info,
            message: 'Login failed for jane@example.com',
            context: ['user' => ['phone' => '(555) 010-2000'], 'attempts' => 3],
            extra: ['ip_note' => 'card 4111 1111 1111 1111'],
        );

        $out = (new RedactPiiProcessor(new PiiRedactor))($record);

        $this->assertSame('Login failed for [EMAIL]', $out->message);
        $this->assertSame(['user' => ['phone' => '[PHONE]'], 'attempts' => 3], $out->context);
        $this->assertSame(['ip_note' => 'card [CARD_NUMBER]'], $out->extra);
    }

    public function test_the_tap_scrubs_a_real_log_channel(): void
    {
        $path = sys_get_temp_dir().'/pii-'.uniqid().'.log';
        config()->set('logging.channels.redacted', ['driver' => 'single', 'path' => $path, 'tap' => [RedactPii::class]]);

        Log::channel('redacted')->warning('Refund for Account No. 0012345678', ['email' => 'jane@example.com']);

        $written = file_get_contents($path);
        $this->assertStringContainsString('Refund for [ACCOUNT_NUMBER]', $written);
        $this->assertStringContainsString('"email":"[EMAIL]"', $written);
        $this->assertStringNotContainsString('jane@example.com', $written);
        unlink($path);
    }
}
