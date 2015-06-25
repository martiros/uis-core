<?php

namespace UIS\Core\Image;

interface Store
{
    /**
     * @param string $imageId
     * @param string $config
     *
     * @return string
     */
    public function get($imageId, $config);

    /**
     * @param string $imageId
     * @param string $config
     */
    public function clearCache($imageId, $config);

    /**
     * @param string $imageId
     * @param string $config
     */
    public function delete($imageId, $config);
}
