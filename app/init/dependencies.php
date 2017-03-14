<?php

use Allypost\Helpers\Files;
use Allypost\Helpers\Hash;
use Allypost\Helpers\Output;
use Allypost\Helpers\SiteSettings;
use Allypost\Security\Log;
use Allypost\Security\LoginAttempts;
use Allypost\User\User;
use Allypost\Validation\Validator;
use Carbon\Carbon as Carbon;
use RandomLib\Factory as RandomLib;
use ReCaptcha\ReCaptcha;

$app->auth = false;


$app->container->singleton('cache', function () {
    $cache = new Memcache();

    $cache->addserver('127.0.0.1', 11211, true, 1);

    return $cache;
});

$app->container->singleton('hash', function () use ($app) {
    return new Hash($app->config);
});

$app->container->singleton('validation', function () use ($app) {
    return new Validator;
});

$app->container->singleton('randomlib', function () {
    $factory = new RandomLib;

    return $factory->getMediumStrengthGenerator();
});

$app->container->singleton('SiteSettings', function () {
    return new SiteSettings;
});

$app->container->singleton('siteSettings', function () use ($app) {
    return $app->SiteSettings->retrieve();
});

$app->container->set('time', function () {
    return new Carbon;
});

$app->container->set('loginAttempts', function () {
    return new LoginAttempts;
});

$app->container->set('user', function () {
    $user = new User();
    $loginAttempts = new LoginAttempts();

    return $user->_addLogins($loginAttempts);
});

$app->container->set('log', function () {
    return new Log;
});

$app->container->set('recaptcha', function () use ($app) {
    return new ReCaptcha($app->config->get('google.recaptcha.secret_key'));
});

$app->container->set('o', function () use ($app) {
    return new Output();
});

$app->notFound(function () use ($app) {
    $app->status(404);

    if ($app->request->headers('x-requested-with') == 'XMLHttpRequest')
        err('Page not found', [], '', [], 404);
    else
        $app->render('errors/404.twig');

    $app->stop();
});
