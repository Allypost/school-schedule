<?php

namespace Allypost\Helpers;

use Allypost\Helpers\Minifiers\MinifierInterface;

class Minify implements MinifierInterface {

    const MINIFIERS = [
        'js' => 'Allypost\Helpers\Minifiers\JS',
    ];

    private $minifier;

    function __construct(string $type) {
        $minifier = self::MINIFIERS[ $type ] ?? false;

        if (!$minifier)
            throw new \Exception(sprintf('No minifier for type `%s`', $type));

        $this->minifier = new $minifier();
    }

    public function addFile(string $file): self {
        $this->minifier->addFile($file);

        return $this;
    }

    public function addFiles(array $files): self {
        $this->minifier->addFiles($files);

        return $this;
    }

    public function addRemoteFile(string $file): self {
        $this->minifier->addRemoteFile($file);

        return $this;
    }

    public function addRemoteFiles(array $files): self {
        $this->minifier->addRemoteFiles($files);

        return $this;
    }

    public function addAsset(string $asset): self {
        $this->minifier->addAsset($asset);

        return $this;
    }

    public function minify(): array {
        return $this->minifier->minify();
    }

}
