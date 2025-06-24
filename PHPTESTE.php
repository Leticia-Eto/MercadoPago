<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once __DIR__ . '/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;

MercadoPagoConfig::setAccessToken("TEST-1257163527376609-062413-f7088612d1843fbf7714ba8585561022-194484818");

$data = json_decode(file_get_contents("php://input"), true);
$nome = isset($data['nome']) ? trim($data['nome']) : '';
$userId = isset($data['user_id']) ? intval($data['user_id']) : 0;

if ($nome === '' || !is_numeric($userId) || $userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Parâmetros inválidos']);
    exit;
}

try {
    $client = new PaymentClient();
    file_put_contents("debug_payload.txt", json_encode([
    "transaction_amount" => 0.01,
    "description" => "Plano Plus",
    "payment_method_id" => "pix",
    "payer" => ["email" => "TESTUSER360938794@testuser.com"],
    "metadata" => ["user_id" => $userId]
], JSON_PRETTY_PRINT));


    $payment = $client->create([
        "transaction_amount" => 0.01,
        "description" => "Plano Plus",
        "payment_method_id" => "pix",
        "payer" => [
            "email" => "TESTUSER360938794@testuser.com"
        ],
        "metadata" => [
            "user_id" => $userId
        ]
    ]);

    echo json_encode([
        "success" => true,
        "status" => $payment->status,
        "qr_code_base64" => $payment->point_of_interaction->transaction_data->qr_code_base64,
        "qr_code" => $payment->point_of_interaction->transaction_data->qr_code,
        "id" => $payment->id
    ]);

} catch (MPApiException $e) {
    http_response_code(500);

    // Captura a resposta crua da API
    $rawResponse = $e->getApiResponse();
    $errorMessage = $e->getMessage();

    // Salva no log
    file_put_contents("mp_error_log.txt", json_encode([
        'error_message' => $errorMessage,
        'raw_response' => $rawResponse
    ], JSON_PRETTY_PRINT));

    echo json_encode([
        'success' => false,
        'error' => 'Erro da API Mercado Pago',
        'message' => $errorMessage,
        'raw_response' => $rawResponse
    ]);
}
