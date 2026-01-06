<?php

namespace App\Services;

use App\Models\System\Client;
use App\Models\System\EnvioClass as Envio;
use App\Models\System\Configuration;
use Exception;

class GekawaService
{
    public function sendMessage(Client $client, Envio $envio)
    {
        try {
            $configuration = Configuration::first();
            $message = $envio->descripcion;
            $telephone = $this->sanitizeTelephone($client->telephone);
    
            // Configurar cURL
            $curl = curl_init();
    
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://neoskynet.com/api/create-message',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'appkey' => $configuration->gekawa1,
                    'authkey' => $configuration->gekawa2,
                    'to' => '51' . $telephone,
                    'message' => $message,
                    'sandbox' => 'false'
                ),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: multipart/form-data'
                ),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ));
    
            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);
    
            $responseDecoded = json_decode($response, true);
    
            if ($responseDecoded && isset($responseDecoded['message_status']) && $responseDecoded['message_status'] === 'Success') {
                return ['success' => true, 'message' => '¡Mensaje enviado correctamente!'];
            } else {
                return ['success' => false, 'message' => $responseDecoded['message'] ?? 'Respuesta no válida', 'response' => $response];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al enviar el mensaje: ' . $e->getMessage()];
        }
    }  

    private function sanitizeTelephone($telephone)
    {
        // Eliminar el prefijo +51 si existe
        $telephone = preg_replace('/^\+?51/', '', $telephone);
        // Eliminar todos los espacios
        $telephone = str_replace(' ', '', $telephone);
        return $telephone;
    }
}
