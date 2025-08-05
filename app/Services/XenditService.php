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
        $config = Configuration::getDefaultConfiguration()
            ->setApiKey(config('services.xendit.api_key'));
        $this->invoiceApi = new InvoiceApi(null, $config);
    }

    public function createInvoice($params)
    {
        Log::info('Xendit createInvoice params', $params);
        $invoiceRequest = new CreateInvoiceRequest($params);
        Log::info('Xendit CreateInvoiceRequest', $invoiceRequest->jsonSerialize());
        return $this->invoiceApi->createInvoice($invoiceRequest);
    }

    public function getInvoice($id)
    {
        return $this->invoiceApi->getInvoiceById($id);
    }
}
