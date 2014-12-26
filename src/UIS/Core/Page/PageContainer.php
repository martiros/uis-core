<?php

namespace UIS\Core\Page;

class PageContainer
{
    private static $scTitle = true;
    public static $title = null;


    public function title($title = null, $scString = true)
    {
        if ( $title === null ) {
            return '<title>'.( self::$scTitle === true ? sc_string( self::$title ) :  self::$title ).'</title>';
        }
        self::$title = $title;
        self::$scTitle = $scString;
    }

    public static function url($url, $appendBasePath=true)// fixme
    {

    }

}



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


    /**
     *  Remove  style file
     *	@param   string   $href
     *  @return  boolean  true if file removed, else return false
     */
    public function removeFile( $href ) {
        if( isset(  $this->filesData[ $href ]  ) ){
            unset( $this->filesData[ $href ] );
            return true;
        }
        return false;
    }

    /**
     *  Prepend  style file
     *	@param   string 	  $href
     *  @param   string 	  $type
     *  @param   array        $attrs
     *  @return  void
     */
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


    /**
     *  Prepend  style file
     *	@param   string 	  $href
     *	@param   boolean	  $checkDate
     *  @param   string 	  $type
     *  @param   array        $attrs
     *  @return  void
     */
    public function appendFile( $href  , $minGroup = 'default',  $type = 'text/css', $attrs = array( 'rel' => 'stylesheet' ) ){
        $this->filesData[ $href ] = array (
            'href'			=> 		$this->getAppendPath().$href,
            'type'  		=> 		$type,
            'attrs'			=> 		$attrs ,
            'minGroup'		=> 		$minGroup
        );
    }

    /**
     * @return boolean
     */
    public function isSetFile( $src ) {
        if ( isset( $this->filesData[ $src ] ) ) {
            return true;
        }
        return false;
    }

    public function getVersion (){
        return $this->cacheVersion;
    }

    /**
     * @return string
     */
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