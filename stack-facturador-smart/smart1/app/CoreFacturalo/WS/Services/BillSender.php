<?php

namespace App\CoreFacturalo\WS\Services;

use App\CoreFacturalo\WS\Response\BillResult;
use Illuminate\Support\Facades\Log;

/**
 * Class BillSender.
 */
class BillSender extends BaseSunat
{
    /**
     * @param string $filename
     * @param string $content
     *
     * @return mixed
     */
    public function send($filename, $content)
    {
        $client = $this->getClient();
        $result = new BillResult();

        try {
            $zipContent = $this->compress($filename.'.xml', $content);
            $params = [
                'fileName' => $filename.'.zip',
                'contentFile' => $zipContent,
            ];
            $response = $client->call('sendBill', ['parameters' => $params]);
            $cdrZip = $response->applicationResponse;
            $result
                ->setCdrResponse($this->extractResponse($cdrZip))
                ->setCdrZip($cdrZip)
                ->setSuccess(true);
        } catch (\SoapFault $e) {
            // Log::error('No xml: ' . $content);
            $result->setError($this->getErrorFromFault($e));
        }

        return $result;
    }
}
