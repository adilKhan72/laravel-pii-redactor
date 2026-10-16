<?php

namespace AdilKhan\PiiRedactor\Tests;

use AdilKhan\PiiRedactor\Facades\Pii;
use AdilKhan\PiiRedactor\PiiRedactor;

class ServiceProviderTest extends TestCase
{
    public function test_the_redactor_is_a_configured_singleton(): void
    {
        $this->assertSame($this->app->make(PiiRedactor::class), $this->app->make(PiiRedactor::class));
        $this->assertContains('SSN', $this->app->make(PiiRedactor::class)->types());
    }

    public function test_the_facade_redacts(): void
    {
        $this->assertSame('mail [EMAIL]', Pii::redact('mail jane@example.com')->text);
    }

    public function test_config_controls_types_and_custom_patterns(): void
    {
        config()->set('pii-redactor.types', ['EMAIL']);
        config()->set('pii-redactor.custom', ['TICKET' => ['/\bTCK-\d+\b/']]);
        $this->app->forgetInstance(PiiRedactor::class);

        $this->assertSame(['EMAIL', 'TICKET'], Pii::types());
        $this->assertSame('[EMAIL] [TICKET] 900-12-3456', Pii::redact('a@b.co TCK-99 900-12-3456')->text);
    }

    public function test_config_is_publishable(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'pii-redactor-config'])->assertSuccessful();

        $this->assertFileExists(config_path('pii-redactor.php'));
        unlink(config_path('pii-redactor.php'));
    }
}
