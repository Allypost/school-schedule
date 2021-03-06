<?php

use Carbon\Carbon as Carbon;
use Slim\Views\TwigExtension;

/**
 * Get and setup View handler
 */
$view = $app->view;
$view->parserOptions = [
    'debug' => $app->config->get('twig.debug'),
];
$view->parserExtensions = [
    new TwigExtension(),
];

/**
 * Get Twig instance
 */
$twigInstance = $view->getInstance();


/* ############### */
/* #  <FILTERS>  # */
/* ############### */

/**
 * Filter to wrap Carbon's time ago
 */
$filterAgo = new Twig_SimpleFilter('ago', function ($datetime) {

    $time = (new Carbon($datetime))->diffForHumans();

    return $time;

});

/**
 * Filter to wrap Carbon's ISO8601
 */
$filterISO8601 = new Twig_SimpleFilter('ISO8601', function ($datetime) {

    $time = (new Carbon($datetime))->format(Carbon::ISO8601);

    return $time;

});

/* ################ */
/* #  </FILTERS>  # */
/* ################ */


/* ###################### */
/* #    <FUNCTIONS>     # */
/* ###################### */

$functionTrimText = new Twig_SimpleFunction('trim_text', function ($text, $length, $ellipses = true, $strip_html = true) {
    return trim_text($text, $length, $ellipses, $strip_html);
});

$functionStaticAsset = new Twig_SimpleFunction('static', function ($path) use ($app) {
    $ds = DIRECTORY_SEPARATOR;
    $basePath = INC_ROOT . $ds . 'static' . $ds;

    $filePath = $basePath . trim($path, " \t\n\r\0\x0B\\/");

    return (string) $filePath ? file_get_contents($filePath) : '';
}, [ 'is_safe' => [ 'html' ] ]);

/* ####################### */
/* #    </FUNCTIONS>     # */
/* ####################### */


/**
 * Extend Twig instance
 */
$twigInstance->addFilter($filterAgo);
$twigInstance->addFilter($filterISO8601);

$twigInstance->addFunction($functionTrimText);
$twigInstance->addFunction($functionStaticAsset);

$twigInstance->addExtension(new Twig_Extension_Debug());
