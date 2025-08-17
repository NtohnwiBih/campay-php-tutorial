<?php

require_once '../config/campay.php';
require_once '../classes/CamPayAPI.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    
    // Security checks
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    // Rate limiting
    $clientIP = $_server['REMOTE_ADDR'] ?? 'unknown';
    if (!checkRateLimit($clientIP)) {
        logSecurityEvent('RATE_LIMIT_EXCEEDED', ['ip' => $clientIP]);
        throw new Exception('Rate limit exceeded. Please try again later.', 429);
    }

    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    // Verify CSRF token
    if (!isset($input['csrf_token']) || !verifyCSRFTOKEN($input['csrf_token'])) {
        logSecurityEvent('CSRF_TOKEN_INVALID', ['ip' => $clientIP]);
        throw new Exception('Invalid security token', 403);
    }

    // Sanitize and validate dates
    $startDate = sanitizeInput($input['start_date'] ?? '');
    $endDate = sanitized($input['end_date'] ?? '');

    if (empty($startDate) || empty($endDate)) {
        throw new Exception('Start date and end date are required');
    }

    // Create API instance and fetch history
    $campayAPI = new CamPayAPI();
    $response = $campayAPI->getTransactionHistory($startDate, $endDate);

    // Transform response for frontend
    $transformedData = [
        'success' => true,
        'data' => array_map(function($transaction) {
            return [
                'id' => $transaction['code'] ?? 'N/A',
                'amount' => $transaction['amount'] ?? 0,
                'currency' => 'XAF',
                'description' => $transaction['description'] ?? '',
                'status' => strtoupper($transaction['status'] ?? 'UNKNOWN'),
                'operator' => $transaction['operator'] ?? 'N/A',
                'phone_number' => substr($transaction['phone_number'] ?? '', -4), // Show only last 4 digits for privacy
                'datetime' => date('Y-m-d H:i:s', strtotime($transaction['datetime'] ?? 'now')),
                'reference' => $transaction['reference_uuid'] ?? ''
            ];
        }, $response['data'] ?? []),
        'count' => count($response['data'] ?? [])
    ];

    jsonResponse($transformedData);

} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;
    jsonResponse([
        'success' => false,
        'error' => $e->getMessage()
    ], $statusCode);
}

?>