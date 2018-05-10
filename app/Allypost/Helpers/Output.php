<?php

namespace Allypost\Helpers;

use Slim\Slim;

class Output {

    /**
     * Announces a success message and stops execution
     *
     * @param string $reason A success message or name for the success
     * @param array  $data   Data to be supplied with alongside the message
     * @param string $action Possible additional actions
     *
     * @return void
     */
    public function say(string $reason, $data = [], string $action = ''): void {
        self::output(self::sayResponse($reason, $data, $action), 200);
    }

    /**
     * Announces an error and stops execution
     *
     * @param string $reason A name or reason for throwing an error
     * @param array  $errors List of errors
     * @param string $action Possible additional actions
     * @param int    $status The status code for the error
     *
     * @return void
     */
    public function err(string $reason, $errors = [], $action = '', int $status = 400): void {
        self::output(self::errResponse($reason, $errors, $action), $status);
    }

    /**
     * Get a new standard response object
     *
     * @param string $reason A success message or name for the success
     * @param array  $data   Data to be supplied with alongside the message
     * @param string $action Possible additional actions
     *
     * @return array
     */
    public static function sayResponse(string $reason, $data = [], $action = ''): array {
        return self::response(FALSE, $reason, $data, $action);
    }

    /**
     * Get a new error response object
     *
     * @param string $reason A name or reason for throwing an error
     * @param array  $errors List of errors
     * @param string $action Possible additional actions
     *
     * @return array The response object
     */
    public static function errResponse(string $reason, $errors = [], $action = ''): array {
        return self::response(TRUE, $reason, $errors, $action);
    }

    /**
     * Generate integer value from string (used for error codes, about 5% chance of duplicates)
     *
     * @param string $error The error string
     *
     * @return int A integer based on the input string
     */
    private static function getErrorCode(string $error): int {

        if (strlen($error) == 1)
            return ord($error);

        $err = (int) array_reduce(str_split($error), function ($i, $char) {
            return $i ^ ord($char);
        }, 500);

        $err *= strlen($error);
        $err ^= ord(substr($error, 0, 1));
        $err ^= ord(substr($error, -1, 1));
        $err ^= ord(substr($error, (int) strlen($error) / 2, 1));

        return $err;
    }

    /**
     * Add and format errors for response
     *
     * @param array $response The response array
     * @param array $errors   Array (list) of errors
     *
     * @return array Modified response
     */
    private static function responseError(array $response, $errors = []): array {

        $errors = (array) $errors;

        if (isset($errors[ 'errors' ])) {
            $response[ 'errors' ] = array_pull($errors, 'errors');;
            $response[ 'data' ] = $errors;
        } else {
            $response[ 'errors' ] = $errors;
            $response[ 'data' ]   = [];
        }

        return $response;
    }

    /**
     * Add and format data for response
     *
     * @param array $response The response array
     * @param array $data     Data array or object
     *
     * @return array Modified response
     */
    private static function responseSuccess(array $response, $data = []): array {
        $data = (array) $data;

        $response[ 'data' ] = $data;

        return $response;
    }

    /**
     * Announces a response message and stops execution
     *
     * @param bool   $isError Whether the response is an error message
     * @param string $reason  A success message or name for the response
     * @param array  $data    Data to be supplied with alongside the message
     * @param string $action  Possible additional action
     *
     * @return array The response array
     */
    private static function response(bool $isError, string $reason, $data = [], string $action = ''): array {
        $return = [
            'error'        => $isError,
            'responseCode' => self::getErrorCode($reason),
            'reason'       => $reason,
            'data'         => [],
            'actions'      => $action,
            'timestamp'    => time(),
        ];

        if ($isError)
            $return = self::responseError($return, $data);
        else
            $return = self::responseSuccess($return, $data);

        return $return;
    }

    /**
     * Output the message and halt the app
     *
     * @param array|object $array  JSON serializable data
     * @param int          $status The HTTP status code for the response
     */
    private static function output($array, int $status = 200): void {
        $app = Slim::getInstance();

        $app->contentType('application/json');
        $app->status($status);
        $app->halt($app->response->getStatus(), json_encode($array));
    }
}
