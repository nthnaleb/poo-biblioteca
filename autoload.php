<?php
declare(strict_types=1);

/**
 * Autoload PSR-4 simples, sem dependência do Composer.
 * Classes App\Models\Livro são procuradas em src/Models/Livro.php.
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/src/' . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});
