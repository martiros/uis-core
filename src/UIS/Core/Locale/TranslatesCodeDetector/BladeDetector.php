<?php
namespace UIS\Core\Locale\TranslatesCodeDetector;

class BladeDetector extends PhpFileDetector
{
    protected $extensions = ['.blade.php'];

    public function detect($path)
    {
        $fileContent = $this->files->get($path);
        $keys = $this->detectMvfKeys($fileContent);
        $keys = array_merge($keys, $this->detectTransFunctionKeys($fileContent));
        $keys = array_merge($keys, $this->detectTransChoiceFunctionKeys($fileContent));
        $keys = array_merge($keys, $this->detectTransMethodKeys($fileContent));
        $keys = array_merge($keys, $this->detectGetMethodKeys($fileContent));
        $keys = array_merge($keys, $this->detectChoiceMethodKeys($fileContent));
        $keys = array_merge($keys, $this->detectTransControlKeys($fileContent));
        $keys = array_merge($keys, $this->detectTransChoiceControlKeys($fileContent));
        return array_unique($keys);
    }


    protected function detectTransControlKeys($fileContent)
    {
        preg_match_all('#\@lang\s{0,}\(\s{0,}[\'\"]{1}' . $this->createKeyMatcher() . '#imsu', $fileContent, $matches);
        if (!empty($matches[0])) {
            return $matches['key'];
        }
        return [];
    }

    protected function detectTransChoiceControlKeys($fileContent)
    {
        preg_match_all('#\@choice\s{0,}\(\s{0,}[\'\"]{1}' . $this->createKeyMatcher() . '#imsu', $fileContent, $matches);
        if (!empty($matches[0])) {
            return $matches['key'];
        }
        return [];
    }

}