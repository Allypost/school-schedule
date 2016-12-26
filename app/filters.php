<?php

if (!defined('FILTERS_AUTHENTICATION_REDIRECT')) {
    define('FILTERS_AUTHENTICATION_REDIRECT', TRUE);
}
if (!defined('FILTERS_AUTHENTICATION_ADD_BACK')) {
    define('FILTERS_AUTHENTICATION_ADD_BACK', TRUE);
}

$authenticationCheck = function ($required, $redirect = NULL, $redirectTo = '/', $flash = TRUE) use ($app) {
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
                $action  = 'do:login';
            } else {
                $message = 'You can\'t access this page if you\'re logged in.';
                $action  = 'do:logout';
            }

            if (is_null($redirect) && defined('FILTERS_AUTHENTICATION_REDIRECT')) {
                $redirect = FILTERS_AUTHENTICATION_REDIRECT;
            }

            $redirect ? $app->redirect($redirectTo) : err('authentication mismatch', [ $message ], $action);
        }
    };
};

$loggedIn = function ($redirect = NULL, $redirectTo = '', $addBack = NULL, $flash = TRUE) use ($authenticationCheck, $app) {
    $e = $app->hash;

    if (empty($redirectTo))
        $redirectTo = '/auth/login';

    if (is_null($addBack) && defined('FILTERS_AUTHENTICATION_ADD_BACK')) {
        $addBack = FILTERS_AUTHENTICATION_ADD_BACK;
    }

    if ($addBack && $e) {
        $separator = '&';

        if (strpos('?', $redirectTo) === FALSE) {
            $separator = '?';
        }

        $redirectTo .= $separator . 'b=' . $e->encrypt($app->request->getUrl() . $app->request->getPath(), 'login');
    }

    return $authenticationCheck(TRUE, $redirect, $redirectTo, $flash);
};

$guest = function ($redirect = NULL, $redirectTo = '/', $flash = TRUE) use ($authenticationCheck, $app) {
    $e       = $app->hash;
    $backUrl = $app->request->get('b');

    if ($redirectTo === '/' && $backUrl && $e) {

        if (is_null($redirect)) {
            $redirect = TRUE;
            $flash    = FALSE;
        }

        $redirectTo = $e->decrypt($backUrl, 'login');
    }

    return $authenticationCheck(FALSE, $redirect, $redirectTo, $flash);
};

$admin = function ($redirect = NULL, $redirectTo = '/') use ($app, $loggedIn) {
    return function () use ($redirect, $redirectTo, $app, $loggedIn) {
        if (!$app->auth || !$app->auth->isAdmin()) {
            if (defined('FILTERS_AUTHENTICATION_REDIRECT')) {
                $redirect = FILTERS_AUTHENTICATION_REDIRECT;
            }
            $redirect ? $app->redirect($redirectTo) : err('authentication mismatch', [ 'You don\'t have sufficient permissions to use this resource' ]);
        }
    };
};

$cache = function () use ($app) {
    return function () use ($app) {
        $expire = 60 * 60;
        header('Pragma: public');
        header('Vary: Accept-Encoding');
        header("Cache-Control: public, max-age={$expire}");
        header('Date: ' . gmdate('D, d M Y H:i:s \G\M\T', time()));
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $expire));
    };
};
