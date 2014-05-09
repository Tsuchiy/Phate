<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class MsgPack 
{
    /**
     * serializer
     * @param mixed $value
     * @return string
     */
    public static function serialize($value)
    {
        if (is_object($value)) {
            if (method_exists($value, 'serialize')) {
                return $value->serialize();
            } elseif (method_exists($value, '__wakeup')) {
                $value->__wakeup();
            }
        }
        $binary = self::valiable_serializer($value);
        
        return $binary;
    }
    
    private static function valiable_serializer($value)
    {
        $type = gettype($value);
        switch ($type) {
            case "NULL" :
                return self::null_serializer($value);
            case "boolean" :
                return self::boolean_serializer($value);
            case "integer" :
                return self::integer_serializer($value);
            case "double" :
                return self::double_serializer($value);
            case "string" :
                return self::string_serializer($value);
            case "array" :
                return self::array_serializer($value);
            case "object" :
                return self::object_serializer($value);
            case "resource" :
            default :
                trigger_error('[msgpack] (msgpack_serialize_zval) type is unsupported, encoded as null',  E_WARNING);
                return chr(0xc0);
        }
    }
    
    /**
     * null用serializer
     * @param null $value
     * @return char
     */
    private static function null_serializer($value)
    {
        return chr(0xc0);
    }
    
    /**
     * boolean用serializer
     * @param boolean $value
     * @return char
     */
    private static function boolean_serializer($value)
    {
        return $value === true ? chr(0xc3) : chr(0xc2);
    }
    
    /**
     * integer用serializer
     * @param integer $value
     * @return string
     */
    private static function integer_serializer($value)
    {
        $rtn = '';
        // phpにunsigned integerはないはず
        $negative = $value < 0 ? true : false;
        $padstr = $negative ? '1' : '0';
        if ($negative && abs($value) <= pow(2, (5 - 1))) {
            $bin = '111' . str_pad(substr(decbin($value), -5), 5, $padstr, STR_PAD_LEFT);
            $rtn = chr(bindec($bin));
        } elseif (!$negative && $value < pow(2, (7 - 1))) {
            $bin = '0' . str_pad(decbin($value), 7, $padstr, STR_PAD_LEFT);
            $rtn = chr(bindec($bin));
        } elseif ($value >= - pow(2, (8 - 1)) && $value < pow(2, (8 - 1))) {
            $rtn = chr(0xd0);
            $bin = str_pad(substr(decbin($value), -8), 8, $padstr, STR_PAD_LEFT);
            $rtn .= chr(bindec($bin));
        } elseif ($value >= - pow(2, (16 - 1)) && $value < pow(2, (16 - 1))) {
            $rtn = chr(0xd1);
            $bin = str_pad(substr(decbin($value), -16), 16, $padstr, STR_PAD_LEFT);
            $rtn .= chr(bindec(substr($bin, 0, 8)));
            $rtn .= chr(bindec(substr($bin, 8, 8)));
        } elseif ($value >= - pow(2, (32 - 1)) && $value < pow(2, (32 - 1))) {
            $rtn = chr(0xd2);
            $bin = str_pad(substr(decbin($value), -32), 32, $padstr, STR_PAD_LEFT);
            $rtn .= chr(bindec(substr($bin, 0, 8)));
            $rtn .= chr(bindec(substr($bin, 8, 8)));
            $rtn .= chr(bindec(substr($bin, 16, 8)));
            $rtn .= chr(bindec(substr($bin, 24, 8)));
        } elseif (PHP_INT_SIZE == 4) {
            trigger_error('[msgpack] (msgpack_serialize_zval) too large integer, encoded as null',  E_WARNING);
            $rtn = chr(0xc0);
        } elseif ($value >= - pow(2, (64 - 1)) && $value < pow(2, (64 - 1))) {
            $rtn = chr(0xd3);
            $bin = str_pad(substr(decbin($value), -64), 64, $padstr, STR_PAD_LEFT);
            $rtn .= chr(bindec(substr($bin, 0, 8)));
            $rtn .= chr(bindec(substr($bin, 8, 8)));
            $rtn .= chr(bindec(substr($bin, 16, 8)));
            $rtn .= chr(bindec(substr($bin, 24, 8)));
            $rtn .= chr(bindec(substr($bin, 32, 8)));
            $rtn .= chr(bindec(substr($bin, 40, 8)));
            $rtn .= chr(bindec(substr($bin, 48, 8)));
            $rtn .= chr(bindec(substr($bin, 56, 8)));
        } else {
            trigger_error('[msgpack] (msgpack_serialize_zval) too large integer, encoded as null',  E_WARNING);
            $rtn = chr(0xc0);
        }
        return $rtn;
    }
    
    /**
     * double用serializer
     * @param double $value
     * @return string
     */
    private static function double_serializer($value)
    {
        $rtn = '';
        // おそらくPHPは単精度は使わないはず
        $bin = pack("d", $value);
        $rtn .= chr(0xcb);
        // バイトオーダが逆らしい
        for ($i=7; $i>=0; --$i) {
            $rtn .= substr($bin, $i, 1);
        }
        return $rtn;
    }
    
    /**
     * string用serializer
     * @param string $value
     * @return string
     */
    private static function string_serializer($value)
    {
        $rtn = '';
        $len = strlen($value);
        // peclのmsgpackや本家c++は0xd9に未対応らしい
        // phpのstringはバイナリなのでバイナリだけど明確で無いのでとりあえずstringで実装
        if ($len < 32) {
            $bin = '101' . str_pad(decbin($len), 5, "0", STR_PAD_LEFT);
            $rtn .= chr(bindec($bin));
            $rtn .= $value;
        } elseif ($len < pow(2, 8)) {
            $rtn .= chr(0xd9);
            $rtn .= chr($len);
            $rtn .= $value;
        } elseif ($len < pow(2, 16)) {
            $rtn .= chr(0xda);
            $bin = str_pad(decbin($len), 16, "0", STR_PAD_LEFT);
            $rtn .= chr(bindec(substr($bin, 0, 8)));
            $rtn .= chr(bindec(substr($bin, 8, 8)));
            $rtn .= $value;
        } elseif ($len < pow(2, 32)) {
            $rtn .= chr(0xdb);
            $bin = str_pad(decbin($len), 32, "0", STR_PAD_LEFT);
            $rtn .= chr(bindec(substr($bin, 0, 8)));
            $rtn .= chr(bindec(substr($bin, 8, 8)));
            $rtn .= chr(bindec(substr($bin, 16, 8)));
            $rtn .= chr(bindec(substr($bin, 24, 8)));
            $rtn .= $value;
        } else {
            trigger_error('[msgpack] (msgpack_serialize_zval) too long string, encoded as empty',  E_WARNING);
            $rtn = chr(bindec('10100000'));
        }
        return $rtn;
    }
    
    /**
     * array用serializer
     * @param array $value
     * @return string
     */
    private static function array_serializer($value)
    {
        // phpのarrayは基本ハッシュ
        $rtn = '';
        $cnt = count($value);
        if ($cnt < 15) {
            $bin = '1000' . str_pad(decbin($cnt), 4, "0", STR_PAD_LEFT);
            $rtn .= chr(bindec($bin));
        } elseif ($cnt < pow(2, 16)) {
            $rtn .= chr(0xde);
            $bin = str_pad(decbin($cnt), 16, "0", STR_PAD_LEFT);
            $rtn .= chr(bindec(substr($bin, 0, 8)));
            $rtn .= chr(bindec(substr($bin, 8, 8)));
            
        } elseif ($cnt < pow(2, 32)) {
            $rtn .= chr(0xdf);
            $bin = str_pad(decbin($cnt), 32, "0", STR_PAD_LEFT);
            $rtn .= chr(bindec(substr($bin, 0, 8)));
            $rtn .= chr(bindec(substr($bin, 8, 8)));
            $rtn .= chr(bindec(substr($bin, 16, 8)));
            $rtn .= chr(bindec(substr($bin, 24, 8)));
        }
        foreach ($value as $key => $object) {
            $rtn .= self::serialize($key);
            $rtn .= self::serialize($object);
        }
        return $rtn;
    }
    
    /**
     * object用serializer
     * @param object $value
     * @return string
     */
    private static function object_serializer($value)
    {
        // 作成中
    }
    
    /**
     * unserializer
     * @param string $binary
     * @return mixed
     */
    public static function unserialize($binary)
    {
        
        if (is_object($object)) {
            if (method_exists($object, 'unserialize')) {
                $object->unserialize($object);
            } elseif (method_exists($object, '__wakeup')) {
                
            }
        }
    }
    
}
