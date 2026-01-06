<?php

namespace App\Services\DocumentProcessors;

interface DocumentProcessorInterface
{
    public function process($cash_document, $status_type_id, &$methods_payment, &$result);
    public function processBatch($payment_ids, $status_type_id, &$methods_payment, &$result);
} 