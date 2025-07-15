<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load configuration
$config = json_decode(file_get_contents('config.json'), true);

// Enable error logging for debugging
error_log("Chat request received: " . date('Y-m-d H:i:s'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message']) || empty(trim($input['message']))) {
    echo json_encode(['success' => false, 'error' => 'Message is required']);
    exit;
}

$userMessage = trim($input['message']);

// Prepare OpenAI API request
$openaiData = [
    'model' => $config['openai']['model'],
    'messages' => [
        [
            'role' => 'system',
            'content' => $config['chatbot']['system_prompt']
        ],
        [
            'role' => 'user',
            'content' => $userMessage
        ]
    ],
    'max_tokens' => $config['openai']['max_tokens'],
    'temperature' => $config['openai']['temperature']
];

// Use file_get_contents with stream context as cURL alternative
$postData = json_encode($openaiData);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $config['openai']['api_key']
        ],
        'content' => $postData,
        'timeout' => 30,
        'ignore_errors' => true
    ]
]);

$response = file_get_contents('https://api.openai.com/v1/chat/completions', false, $context);

// Get HTTP response code from headers
$httpCode = 200; // Default
if (isset($http_response_header)) {
    foreach ($http_response_header as $header) {
        if (preg_match('/HTTP\/\d\.\d (\d{3})/', $header, $matches)) {
            $httpCode = intval($matches[1]);
            break;
        }
    }
}

// Log API response for debugging
error_log("OpenAI API response - HTTP Code: $httpCode, Response: " . substr($response, 0, 200));

if ($response === false) {
    echo json_encode([
        'success' => false, 
        'error' => 'Connection error: Unable to connect to OpenAI API'
    ]);
    exit;
}

if ($httpCode !== 200) {
    $errorDetails = json_decode($response, true);
    
    // If it's an API key error, provide a helpful fallback response
    if ($httpCode === 401 && $errorDetails && isset($errorDetails['error']['message']) && 
        strpos($errorDetails['error']['message'], 'Incorrect API key') !== false) {
        
        echo json_encode([
            'success' => true,
            'response' => "I'm currently experiencing technical difficulties with my AI connection. However, I can still help you with basic information about adoption in the Philippines:\n\n" .
                         "📋 Basic Requirements:\n" .
                         "• Must be at least 27 years old\n" .
                         "• Must be at least 16 years older than the child\n" .
                         "• Financially capable\n" .
                         "• Emotionally and psychologically capable\n\n" .
                         "📄 Required Documents:\n" .
                         "• Birth certificate\n" .
                         "• Marriage certificate (if married)\n" .
                         "• Medical certificate\n" .
                         "• Police clearance\n" .
                         "• Income tax return\n\n" .
                         "For detailed guidance, please contact DSWD or consult with a family lawyer."
        ]);
        exit;
    }
    
    $errorMessage = 'API error: HTTP ' . $httpCode;
    if ($errorDetails && isset($errorDetails['error']['message'])) {
        $errorMessage .= ' - ' . $errorDetails['error']['message'];
    }
    echo json_encode([
        'success' => false, 
        'error' => $errorMessage
    ]);
    exit;
}

$openaiResponse = json_decode($response, true);

if (!$openaiResponse || !isset($openaiResponse['choices'][0]['message']['content'])) {
    echo json_encode([
        'success' => false, 
        'response' => $config['chatbot']['fallback_message']
    ]);
    exit;
}

$botResponse = trim($openaiResponse['choices'][0]['message']['content']);

// If the response is empty or too short, use fallback
if (empty($botResponse) || strlen($botResponse) < 3) {
    $botResponse = $config['chatbot']['fallback_message'];
}

echo json_encode([
    'success' => true,
    'response' => $botResponse
]);
?> 