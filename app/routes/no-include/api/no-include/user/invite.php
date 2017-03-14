<?php

use Allypost\User\User;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;
use ReCaptcha\ReCaptcha;

$app->group('/invitation', $loggedIn(), $teacher(), function () use ($app, $loggedIn, $guest, $teacher, $student) {

    $app->post('/', function () use ($app) {
        $r = $app->request();
        $u = new User();
        $re = new ReCaptcha($app->config->get('google.recaptcha.secret_key'));

        $name = $r->post('full-name');
        $uuid = $r->post('user-identifier');
        $email = $r->post('email');
        $dob = $r->post('dob');
        $sex = $r->post('sex');
        $passcode = $r->post('passcode');
        $resp = $r->post('g-recaptcha-response');

        $reValid = $re->verify($resp, $r->getIp());

        if (!in_array($app->auth->uuid, [ 'Allypost', 'qqoxoir' ])) {
            if (!$reValid->isSuccess())
                $app->o->err('user invitation', [ 'You failed the reCaptcha' ]);

            if ($passcode != 'I to eat pie all day every day')
                $app->o->err('user invitation', [ 'Invalid passcode' ]);
        }

        if (empty($uuid))
            $uuid = slug($name);

        $similarIds = $u->where('uuid', 'like', "{$uuid}%")
                        ->orWhere('email', $email)
                        ->orderBy(DB::raw('LENGTH(uuid)'), 'desc')
                        ->orderBy('uuid', 'desc')
                        ->get()->toArray();

        if (!empty($similarIds)) {
            $sim = $similarIds[ 0 ];

            if ($sim[ 'email' ] == $email)
                $app->o->err('user invitation', [ 'That email is already in use' ]);

            preg_match('/\d+$/', $sim[ 'uuid' ], $matches);

            $lastDigit = (int) empty($matches) ? 0 : $matches[ 0 ];

            $uuid .= (string) ($lastDigit + 1);
        }

        try {
            $dob = Carbon::createFromFormat('d/m/Y', $dob, 'UTC');
        } catch (\Throwable $e) {
            $app->o->err('user invitation', [ 'Invalid DOB' ]);
        }

        $data = compact('name', 'uuid', 'email', 'dob', 'sex', 'password');

        if (in_array('', $data) || in_array(null, $data))
            $app->o->err('user invitation', [ 'All fields are required' ]);

        $data[ 'password' ] = 'This is the default password that won\'t be used to log in';

        try {
            $newUser = $u->make($data);
        } catch (\Throwable $e) {
            $app->o->err('user invitation', [ $e->getMessage() ]);
        }

        $activationCode = $newUser[ 'activationCode' ];
        $user = $newUser[ 'user' ]->toArray();
        $url = $app->config->get('app.url') . $app->urlFor('user:signup', [ 'code' => $activationCode, 'user' => $uuid ]);

        $app->o->say('user invitation', compact('activationCode', 'url', 'user'));
    })->name('api:user:invitation:create');

});
