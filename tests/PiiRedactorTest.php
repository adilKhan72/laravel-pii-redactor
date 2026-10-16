<?php

namespace AdilKhan\PiiRedactor\Tests;

use AdilKhan\PiiRedactor\PiiRedactor;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PiiRedactorTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function identifiers(): array
    {
        return [
            'dashed ssn' => ['Employee SSN: 900-12-3456 Dept 4', 'Employee SSN: [SSN] Dept 4', 'SSN'],
            'labelled 9-digit ssn' => ['Social Security Number: 900123456', '[SSN]', 'SSN'],
            'routing number' => ['Routing #: 121000358', '[ROUTING_NUMBER]', 'ROUTING_NUMBER'],
            'account number' => ['Account No. 0012345678 checking', '[ACCOUNT_NUMBER] checking', 'ACCOUNT_NUMBER'],
            'masked account number' => ['Acct: XXXX1234', '[ACCOUNT_NUMBER]', 'ACCOUNT_NUMBER'],
            'card number' => ['Card 4111 1111 1111 1111 exp', 'Card [CARD_NUMBER] exp', 'CARD_NUMBER'],
            'email' => ['Contact jane.doe@example.com today', 'Contact [EMAIL] today', 'EMAIL'],
            'phone' => ['Call (555) 010-2000 now', 'Call [PHONE] now', 'PHONE'],
            'international phone' => ['Call +1 555-010-2000', 'Call [PHONE]', 'PHONE'],
            'date of birth' => ['DOB: 04/12/1990', '[DATE_OF_BIRTH]', 'DATE_OF_BIRTH'],
            'labelled name stops at next label' => ['Employee: Jordan Avery Employee ID: 7731', '[NAME] Employee ID: 7731', 'NAME'],
            'account holder name' => ['Account Holder: Maria Ruiz', '[NAME]', 'NAME'],
        ];
    }

    #[DataProvider('identifiers')]
    public function test_it_redacts_each_identifier_type(string $input, string $expected, string $type): void
    {
        $result = (new PiiRedactor)->redact($input);

        $this->assertSame($expected, $result->text);
        $this->assertSame(1, $result->counts[$type]);
    }

    public function test_numbers_that_are_not_pii_survive(): void
    {
        $text = "Pay Period: 09/01/2026 - 09/14/2026\nGROSS PAY 2,107.00 YTD 36,750.00\nOrder 48213 total 120.50";

        $result = (new PiiRedactor)->redact($text);

        $this->assertSame($text, $result->text);
        $this->assertFalse($result->changed());
    }

    public function test_counts_are_reported_without_values(): void
    {
        $result = (new PiiRedactor)->redact('SSN 900-12-3456 and 900-65-4321, email a@b.co');

        $this->assertSame(['SSN' => 2, 'EMAIL' => 1], $result->counts);
        $this->assertSame(3, $result->total());
        $this->assertStringNotContainsString('900', $result->text);
    }

    public function test_specific_patterns_win_over_general_ones(): void
    {
        $result = (new PiiRedactor)->redact('Routing Number: 121000358 Account Number: 000123456789');

        $this->assertSame('[ROUTING_NUMBER] [ACCOUNT_NUMBER]', $result->text);
    }

    public function test_types_can_be_limited_and_reordered(): void
    {
        $redactor = new PiiRedactor(['EMAIL']);

        $this->assertSame(['EMAIL'], $redactor->types());
        $this->assertSame('SSN 900-12-3456 [EMAIL]', $redactor->redact('SSN 900-12-3456 x@y.io')->text);
    }

    public function test_custom_patterns_get_their_own_placeholder(): void
    {
        $redactor = new PiiRedactor([], ['EMPLOYEE_ID' => ['/\bEMP-\d{6}\b/']]);

        $result = $redactor->redact('Badge EMP-004213 issued');

        $this->assertSame('Badge [EMPLOYEE_ID] issued', $result->text);
        $this->assertSame(['EMPLOYEE_ID' => 1], $result->counts);
    }

    public function test_misconfiguration_fails_loudly(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PiiRedactor(['PASSPORT']);
    }

    public function test_invalid_custom_regex_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PiiRedactor([], ['BADGE' => ['/unclosed(/']]);
    }

    public function test_nested_arrays_are_redacted_and_keys_kept(): void
    {
        $out = (new PiiRedactor)->redactArray([
            'user' => ['email' => 'jane@example.com', 'age' => 41],
            'note' => 'call (555) 010-2000',
        ]);

        $this->assertSame(['user' => ['email' => '[EMAIL]', 'age' => 41], 'note' => 'call [PHONE]'], $out);
    }
}
