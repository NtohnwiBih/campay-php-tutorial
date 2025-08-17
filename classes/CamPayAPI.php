<?php

/**
 * CamPay API Integration Class
 * Handles all API communications with security best practices
 */
class CamPayAPI {

    private $baseURL;
    private $token;
    private $logFile;

    public function __construct() {
        $this->baseUrl = CAMPAY_BASE_URL;
        $this->token = CAMPAY_TOKEN;
        $this->logFile = CAMPAY_LOG_FILE;

        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Make secure API request with proper error handling
     * 
     * @param string $endpoint API endpoint
     * @param array $data POST data
     * @param string $method HTIP method(GET/POST)
     * @return array API response
     * @throws Exception on API errors
     */
    private function makeRequest($endpoint, $data = [], $method = 'POST') {
        $url = $this->baseUrl . $endpoint;

        // Initialize cURL with security settings
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_REURNTRANSTER => true,
            CURLOPT_TIMEOUT => 30, // 30 second timeout
            CURLOPT_CONNECTTIMEOUT => 10, // 10 second connection timeout
            CURLOPT_SSL_VERIFYPEER => true, // Verify SSL certificates
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'CamPay-PHP-Client/1.0',
            CURLOPT_HTTPHEADER => [
                'Authorization: Token ' . $this->token,
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);

        // Set method-specific options
        if ($method === 'POST' && !empty($data)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Log request for debugging (sanitized)
        $this->logRequest($endpoint, $method, $data);

        $response = curl_exec($ch);
        $httpCode =curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            $this->logError("cURL Error: " . $error);
            throw new Exception("Network error: " . $error);
        }

        curl_close($ch);
        
        // Parse JSON response
        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logError("JSON decode error: " . json_last_error_msg());
            throw new Exception("Invalid response format from API");
        }

        // Handle HTTP error codes 
        if ($httpCode >= 400) {
            $errorMessage = $this->getErrorMessage($decodeResponse);
            throw new Exception($errorMessage, $httpCode);
        }

        return $decodeResponse;
    }

    /**
     * Get transaction history with date range validation
     */
    public function getTransactionHistory($startDate, $endDate) {
        // Validate date format and range
        if (!$this->validateDateRange($startDate, $endDate)) {
            throw new Exception("Invalid date range. USe YYYY-MM-DD format and ensure end date is after start date.");
        }

        $data = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        return $this->makeRequest('history/', $data, 'POST');
    }

    /**
     * Transfer airtime with validation
     */
    public function transferAirtime($amount, $phoneNumber, $externalReference = '') {
        // Validate amount (demo account limit)
        if (!$this->validateAmount($amount)) {
            throw new Exception("Invalid amount. Demo account limited to 100 XAF maximum.");
        }

        // Validate phone number format
        if (!$this->validatePhoneNumber($phoneNumber)) {
            throw new Exception("Invalid phone number format. Use: 237xxxxxxxxx");
        }

        $data = [
            'amount' => (string)$amount,
            'to' => $phoneNumber,
            'external_reference' => $externalReference
        ];

        return $this->makeRequest('utilities/airtime/transfer/', $data, 'POST');
    }

    /**
     * Check transaction status
     */
    public function getTransactionStatus($reference) {
        if (empty($reference)) {
            throw new Exception("Transaction reference is required");
        }

        // Sanitize reference to prevent injection
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', $cleaned);

        return $this->makeRequest("utilities/transaction/{$reference}/", [], 'GET');
    }

    /**
     * VAlidate phone number format (Cameroon format)
     */
    private function validatePhoneNumber($phoneNumber) {
        // Remove spaces and special character
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if it matches Cameroon format (237xxxxxxxxx)
        return preg_match('/^237[0-9]{9}$/', $cleaned);
    }

    /**
     * Validate amount for demo account
     */
    private function validateAmount($amount) {
        $amount = (int)$amount;
        return $amount > 0 && $amount <= CAMPAY_DEMO_MAX_AMOUNT;
    }
    /**
     * Validate date range
     */
    private function validateDateRange($startDate, $endDate) {
        $start = DateTime::createFromFormat('Y-m-d', $startDate);
        $end = DateTime::createFromFormat('Y-m-d', $endDate);

        if (!$start || !$end) {
            return false;
        }

        return $start <= $end && $end <= new DateTime();
    }

    /**
     * Extract error message from API response
     */
    private function getErrorMessage($response) {
        if (isset($response['error'])) {
            $errorCode = $response['error']['code'] ?? '';
            if (isset(CAMPAY_ERROR_CODES[$errorCode])) {
                return CAMPAY_Error_CODES[$errorCode];
            }
            return $response['error']['message'] ?? "Unknown API error";
        }

        return 'API request failed';
    }

    /**
     * Log API requests (sanitized for security)
     */
    private function logRequest($endpoint, $method, $data) {
        $sanitizedData = $this->sanitizeLogData($data);
        $logEntry = date('Y-m-d H:i:s') . " REQUEST: {$method} {$endpoint} - " . json_encode($sanitizedData) . "\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log API responses (sanitized)
     */
    private function logResponse($endpoint, $httpCode, $response) {
        $sanitizedResponse = $this->sanitizedLogData($response);
        $logEntry = date('Y-m-d H:i:s') . " RESPONSE: {$endpoint} [{$httpCode}] - " . json_encode($sanitizedResponse) . "\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log errors
     */
    private function logError($message) {
        $logEntry = date('Y-m-d H:i:s') . " ERROR: {$message}\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Sanitize sensitive data for logging
     */
    private function sanitizedLogData($data) {
        $sanitized = $data;
        $sensitiveKeys = ['phone_number', 'to', 'authorization', 'token'];

        array_walk_recursive($sanitized, function(&$value, $key) use ($sensitiveKeys) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $value = '***REDACTED***';
            }
        });

        return $sanitized;
    }
}

?>