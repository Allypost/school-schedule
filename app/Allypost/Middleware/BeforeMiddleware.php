<?php

namespace Allypost\Middleware;

use Slim\Middleware;
use Slim\Slim;

class BeforeMiddleware extends Middleware {

    public function call() {
        $this->app->hook('slim.before', [ $this, 'run' ]);
        $this->app->hook('slim.before.dispatch', [ $this, 'addRouteNameToView' ]);
        $this->next->call();
    }

    public function run() {
        $id = $this->getID();

        $this->setAppData($id);

        $this->checkRememberMe();

        $this->addDataToView();
    }

    /**
     * Get user ID from session
     */
    protected function getID() {
        $sessionKey = $this->getSessionKey();

        $id = @$_SESSION[ $sessionKey ];

        if (!$id) {
            $id = false;
        }

        return $id;
    }

    /**
     * Get auth session key from configuration
     *
     * @return string The key
     */
    protected function getSessionKey() {
        return $this->app->config->get('auth.session');
    }

    /**
     * Sets the user authentication data
     *
     * @param mixed $id The user data
     */
    protected function setAppData($id) {
        if ($id) {
            $this->app->auth = $this->getAppDataCache((string) $id);
        } else {
            $this->app->auth = $id;
        }
    }

    /**
     * Gets the cached user data or returns fresh data if the cached object doesn't exist
     *
     * @param string $id The identifier of the user
     *
     * @return array The user object
     */
    protected function getAppDataCache(string $id) {
        $cache = $this->app->cache;

        $cacheKey = ":user-data|{$id}:";
        $cacheFor = 5;

        $cacheHit = $userData = $cache->get($cacheKey);

        if (!$cacheHit) {
            $userData = $this->app->user->fetch($id, true);

            $cache->set($cacheKey, $userData, MEMCACHE_COMPRESSED, $cacheFor);
        }

        return $userData;
    }

    /**
     * Check and validate authentication cookie
     */
    protected function checkRememberMe() {
        $rememberName = $this->app->config->get('auth.remember');
        if ($this->app->getCookie($rememberName) && !$this->app->auth) {

            $data = $this->app->getCookie($rememberName);
            $credentials = explode('..', $data);

            //dd($credentials);
            if (empty(trim($data)) || count($credentials) !== 2) {
                setcookie($rememberName, '', -1, '/');
            } else {
                $app = Slim::getInstance();
                $app->user->refresh($credentials);
            }
        }
    }

    /**
     * Add misc data to all views
     */
    protected function addDataToView() {
        $app = $this->app;
        $auth = $app->auth;
        $baseUrl = $app->config->get('app.url');
        $viewsDir = $app->view->getTemplatesDirectory();
        $settings = $app->siteSettings;

        $this->app->view()->appendData(compact('app', 'auth', 'baseUrl', 'viewsDir', 'routeName', 'settings'));
    }

    /**
     * Add route name to all views (after router assignment)
     */
    public function addRouteNameToView() {
        $app = $this->app;
        $routeName = $app->router->getCurrentRoute()->getName();

        $this->app->view()->appendData(compact('routeName'));
    }
}
