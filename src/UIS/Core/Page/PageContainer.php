<?php
namespace UIS\Core\Page;

class PageContainer
{
    protected $title = null;

    protected $titleTemplate = null;

    protected $escapeTitle = true;

    protected $disableTitleTemplate = false;

    protected $translates = [];

    /**
     * @param string $title
     * @param bool $escape
     * @param bool $disableTitleTemplate
     * @return string
     */
    public function title($title = null, $escape = true, $disableTitleTemplate = false)
    {
        if ($title === null) {
            return '<title>'.$this->getTitle().'</title>';
        } else {
            $this->setTitle($title, $escape, $disableTitleTemplate);
        }
    }

    public function setTitle($title, $escape = true, $disableTitleTemplate = false)
    {
        $this->title = $title;
        $this->escapeTitle = $escape;
        $this->disableTitleTemplate = $disableTitleTemplate;
    }

    public function getTitle()
    {
        $title = $this->escapeTitle === true ? e($this->title) : $this->title;
        if (!$this->disableTitleTemplate && $this->titleTemplate !== null) {
            $title = str_replace('{title}', $title, $this->titleTemplate);
        }

        return $title;
    }

    /**
     * @return bool
     */
    public function isSetTitle()
    {
        return !is_null($this->title);
    }

    public function setTitleTemplate($titleTemplate)
    {
        $this->titleTemplate = $titleTemplate;
    }

    public function disableTitleTemplate()
    {
        $this->disableTitleTemplate = true;
    }

    public function enableTitleTemplate()
    {
        $this->disableTitleTemplate = false;
    }

    /**********************************************************************************************************************/
    /**********************************************************************************************************************/
    /**********************************************************************************************************************/

    public function addTrans($trans)
    {
        if (is_array($trans)) {
            $this->translates = array_merge($this->translates, array_flip($trans));
        } else {
            $this->translates[$trans] = true;
        }
    }

    public function getTrans()
    {
        $trans = [];
        foreach ($this->translates as $transKey => $value) {
            $trans[$transKey] = trans($transKey);
        }

        return $trans;
    }

    public function setPageData()
    {
        ?>
            <script type="application/javascript" >
                var $locSettings = {};
                $locSettings.transData = <?= json_encode($this->getTrans())?>;
            </script>
        <?php

    }

    /**
     * @var string
     */
    protected $url = null;

    /**
     * @var array
     */
    protected $ogData = [];

    /**
     * @param ScriptsContract $scripts\
     */
    public function __construct()
    {
    }

    /**
     * @param string $url
     * @param bool $appendBasePath
     */
    public function url($url, $appendBasePath = false)
    {
        if ($appendBasePath) {
            $url = url($url);
        }
        $this->url = $url;
        if (!isset($this->ogData['url'])) {
            $this->og('url', $url);
        }
    }

    public function scripts()
    {
        return app('uis.core.page.scripts');
    }

    /**
     * @param string $key
     * @param string $content
     * @param bool $sc
     * @return string
     */
    public function og($key, $content = null, $sc = true)
    {
        if ($key === null) {
            if (isset($this->ogData[$key])) {
                return $this->ogData[$key]['value'];
            }

            return;
        }
        $this->ogData[$key] = [
            'value' => $content,
            'sc' => $sc,
        ];
    }

    public function generatePageHeadData()
    {
        $headData = '';
        if ($this->url !== null) {
            $headData .= "\n\t<link rel=\"canonical\" href=\"".sc_attr($this->url).'"/>';
        }

        $headData .= $this->scripts()->generate();

        return $headData."\n";
    }

    public function generateOgData()
    {
        $ogData = '';
        foreach ($this->ogData as $ogKey => $data) {
            $content = $data['sc'] ? sc_attr($data['value']) : sc_attr($data['value']);
            $ogData .= "\n\t<meta property=\"og:{$ogKey}\" content=\"".$content.'" />';
        }

        return $ogData."\n";
    }
}

