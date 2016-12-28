<?php

namespace Allypost\User;

use Allypost\Lessons\Lesson;
use Allypost\Security\LoginAttempts;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Slim\Slim;

class User extends Eloquent {

    const TYPES = UserPermission::TYPES;

    protected $table  = 'users';
    protected $logins = NULL;

    protected $fillable = [
        'uuid',
        'name',
        'dob',
        'sex',
        'password',
        'email',
        'remember_identifier',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = [
        'password',
        'remember_identifier',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static $domainNames = [
        'password' => [
            'requestChange' => 'user.password-reset:confirm',
        ],
    ];

    public $app = NULL;

    /**
     * Initialization (injection) of LoginAttempts
     *
     * @param LoginAttempts $logins The LoginAttempts class
     *
     * @return self
     */
    public function _addLogins(LoginAttempts $logins): self {
        $this->logins = $logins;

        return $this;
    }

    /**
     * Log the current user in (step 1 of 3)
     *  - Basic data sanatization
     *  - Check login attempts
     *
     * @param string $identifier The user identifier
     * @param string $password   The user supplied password
     * @param bool   $remember   Whether to add a remember cookie
     *
     * @return array Login success data
     */
    public function login(string $identifier, string $password, bool $remember = FALSE) {
        $validation = $this->loginValidate($identifier, $password);

        if (!$validation[ 'tries' ]) {
            return $this->_error('Too many failed login attempts. Try again after a while.');
        }

        if (!$validation[ 'data' ]) {
            return $this->_error('Both fields are required');
        }

        return $this->loginPropagate($identifier, $password, $remember);
    }

    /**
     * Validate user login data
     *
     * @param string $identifier The user identifier
     * @param string $password   The user supplied password
     *
     * @return array Success data (elements: tries => login attempts, data => user supplied data is valid)
     */
    public function loginValidate(string $identifier, string $password): array {
        $tries = $this->loginValidateTries();
        $data  = $this->loginValidateData($identifier, $password);

        return compact('tries', 'data');
    }

    /**
     * Validate the user has remaining login tries
     *
     * @return bool Whether the user has remaining login tries
     */
    public function loginValidateTries(): bool {
        $l = $this->logins;

        $maxAttempts = $l->max;

        return (bool) !($l->tries() >= $maxAttempts);
    }

    /**
     * Validate user supplied login data (not checked against DB)
     *
     * @param string $identifier User identifier
     * @param string $password   User supplied password
     *
     * @return bool Whether the data is valid (not correct)
     */
    public function loginValidateData(string $identifier, string $password): bool {
        $app = $this->app();
        $v   = $app->validation;

        $v->validate(
            [
                'identifier|ID' => [
                    $identifier, 'required',
                ],
                'password'      => [
                    $password, 'required',
                ],
            ]
        );

        return (bool) $v->passes();
    }

    /**
     * Log the user in (step 2 of 3)
     *  - Check data against DB
     *
     * @param string $identifier The user identifier
     * @param string $password   The user supplied password
     * @param bool   $remember   Whether to add a remember cookie
     *
     * @return array Login success data
     */
    protected function loginPropagate(string $identifier, string $password, bool $remember): array {
        $app = $this->app();
        $h   = $app->hash;
        $l   = $this->logins;

        $return = [
            'passed'  => FALSE,
            'reasons' => [],
        ];

        $user = $this->exists($identifier, TRUE, TRUE);

        if (!$user)
            return $this->_error("Wrong ID or password", $identifier);

        $passwordsMatch = $h->checkPassword($password, $user->password);

        if (!$passwordsMatch)
            return $this->_error("Wrong ID or password", $identifier, $user);

        $final = $this->loginFinalise($user, $remember);

        if ($final)
            $return[ 'passed' ] = TRUE;
        else
            $return[ 'reasons' ][] = 'Something went wrong';

        $l->clean();

        $app->log->log('user login', $this->arrayify($user), 200, $user->uuid);

        return $return;
    }

    /**
     * Log the user in (step 3 of 3)
     *  - Push data to session
     *  - Add remember cookie if necessary
     *
     * @param self $user     The user object
     * @param bool $remember Whether to add a remember cookie
     *
     * @return bool Whether the login was successful
     */
    protected function loginFinalise(self $user, bool $remember): bool {
        $app = $this->app();

        $sessionName = $app->config->get('auth.session');

        $u = @$_SESSION[ $sessionName ] = $user->id;
        $u = is_int($u);

        if (!$u || !isset($_SESSION[ $sessionName ]) || !is_int($_SESSION[ $sessionName ]))
            return FALSE;

        if ($remember)
            $r = $this->loginRemember($user);
        else
            $r = TRUE;

        return $u && $r;
    }

    /**
     * Add the remember cookie
     *
     * @param self $user The user object
     *
     * @return bool Whether the remember was successful
     */
    protected function loginRemember(self $user): bool {
        $app = $this->app();


        $rememberIdentifier = $app->randomlib->generateString(128);
        $rememberToken      = $app->randomlib->generateString(128);
        $domain             = $app->config->get('auth.domain');

        $name     = $app->config->get('auth.remember');
        $value    = "{$rememberIdentifier}..{$rememberToken}";
        $expires  = Carbon::parse($app->config->get('auth.remember_for'))->timestamp;
        $path     = '/';
        $secure   = TRUE;
        $httpOnly = TRUE;

        $u = $user->updateRememberCredentials(
            $rememberIdentifier,
            $app->hash->hash($rememberToken)
        );

        $c = setcookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);

        return (bool) $u && $c;
    }

    /**
     * Log the user out
     *
     * @param string $redirect The URL to which to redirect after logging the user out
     * @param bool   $flash    Whether to add a flash message after logging the user out
     *
     * @return bool Whether everything was successful
     */
    public function logout(string $redirect = '', bool $flash = FALSE): bool {
        $app = $this->app();
        unset($_SESSION[ $app->config->get('auth.session') ]);

        $user = $app->auth;

        if (!$user)
            return TRUE;

        if ($app->getCookie($app->config->get('auth.remember'))) {
            $app->auth->removeRememberCredentials();
            $app->deleteCookie($app->config->get('auth.remember'));
        }

        if ($flash)
            $app->flash('global', 'You have been logged out');

        if ($redirect)
            $app->response->redirect($redirect);

        $app->log->log('user logout', toArray($user), 200, $user->uuid);

        return TRUE;
    }

    /**
     * Refreshes the user session
     *
     * @param array $credentials The credentials ([ 0 => the identifier, 1 => The token (unhashed) ])
     *
     * @return null|array Null if unsuccessful or user object if valid
     */
    public function refresh(array $credentials) {
        $app = $this->app();
        $h   = $app->hash;

        $identifier = $credentials[ 0 ];
        $token      = $h->hash($credentials[ 1 ]);

        $user = $this->where('remember_identifier', $identifier)->first();

        if (!$user)
            return NULL;

        if ($h->hashCheck($token, $user->remember_token)) {
            $_SESSION[ $app->config->get('auth.session') ] = $user->id;

            $app->auth = $user;

            return $user;
        } else
            $this->removeRememberCredentials();

        return NULL;
    }

    /**
     * Get the current user's UUID
     *
     * @return string The UUID
     */
    public function getUUID(): string {
        return "{$this->uuid}";
    }

    /**
     * Activate the current user's account
     */
    public function activateAccount() {
        return $this->data->update(
            [
                'active'          => TRUE,
                'activation_code' => NULL,
            ]
        );
    }

    /**
     * Activate a user by UUID and activation code
     *
     * @param string $identifier     The UUID of the user which to activate
     * @param string $activationCode The user supplied activation code
     *
     * @return bool Whether the activation was successful
     */
    public function activate(string $identifier, string $activationCode): bool {
        $user = $this->activateCheck($identifier, $activationCode, TRUE);
        $app  = $this->app();

        if (!$user)
            return FALSE;

        $app->log->log('user activate', json_decode(json_encode($user), TRUE), 200, $user->uuid);

        return (bool) $user->activateAccount();
    }

    /**
     * Check if the activation code is valid for the user
     *
     * @param string $identifier     The UUID of the user
     * @param string $activationCode The user supplied activation code
     * @param bool   $returnRaw      Whether to return the user or success bool
     *
     * @return User|bool True or User for valid activation check, false or null for invalid code
     */
    public function activateCheck(string $identifier, string $activationCode, bool $returnRaw = FALSE) {
        $user = $this->fetch($identifier, TRUE);
        $app  = $this->app();

        if (!$user)
            return FALSE;

        $vaild = (bool) $user->data->activation_code == $app->hash->password($activationCode);

        if ($returnRaw) {
            return $vaild ? $user : FALSE;
        }

        return $vaild;
    }

    /**
     * Generate new activation code for a user
     *
     * @param string $identifier The UUID of the user for which to create the activation code
     *
     * @return string The activation code
     */
    public function createActivationCode(string $identifier): string {
        $user = ($identifier == '-') ? $this : $this->fetch($identifier, TRUE);
        $app  = $this->app();

        if (!$user)
            return FALSE;

        $activationCode     = (string) $app->hash->random(128);//$app->randomlib->generateString(128, 7);
        $activationCodeHash = (string) $app->hash->hash($activationCode);

        $user->data->update(
            [
                'activation_code' => $activationCodeHash,
            ]
        );

        return $activationCode;
    }

    /**
     * Update the remember (auth) credentials for the current user
     *
     * @param string $identifier The identifier
     * @param string $token      The token
     *
     * @return bool Whether the update was successful
     */
    public function updateRememberCredentials($identifier, $token) {
        return $this->update(
            [
                'remember_identifier' => $identifier,
                'remember_token'      => $token,
            ]
        );
    }

    /**
     * Remove the remember (auth) credentials for the current user
     */
    public function removeRememberCredentials() {
        $this->updateRememberCredentials(NULL, NULL);
    }

    /**
     * Get the User's Type
     *
     * @return int The type of the User
     */
    public function getType(): int {
        return $this->permissions->type;
    }

    /**
     * Get name of User type
     *
     * @param int $type User type
     *
     * @return string Name of User type
     */
    public function getTypeString(int $type = -1): string {
        $types = array_flip($this::TYPES);

        if ($type < 0)
            $type = $this->getType();

        return $types[ $type ] ?? 'unknown';
    }

    /**
     * Check whether the current User is a teacher
     *
     * @return bool
     */
    public function isTeacher(): bool {
        return $this->isType('teacher');
    }

    /**
     * Check whether the current User is a student
     *
     * @return bool
     */
    public function isStudent(): bool {
        return $this->isType('student');
    }

    /**
     * Check whether the current User is of type $type
     *
     * @param string $type The type
     *
     * @return bool
     */
    public function isType(string $type): bool {
        $typeInt = $this::TYPES[ $type ] ?? NULL;

        return $this->getType() === $typeInt;
    }

    /**
     * Fetch the user by identifier
     *
     * @param string $identifier The user identifier (email, uuid or DB ID)
     * @param bool   $withData   Add all data related to the user
     *
     * @return User The user that matches the identifier or NULL if none match
     */
    public function fetch(string $identifier, bool $withData = FALSE) {
        $d = $this->orWhere('email', $identifier)
                  ->orWhere('uuid', $identifier)
                  ->orWhere('id', $identifier);

        if ($withData)
            $d = $d->with([ 'permissions', 'data' ]);

        return $d->first();
    }

    /**
     * Create a new user
     *
     * @param array  $data The data for the user
     * @param string $type The type of user to create (eg. default, admin, moderator, etc.)
     *
     * @return array All the data for the created user (the user, permissions, user data, activation code)
     */
    public function make(array $data, string $type = 'student'): array {
        $app = $this->app();

        $data = $this->fixMakeData($data);

        $user           = $this->create($data);
        $permissions    = $user->permissions()->create(UserPermission::$$type);
        $data           = $user->data()->create(UserData::$default);
        $activationCode = $user->createActivationCode('-');

        $app->log->log('user create', toArray($user), 200, $user->uuid);

        return [ 'user' => $user, 'permissions' => $permissions, 'data' => $data, 'activationCode' => $activationCode ];
    }

    /**
     * Check whether all required data is set
     *
     * @param array $data The make data array to check
     *
     * @return array Array with keys of required values and values of whether it is set and not empty
     */
    public function validateMakeData(array $data): array {
        $keys = [
            'uuid',
            'name',
            'dob',
            'sex',
            'password',
            'email',
        ];

        $checks = [];

        foreach ($keys as $key) {
            $checks[ $key ] = isset($data[ $key ]) && !empty($data[ $key ]);
        }

        return $checks;
    }

    /**
     * Infer what can be inferred from user provided data
     *
     * @param array $data                  The make data array to fix
     * @param array $validateMakeDataArray The array returned from validateMakeData
     *
     * @return array Array with fixed missing values
     */
    public function fixMakeData(array $data, array $validateMakeDataArray = []): array {
        $validateMakeDataArray = $validateMakeDataArray ?: $this->validateMakeData($data);

        $vMDA = array_not($validateMakeDataArray);

        if ($vMDA[ 'uuid' ] || $vMDA[ 'password' ] || $vMDA[ 'email' ])
            return [ FALSE ];

        $app = $this->app();

        $data[ 'password' ] = $app->hash->password($data[ 'password' ]);

        return $data;
    }

    /**
     * Check whether the user exists
     *
     * @param string $identifier   The user identifier
     * @param bool   $returnObject Whether to return the user object or just whether it exists
     * @param bool   $withData     Whether to add data associated with user (only used if $return is true)
     *
     * @return User|bool User object or true if the user exists or null or false if it doesn't
     */
    public function exists(string $identifier = '', bool $returnObject = FALSE, bool $withData = FALSE) {
        if (empty($identifier))
            return FALSE;

        $user = $this->fetch($identifier, $withData);
        $pass = !is_null($user);

        return $pass ? ($returnObject ? $user : $pass) : FALSE;
    }

    /* ######################################### */
    /* # <Wrappers for Eloquent relationships> # */
    /* ######################################### */
    public function permissions() {
        return $this->hasOne('Allypost\User\UserPermission', 'user_id', 'id');
    }

    public function data() {
        return $this->hasOne('Allypost\User\UserData', 'user_id', 'id');
    }

    public function attendee() {
        return $this->hasMany('Allypost\Lessons\Attendee');
    }

    public function lessons() {
        return $this->hasMany('Allypost\Lessons\Lesson', 'owner');
    }
    /* ########################################## */
    /* # </Wrappers for Eloquent relationships> # */
    /* ########################################## */

    /**
     * Returns the current user's full name
     *
     * @return string The current user's name
     */
    public function getFullName(): string {
        if (!$this->name) {
            return '';
        }

        return "{$this->name}";
    }

    /**
     * Get only basic info for the current user (uuid, name, email)
     *
     * @param bool $withPermission Whether to also include permissions
     * @param bool $withData       Whether to also include user data
     *
     * @return array The current user's info
     */
    public function getBasicInfo(bool $withPermission = FALSE, bool $withData = FALSE): array {
        $return = [
            'uuid'  => $this->uuid,
            'name'  => $this->name,
            'email' => $this->email,
        ];

        if ($withPermission) {
            $return[ 'permissions' ] = $this->arrayify($this->permissions);
        }

        if ($withData) {
            $d = $this->data ?? $this->data()->first();
            $this->_arrayify($d);

            unset($d[ 'activation_code' ]);
            $return[ 'data' ] = $d;
        }

        return $return;
    }

    /**
     * Convert a object to an array
     *
     * @param mixed $object The object to convert to array
     *
     * @return array The converted array
     */
    protected function arrayify($object): array {
        if (is_array($object))
            return $object;

        $class = get_class($object);

        if (method_exists($class, 'toArray'))
            return (array) $object->toArray();

        return (array) $object;
    }

    /**
     * Pointer wrapper for arrayify
     */
    protected function _arrayify(&$object): array {
        return $object = $this->arrayify($object);
    }

    /**
     * Log an error
     *
     * @param string $message    The message for the error
     * @param string $identifier The user's identifier
     * @param array  $data       The data to log alongside the message
     *
     * @return array The error array
     */
    protected function _error(string $message, string $identifier = '', $data = NULL): array {
        return $this->_errors([ $message ], $identifier, $data);
    }

    /**
     * Log multiple errors
     *
     * @param array  $messages   The messages for the errors
     * @param string $identifier The user's identifier
     * @param array  $data       The data to log alongside the message
     *
     * @return array The error array
     */
    protected function _errors(array $messages, string $identifier = '', $data = NULL): array {
        $l   = $this->logins;
        $app = $this->app();

        if (!empty($identifier) && $identifier !== '-') {
            $remaining = $l->max - $l->add('', $identifier, current($messages), FALSE);
            $app->log->log('login attempt', [ 'messages' => $messages, 'data' => $data ], 400, $data->uuid ?? '');
        } else
            $remaining = $l->remaining();

        return [
            'passed'          => FALSE,
            'reasons'         => $messages,
            'loginsRemaining' => $remaining,
        ];
    }

    /**
     * Get lessons that the user is attending
     *
     * @param int $id User ID
     *
     * @return Collection The lessons
     */
    public function attending(int $id = 0): Collection {
        $sql     = 'SELECT `lessons`.* FROM `lessons` INNER JOIN `lessons_attendees` ON `lessons_attendees`.`lesson_id` = `lessons`.`id` WHERE `lessons_attendees`.`user_id` = :id';
        $sqlID   = $id ?: $this->id;
        $lessons = DB::select($sql, [ 'id' => $sqlID ]);

        array_walk($lessons, function (&$lesson) {
            $lesson = new Lesson((array) $lesson);
        });

        return new Collection($lessons);
    }

    public function schedule(int $id = 0): Collection {
        $sql = 'SELECT `lessons`.*, `schedule`.`week` , `schedule`.`day`, `schedule`.`period`, (`lessons`.`owner` = ?) AS `owned`
                        FROM `lessons`
                        INNER JOIN `schedule`
                          ON `schedule`.`lesson_id` = `lessons`.`id`
                        INNER JOIN `lessons_attendees`
                          ON `lessons_attendees`.`lesson_id` = `lessons`.`id`
                        WHERE `lessons_attendees`.`user_id` = ?
                        OR `lessons`.`owner` = ?';

        $sqlID    = $id ?: $this->id;
        $schedule = DB::select($sql, [ $sqlID, $sqlID, $sqlID ]);

        array_walk($schedule, function (&$lesson) {
            $lesson = new Lesson((array) $lesson);
        });

        return new Collection($schedule);
    }

    /**
     * Returns the instance of Slim
     *
     * @return Slim The current Slim instance
     */
    public function app() {
        return $this->app ?: Slim::getInstance();
    }
}
