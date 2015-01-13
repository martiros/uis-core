<?php

namespace UIS\Core\Controllers\Api;

use UIS\Core\Controllers\BaseController;
use Illuminate\Foundation\Application;
use Lang, Config;

class ConfigController extends BaseController
{
    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app = null;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function config()
    {
        $apiResultData = array();
        $apiConfig = Config::get('api');
        if (!empty($apiConfig)) {
            if (isset($apiConfig['data'])) {
                foreach ($apiConfig['data'] as $configItemKey => $configItem) {
                    if (is_callable($configItem)) {
                        $parameters = [];
                        $reflector = new \ReflectionFunction($configItem);
                        foreach ($reflector->getParameters() as $key => $parameter) {
                            $parameters[$key] = null;
                            $class = $parameter->getClass();
                            if ($class) {
                                $parameters[$key] = $this->app->make($class->name);
                            }
                        }
                        $apiResultData[$configItemKey] = call_user_func_array($configItem, $parameters);
                    } else {
                        $apiResultData[$configItemKey] = $configItem;
                    }
                }
            }
        }

        if (!array_key_exists('languages', $apiResultData)) {
            $apiResultData['languages'] = Lang::getLanguages();
        }

        if (!isset($apiResultData['config'])) {
            $apiResultData['config'] = array();
        }

        if (!array_key_exists('dictionary_last_update_date', $apiResultData['config'])) {
            $apiResultData['config']['dictionary_last_update_date'] = Lang::getDictionaryLastUpdateDate();
        }
        return $this->api('OK', $apiResultData);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @TODO: Implements for other modules and allow from api.php config
     */
    public function dictionary()
    {
        $group = 'mobile';
        $data = array(
            'dictionary' => Lang::getDictionary($group),
            'dictionary_last_update_date' => Lang::getDictionaryLastUpdateDate()
        );
        return $this->api('OK', $data);
    }
}