/*************************************************************************************************/
/****************************----------------------------------***********************************/
/*************************************************************************************************/

class Core_Helper_Head
{
    private static $canonical = null;

    /**
     * @var UIS_Paging
     */
    private static $mainPaging = null;

    /**
     * @return bool
     */
    public static function isSetCanonical()
    {
        return !empty(self::$canonical);
    }

    /**
     * @param string $url
     * @return string
     */
    public static function canonical($url = null)
    {
        if ($url !== null) {
            self::$canonical = $url;

            return;
        }

        if (self::$canonical === null) {
            return '';
        }

        return '<link rel="canonical" href="'.(self::$canonical).'"/>';
    }

    /**
     * @return bool
     */
    public static function isSetPaging()
    {
        return !empty(self::$mainPaging);
    }

    /**
     * @param UIS_Paging $paging
     */
    public static function setPaging(UIS_Paging $paging)
    {
        self::$mainPaging = $paging;
    }

    /**
     * @return string
     */
    public static function generatePaginationHelper()
    {
        if (self::$mainPaging == null) {
            return '';
        }

        return self::$mainPaging->generatePaginationHelper();
    }

    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /******************************************************************************************************************/

    /**
     * @var Core_Helper_Head_OpenGraph
     */
    private static $headOpenGraphData = null;

    public static function openGraph()
    {
        if (self::$headOpenGraphData === null) {
            self::$headOpenGraphData = new Core_Helper_Head_OpenGraph();
        }

        return self::$headOpenGraphData;
    }

    /**
     * @var Core_Helper_Head_Meta
     */
    private static $headMeta = null;

    /**
     * @return Core_Helper_Head_Meta
     */
    public static function meta()
    {
        if (self::$headMeta === null) {
            self::$headMeta = new Core_Helper_Head_Meta();
        }

        return self::$headMeta;
    }

    private static $headScript = null;

    /**
     * @return Core_Helper_Head_Script
     */
    public static function script()
    {
        if (self::$headScript === null) {
            self::$headScript = new Core_Helper_Head_Script();
        }

        return self::$headScript;
    }

    /**
     * @var Core_Helper_Head_Style
     */
    private static $headStyle = null;

    /**
     * @return Core_Helper_Head_Style
     */
    public static function style()
    {
        if (self::$headStyle === null) {
            self::$headStyle = new Core_Helper_Head_Style();
        }

        return self::$headStyle;
    }

    //	Core_Helper_Head_Rss

    /**
     * @var Core_Helper_Head_Style
     */
    private static $headStyleRss = null;

    /**
     * @return Core_Helper_Head_Rss
     */
    public static function rss()
    {
        if (self::$headStyleRss === null) {
            self::$headStyleRss = new Core_Helper_Head_Rss();
        }

        return self::$headStyleRss;
    }

    private static $dataStore = [];

    public static function setData($dataKey, $data)
    {
        self::$dataStore[ $dataKey ] = $data;
    }

    public static function getData($dataKey)
    {
        if (isset(self::$dataStore[ $dataKey ])) {
            return self::$dataStore[ $dataKey ];
        }

        return;
    }
}

/***********************************************************************************************************/
/***********************************************************************************************************/
/***********************************************************************************************************/

class Core_Helper_Head_Script
{
    /**
     *  @var array
     */
    protected $filesData = [];
    protected $cacheVersion = '';

    public function __construct()
    {
        $appConfig = UIS_Config::getConfig();
        $this->cacheVersion = $appConfig->cache->killer->js_version;
    }

    private function getAppendPath()
    {
        return '';
    }

    /**
     *  Remove  script file.
     *	@param   string   $src
     *  @return  bool  true if file removed, else return false
     */
    public function removeFile($src)
    {
        if (isset($this->filesData[ $src ])) {
            unset($this->filesData[ $src ]);

            return true;
        }

        return false;
    }

