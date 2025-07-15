<?php
echo "Testing cURL availability...\n";

if (function_exists('curl_init')) {
    echo "✅ cURL is available\n";
    
    // Test basic HTTP request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://httpbin.org/get');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ cURL error: $error\n";
    } else {
        echo "✅ cURL test successful - HTTP Code: $httpCode\n";
    }
} else {
    echo "❌ cURL is not available\n";
    echo "Please enable cURL extension in PHP\n";
}

echo "\nPHP Extensions loaded:\n";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (strpos(strtolower($ext), 'curl') !== false) {
        echo "- $ext\n";
    }
}
?> 