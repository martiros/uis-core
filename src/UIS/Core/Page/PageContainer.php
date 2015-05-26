<?php
namespace UIS\Core\Page;

use UIS\Core\Page\ScriptsContract;

class PageContainer
{
    protected $title = null;

    protected $escapeTitle = true;

    protected $translates = [];

    /**
     * @param string $title
     * @param bool $escape
     * @return string
     */
    public function title($title = null, $escape = true)
    {
        if ($title === null) {
            $title = $this->escapeTitle === true ? e($this->title) : $this->title;
            return '<title>' . $title . '</title>';
        }
        $this->title = $title;
        $this->escapeTitle = $escape;
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
    protected $ogData = array();

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
            return null;
        }
        $this->ogData[$key] = array(
            'value' => $content,
            'sc' => $sc
        );
    }

    public function generatePageHeadData()
    {
        $headData = '';
        if ($this->url !== null) {
            $headData .= "\n\t<link rel=\"canonical\" href=\"".sc_attr($this->url)."\"/>";
        }

        $headData .= $this->scripts()->generate();
        return $headData."\n";
    }

    public function generateOgData()
    {
        $ogData = '';
        foreach ($this->ogData as $ogKey => $data) {
            $content = $data['sc'] ? sc_attr($data['value']) : sc_attr($data['value']);
            $ogData .= "\n\t<meta property=\"og:{$ogKey}\" content=\"".$content."\" />";
        }
        return $ogData."\n";
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