    /**
     * @retrun string
     */
    public function path($src)
    {
        return $this->getAppendPath().$src;
    }

    /**
     *  Prepend  script file.
     *	@param   string 	  $src
     *  @param   string 	  $type
     *  @param   array        $attrs
     *  @return  void
     */
    public function prependFile($src, $minGroup = 'default',  $type = 'text/javascript', $attrs = [])
    {
        $oldData = [];
        $oldData[ $src ] = [
            'src'        =>     $this->getAppendPath().$src,
            'type'    =>     $type,
            'attrs'        =>     $attrs,
            'minGroup'    =>     $minGroup,
        ];
        foreach ($this->filesData as $key => $value) {
            $oldData[ $key ] = $value;
        }
        $this->filesData = $oldData;
    }

    /**
     *  Prepend  script file.
     *	@param   string 	  $src
     *  @param   string 	  $type
     *  @param   array        $attrs
     *  @return  Core_Helper_Head_Script
     */
    public function appendFile($src, $minGroup = 'default',  $type = 'text/javascript', $attrs = ['rel' => 'stylesheet'])
    {
        $this->filesData[ $src ] = [
            'src'        =>    $this->getAppendPath().$src,
            'type'    =>    $type,
            'attrs'        =>    $attrs,
            'minGroup'    =>    $minGroup,
        ];

        return $this;
    }

    /**
     * @return Core_Helper_Head_Script
     */
    public function includeDataTables()
    {
        return $this;
    }

    /**
     * @return bool
     */
    public function isSetFile($src)
    {
        if (isset($this->filesData[ $src ])) {
            return true;
        }

        return false;
    }

    public function getVersion()
    {
        return $this->cacheVersion;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $appConfig = UIS_Config::getConfig();

        $scriptStr = '';
        foreach ($this->filesData  as $key => $script) {
            $attrStr = '';
            foreach ($script['attrs']  as $attrKey => $attrValue) {
                $attrStr .= "  $attrKey = \"$attrValue\" ";
            }

            $script['src'] .= '.js';
            if (strpos($script['src'], '?')  === false) {
                $script['src'] = ($appConfig->web->url->static_js).$script['src'].'?q='.$this->getVersion();
            } else {
                $script['src'] = ($appConfig->web->url->static_js).$script['src'].'&q='.$this->getVersion();
            }

            $scriptStr .= " <script src=\"{$script['src']}\" type=\"{$script['type']}\" $attrStr ></script> \n\r ";
        }

        return $scriptStr;
    }
}

class  Core_Helper_Head_Style
{
    protected $filesData = [];
    protected $cacheVersion = '';

    public function __construct()
    {
        $appConfig = UIS_Config::getConfig();
        $this->cacheVersion = $appConfig->cache->killer->css_version;
    }

    private function getAppendPath()
    {
        return '';
    }

    /**
     *  Remove  style file.
     *	@param   string   $href
     *  @return  bool  true if file removed, else return false
     */
    public function removeFile($href)
    {
        if (isset($this->filesData[ $href ])) {
            unset($this->filesData[ $href ]);

            return true;
        }

        return false;
    }

    /**
     *  Prepend  style file.
     *	@param   string 	  $href
     *  @param   string 	  $type
     *  @param   array        $attrs
     *  @return  void
     */
    public function prependFile($href, $minGroup = 'default', $type = 'text/css', $attrs = ['rel' => 'stylesheet'])
    {
        $oldData = [];
        $oldData[ $href ] = [
            'href'            =>         $this->getAppendPath().$href,
            'type'        =>         $type,
            'attrs'            =>         $attrs,
            'minGroup'        =>         $minGroup,
        ];
        foreach ($this->filesData as $key => $value) {
            $oldData[ $key ] = $value;
        }
        $this->filesData = $oldData;
    }

