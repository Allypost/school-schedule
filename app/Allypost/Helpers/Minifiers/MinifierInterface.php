<?php

namespace Allypost\Helpers\Minifiers;

interface MinifierInterface {

    public function minify(): array;

    public function addFile(string $path);

    public function addFiles(array $paths);

    public function addRemoteFile(string $path);

    public function addRemoteFiles(array $paths);

    public function addAsset(string $asset);

}
