<?php

use Carbon\Carbon as Carbon;
use Slim\Views\TwigExtension;

/**
 * Get and setup View handler
 */
$view                   = $app->view;
$view->parserOptions    = [
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

$functionTrimText = new Twig_SimpleFunction('trim_text', function ($text, $length, $ellipses = TRUE, $strip_html = TRUE) {
    return trim_text($text, $length, $ellipses, $strip_html);
});

/* ####################### */
/* #    </FUNCTIONS>     # */
/* ####################### */


/**
 * Extend Twig instance
 */
$twigInstance->addFilter($filterAgo);
$twigInstance->addFilter($filterISO8601);

$twigInstance->addFunction($functionTrimText);

$twigInstance->addExtension(new Twig_Extension_Debug());