    /**
     *  Prepend  style file.
     *	@param   string 	  $href
     *	@param   bool	  $checkDate
     *  @param   string 	  $type
     *  @param   array        $attrs
     *  @return  void
     */
    public function appendFile($href, $minGroup = 'default',  $type = 'text/css', $attrs = ['rel' => 'stylesheet'])
    {
        $this->filesData[ $href ] = [
            'href'            =>        $this->getAppendPath().$href,
            'type'        =>        $type,
            'attrs'            =>        $attrs ,
            'minGroup'        =>        $minGroup,
        ];
    }

    /**
     * @return bool
     */
    public function isSetFile($src)
    {
        if (isset($this->filesData[ $src ])) {
            return true;
        }

        return false;
    }

    public function getVersion()
    {
        return $this->cacheVersion;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $appConfig = UIS_Config::getConfig();

        $stylesStr = '';
        foreach ($this->filesData  as $key => $style) {
            $attrStr = '';
            foreach ($style['attrs']  as $attrKey => $attrValue) {
                $attrStr .= "  $attrKey = \"$attrValue\" ";
            }

            $style['href'] .= '.css';
            if (strpos($style['href'], '?')  === false) {
                $style['href'] = ($appConfig->web->url->static_css).$style['href'].'?q='.$this->getVersion();
            } else {
                $style['href'] = ($appConfig->web->url->static_css).$style['href'].'&q='.$this->getVersion();
            }

            $stylesStr .= " <link href=\"{$style['href']}\" type=\"{$style['type']}\" $attrStr />";
        }

        return $stylesStr;
    }
}

class  Core_Helper_Head_OpenGraph
{
    private $metaData = [];

    public function description($content = null, $scString = true)
    {
        if ($content === null) {
            if (isset($this->metaData[ 'description' ])) {
                return $this->metaData[ 'description' ]['content'];
            }

            return;
        }

        if (empty($content)) {
            return false;
        }

        $this->metaData[ 'description' ] = [
            'sc_string'    =>    $scString,
            'content'    =>    $content,
            'key'        =>    'description',
        ];
    }

    public function title($content = null, $scString = true, $disableTitleTemplate = false)
    {
        if ($content === null) {
            if (isset($this->metaData[ 'title' ])) {
                return $this->metaData[ 'title' ]['content'];
            }

            return;
        }

        if (empty($content)) {
            return false;
        }

        if (!$disableTitleTemplate) {
            $content = $content.' - '.trans('core.home.head_title', 'core');
        }

        $this->metaData[ 'title' ] = [
            'sc_string'    =>    $scString,
            'content'    =>    $content,
            'key'        =>    'title',
        ];
    }

    public function image($content = null, $scString = true)
    {
        if ($content === null) {
            if (isset($this->metaData[ 'image' ])) {
                return $this->metaData[ 'image' ]['content'];
            }

            return;
        }

        if (empty($content)) {
            return false;
        }

        $this->metaData[ 'image' ] = [
            'sc_string'    =>    $scString,
            'content'    =>    $content,
            'key'        =>    'image',
        ];
    }

    public function append($key, $content, $scString = true)
    {
        $this->metaData[ $key ] = [
            'sc_string'    =>    $scString,
            'content'    =>    $content,
            'key'        =>    $key,
        ];
    }

    public function generate()
    {
        $dataStr = '';
        foreach ($this->metaData as $key => $data) {
            $dataStr .= '<meta property="og:'.$key.'" content="'.($data['sc_string'] === true ? sc_string($data['content']) :  $data['content']).'" />';
        }

        return $dataStr;
    }
}

class  Core_Helper_Head_Rss
{
    protected $filesData = [];

    public function isEmpty()
    {
        if (empty($this->filesData)) {
            return true;
        }

        return false;
    }

    /**
     *  Remove  RSS file.
     *	@param   string   $href
     *  @return  bool  true if file removed, else return false
     */
    public function removeFile($href)
    {
        if (isset($this->filesData[ $href ])) {
            unset($this->filesData[ $href ]);

            return true;
        }

        return false;
    }

