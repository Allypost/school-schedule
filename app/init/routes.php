<?php

$routeFiles = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(INC_ROOT . '/app/routes/')), "/\/*.php$/i");
$noInclude = 'no-include';
foreach ($routeFiles as $routeFile) {
    $filename = $routeFile->getPathname();
    $exp = explode('.', $filename);
    if ($exp[ count($exp) - 2 ] == $noInclude || strstr($filename, $noInclude) != false)
        continue;
    else
        require_once $routeFile->getPathname();
}
