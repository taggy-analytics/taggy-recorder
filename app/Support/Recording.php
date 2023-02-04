<?php

namespace App\Support;

use SplFileInfo;

class Recording
{
    public function __construct(
        private SplFileInfo $fileInfo,
    ) {}

    public static function fromFile($file)
    {
        return new self($file);
    }

    public function getFilename()
    {
        return $this->fileInfo->getFilename();
    }
}
