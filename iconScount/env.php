<?php
function loadEnv($path = null) {
	// echo $path;
	// exit;
    // First, try to load from environment variables (Docker/system)
    $required_vars = ['API_URL', 'API_KEY', 'MODE', 'APP_NAME'];
    
    foreach ($required_vars as $var) {
        $value = getenv($var);
        if ($value !== false) {
            $_ENV[$var] = $value;
            continue;
        }
        
        // If not found in environment, try to load from .env file as fallback
        if ($path && file_exists($path)) {
            loadEnvFromFile($path);
            return;
        }
        
        // Set default values if neither environment nor file exists
        switch ($var) {
            case 'API_URL':
                $_ENV[$var] = 'https://your-api-url.com';
                putenv("$var=https://your-api-url.com");
                break;
            case 'API_KEY':
                $_ENV[$var] = 'your-api-key-here';
                putenv("$var=your-api-key-here");
                break;
            case 'MODE':
                $_ENV[$var] = 'production';
                putenv("$var=production");
                break;
            case 'APP_NAME':
                $_ENV[$var] = 'IconScout Proxy';
                putenv("$var=IconScout Proxy");
                break;
        }
    }
}

function loadEnvFromFile($path) {
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }

        if (strpos($line, '=') === false) {
            continue; // Skip invalid lines
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove quotes if present 
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        }

        // Only set if not already set by environment variables
        if (getenv($name) === false) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
    
    return true;
}

// Try environment variables first, fallback to .env file if needed
loadEnv(realpath(__DIR__).'/.env');


?>