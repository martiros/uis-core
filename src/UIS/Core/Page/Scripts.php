<?php namespace UIS\Core\Page;

class Scripts implements ScriptsContract
{
    protected $files = [];

    protected $version = '';

    public function __construct()
    {
        // @TODO: Read from config
        $this->version = '1.0.0';
    }

    public function appendFile(
        $file,
        $minGroup = 'default',
        $attributes = array('type' => 'text/javascript')
    ) {

        $this->files[$file] = [
            'file' => $this->getAppendPath() . $file,
            'attributes' => $attributes,
            'minGroup' => $minGroup
        ];
        return $this;
    }

    public function removeFile($file)
    {
        if (isset($this->files[$file])) {
            unset($this->files[$file]);
        }
        return $this;
    }

    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /******************************************************************************************************************/

    private function getAppendPath()
    {
        return '';
    }

    /**
     * @retrun string
     */
    public function path($src)
    {
        return $this->getAppendPath() . $src;
    }

    /**
     *  Prepend  script file
     * @param   string $src
     * @param   array $attrs
     * @return  void
     */
    public function prependFile($src, $minGroup = 'default', $attrs = array())
    {
        $oldData = array();
        $oldData[$src] = array(
            'src' => $this->getAppendPath() . $src,
            'attributes' => $attrs,
            'minGroup' => $minGroup
        );
        foreach ($this->files AS $key => $value) {
            $oldData[$key] = $value;
        }
        $this->files = $oldData;
    }

    /**
     * @return boolean
     */
    public function isSetFile($src)
    {
        if (isset($this->files[$src])) {
            return true;
        }
        return false;
    }


    public function getVersion()
    {
        return '1.0.0';
        return $this->cacheVersion;
    }


    /**
     * @return string
     */
    public function generate()
    {
        $static_js = '';

        $scriptStr = '';
        foreach ($this->files AS $key => $script) {
            $attrStr = "";
//            uis_dump($script);
            foreach ($script['attributes'] AS $attrKey => $attrValue) {
                $attrStr .= "  $attrKey = \"$attrValue\" ";
            }


            $script['file'] .= '.js';
            if (strpos($script['file'], '?') === false) {
                $script['file'] = ($static_js) . $script['file'] . '?q=' . $this->getVersion();
            } else {
                $script['file'] = ($static_js) . $script['file'] . '&q=' . $this->getVersion();
            }

            $scriptStr .= " <script src=\"{$script['file']}\"  $attrStr ></script> \n\r ";
        }
        return $scriptStr;
    }
}