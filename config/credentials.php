<?php
/**
 * Generador de credenciales de Google Service Account
 * Usa variables de entorno para crear el JSON de credenciales
 */

require_once __DIR__ . '/env.php';

function getGoogleServiceAccountCredentials() {
    return [
        'type' => getenv('GOOGLE_SERVICE_ACCOUNT_TYPE') ?: 'service_account',
        'project_id' => getenv('GOOGLE_SERVICE_ACCOUNT_PROJECT_ID'),
        'private_key_id' => getenv('GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY_ID'),
        'private_key' => getenv('GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY'),
        'client_email' => getenv('GOOGLE_SERVICE_ACCOUNT_CLIENT_EMAIL'),
        'client_id' => getenv('GOOGLE_SERVICE_ACCOUNT_CLIENT_ID'),
        'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
        'token_uri' => 'https://oauth2.googleapis.com/token',
        'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
        'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/' . urlencode(getenv('GOOGLE_SERVICE_ACCOUNT_CLIENT_EMAIL')),
        'universe_domain' => 'googleapis.com'
    ];
}

function getGoogleServiceAccountJson() {
    return json_encode(getGoogleServiceAccountCredentials(), JSON_PRETTY_PRINT);
}
?>