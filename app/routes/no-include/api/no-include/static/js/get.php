<?php

use Allypost\Helpers\Asset;

$fn = function ($file) use ($app) {
    $asset = new Asset('js');
    $file = $file . '.js';

    $cache = $app->cache;
    $cacheKey = $app->config->get('auth.domain') . ':' . $file;
    $cacheFor = 60;

    $compiled = $cache->get($cacheKey);

    if (!$compiled) {
        if (!$asset->exists($file))
            $app->o->err('asset min js', [ 'That file doesn\'t exist' ], '', 404);

        $compiled = $asset->outputFile($file);

        if (!$compiled[ 'error' ])
            $cache->set($cacheKey, $compiled, MEMCACHE_COMPRESSED, $cacheFor);

        $app->response->header('X-File-Cache', 'MISS');
    } else {
        $app->response->header('X-File-Cache', 'HIT');
    }

    if ($compiled[ 'error' ])
        $app->o->err('asset js', $compiled[ 'errors' ]);

    $app->response->header('Content-Type', 'application/javascript; charset=utf-8');
    $app->response->setBody($compiled[ 'code' ]);
};

$app->get('/:filename.js', $fn);
$app->get('/:filename', $fn);
