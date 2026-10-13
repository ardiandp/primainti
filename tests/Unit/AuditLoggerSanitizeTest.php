<?php

namespace Tests\Unit;

use App\Services\AuditLogger;
use PHPUnit\Framework\TestCase;

class AuditLoggerSanitizeTest extends TestCase
{
    public function test_password_and_secret_keys_are_redacted(): void
    {
        $clean = AuditLogger::sanitize([
            'name' => 'Budi',
            'password' => 'rahasia',
            'password_confirmation' => 'rahasia',
            'api_key' => 'abc123',
            'token' => 'xyz',
        ]);

        $this->assertSame('Budi', $clean['name']);
        $this->assertSame('[REDACTED]', $clean['password']);
        $this->assertSame('[REDACTED]', $clean['password_confirmation']);
        $this->assertSame('[REDACTED]', $clean['api_key']);
        $this->assertSame('[REDACTED]', $clean['token']);
    }

    public function test_nested_arrays_are_sanitized_recursively(): void
    {
        $clean = AuditLogger::sanitize([
            'user' => [
                'email' => 'a@b.test',
                'credentials' => ['password' => 'rahasia'],
            ],
        ]);

        $this->assertSame('a@b.test', $clean['user']['email']);
        $this->assertSame('[REDACTED]', $clean['user']['credentials']['password']);
    }
}
