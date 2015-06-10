<?php

namespace UIS\Core\Utils;

class Util
{
    public static function makeAlias($string)
    {
        $string = preg_replace("/[\/\.\s\–]{1,}/ims", '-', $string);
        $string = preg_replace('/[-]+/ims', '-', $string);
        $string = trim($string, '-');
        $string = preg_replace('/[-]+/ims', '-', $string);

        return $string;
    }

    public static function cropStr($str, $maxLength, $appendStr = '')
    {
        if (self::strLen($str) > $maxLength) {
            $str = mb_substr($str, 0, $maxLength, 'UTF-8').$appendStr;
        }

        return $str;
    }

    public static function strLen($str, $encoding = 'UTF-8')
    {
        if ($encoding === null) {
            return mb_strlen($str);
        }

        return mb_strlen($str, $encoding);
    }
}
