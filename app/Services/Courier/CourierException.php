<?php

namespace App\Services\Courier;

use RuntimeException;

/**
 * A courier call that did not succeed. `retryable` separates a transient
 * failure (timeout, 5xx, rate limit) from a permanent rejection (bad phone
 * number, duplicate invoice, invalid credentials) so the job knows whether
 * trying again could ever help.
 */
class CourierException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly bool $retryable = false,
        public readonly ?array $response = null,
        public readonly ?int $statusCode = null,
    ) {
        parent::__construct($message);
    }

    public static function transient(string $message, ?int $statusCode = null, ?array $response = null): self
    {
        return new self($message, true, $response, $statusCode);
    }

    public static function permanent(string $message, ?int $statusCode = null, ?array $response = null): self
    {
        return new self($message, false, $response, $statusCode);
    }
}
