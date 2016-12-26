<?php

namespace Allypost\Helpers;

use Slim\Slim;

class Hash {

    public static $keywords = [
        'encrypt' => [
            'encrypt',
            'enc',
        ],
        'decrypt' => [
            'decrypt',
            'dec',
        ],
    ];
    protected     $config;

    public function __construct($config = []) {
        if (empty($config))
            $config = Slim::getInstance()->config;
        $this->config = $config;
    }

    /**
     * Custom PHP password_hash function
     *
     * @param string $password The password you want to encrypt
     *
     * @return string The password hash
     */
    public function password(string $password): string {
        $algorithm = $this->getAlgorithm();
        $cost      = $this->getCost();
        $settings  = [
            'cost' => $cost,
        ];

        $generatedPassword = password_hash($password, $algorithm, $settings);

        $returnPassword = $this->encrypt($generatedPassword);

        return $returnPassword;
    }

    /**
     * Returns the encryption algorithm from the config
     */
    protected function getAlgorithm(): int {
        return (int) $this->config->get('app.hash.algo');
    }

    /**
     * Returns the default encryption cost from the config or $cost if set
     *
     * @param int $cost The cost for the encryption
     *
     * @return int The cost for the encrypt function
     */
    protected function getCost(int $cost = NULL): int {
        if (empty($cost) || !is_int($cost))
            $cost = $this->config->get('app.hash.cost');

        return (int) ($cost > 0) ? $cost : 1;
    }

    /**
     * Encrypt a string
     *
     * @param string $string   The string to be encrypted
     * @param string $password The password with wich you want to add a layer of encryption
     *
     * @return string The hash
     */
    public function encrypt(string $string, string $password = ''): string {
        return (string) $this->encrypt_decrypt($string, 'enc', $password);
    }

    /**
     * Alias for passwordCheck
     *
     * @param string $password     The user supplied password
     * @param string $passwordHash The hash generated with the password function
     *
     * @return bool Returns whether the passwords match
     */
    public function checkPassword(string $password, string $passwordHash) {
        return $this->passwordCheck($password, $passwordHash);
    }

    /**
     * Custom wrapper for PHP password_verify
     *
     * @param string $password     The user supplied password
     * @param string $passwordHash The hash generated with the password function
     *
     * @return bool Returns whether the passwords match
     */
    public function passwordCheck(string $password, string $passwordHash): bool {
        return password_verify($password, $this->decrypt($passwordHash));
    }

    /**
     * Decrypt a string
     *
     * @param string $hash     The string to be encrypted
     * @param string $password The password with wich you want to add a layer of encryption
     *
     * @return string The hash
     */
    public function decrypt(string $hash, string $password = '') {
        return $this->encrypt_decrypt($hash, 'dec', $password);
    }

    /**
     * Encrypts or decrypts the supplied string depending on the $action parameter
     *
     * @param string $string     The string to be encrypted
     * @param string $action     Whether to encrypt or decrypt the string
     * @param string $secret_key The 'password' for the encryption
     *
     * @return string The hash encrypted with the $secret_key as 'password'
     */
    protected function encrypt_decrypt(string $string = "", string $action = 'enc', string $secret_key = NULL): string {
        $string = $this->fixValue($string);

        $encrypt_method = 'AES-256-CBC';

        $secret_key = $this->getSecretKey((string) $secret_key);

        $password = hash('sha256', $secret_key);

        $output = $this->doEncryptDecrypt($action, $string, $encrypt_method, $password);

        return (string) $output;
    }

    /**
     * Basic value serialization
     *
     * @param $value  mixed The value to be serialized
     *
     * @return string The serialized string (returns original value if string)
     */
    protected function fixValue($value): string {
        if (is_string($value))
            return $value;

        if (is_resource($value))
            return '';

        return serialize($value);
    }

    /**
     * Returns the default secret key from the config if $secretKey isn't set
     *
     * @param string $secretKey The default secret key value
     *
     * @return string The secret key
     */
    protected function getSecretKey(string $secretKey = ''): string {
        if (empty($secretKey))
            $secretKey = $this->config->get('app.hash.secret_key');

        return (string) $secretKey;
    }

    /**
     * 'Routes' the value to encryption/decryption
     *
     * @param string $action      Whether to encrypt or decrypt the string
     * @param string $value       The string to be encrypted/decrypted
     * @param string $cryptMethod The algorythm for the encryption/decryption
     * @param string $password    The 'password' for the encryption/decryption
     *
     * @return string Either the hash or the decrypted string
     */
    private function doEncryptDecrypt($action, $value, $cryptMethod, $password): string {
        $kw              = $this::$keywords;
        $decryptKeywords = $kw[ 'decrypt' ];

        if (in_array($action, $decryptKeywords)) {
            return $this->doDecrypt($value, $cryptMethod, $password);
        }

        return $this->doEncrypt($value, $cryptMethod, $password);
    }

    /**
     * Performs the decryption (wrapper for PHP openssl_decrypt)
     *
     * @param string $value       The value to be decrypted
     * @param string $cryptMethod The algorythm for the decryption
     * @param string $password    The 'password' for the decryption
     *
     * @return string The hash encrypted with the $password
     */
    private function doDecrypt(string $value, string $cryptMethod, string $password): string {
        $data = $value;
        $iv   = $this->getIV();

        return (string) openssl_decrypt($data, $cryptMethod, $password, FALSE, $iv);
    }

    /**
     * Generate a new initialization vector for openssl crypto
     */
    protected function getIV(): string {
        return '55a87a4288a2e9a5';
    }

    /**
     * Performs the encryption (wrapper for PHP openssl_encrypt)
     *
     * @param string $value       The value to be encrypted
     * @param string $cryptMethod The algorythm for the encryption
     * @param string $password    The 'password' for the encryption
     *
     * @return string The hash encrypted with the $password
     */
    private function doEncrypt(string $value, string $cryptMethod, string $password): string {
        $data = $value;
        $iv   = $this->getIV();

        return (string) openssl_encrypt($data, $cryptMethod, $password, FALSE, $iv);
    }

    /**
     * Custom wrapper for PHP hash
     *
     * @param $input string Value to be hashed
     *
     * @return string Returns the hash
     */
    public function hash($input): string {
        return hash('sha256', $input, FALSE);
    }

    /**
     * Generate random alphanumerical string
     *
     * @param int $length The length of the string
     *
     * @return string Random string of length $length
     */
    public function random(int $length): string {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $count = strlen($chars);

        // Generate random bytes
        $bytes = random_bytes($length);

        // Construct the output string
        $result = '';
        // Split the string of random bytes into individual characters
        foreach (str_split($bytes) as $byte) {
            // ord($byte) converts the character into an integer between 0 and 255
            // ord($byte) % $count wrap it around $chars
            $result .= $chars[ ord($byte) % $count ];
        }

        return $result;
    }

    /**
     * Custom wrapper for PHP hash_equals
     *
     * @param string $known The known hash
     * @param string $user  The use supplied hash
     *
     * @return bool Whether the hashes match
     */
    public function hashCheck(string $known, string $user): bool {
        return hash_equals($known, $user);
    }

    /**
     * Pointer alias for fixValue
     *
     * @param $value  mixed The value to be serialized
     *
     * @return string The serialized string (returns original value if string)
     */
    protected function _fixValue(&$value) {
        return $value = $this->fixValue($value);
    }

}
