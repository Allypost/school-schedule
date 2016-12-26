<?php

/**/
ini_set("display_errors", 1);
error_reporting(E_ALL);
/**/

use Allypost\Middleware\BeforeMiddleware;
use Allypost\Middleware\CsrfMiddleware;
use Noodlehaus\Config;
use Slim\Slim;
use Slim\Views\Twig;

/**
 * Initialise session
 */
session_cache_limiter(FALSE);
session_start();

/**
 * Global variable definitions
 */
define("INC_ROOT", dirname(__DIR__));

/**
 * Initialise Composer
 */
require_once INC_ROOT . '/vendor/autoload.php';

/**
 * Initialise Slim
 */
$app = new Slim(
    [
        'mode'               => trim(file_get_contents(INC_ROOT . '/mode.php')),
        'templates.path'     => INC_ROOT . '/app/views',
        'cookies.secret_key' => 'iLffqWuNNzVqqdwKzjrLUyq2Ez6gx05qagyp19Kp8rSU4PGmZZEYAQ43WtR0EZu1mHL1LCmk3VSo8qfX1wjGY0Wp0cciwYkM7x3zH9vpWWfX3t3Psa49Xpx1cwVVUCh2',
        'view'               => new Twig(),
    ]
);

/**
 * Register global middleware
 */
$app->add(new BeforeMiddleware());
$app->add(new CsrfMiddleware());

/**
 * Set mode and load config
 */
$app->configureMode($app->config('mode'), function () use ($app) {
    $app->config = Config::load(INC_ROOT . "/app/config/{$app->mode}.php");
});

/**
 * Require dependencies
 */
require_once 'init/db.php';
require_once 'filters.php';
require_once 'init/routes.php';
require_once 'init/dependencies.php';
require_once 'functions.php';

/**
 * Extend Twig
 */
require_once 'init/twigExtensions.php';
