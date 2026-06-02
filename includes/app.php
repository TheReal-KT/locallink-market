<?php

if (!function_exists('app_normalize_path')) {
    function app_normalize_path(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        static $basePath = null;

        if ($basePath !== null) {
            return $basePath;
        }

        $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
        $projectRoot = realpath(dirname(__DIR__));

        if ($documentRoot === false || $projectRoot === false) {
            $basePath = '';
            return $basePath;
        }

        $normalizedDocumentRoot = app_normalize_path($documentRoot);
        $normalizedProjectRoot = app_normalize_path($projectRoot);

        if (stripos($normalizedProjectRoot, $normalizedDocumentRoot) !== 0) {
            $basePath = '';
            return $basePath;
        }

        $relativePath = substr($normalizedProjectRoot, strlen($normalizedDocumentRoot));
        $basePath = $relativePath === false ? '' : $relativePath;

        return $basePath;
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        $normalizedPath = '/' . ltrim($path, '/');
        $basePath = app_base_path();

        return $basePath === '' ? $normalizedPath : $basePath . $normalizedPath;
    }
}
