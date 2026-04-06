<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FacturaComService
{
    private const PLUGIN_TOKEN = '9d4095c8f7ed5785cb14c0e3b033eeb8252416ed';

    public function isConfigured(): bool
    {
        return (bool) config('services.facturacom.api_key')
            && (bool) config('services.facturacom.secret_key');
    }

    public function createCfdi40(array $payload): array
    {
        $response = $this->request('POST', '/v4/cfdi40/create', $payload);

        return $response->json() ?? [
            'status' => 'error',
            'message' => 'Respuesta vacia de Factura.com',
        ];
    }

    public function getCfdiByUid(string $uid): array
    {
        $response = $this->request('GET', '/v4/cfdi/uid/' . urlencode($uid));

        return $response->json() ?? [
            'status' => 'error',
            'message' => 'Respuesta vacia de Factura.com',
        ];
    }

    public function getCfdiByUuid(string $uuid): array
    {
        $response = $this->request('GET', '/v4/cfdi/uuid/' . urlencode($uuid));

        return $response->json() ?? [
            'status' => 'error',
            'message' => 'Respuesta vacia de Factura.com',
        ];
    }

    public function getCfdiByOrder(string $order): array
    {
        $response = $this->request('GET', '/v4/cfdi/order/' . urlencode($order));

        return $response->json() ?? [
            'status' => 'error',
            'message' => 'Respuesta vacia de Factura.com',
        ];
    }

    private function request(string $method, string $endpoint, array $payload = []): Response
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Configura FACTURACOM_API_KEY y FACTURACOM_SECRET_KEY en tu .env');
        }

        $baseUrl = rtrim((string) config('services.facturacom.base_url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('Configura FACTURACOM_BASE_URL en tu .env');
        }

        $client = Http::baseUrl($baseUrl)
            ->timeout((int) config('services.facturacom.timeout', 20))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'F-PLUGIN' => self::PLUGIN_TOKEN,
                'F-Api-Key' => (string) config('services.facturacom.api_key'),
                'F-Secret-Key' => (string) config('services.facturacom.secret_key'),
            ]);

        $response = match (strtoupper($method)) {
            'GET' => $client->get($endpoint),
            'POST' => $client->post($endpoint, $payload),
            default => throw new RuntimeException('Metodo HTTP no soportado para Factura.com'),
        };

        if (! $response->ok()) {
            throw new RuntimeException(
                'Error HTTP Factura.com: ' . $response->status() . ' - ' . $response->body()
            );
        }

        return $response;
    }
}
