<?php

namespace UIS\Core\Page;

interface ScriptsContract
{
    public function appendFile(
        $file,
        $minGroup = 'default',
        $attributes = ['type' => 'text/javascript']
    );

    public function removeFile($file);

    public function prependFile($src, $minGroup = 'default', $attributes = []);

    public function isSetFile($src);

    public function getVersion();

    public function generate();
}
