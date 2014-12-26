<?php

namespace UIS\Core\Locale;

use Illuminate\Filesystem\Filesystem;
//use Illuminate\Translation\LoaderInterface;

class JsFileLoader implements LoaderInterface {

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The default path for the loader.
     *
     * @var string
     */
    protected $path;

    /**
     * All of the namespace hints.
     *
     * @var array
     */
    protected $hints = array();

    /**
     * Create a new file loader instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $path
     * @return void
     */
    public function __construct(Filesystem $files, $path)
    {
        $this->path = $path;
        $this->files = $files;
    }

    /**
     * Load the messages for the given locale.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     * @todo Implement namespaces
     */
    public function load($locale, $group, $namespace = null)
    {
        return $this->loadPath($this->path, $locale, $group);
    }

    /**
     * Load a locale from a given path.
     *
     * @param  string  $path
     * @param  string  $locale
     * @param  string  $group
     * @return array
     */
    protected function loadPath($path, $locale, $group)
    {
        $path = $path.$group.DIRECTORY_SEPARATOR.$locale.'.js';
        if (!file_exists($path)) {
            return array();
        }
        $data = json_decode(file_get_contents($path), true);
        $data = is_array($data) ? $data : array();
        return $data;
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string  $namespace
     * @param  string  $hint
     * @return void
     */
    public function addNamespace($namespace, $hint)
    {
        $this->hints[$namespace] = $hint;
    }

}