    /**
     *  Prepend  RSS file.
     *	@param   string 	  $href
     *  @param   string 	  $type
     *  @param   array        $attrs
     *  @return  void
     */
    public function prependFile($href, $minGroup = 'default', $type = 'application/rss+xml', $attrs = ['rel' => 'alternate', 'title' => 'RSS'])
    {
        $oldData = [];
        $oldData[ $href ] = [
            'href'            =>         $href,
            'type'        =>         $type,
            'attrs'            =>         $attrs,
            'minGroup'        =>         $minGroup,
        ];
        foreach ($this->filesData as $key => $value) {
            $oldData[ $key ] = $value;
        }
        $this->filesData = $oldData;
    }

    /**
     *  Prepend  style file.
     *	@param   string 	  $href
     *	@param   bool	  $checkDate
     *  @param   string 	  $type
     *  @param   array        $attrs
     *  @return  void
     */
    public function appendFile($href, $minGroup = 'default',  $type = 'application/rss+xml', $attrs = ['rel' => 'alternate', 'title' => 'RSS'])
    {
        if ($minGroup === null) {
            $minGroup = 'default';
        }

        $this->filesData[ $href ] = [
            'href'            =>        $href,
            'type'        =>        $type,
            'attrs'            =>        $attrs ,
            'minGroup'        =>        $minGroup,
        ];
    }

    /**
     * @return bool
     */
    public function isSetFile($src)
    {
        if (isset($this->filesData[ $src ])) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $stylesStr = '';
        foreach ($this->filesData  as $key => $style) {
            $attrStr = '';
            foreach ($style['attrs']  as $attrKey => $attrValue) {
                $attrStr .= "  $attrKey=\"$attrValue\" ";
            }

            $stylesStr .= " <link href=\"{$style['href']}\" type=\"{$style['type']}\" $attrStr />";
        }

        return $stylesStr;
    }
}

class Core_Helper_Head_Meta
{
    private $metaData = [];

    public function description($content = null, $scString = true)
    {
        if ($content === null) {
            if (isset($this->metaData[ 'description' ])) {
                return $this->metaData[ 'description' ]['content'];
            }

            return;
        }

        if (empty($content)) {
            return false;
        }

        $this->metaData[ 'description' ] = [
            'sc_string'    =>    $scString,
            'content'    =>    $content,
            'key'        =>    'description',
        ];
    }

    public function keywords($content = null, $scString = true)
    {
        if ($content === null) {
            if (isset($this->metaData[ 'keywords' ])) {
                return $this->metaData[ 'keywords' ]['content'];
            }

            return;
        }

        if (empty($content)) {
            return false;
        }

        $this->metaData[ 'keywords' ] = [
            'sc_string'    =>    $scString,
            'content'    =>    $content,
            'key'        =>    'keywords',
        ];
    }

    public function append($key, $content, $scString = true)
    {
        $this->metaData[ $key ] = [
            'sc_string'    =>    $scString,
            'content'    =>    $content,
            'key'        =>    $key,
        ];
    }

    public function generate()
    {
        $dataStr = '';
        foreach ($this->metaData as $key => $data) {
            $dataStr .= '<meta name="'.$key.'" content="'.($data['sc_string'] === true ? sc_string($data['content']) :  $data['content']).'" />';
        }

        return $dataStr;
    }
}

