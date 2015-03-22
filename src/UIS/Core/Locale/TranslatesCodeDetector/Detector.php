<?php
namespace UIS\Core\Locale\TranslatesCodeDetector;

use Illuminate\Filesystem\Filesystem;

abstract class Detector
{
    protected $files = null;

    protected $extensions = [];

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function canParse($path)
    {
        foreach ($this->extensions as $extension) {
            if (ends_with($path, $extension)) {
                return true;
            }
        }
        return false;
    }
}
