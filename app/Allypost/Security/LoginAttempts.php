<?php

namespace Allypost\Security;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Slim\Slim;

class LoginAttempts extends Eloquent {

    public $app = NULL;

    public $config;

    public $max;

    public $expires;

    protected $table = 'login_attempts';

    protected $fillable = [
        'ip',
        'user',
        'tries',
        'message',
    ];

    protected $casts = [
        'tries' => 'integer',
    ];

    protected $hidden = [
        'id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function __construct() {
        $this->config  = Slim::getInstance()->config;
        $this->max     = $this->config->get('auth.login.attemptsMax');
        $this->expires = $this->config->get('auth.login.attemptsExpire');
    }

    /**
     * Log a login attempt
     *
     * @param string $ip      The IP of the user
     * @param string $user    The UUID of the user which is trying to log in
     * @param string $message The message to be logged
     * @param bool   $log     Whether to log the attempt
     *
     * @return int The number of tries the user has done
     */
    public function add(string $ip = '', string $user = '', string $message = '', bool $log = TRUE): int {
        $ip  = $this->getIP($ip);
        $u   = $this->fetch($ip);
        $app = $this->app();

        $new = ($u === $this);

        $u->ip      = $ip;
        $u->user    = $user;
        $u->message = $message;

        if ($new)
            $u->tries = 1;
        else
            $u->tries++;

        if ($u->tries > $this->max)
            return $this->max;

        $u->save();

        if ($log)
            $app->log->log('login attempt', $u->toArray(), 200, $u->uuid ?? '');

        return (int) $u->tries;
    }

    /**
     * The number of tries the user has made
     *
     * @param string $ip The IP of the user
     *
     * @return int The number of tries the user has made
     */
    public function tries(string $ip = ''): int {
        $ip = $this->getIP($ip);
        $u  = $this->fetch($ip, TRUE);

        return (int) (!$u ? 0 : $u->tries);
    }

    /**
     * The number of remaining tries
     *
     * @param string $ip The IP of the user
     *
     * @return int The number of tries the user has left
     */
    public function remaining(string $ip = ''): int {
        return (int) ($this->max - $this->tries($ip));
    }

    /**
     * Fetches the DB element of the user
     *
     * @param string $ip  The IP of the user
     * @param bool   $raw Whether to return the raw Eloquent class or an array
     */
    public function fetch(string $ip, bool $raw = FALSE) {
        $return = $this->where('ip', $ip)->first();

        if ($return) {
            $return = $this->clear($return);
        }

        return $raw ? $return : ($return ?? $this);
    }

    /**
     * Removes the entry if it has expired
     *
     * @param self $logins The logins entries to be checked and deleted
     *
     * @return self The cleared entries
     */
    public function clear(self $logins): self {
        $remove = Carbon::instance(new \DateTime($this->expires . ' ago'))->gt($logins->updated_at);

        if ($remove) {
            $logins->delete();

            return $this;
        }

        return $logins;
    }

    /**
     * Remove all entries associated with the IP
     *
     * @param string $ip The IP to be cleaned
     *
     * @return bool Whether the action was successfull
     */
    public function clean(string $ip = ''): bool {
        $ip = $this->getIP($ip);

        $u = $this->fetch($ip, TRUE);

        return $u && $u->delete();
    }

    /**
     * Gets the current user's IP
     *
     * @param string $ip The default IP to return
     *
     * @return string The current IP
     */
    public function getIP(string $ip = ''): string {
        return $ip ?: $this->app()->request->getIp();
    }

    /**
     * Returns the instance of Slim
     *
     * @return Slim The current Slim instance
     */
    public function app(): Slim {
        return Slim::getInstance();
    }
}
