<?php

namespace App\Services;

class ApiEncryption {
    private $encryptionKey = 'tu_clave_secreta_personalizada'; // Clave fija para API

    public function encrypt($data) {
        // Convertir a JSON si es un array
        if (is_array($data)) {
            $data = json_encode($data);
        }
        
        // Generar IV aleatorio
        $iv = openssl_random_pseudo_bytes(16);
        
        // Asegurarse de que la clave tenga 32 bytes (para AES-256)
        $key = str_pad($this->encryptionKey, 32, '0');
        
        // Encriptar con AES-256-CBC
        $encrypted = openssl_encrypt(
            $data,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,  // Usar datos binarios
            $iv
        );
        
        // Combinar IV y datos encriptados y codificar en base64
        return base64_encode($iv . $encrypted);
    }
    
    public function decrypt($encryptedData) {
        // Decodificar de base64
        $decoded = base64_decode($encryptedData);
        
        // Extraer IV (primeros 16 bytes)
        $iv = substr($decoded, 0, 16);
        
        // Extraer datos encriptados
        $encrypted = substr($decoded, 16);
        
        // Asegurarse de que la clave tenga 32 bytes (para AES-256)
        $key = str_pad($this->encryptionKey, 32, '0');
        
        // Desencriptar
        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,  // Usar datos binarios
            $iv
        );
        
        return $decrypted;
    }
}

