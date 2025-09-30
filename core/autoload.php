<?php
spl_autoload_register(function ($class) {
    $prefixes = [
        'Core\\Lib\\' => __DIR__ . '/lib/',
        'App\\Controller\\' => __DIR__ . '/../app/controller/',
        'App\\Model\\' => __DIR__ . '/../app/model/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) continue;
        $relative = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) { require $file; return; }
    }
});
