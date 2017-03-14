<?php

namespace Allypost\Helpers\Minifiers;

class JS implements MinifierInterface {
    const URL = 'https://closure-compiler.appspot.com/compile';
    const BASE_PARAMS = [
        'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
        'output_format' => 'json',
        'output_info' => 'compiled_code',
        'language_out' => 'ECMASCRIPT5_STRICT',
    ];
    const BASE_HEADERS = [
        'cache-control' => 'no-cache',
        'content-type' => 'application/x-www-form-urlencoded',
    ];

    private $filePaths = [];
    private $fileUrls = [];
    private $assets = [];

    public function minify(): array {
        return $this->getCompiled();
    }

    public function addFile(string $filePath): self {
        if (is_file($filePath))
            $this->filePaths[] = $filePath;

        return $this;
    }

    public function addFiles(array $filePaths): self {
        foreach ($filePaths as $filePath)
            $this->addFile($filePath);

        return $this;
    }

    public function getFiles(): array {
        $files = $this->filePaths;
        $contents = [];

        foreach ($files as $file) {
            if (!isset($contents[ $file ]))
                $contents[ $file ] = file_get_contents($file);
        }

        return $contents;
    }

    public function addRemoteFile(string $fileURL): self {
        $this->fileUrls[] = $fileURL;

        return $this;
    }

    public function addRemoteFiles(array $fileURLs): self {
        foreach ($fileURLs as $fileURL)
            $this->addRemoteFile($fileURL);

        return $this;
    }

    public function getRemoteFiles() {
        return $this->fileUrls;
    }

    public function addAsset(string $contents): self {
        $this->assets[] = $contents;

        return $this;
    }

    public function getAssets(): array {
        return $this->assets;
    }

    public static function getUrl() {
        return self::URL;
    }

    private function getLocalCode() {
        $assets = $this->getAssets();
        $files = $this->getFiles();

        return implode(';', $assets) . ';' . implode(';', $files);
    }

    private function initCompiled() {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::getUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => self::BASE_HEADERS,
        ]);

        return $curl;
    }

    private function encodeData($data = null): string {
        if (!$data) {
            $data = self::BASE_PARAMS;

            $data[ 'js_code' ] = $this->getLocalCode();
            $data[ 'code_url' ] = $this->getRemoteFiles();
        }

        $encoded = http_build_query($data, null, ini_get('arg_separator.output'), PHP_QUERY_RFC3986);

        return preg_replace('/%5B[0-9]+%5D/simU', '', $encoded);
    }

    private function fetchCompiled() {
        $request = $this->initCompiled();
        $data = $this->encodeData();

        curl_setopt($request, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($request);
        $err = curl_error($request);

        curl_close($request);

        return $err ? false : $response;
    }

    private static function parseCompiled($result): array {
        $arr = json_decode($result, true);

        $data = [
            'error' => true,
            'code' => '',
            'errors' => [],
        ];

        if ($result === false) {
            $data[ 'errors' ][] = 'Compiler offline';
        } elseif (is_null($arr)) {
            $data[ 'errors' ][] = 'Compile error';
        } elseif (isset($arr[ 'serverErrors' ])) {
            $data[ 'errors' ][] = 'Compiler error';
            $data[ 'errors' ][] = $arr[ 'serverErrors' ][ 0 ][ 'error' ];
        } else {
            $data[ 'code' ] = $arr[ 'compiledCode' ];
            $data[ 'error' ] = false;
        }

        return $data;
    }

    private function getCompiled() {
        $result = $this->fetchCompiled();

        return $this->parseCompiled($result);
    }

}