/*

class  Core_Helper_Head_Style {

    protected  $filesData 	 	= 	 array();
    protected  $minify    	 	=	 false;
    protected  $cacheVersion 	=	 '';
    protected  $minPath 		=    '';

    public function __construct () {

        $appConfig = UIS_Config::getConfig();
        if ( $appConfig->web->url->css_min == true ) {
            $this->minify = true;
        }
        else {
            $this->minify = false;
        }
        $this->cacheVersion 	=	$appConfig->cache->killer->css_version;
        $this->minPath			=	$appConfig->web->url->css_minify;
    }

    private function getAppendPath(){
        return UIS_ADM_MEDIA_PATH;
    }


    public function removeFile( $href ) {
        if( isset(  $this->filesData[ $href ]  ) ){
            unset( $this->filesData[ $href ] );
            return true;
        }
        return false;
    }

    public function prependFile( $href , $minGroup = 'default', $type = 'text/css', $attrs = array( 'rel' => 'stylesheet' ) ) {

        $oldData = array();
        $oldData[ $href ] = array (
            'href' 			=> 	 	 $this->getAppendPath().$href,
            'type'  		=>		 $type,
            'attrs'			=>		 $attrs,
            'minGroup'		=> 		 $minGroup
        );
        foreach( $this->filesData AS $key => $value ){
            $oldData[ $key ] =  $value;
        }
        $this->filesData = 	$oldData;

    }


    public function appendFile( $href  , $minGroup = 'default',  $type = 'text/css', $attrs = array( 'rel' => 'stylesheet' ) ){
        $this->filesData[ $href ] = array (
            'href'			=> 		$this->getAppendPath().$href,
            'type'  		=> 		$type,
            'attrs'			=> 		$attrs ,
            'minGroup'		=> 		$minGroup
        );
    }

    public function isSetFile( $src ) {
        if ( isset( $this->filesData[ $src ] ) ) {
            return true;
        }
        return false;
    }

    public function getVersion (){
        return $this->cacheVersion;
    }


    public function generate() {

        if (  $this->minify === true ) {
            return $this->generateMin();
        }
        else {
            return $this->generateStandard();
        }

    }


    public function generateMin(){

        $filesByGroup = array();
        foreach( $this->filesData  AS $key => $style ){
            if ( !isset( $filesByGroup[ $style['minGroup'] ] ) ) {
                $filesByGroup[ $style['minGroup'] ] = array();
                $filesByGroup[ $style['minGroup'] ][ 'href' ] = $this->minPath.'?f='.$style['href'];
            }
            else {
                $filesByGroup[ $style['minGroup'] ][ 'href' ] .= ','.$style['href'];
            }
            $attrStr = '';
            foreach ( $style['attrs']  AS $attrKey => $attrValue ) {
                $attrStr.="  $attrKey = \"$attrValue\" ";
            }
            $filesByGroup[ $style['minGroup'] ][ 'attrs' ]  =  $attrStr;
            $filesByGroup[ $style['minGroup'] ][ 'type'  ]  =  $style[ 'type' ];
        }
        if ( empty( $this->filesData  ) ) {
            return '';
        }
        $linksData = '';
        foreach (  $filesByGroup  AS $key => $data  ) {
            $data['href'] = $data['href'].'&q='.md5( $this->getVersion () );
            $linksData .= " <link    href=\"{$data['href']}\"    type=\"{$data['type']}\"    {$data['attrs']} /> ";
        }
        return $linksData;
    }





    public function generateStandard() {

        $appConfig = UIS_Config::getConfig();

        $stylesStr ='';
        foreach( $this->filesData  AS $key => $style ){
            $attrStr = "";
            foreach( $style['attrs']  AS $attrKey => $attrValue ){
                $attrStr.="  $attrKey = \"$attrValue\" ";
            }

            $style['href'] .= '.css';
            if (  strpos(  $style['href'] , '?' )  === false ) {
                $style['href'] =  ($appConfig->web->url->static_css	).$style['href'].'?q='.md5( $this->getVersion () );
            }
            else {
                $style['href'] =  ($appConfig->web->url->static_css	).$style['href'].'&q='.md5( $this->getVersion () );
            }

            $stylesStr .= " <link href=\"{$style['href']}\" type=\"{$style['type']}\" $attrStr />";

        }
        return $stylesStr;
    }

}

*/
