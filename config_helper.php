<?php
/**
 * Configuration Helper for Environment Variables
 * Compatible with Vercel deployment
 */

function getConfig() {
    return [
        'openai' => [
            'api_key' => $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY'),
            'model' => $_ENV['OPENAI_MODEL'] ?? getenv('OPENAI_MODEL') ?? 'gpt-3.5-turbo',
            'max_tokens' => intval($_ENV['OPENAI_MAX_TOKENS'] ?? getenv('OPENAI_MAX_TOKENS') ?? 150),
            'temperature' => floatval($_ENV['OPENAI_TEMPERATURE'] ?? getenv('OPENAI_TEMPERATURE') ?? 0.7)
        ],
        'firebase' => [
            'apiKey' => $_ENV['FIREBASE_API_KEY'] ?? getenv('FIREBASE_API_KEY'),
            'serverKey' => $_ENV['FIREBASE_SERVER_KEY'] ?? getenv('FIREBASE_SERVER_KEY'),
            'projectId' => $_ENV['FIREBASE_PROJECT_ID'] ?? getenv('FIREBASE_PROJECT_ID'),
            'messagingSenderId' => $_ENV['FIREBASE_MESSAGING_SENDER_ID'] ?? getenv('FIREBASE_MESSAGING_SENDER_ID'),
            'appId' => $_ENV['FIREBASE_APP_ID'] ?? getenv('FIREBASE_APP_ID'),
            'measurementId' => $_ENV['FIREBASE_MEASUREMENT_ID'] ?? getenv('FIREBASE_MEASUREMENT_ID'),
            'serviceAccount' => $_ENV['FIREBASE_SERVICE_ACCOUNT_KEY'] ?? getenv('FIREBASE_SERVICE_ACCOUNT_KEY')
        ],
        'chatbot' => [
            'system_prompt' => $_ENV['CHATBOT_SYSTEM_PROMPT'] ?? getenv('CHATBOT_SYSTEM_PROMPT') ?? 'You are a helpful assistant for child adoption in the Philippines.',
            'fallback_message' => $_ENV['CHATBOT_FALLBACK_MESSAGE'] ?? getenv('CHATBOT_FALLBACK_MESSAGE') ?? 'I apologize, but I\'m having trouble processing your request right now. Please try again later.'
        ]
    ];
}

function getFirebaseConfig() {
    $config = getConfig();
    return $config['firebase'];
}

function getOpenAIConfig() {
    $config = getConfig();
    return $config['openai'];
}

function getChatbotConfig() {
    $config = getConfig();
    return $config['chatbot'];
}

// Fallback to config.json if available (for local development)
function getConfigWithFallback() {
    $config = getConfig();
    
    if (file_exists('config.json')) {
        $fileConfig = json_decode(file_get_contents('config.json'), true);
        if ($fileConfig) {
            $config = array_merge_recursive($config, $fileConfig);
        }
    }
    
    return $config;
}
?> 