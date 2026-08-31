<?php

namespace App\Services\Courier;

/**
 * Validator for Steadfast API payloads.
 *
 * Ensures all required fields meet Steadfast's specifications before sending.
 * Prevents API errors by catching issues early with clear messages.
 */
class CourierPayloadValidator
{
    /**
     * Validate a complete order payload.
     *
     * @throws CourierException when validation fails
     */
    public static function validate(array $payload): void
    {
        self::validateInvoice($payload['invoice'] ?? null);
        self::validateRecipient($payload);
        self::validateAddress($payload['recipient_address'] ?? null);
        self::validateCodAmount($payload['cod_amount'] ?? null);
        self::validateNote($payload['note'] ?? null);
        self::validateItemDescription($payload['item_description'] ?? null);
        self::validateDeliveryType($payload['delivery_type'] ?? null);
    }

    /**
     * Invoice: Must be unique, alphanumeric, hyphens, underscores allowed.
     * Pattern: alphanumeric with optional hyphens/underscores, max 50 chars.
     */
    private static function validateInvoice(?string $invoice): void
    {
        if (blank($invoice)) {
            throw CourierException::permanent('Invoice is required.');
        }

        if (strlen($invoice) > 50) {
            throw CourierException::permanent('Invoice must not exceed 50 characters.');
        }

        if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $invoice)) {
            throw CourierException::permanent(
                'Invoice must contain only alphanumeric characters, hyphens, and underscores.'
            );
        }
    }

    /**
     * Recipient: Name (required, max 100 chars), Phone (required, 11 digits BD format).
     */
    private static function validateRecipient(array $payload): void
    {
        // Recipient name
        $name = $payload['recipient_name'] ?? null;
        if (blank($name)) {
            throw CourierException::permanent('Recipient name is required.');
        }

        if (strlen($name) > 100) {
            throw CourierException::permanent('Recipient name must not exceed 100 characters.');
        }

        // Recipient phone (validation is done by CourierDispatcher::normalisePhone)
        $phone = $payload['recipient_phone'] ?? null;
        if (blank($phone)) {
            throw CourierException::permanent('Recipient phone is required.');
        }

        if (!preg_match('/^\d{11}$/', $phone)) {
            throw CourierException::permanent(
                'Recipient phone must be exactly 11 digits in format 01XXXXXXXXX.'
            );
        }

        // Alternative phone (optional but if present, must be valid)
        if (!blank($payload['alternative_phone'] ?? null)) {
            if (!preg_match('/^\d{11}$/', $payload['alternative_phone'])) {
                throw CourierException::permanent(
                    'Alternative phone must be exactly 11 digits in format 01XXXXXXXXX.'
                );
            }
        }
    }

    /**
     * Address: Required, max 250 characters.
     */
    private static function validateAddress(?string $address): void
    {
        if (blank($address)) {
            throw CourierException::permanent('Recipient address is required.');
        }

        if (strlen($address) > 250) {
            throw CourierException::permanent('Recipient address must not exceed 250 characters.');
        }
    }

    /**
     * COD Amount: Required, numeric, >= 0.
     */
    private static function validateCodAmount($amount): void
    {
        if (!isset($amount)) {
            throw CourierException::permanent('COD amount is required.');
        }

        if (!is_numeric($amount)) {
            throw CourierException::permanent('COD amount must be numeric.');
        }

        $amount = (float) $amount;
        if ($amount < 0) {
            throw CourierException::permanent('COD amount cannot be negative.');
        }

        // Reasonable upper limit (adjust based on your business)
        if ($amount > 1000000) {
            throw CourierException::permanent('COD amount exceeds reasonable limit (1,000,000 BDT).');
        }
    }

    /**
     * Note: Optional, max 255 characters (delivery instructions).
     */
    private static function validateNote(?string $note): void
    {
        if (blank($note)) {
            return;
        }

        if (strlen($note) > 255) {
            throw CourierException::permanent('Note/delivery instructions must not exceed 255 characters.');
        }
    }

    /**
     * Item Description: Optional, max 240 characters.
     */
    private static function validateItemDescription(?string $description): void
    {
        if (blank($description)) {
            return;
        }

        if (strlen($description) > 240) {
            throw CourierException::permanent('Item description must not exceed 240 characters.');
        }
    }

    /**
     * Delivery Type: Optional, must be 0 (home) or 1 (pickup point).
     */
    private static function validateDeliveryType($type): void
    {
        if ($type === null) {
            return;
        }

        if (!in_array((int) $type, [0, 1], true)) {
            throw CourierException::permanent(
                'Delivery type must be 0 (home delivery) or 1 (pickup point).'
            );
        }
    }

    /**
     * Validate a bulk orders array (max 500 items).
     *
     * @throws CourierException when validation fails
     */
    public static function validateBulk(array $orders): void
    {
        if (empty($orders)) {
            throw CourierException::permanent('Bulk order list cannot be empty.');
        }

        if (count($orders) > 500) {
            throw CourierException::permanent(
                'Bulk order limit exceeded. Maximum 500 orders per batch, got ' . count($orders) . '.'
            );
        }

        foreach ($orders as $index => $payload) {
            try {
                self::validate($payload);
            } catch (CourierException $e) {
                throw CourierException::permanent(
                    "Validation failed for order at index {$index} (invoice: " .
                    ($payload['invoice'] ?? 'unknown') . "): " . $e->getMessage()
                );
            }
        }
    }
}
