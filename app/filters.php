<?php

if (!defined('FILTERS_AUTHENTICATION_REDIRECT')) {
    define('FILTERS_AUTHENTICATION_REDIRECT', true);
}
if (!defined('FILTERS_AUTHENTICATION_ADD_BACK')) {
    define('FILTERS_AUTHENTICATION_ADD_BACK', true);
}

function Filter_isType($redirect = null, $redirectTo = '/', callable $validator) {
    global $app;
    global $loggedIn;

    return function () use ($redirect, $redirectTo, $validator, $app, $loggedIn) {
        return function () use ($redirect, $redirectTo, $validator, $app, $loggedIn) {
            if (!$validator()) {
                if (defined('FILTERS_AUTHENTICATION_REDIRECT')) {
                    $redirect = FILTERS_AUTHENTICATION_REDIRECT;
                }
                $redirect ? $app->redirect($redirectTo) : err('authentication mismatch', [ 'You don\'t have sufficient permissions to use this resource' ]);
            }
        };
    };
}

$authenticationCheck = function ($required, $redirect = null, $redirectTo = '/', $flash = true) use ($app) {
    return function () use ($required, $redirect, $redirectTo, $app, $flash) {
        if ((!$app->auth && $required) || ($app->auth && !$required)) {

            if (!$required) {
                $flashMessage = "You can't access this page if you're logged in.";
            } else {
                $flashMessage = "You can't access this page if you're not logged in.";
            }

            if ($flash) {
                $app->flash('global', $flashMessage);
            }

            if ($required) {
                $message = 'You can\'t access this page if you\'re not logged in.';
                $action = 'do:login';
            } else {
                $message = 'You can\'t access this page if you\'re logged in.';
                $action = 'do:logout';
            }

            if (is_null($redirect) && defined('FILTERS_AUTHENTICATION_REDIRECT')) {
                $redirect = FILTERS_AUTHENTICATION_REDIRECT;
            }

            $redirect ? $app->redirect($redirectTo) : err('authentication mismatch', [ $message ], $action);
        }
    };
};

$loggedIn = function ($redirect = null, $redirectTo = '', $addBack = null, $flash = true) use ($authenticationCheck, $app) {
    $e = $app->hash;

    if (empty($redirectTo))
        $redirectTo = '/';

    if (is_null($addBack) && defined('FILTERS_AUTHENTICATION_ADD_BACK')) {
        $addBack = FILTERS_AUTHENTICATION_ADD_BACK;
    }

    if ($addBack && $e) {
        $separator = '&';

        if (strpos('?', $redirectTo) === false) {
            $separator = '?';
        }

        $redirectTo .= $separator . 'b=' . $e->encrypt($app->request->getUrl() . $app->request->getPath(), 'login');
    }

    return $authenticationCheck(true, $redirect, $redirectTo, $flash);
};

$guest = function ($redirect = null, $redirectTo = '/', $flash = true) use ($authenticationCheck, $app) {
    $e = $app->hash;
    $backUrl = $app->request->get('b');

    if ($redirectTo === '/' && $backUrl && $e) {

        if (is_null($redirect)) {
            $redirect = true;
            $flash = false;
        }

        $redirectTo = $e->decrypt($backUrl, 'login');
    }

    return $authenticationCheck(false, $redirect, $redirectTo, $flash);
};

$teacher = Filter_isType($redirect = null, $redirectTo = '/', function () use ($app) {
    return $app->auth && $app->auth->isTeacher();
});

$student = Filter_isType($redirect = null, $redirectTo = '/', function () use ($app) {
    return $app->auth && $app->auth->isStudent();
});
