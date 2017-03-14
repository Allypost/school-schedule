<?php

namespace Allypost\Security;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Slim\Slim;

class Log extends Eloquent {

    public $config;

    protected $table = 'log';

    protected $fillable = [
        'code',
        'type',
        'user',
        'message',
        'data',
    ];

    protected $casts = [
        'code' => 'integer',
        'type' => 'integer',
        'data' => 'array',
    ];

    protected $hidden = [
        #'id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Logs an event to the DB
     *
     * @param string $message The type of log (eg. login attempt failed, successfully sold item, etc.)
     * @param array  $data    The data to be stored for the event
     * @param int    $type    Code for the log message (mirrors HTTP codes)
     * @param string $forUser UUID of the user who performed the action
     *
     * @return void
     */
    public function log(string $message, array $data = [], int $type = 200, string $forUser = '') {
        $app = $this->app();

        $this->create(
            [
                'code' => getErrorCode((string) $message),
                'type' => $type,
                'user' => $forUser ?: ($app->auth->uuid ?? '00000000-0000-0000-0000-000000000000'),
                'message' => $message,
                'data' => $data,
                'ip' => $app->request->getIp(),
            ]
        );
    }

    /**
     * Returns the instance of Slim
     *
     * @return Slim The current Slim instance
     */
    public function app(): Slim {
        return Slim::getInstance();
    }

    /**
     * Get all logs
     */
    public function every() {
        return $this->orderBy('id', 'desc')->get();
    }

    /**
     * Get list of laa logs with data
     *
     * @param bool $asArray Whether to return the list as array
     *
     * @return self|array The list
     */
    public function list(bool $asArray = false) {
        $raw = $this->listRaw();
        $return = $raw->get();

        if (!$asArray)
            return $return;
        else
            return $return->toArray() ?? [];
    }

    /**
     * Get raw list (Builder) of all logs
     *
     * @return \Illuminate\Database\Query\Builder The builder object
     */
    public function listRaw() {
        return $this->orderBy('id', 'DESC')->with([ 'user' ]);
    }

    /**
     * Get logs for the current user
     *
     * @return Log The log entries of the current user or NULL if there are no entries
     */
    public function mine() {
        $app = $this->app();

        return $this->where('user', $app->auth->uuid)->orderBy('id', 'desc')->get();
    }

    /**
     * Get the logs for a specific user
     *
     * @param string $user The UUID of the user or 00000000-0000-0000-0000-000000000000 for non logged in users
     *
     * @return Log The log entries of the selected user or NULL if there are no entries
     */
    public function of(string $user) {
        return $this->where('user', $user)->orderBy('id', 'desc')->get();
    }

    /**
     * User relation
     *
     * @return \Allypost\User\User The user object
     */
    public function user() {
        return $this->belongsTo('Allypost\User\User', 'user', 'uuid')->select([ 'username', 'name', 'email', 'uuid' ]);
    }
}
