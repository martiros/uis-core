<?php

namespace UIS\Core\Image;

class FileStore implements Store
{
    protected $configCache = [];

    protected $imagesDir;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $fileSystem;

    public function __construct()
    {
        $this->imagesDir = config('images.dir') ?: public_path();
        $this->fileSystem = app('files');
    }

    public function get($imageId, $configKey)
    {
        $config = $this->getConfig($configKey);
        $imagePathInfo = $this->getCachePathInfo($imageId, $configKey);
        if ($this->fileSystem->exists($imagePathInfo['cache_file'])) {
            return $imagePathInfo['cache_path'];
        }

        if (!$this->fileSystem->exists($imagePathInfo['file'])) {
            return;
        }

        if (!$this->fileSystem->isDirectory($imagePathInfo['cache_dir'])) {
            $this->fileSystem->makeDirectory($imagePathInfo['cache_dir']);
        }

        $image = new Image($imagePathInfo['file']);
        $action = isset($config['dimension']['crop']) ? 'cut' : 'scaleImage';
        $image->$action($config['dimension']['width'], $config['dimension']['height']);
        $image->save($imagePathInfo['cache_file']);

        return $imagePathInfo['cache_path'];
    }

    public function clearCache($imageId, $config)
    {
        $imagePathInfo = $this->getCachePathInfo($imageId, $config);
        $this->fileSystem->deleteDirectory($imagePathInfo['cache_dir']);
    }

    public function delete($imageId, $config)
    {
        $imagePathInfo = $this->getCachePathInfo($imageId, $config);
        $this->clearCache($imageId, $config);
        $this->fileSystem->delete($imagePathInfo['file']);
    }

    protected function getCachePathInfo($imageId, $config)
    {
        $info = [];
        $config = $this->getConfig($config);

        $pathInfo = pathinfo($config['path'].DIRECTORY_SEPARATOR.$imageId);

        $info['file'] = $this->imagesDir.$config['path'].DIRECTORY_SEPARATOR.$imageId;
        $info['path'] = $config['path'].'/'.$imageId;
        $info['dir'] = $this->imagesDir.$pathInfo['dirname'];
        $info['extension'] = $pathInfo['extension'];
        $info['cache_dir'] = $this->imagesDir.$pathInfo['dirname'].DIRECTORY_SEPARATOR.$pathInfo['filename'];

        if (isset($config['dimension']['crop'])) {
            $info['cache_file'] = $info['cache_dir'].'/'.$config['dimension']['width'].'x'.$config['dimension']['height'].'c-'.$config['dimension']['crop'].'.'.$pathInfo['extension'];
            $info['cache_path'] = $pathInfo['dirname'].'/'.$pathInfo['filename'].'/'.$config['dimension']['width'].'x'.$config['dimension']['height'].'c-'.$config['dimension']['crop'].'.'.$pathInfo['extension'];
        } else {
            $info['cache_file'] = $info['cache_dir'].'/'.$config['dimension']['width'].'x'.$config['dimension']['height'].'.'.$pathInfo['extension'];
            $info['cache_path'] = $pathInfo['dirname'].'/'.$pathInfo['filename'].'/'.$config['dimension']['width'].'x'.$config['dimension']['height'].'.'.$pathInfo['extension'];
        }

        return $info;
    }

    protected function getConfig($configKey)
    {
        if (isset($this->configCache[$configKey])) {
            return $this->configCache[$configKey];
        }
        $config = explode('.', $configKey);

        $imageProportion = array_pop($config);
        $configPath = 'images.images.'.implode('.', $config);
        $config = config($configPath);

        return $this->configCache[$configKey] = [
            'dimension' => $config['sizes'][$imageProportion],
            'path' => $config['path'],
        ];
    }
}
