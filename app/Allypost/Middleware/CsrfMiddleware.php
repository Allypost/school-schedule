<?php

namespace Allypost\Middleware;

use Slim\Middleware;

class CsrfMiddleware extends Middleware {

    const METHODS = [
        'POST',
        'PUT',
        'DELETE',
    ];

    protected $key;

    public function call() {
        $this->key = $this->app->config->get('csrf.key');
        $this->app->hook('slim.before', [ $this, 'check' ]);
        $this->next->call();
    }

    /**
     * Check whether the CSRF token matches
     */
    public function check() {

        $token = $this->setup(true);

        if ($this->methodMatches()) {
            $submittedToken = $this->getSubmittedToken();

            if (!$this->tokenMatches($token, $submittedToken))
                $this->announceError();

        }

        $this->addDataToView($token);
    }

    /**
     * Check whether the request method matches the checked methods
     *
     * @return bool
     */
    protected function methodMatches(): bool {
        return in_array($this->app->request()->getMethod(), self::METHODS);
    }

    /**
     * Initialise the session token if it doesn't exist
     *
     * @param bool $returnToken Whether to return the token
     *
     * @return string The token or empty string (based on $returnToken)
     */
    protected function setup(bool $returnToken = true): string {
        if (!isset($_SESSION[ $this->key ]))
            $_SESSION[ $this->key ] = $this->generateToken();

        return $returnToken ? $this->getToken() : '';
    }

    /**
     * Get the token from the session
     *
     * @return string The token
     */
    protected function getToken(): string {
        return (string) $_SESSION[ $this->key ] ?? '';
    }

    /**
     * Get the user submitted token
     *
     * @return string The user supplied token
     */
    protected function getSubmittedToken(): string {
        $submittedToken = $this->app->request()->post($this->key) ?: '';

        if (empty($submittedToken))
            $submittedToken = $this->app->request->headers->get('X-Csrf') ?: '';

        return $submittedToken;
    }

    /**
     * Check whether the token matches the user supplied token
     *
     * @param string $token     The token from session
     * @param string $userToken The user supplied token
     *
     * @return bool
     */
    protected function tokenMatches($token, $userToken): bool {
        return $this->app->hash->hashCheck($token, $userToken);
    }

    /**
     * Generate a new token
     *
     * @return string The token
     */
    protected function generateToken(): string {
        $app = $this->app;

        $token = $app->randomlib->generate(128);

        return $app->hash->hash($token);
    }

    /**
     * Announce error and exit application
     */
    protected function announceError() {
        err('CSRF mismatch', [ 'Sent invalid CSRF token' ]);
    }

    /**
     * Add CSRF data to all views
     *
     * @param string $token The CSRF token
     */
    protected function addDataToView(string $token) {
        $this->app->view()->appendData(
            [
                'csrf_key' => $this->key,
                'csrf_token' => $token,
            ]
        );
    }
}
