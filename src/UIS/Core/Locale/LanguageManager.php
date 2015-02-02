<?php
namespace UIS\Core\Locale;

use App;
use Carbon\Carbon;
use Config;
use DateTime;
use DB;
use Illuminate\Translation\Translator;
use Request;
use UIS\Core\DB\BufferInsert;
use UIS\Core\Locale\Exceptions\LanguageNotFound;

class LanguageManager extends Translator
{
    /**
     * @var null
     */
    protected $language = null;

    protected $languages = null;

    protected $loadedKeys = [];

    protected $notDefinedKeywords = null;

    public function __construct(LoaderInterface $loader, $locale)
    {
        $this->loader = $loader;
        $this->locale = $locale;
    }

    public function detectLanguage()
    {
        $locale = $this->locale;
        if ($locale !== null) {
            $this->language = new Language();
            $this->language->code = $locale;
            return;
        }

        $locale = Request::get('locale', '');
        if (empty($locale)) {
            $this->language = $this->getDefaultLanguage();
            if (empty($this->language)) {
                throw new LanguageNotFound();
            }
        } else {
            $this->language = $this->getLanguageByCode($locale);
            if (empty($this->language)) {
                $this->language = $this->getDefaultLanguage();
                $this->locale = $this->language->code;
                throw new LanguageNotFound();
            }
        }
        $this->locale = $this->language->code;
    }

    public function get($key, array $replace = array(), $locale = null)
    {
        $locale = $locale === null ? $this->locale : $locale;
        if (isset($this->loadedKeys[$locale][$key])) {
            return $this->getTransLine($this->loadedKeys[$locale][$key], $replace);
        }
        list($namespace, $group, $item) = $this->parseKey($key);

        $this->load($namespace, $group, $locale);
        if (isset($this->loadedKeys[$locale][$key])) {
            return $this->getTransLine($this->loadedKeys[$locale][$key], $replace);
        }
        $this->addNotDefinedKeyword($namespace, $group, $key);
        return $key;
    }

    protected function getTransLine($line, $replace)
    {
        if (is_string($line)) {
            return $this->makeReplacements($line, $replace);
        } elseif (is_array($line) && count($line) > 0) {
            return $line;
        }
    }

    protected function makeReplacements($line, array $replace)
    {
        $replace = $this->sortReplacements($replace);

        foreach ($replace as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            $line = str_replace(':' . $key, $value, $line);
        }

        return $line;
    }

    /**
     * Sort the replacements array.
     *
     * @param  array  $replace
     * @return array
     */
    protected function sortReplacements(array $replace)
    {
        $replace = array_filter($replace, function($r){
            return is_string($r);
        });
        return parent::sortReplacements($replace);
    }

    protected function addNotDefinedKeyword($namespace, $group, $key)
    {
        if ($this->notDefinedKeywords === null) {
            register_shutdown_function(
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

        $insertBuffer = new BufferInsert(
            'dictionary_ndk', array(
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
            )
        );

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
        if (empty($lastEditInfo) || $lastEditInfo === '0000-00-00' || $lastEditInfo === '0000-00-00 00:00:00') {
            return null;
        }
        $lastEditInfo = $lastEditInfo->edit_date;
        if (empty($lastEditInfo) || $lastEditInfo === '0000-00-00' || $lastEditInfo === '0000-00-00 00:00:00') {
            return null;
        }
        return new Carbon($lastEditInfo);
    }

    /**
     * Get application current language
     * @return ApplicationLanguage
     */
    public function language()
    {
        return $this->language;
    }

    /**
     * Alias of language method
     * @return ApplicationLanguage
     */
    public function cLng()
    {
        return $this->language();
    }

    public function getLanguages()
    {
        if (!$this->languages) {
            $this->languages = ApplicationLanguage::where('show_status', ApplicationLanguage::STATUS_ACTIVE)->get();
        }
        return $this->languages;
    }

    /**
     * @param string $code
     * @return ApplicationLanguage
     */
    public function getLanguageByCode($code)
    {
        $languages = $this->getLanguages();
        foreach ($languages as $lang) {
            if ($lang->code === $code) {
                return $lang;
            }
        }
        return null;
    }

    /**
     * @return ApplicationLanguage
     */
    public function getDefaultLanguage()
    {
        $languages = $this->getLanguages();
        foreach ($languages as $lang) {
            if ($lang->is_default === ApplicationLanguage::TRUE) {
                return $lang;
            }
        }
        return null;
    }
}
