<?php

namespace Modules\Services\Data;

use GuzzleHttp\Client;
use App\Models\System\Configuration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServiceData
{
    public static function service($type, $number)
    {
        if ($type == "ruc") {;
            if (substr($number, 0, 2) == "10") {
                $number = substr($number, 2, 8);
                $type = "dni";
            }
        }
        $configuration = Configuration::first();
        $url = $configuration->url_apiruc = !'' ? $configuration->url_apiruc : config('configuration.api_service_url');
        $token = $configuration->token_apiruc = !'' ? $configuration->token_apiruc : config('configuration.api_service_token');

        $client = new Client(['base_uri' => $url, 'verify' => false]);
        $parameters = [
            'http_errors' => false,
            'connect_timeout' => 5,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ];

        $res = $client->request('GET', '/api/' . $type . '/' . $number, $parameters);
        $response = json_decode($res->getBody()->getContents(), true);

        return $response;
    }

    public function validar_cpe($ruc, $usuario, $clave, $file)
    {
        try {
            $configuration = Configuration::first();
            $this->client = new Client(['base_uri' => $configuration->url_apiruc, 'verify' => false, 'http_errors' => false]);
            $curl = [
                CURLOPT_URL => $configuration->url_apiruc . '/api/validar/txt',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('file' => new \CURLFILE(public_path('storage/txt/' . $file)), 'ruc' => $ruc, 'usuario_sol' => $usuario, 'clave_sol' => $clave),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $configuration->token_apiruc,
                ),
                CURLOPT_SSL_VERIFYPEER => false,
            ];
            $responses = $this->client->request(strtoupper("POST"), '/api/validar/txt', [
                'curl' => $curl,
            ]);
            return $responses->getBody()->getContents();
        } catch (GuzzleHttp\Exception\RequestException $exception) {
            return $exception->getResponse()->getBody();
        }
    }
    public function validar_cpe_http($file)
    {
        try {
            $configuration = Configuration::first();
            $fileContent = Storage::disk('tenant')->get("txt/" . $file);
            
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 600
            ])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $configuration->token_apiruc,
                    'Accept' => 'application/json'
                ])
                ->attach(
                    'file',
                    $fileContent,
                    $file
                )
                ->post($configuration->url_apiruc . '/api/validar/txt');
            return $response->body();
        } catch (\Exception $exception) {
            Log::error("Línea 82 - Error al leer el archivo: " . $exception->getMessage());
            return $exception->getMessage();
        }
    }
    public function validarCpeWithHttp( $file)
    {
        try {
            $configuration = Configuration::first();
            
            // Verificar si el directorio existe y tiene permisos
            $storage_path = Storage::disk('tenant')->path("txt");
            if (!file_exists($storage_path)) {
                mkdir($storage_path, 0755, true);
                chgrp($storage_path, 'www-data'); // Asignar grupo correcto
            }
            
            // Log::info("Línea validarCpeWithHttp - Ruta del archivo: " . $storage_path . '/' . $file);
            
            $filePath = Storage::disk('tenant')->path("txt/" . $file);
            if (!file_exists($filePath)) {
                throw new \Exception("El archivo no existe en la ruta especificada: " . $filePath);
            }
            
            // Verificar permisos de lectura
            if (!is_readable($filePath)) {
                throw new \Exception("No hay permisos de lectura para el archivo: " . $filePath);
            }
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $configuration->token_apiruc,
            ])
            ->withOptions([
                'verify' => false,
                'timeout' => 600
            ])
            ->attach(
                'file',
                file_get_contents($filePath),
                $file
            )->post($configuration->url_apiruc . '/api/validar/txt');
            
            return $response->body();
        } catch (\Exception $exception) {
            Log::error("Línea validarCpeWithHttp - Error: " . $exception->getMessage());
            return $exception->getMessage();
        }
    }
}
