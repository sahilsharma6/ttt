<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the current request URI and normalize it
$request = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'); // Normalize the request

// Output the current request for debugging

// Remove the base path from the request
$basePath = 'tutorial-test';
$request = str_replace($basePath . '/', '', $request);

// Split the request into segments
$segments = explode('/', $request);

// Define routes as string keys
$routes = [
    'My' => 'home.php',
    'My/contact' => 'contact.php',
];

// Check if the requested view exists
$foundRoute = false;
foreach ($routes as $route => $viewFile) {
    if (implode('/', $segments) === $route) {
        require __DIR__ . '/views/' . $viewFile;
        $foundRoute = true;
        break;
    }
}

if (!$foundRoute) {
    header("HTTP/1.0 404 Not Found");
    require __DIR__ . '/views/404.php'; // Load the 404 page
}
?>