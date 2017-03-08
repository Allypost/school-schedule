<?php

use Allypost\Helpers\Asset;

$fn = function ($file) use ($app) {
    $asset = new Asset('js');
    $file  = $file . '.js';

    if (!$asset->exists($file))
        $app->o->err('asset min js', [ 'That file doesn\'t exist' ], '', 404);

    $compiled = $asset->minifyFile($file);

    if ($compiled[ 'error' ])
        $app->o->err('asset min js', $compiled[ 'errors' ]);

    $app->response->header('Content-Type', 'application/javascript; charset=utf-8');
    $app->response->setBody($compiled[ 'code' ]);
};

$app->get('/:filename.min.js', $fn);
