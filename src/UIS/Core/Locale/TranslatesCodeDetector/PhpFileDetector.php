<?php

namespace UIS\Core\Locale\TranslatesCodeDetector;

class PhpFileDetector extends Detector // implements CompilerInterface
{
    protected $extensions = ['.php'];

    public function detect($path)
    {
        $fileContent = $this->files->get($path);

        $keys = $this->detectMvfKeys($fileContent);
        $keys = array_merge($keys, $this->detectTransFunctionKeys($fileContent));
        $keys = array_merge($keys, $this->detectTransMethodKeys($fileContent));
        $keys = array_merge($keys, $this->detectGetMethodKeys($fileContent));
        $keys = array_merge($keys, $this->detectChoiceMethodKeys($fileContent));

        return array_unique($keys);
    }

    protected function detectTransFunctionKeys($fileContent)
    {
        preg_match_all('#trans\s{0,}\(\s{0,}[\'\"]{1}'.$this->createKeyMatcher().'#imsu', $fileContent, $matches);
        if (!empty($matches[0])) {
            return $matches['key'];
        }

        return [];
    }

    protected function detectTransChoiceFunctionKeys($fileContent)
    {
        preg_match_all(
            '#trans_choice\s{0,}\(\s{0,}[\'\"]{1}'.$this->createKeyMatcher().'#imsu',
            $fileContent,
            $matches
        );
        if (!empty($matches[0])) {
            return $matches['key'];
        }

        return [];
    }

    protected function detectTransMethodKeys($fileContent)
    {
        preg_match_all(
            '#Lang\:\:trans\s{0,}\(\s{0,}[\'\"]{1}'.$this->createKeyMatcher().'#imsu',
            $fileContent,
            $matches
        );
        if (!empty($matches[0])) {
            return $matches['key'];
        }

        return [];
    }

    protected function detectGetMethodKeys($fileContent)
    {
        preg_match_all(
            '#Lang\:\:get\s{0,}\(\s{0,}[\'\"]{1}'.$this->createKeyMatcher().'#imsu',
            $fileContent,
            $matches
        );
        if (!empty($matches[0])) {
            return $matches['key'];
        }

        return [];
    }

    protected function detectChoiceMethodKeys($fileContent)
    {
        preg_match_all(
            '#Lang\:\:choice\s{0,}\(\s{0,}[\'\"]{1}'.$this->createKeyMatcher().'#imsu',
            $fileContent,
            $matches
        );
        if (!empty($matches[0])) {
            return $matches['key'];
        }

        return [];
    }

    protected function detectMvfKeys($fileContent)
    {
        preg_match_all('#{'.$this->createKeyMatcher().'}#imsu', $fileContent, $matches);
        if (!empty($matches[0])) {
            return $matches['key'];
        }

        return [];
    }

    protected function createKeyMatcher()
    {
        return '(?<key>[a-z0-9\_\-\.]{1,}\.[a-z0-9\_\-]{1,})';
    }
}
