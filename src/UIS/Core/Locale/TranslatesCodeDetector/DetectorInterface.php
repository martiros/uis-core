<?php

namespace UIS\Core\Locale\TranslatesCodeDetector;

interface CompilerInterface
{
    /**
     * Get possible translate keys from file.
     *
     * @param  string $path
     * @return array
     */
    public function detect($path);

    /**
     * @param string $path
     * @return bool
     */
    public function canParse($path);
}
