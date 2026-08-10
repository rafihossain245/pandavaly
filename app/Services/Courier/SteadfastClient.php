<?php

namespace App\Services\Courier;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Thin transport for the Steadfast Courier API (portal.packzy.com/api/v1).
 *
 * Every endpoint and field name the integration depends on lives in this class,
 * so verifying the contract against Steadfast's own docs means reading one file.
 *
 * The API answers HTTP 200 with its real outcome in the body's `status` field,
 * so both layers are checked before a call is called a success.
 */
class SteadfastClient
{
    public function __construct(private readonly array $config)
    {
    }

    public function driver(): string
    {
        return $this->config['driver'] ?? 'log';
    }

    public function isLive(): bool
    {
        return $this->driver() === 'api';
    }

    /** Live mode needs both keys; without them every call would 401. */
    public function isConfigured(): bool
    {
        return filled($this->config['api_key'] ?? null) && filled($this->config['secret_key'] ?? null);
    }

    /**
     * Creates one consignment.
     *
     * @return array{consignment_id: ?string, tracking_code: ?string, delivery_status: ?string, raw: array}
     */
    public function createOrder(array $payload): array
    {
        if (! $this->isLive()) {
            return $this->pretendToCreate($payload);
        }

        $body = $this->post('create_order', $payload);
        $consignment = $body['consignment'] ?? [];

        // A 200 with no consignment means the call shape changed or the parcel
        // was rejected without an error status — do not record a phantom success.
        if (empty($consignment['consignment_id'])) {
            throw CourierException::permanent(
                'Steadfast accepted the request but returned no consignment id. Response: '
                . json_encode($body),
                response: $body,
            );
        }

        return [
            'consignment_id' => (string) $consignment['consignment_id'],
            'tracking_code' => $consignment['tracking_code'] ?? null,
            'delivery_status' => $consignment['status'] ?? null,
            'raw' => $body,
        ];
    }

    /** Current delivery status for a consignment. */
    public function statusByConsignmentId(string $consignmentId): ?string
    {
        if (! $this->isLive()) {
            return null;
        }

        $body = $this->get('status_by_cid/' . urlencode($consignmentId));

        return $body['delivery_status'] ?? null;
    }

    /** Account balance — the cheapest call, so it doubles as a credentials check. */
    public function balance(): array
    {
        if (! $this->isLive()) {
            return ['current_balance' => null, 'driver' => $this->driver()];
        }

        return $this->get('get_balance');
    }

    /* ------------------------------------------------------------------ */

    /**
     * Development mode: log exactly what would have been sent and return a
     * clearly fake consignment id, so the whole pipeline can be exercised
     * without creating a real shipment.
     */
    private function pretendToCreate(array $payload): array
    {
        Log::info('[steadfast:log] create_order would be sent', $payload);

        return [
            'consignment_id' => 'LOG-' . strtoupper(Str::random(10)),
            'tracking_code' => 'LOGTRACK' . strtoupper(Str::random(6)),
            'delivery_status' => 'in_review',
            'raw' => ['driver' => 'log', 'payload' => $payload],
        ];
    }

    private function post(string $path, array $payload): array
    {
        return $this->send('post', $path, $payload);
    }

    private function get(string $path): array
    {
        return $this->send('get', $path);
    }

    private function send(string $method, string $path, array $payload = []): array
    {
        if (! $this->isConfigured()) {
            throw CourierException::permanent(
                'Steadfast API key and secret are not set. Add STEADFAST_API_KEY and STEADFAST_SECRET_KEY to .env.'
            );
        }

        $url = rtrim($this->config['base_url'], '/') . '/' . ltrim($path, '/');

        try {
            $request = Http::withHeaders([
                'Api-Key' => $this->config['api_key'],
                'Secret-Key' => $this->config['secret_key'],
                'Accept' => 'application/json',
            ])->timeout($this->config['timeout'] ?? 20)->asJson();

            $response = $method === 'post'
                ? $request->post($url, $payload)
                : $request->get($url);
        } catch (ConnectionException $e) {
            // Never reached their server — always worth another attempt.
            throw CourierException::transient('Could not reach Steadfast: ' . $e->getMessage());
        }

        $body = $response->json();
        if (! is_array($body)) {
            $body = ['raw' => $response->body()];
        }

        if ($response->serverError() || $response->status() === 429) {
            throw CourierException::transient(
                'Steadfast returned HTTP ' . $response->status() . '. ' . $this->messageFrom($body),
                $response->status(),
                $body,
            );
        }

        if ($response->failed()) {
            throw CourierException::permanent(
                'Steadfast rejected the request (HTTP ' . $response->status() . '). ' . $this->messageFrom($body),
                $response->status(),
                $body,
            );
        }

        // 200 with a non-200 status field is still a rejection.
        if (isset($body['status']) && (int) $body['status'] !== 200) {
            throw CourierException::permanent(
                'Steadfast rejected the request. ' . $this->messageFrom($body),
                (int) $body['status'],
                $body,
            );
        }

        return $body;
    }

    private function messageFrom(array $body): string
    {
        if (! empty($body['errors']) && is_array($body['errors'])) {
            $first = collect($body['errors'])->flatten()->first();
            if ($first) {
                return (string) $first;
            }
        }

        return (string) ($body['message'] ?? 'No message returned.');
    }
}
