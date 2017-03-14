<?php

namespace Allypost\Helpers;

class Asset {

    const BASE_PATH = INC_ROOT . DIRECTORY_SEPARATOR . 'static';
    const JS_PATH = self::BASE_PATH . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'standalone';
    const CSS_PATH = self::BASE_PATH . DIRECTORY_SEPARATOR . 'css';
    const PATHS = [
        'js' => self::JS_PATH,
        'css' => self::CSS_PATH,
    ];
    const TYPES = [ 'js', 'css' ];

    private $type = '';
    private $dir = '';
    private $acceptable = [];

    function __construct($type) {
        $this->changeType($type);
    }

    public function exists($file): bool {
        return in_array($file, $this->getAcceptableFiles());
    }

    public function minifyFile($file): array {
        if (!$this->exists($file))
            return [];

        $filePath = $this->getPathToFile($file);

        $min = new Minify($this->getType());

        return $min->addFile($filePath)->minify();
    }

    public function minifyAsset($asset): array {
        $min = new Minify($this->getType());

        return $min->addAsset($asset)->minify();
    }

    public function outputFile($file) {
        $filePath = $this->getPathToFile($file);
        $data = file_get_contents($filePath);
        $return = [
            'error' => true,
            'code' => $data,
            'errors' => [
                'Couldn\'t read from file',
            ],
        ];

        if ($data) {
            $return[ 'error' ] = false;
            $return[ 'errors' ] = [];
        } else {
            $return[ 'code' ] = '';
        }

        return $return;
    }

    public function changeType($type): self {
        $this->clear();
        $this->setType($type);
        $this->setPathFor($type);

        return $this;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setPath($path): self {
        if (!$this->checkPath($path))
            throw new \Exception('Supplied path isn\'t a folder');

        $this->dir = $path;

        $this->refreshAcceptableFiles();

        return $this;
    }

    public function getPath(): string {
        return $this->dir;
    }

    public function getPathToFile($filename): string {
        if (!$this->exists($filename))
            return '';

        $basePath = $this->getPath();

        return $basePath . DIRECTORY_SEPARATOR . $filename;
    }

    public function setAcceptableFiles(array $files): self {
        $this->acceptable = $files;

        return $this;
    }

    public function getAcceptableFiles(): array {
        return $this->acceptable;
    }

    private function clear(): self {
        $this->type = '';
        $this->dir = '';
        $this->acceptable = '';

        return $this;
    }

    private static function checkType($type): bool {
        return in_array($type, self::TYPES);
    }

    private function setType($type): self {
        if (!self::checkType($type))
            throw new \Exception('Invalid file type');

        $this->type = $type;

        return $this;
    }

    private function checkPath($path): bool {
        return is_dir($path);
    }

    private function setPathFor($type): self {
        $this->setPath(self::PATHS[ $type ]);

        return $this;
    }

    private function scanAcceptableFilesDir() {
        $scan = scandir($this->getPath()) ?: [];

        return array_diff($scan, [ '.', '..' ]);
    }

    private function refreshAcceptableFiles(): self {
        $this->setAcceptableFiles($this->scanAcceptableFilesDir());

        return $this;
    }
}
