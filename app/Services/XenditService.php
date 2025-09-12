<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Xendit\Invoice\InvoiceApi;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;

class XenditService
{
    protected $invoiceApi;

    public function __construct()
    {
        $apiKey = config('services.xendit.api_key') ?? config('services.xendit.secret_key');
        $config = Configuration::getDefaultConfiguration()->setApiKey($apiKey);
        $this->invoiceApi = new InvoiceApi(null, $config);
    }

    /**
     * Always returns a plain PHP array for easier consumption by controllers.
     */
    public function createInvoice($params): array
    {
        // Normalize params
        if (is_object($params)) {
            $params = (array) $params;
        }

        try {
            Log::info('Xendit createInvoice params', (array) $params);

            $invoiceRequest = new CreateInvoiceRequest($params);
            $serialized = $invoiceRequest->jsonSerialize();
            Log::info('Xendit CreateInvoiceRequest', (array) $serialized);

            $response = $this->invoiceApi->createInvoice($invoiceRequest);

            $data = $this->normalizeResponse($response);
            Log::info('Xendit invoice created', $data);

            return $data;
        } catch (\Throwable $e) {
            Log::error('Xendit createInvoice failed', [
                'message' => $e->getMessage(),
                'params' => $params,
            ]);
            throw $e;
        }
    }

    public function getInvoice($id): array
    {
        $response = $this->invoiceApi->getInvoiceById($id);
        return $this->normalizeResponse($response);
    }

    /**
     * Normalize SDK object/array to plain array.
     */
    private function normalizeResponse($response): array
    {
        if (is_array($response)) {
            return $response;
        }
        if (is_object($response)) {
            if (method_exists($response, 'jsonSerialize')) {
                $serialized = $response->jsonSerialize();
                return is_array($serialized) ? $serialized : (array) $serialized;
            }
            // Fallback: deep cast via json encode/decode
            return json_decode(json_encode($response), true) ?? [];
        }
        return [];
    }
}
