<?php

namespace UIS\Core\Locale;

use Config, App, DateTime, Request, DB;
use Illuminate\Translation\Translator;
use \UIS\Core\Models\Language as LanguageModel;
use \UIS\Core\DB\BufferInsert;
use Carbon\Carbon;

class Language extends Translator
{
    protected $loadedKeys = array();
    protected $notDefinedKeywords = null;

    public function __construct(LoaderInterface $loader, $locale)
    {
        $this->loader = $loader;
        $this->locale = $locale;
    }

    public function get($key, array $replace = array(), $locale = null)
    {
        $locale = $locale === null ? $this->locale : $locale;
        if (isset($this->loadedKeys[$locale][$key])) {
            return $this->loadedKeys[$locale][$key];
        }
        list($namespace, $group, $item) = $this->parseKey($key);

        $this->load($namespace, $group, $locale);
        if (isset($this->loadedKeys[$locale][$key])) {
            return $this->loadedKeys[$locale][$key];
        }
        $this->addNotDefinedKeyword($namespace, $group, $key);
        return $key;
    }

    protected function addNotDefinedKeyword($namespace, $group, $key)
    {
        if ($this->notDefinedKeywords === null) {
            App::shutdown(
                function () {
                    $this->logNotDefinedKeywords();
                }
            );
            $this->notDefinedKeywords = array();
        }
        $appName = Config::get('app.name');
        if (empty($appName)) {
            $appName = 'app';
        }

        $hash = sha1("$namespace, $group, $key");
        $this->notDefinedKeywords[$hash] = array(
            'namespace' => $namespace,
            'key' => $key,
            'module' => $group,
            'app_name' => $appName,
        );
    }

    protected function logNotDefinedKeywords()
    {
        if (empty($this->notDefinedKeywords)) {
            return false;
        }

        $insertBuffer = new BufferInsert('dictionary_ndk', array(
            'hash',
            'key',
            'app_name',
            'module',
            'url',
            'from_url',
            'add_date',
            'ip'
        ),
        array(
            'key',
            'module',
            'app_name',
            'url',
            'from_url',
            'add_date',
            'ip',
        ));

        foreach ($this->notDefinedKeywords as $hash => $data) {
            $insertBuffer->insert(
                array(
                    $hash,
                    $data['key'],
                    $data['app_name'],
                    $data['module'],
                    Request::server('REQUEST_URI', ''),
                    Request::server('HTTP_REFERER', ''),
                    new DateTime(),
                    Request::ip()
                )
            );
        }
        $insertBuffer->flush();
    }

    /**
     * Load the specified language group.
     *
     * @param  string $namespace
     * @param  string $group
     * @param  string $locale
     * @return void
     */
    public function load($namespace, $group, $locale)
    {
        if ($this->isLoaded($namespace, $group, $locale)) {
            return;
        }

        // The loader is responsible for returning the array of language lines for the
        // given namespace, group, and locale. We'll set the lines in this array of
        // lines that have already been loaded so that we can easily access them.
        $loadedKeys = $this->loader->load($locale, $group, $namespace);

        $this->loadedKeys[$locale] = !isset($this->loadedKeys[$locale]) ? array() : $this->loadedKeys[$locale];
        $this->loadedKeys[$locale] = $this->loadedKeys[$locale] + $loadedKeys;
        $this->loaded[$namespace][$group][$locale] = true;
    }

    public function getDictionary($group, $namespace = '*', $locale = null)
    {
        $locale = $locale === null ? $this->locale : $locale;
        return $this->loader->load($locale, $group, $namespace);
    }

    /**
     * @TODO CHECK_IN_VERSION_3
     */
    public function getDictionaryLastUpdateDate()
    {
        $lastEditInfo = DB::table('dictionary_ml')->select('edit_date')->orderBy('edit_date', 'desc')->first();
        if (empty($lastEditInfo)) {
            return null;
        }
        return new Carbon($lastEditInfo->edit_date);
    }

    public function cLng($key = null)
    {
        if ($key !== 'id') {
            throw new \Excption('not implemented');
        }
        return 6;
    }

    public function getLanguages()
    {
        return LanguageModel::where('show_status', '!=', LanguageModel::STATUS_DELETED)->get();
    }
}
