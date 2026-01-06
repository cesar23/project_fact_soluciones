<?php

return [
    'signature_note' => env('SIGNATURE_NOTE_OSE', 'FACTURALO'),
    'signature_uri' => env('SIGNATURE_URI_OSE', 'signatureFACTURALO'),
    'api_service_url' => env('API_SERVICE_URL'),
    'api_service_token' => env('API_SERVICE_TOKEN', false),
    'sunat_alternate_server' => env('SUNAT_ALTERNATE_SERVER', false),
    'app_url_base' => env('APP_URL_BASE'),
    'pse_ip_server' => env('PSE_IP_SERVER'),
    'pse_interface' => env('PSE_INTERFACE'),
    'pse_db_name' => env('PSE_DB_NAME'),
    'pse_url' => env('PSE_URL'),
    'pse_user_name'=>env('PSE_USER_NAME'),
    'pse_user_password'=>env('PSE_USER_PASSWORD'),
    'pse_url_send'=>env('PSE_URL_SEND'),
    'pse_url_download'=>env('PSE_URL_DOWNLOAD'),
    'min_send_pse'=>env('MIN_SEND_PSE',3),
    'app_dev'=>env('APP_DEV',false),
];
