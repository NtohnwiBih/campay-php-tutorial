<?php

require_once '../config/campay.php';
require_once '../classes/CamPayAPI.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    
    // Security checks
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed', 405);
    }
    
    // Rate limiting
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!checkRateLimit($clientIP)) {
        logSecurityEvent('RATE_LIMIT_EXCEEDED', ['ip' => $clientIP]);
        throw new Exception('Rate limit exceeded. Please try again later.', 429);
    }
    
    // Get and validate reference from URL parameter
    $reference = sanitizeInput($_GET['reference'] ?? '');
    
    if (empty($reference)) {
        throw new Exception('Transaction reference is required');
    }
    
    // Additional validation for reference format
    if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $reference)) {
        throw new Exception('Invalid reference format');
    }
    
    // Create API instance and check status
    $campayAPI = new CamPayAPI();
    $response = $campayAPI->getTransactionStatus($reference);
    
    // Transform response for frontend
    $statusData = [
        'success' => true,
        'reference' => $reference,
        'status' => $response['status'] ?? 'UNKNOWN',
        'message' => $response['message'] ?? 'Status retrieved successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Add additional details if available
    if (isset($response['transaction'])) {
        $transaction = $response['transaction'];
        $statusData['transaction'] = [
            'amount' => $transaction['amount'] ?? 0,
            'currency' => 'XAF',
            'operator' => $transaction['operator'] ?? 'N/A',
            'datetime' => $transaction['datetime'] ?? null
        ];
    }
    
    jsonResponse($statusData);
    
} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;
    
    jsonResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'reference' => $reference ?? null
    ], $statusCode);
}
?>