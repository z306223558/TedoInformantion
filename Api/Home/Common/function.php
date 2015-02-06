<?php

/**
 * 过滤特殊字符
 **/

function FL($data,$type='mix')
{
    if(empty($data))
    {
        return false;
    }
    if($type === 'mix')
    {
        if(is_array($data))
        {
            foreach($data as $kay => $value)
            {
                if(!empty($value))
                {
                    preg_match('/^[_\w\-.@\']+$/',$value,$match);
                    if(empty($match))
                    {
                        $data[$kay] = false;
                    }
                }
            }
        }else
        {
            preg_match('/^[_\w\-.@\']+$/',$data,$match);
            if(empty($match))
            {
                $data = false;
            }
        }
        return $data;
    }elseif($type === 'num')
    {
        if(is_array($data))
        {
            foreach($data as $kay => $value)
            {
                if(!empty($value))
                {
                    preg_match('/^[0-9]+$/',$value,$match);
                    if(empty($match))
                    {
                        $data[$kay] = false;
                    }
                }
            }
        }else
        {
            preg_match('/^[0-9]+$/',$data,$match);
            if(empty($match))
            {
                $data = false;
            }
        }
        return $data;
    }elseif($type === 'str')
    {
        if(is_array($data))
        {
            foreach($data as $kay => $value)
            {
                if(!empty($value))
                {
                    preg_match('/^[0-9a-zA-Z]$/',$value,$match);
                    if(empty($match))
                    {
                        $data[$kay] = false;
                    }
                }
            }
        }else
        {
            preg_match('/^[0-9a-zA-Z]$/',$data,$match);
            if(empty($match))
            {
                $data = false;
            }
        }
        return $data;
    }else
    {
        return $data;
    }

}

    /**
     * 验证用户名
     * @param string $value
     * @param int $length
     * @return boolean
     */
    function isNames($value, $minLen=2, $maxLen=20, $charset='ALL'){
        if(empty($value))
            return false;
        switch($charset){
            case 'EN': $match = '/^[_\w\d]{'.$minLen.','.$maxLen.'}$/iu';
                break;
            case 'CN':$match = '/^[_\x{4e00}-\x{9fa5}\d]{'.$minLen.','.$maxLen.'}$/iu';
                break;
            default:$match = '/^[_@\w\d\x{4e00}-\x{9fa5}]{'.$minLen.','.$maxLen.'}$/iu';
        }
        return preg_match($match,$value);
    }

    /**
     * 验证密码
     * @param string $value
     * @param int $length
     * @return boolean
     **/
     function isPWD($value,$minLen=5,$maxLen=16){
        $match='/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{'.$minLen.','.$maxLen.'}$/';
        $v = trim($value);
        if(empty($v))
            return false;
        return preg_match($match,$v);
    }

    /**
     * 验证eamil
     * @param string $value
     * @param int $length
     * @return boolean
     **/
     function isEmail($value,$match='/^[\w\d]+[\w\d-.]*@[\w\d-.]+\.[\w\d]{2,10}$/i'){
        $v = trim($value);
        if(empty($v))
            return false;
        return preg_match($match,$v);
    }

    /**
     * 验证电话号码
     * @param string $value
     * @return boolean
     **/
    function isTelephone($value,$match='/^0[0-9]{2,3}[-]?\d{7,8}$/'){
        $v = trim($value);
        if(empty($v))
            return false;
        return preg_match($match,$v);
    }

    /**
     * 验证手机
     * @param string $value
     * @param string $match
     * @return boolean
     **/
    function isMobile($value,$match='/^[(86)|0]?(13\d{9})|(15\d{9})|(18\d{9})$/'){
        $v = trim($value);
        if(empty($v))
            return false;
        return preg_match($match,$v);
    }
    /**
     * 验证邮政编码
     * @param string $value
     * @param string $match
     * @return boolean
     **/
    function isPostcode($value,$match='/\d{6}/'){
        $v = trim($value);
        if(empty($v))
            return false;
        return preg_match($match,$v);
    }
    /**
     * 验证IP
     * @param string $value
     * @param string $match
     * @return boolean
     **/
    function isIP($value,$match='/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/'){
        $v = trim($value);
        if(empty($v))
            return false;
        return preg_match($match,$v);
    }

    /**
     * 验证身份证号码
     * @param string $value
     * @param string $match
     * @return boolean
     **/
    function isIDcard($value,$match='/^\d{6}((1[89])|(2\d))\d{2}((0\d)|(1[0-2]))((3[01])|([0-2]\d))\d{3}(\d|X)$/i'){
        $v = trim($value);
        if(empty($v))
            return false;
        else if(strlen($v)>18)
            return false;
        return preg_match($match,$v);
    }

    /**
     *
     * 验证URL
     * @param string $value
     * @param string $match
     * @return boolean
     **/
    function isURL($value,$match='/^(http:\/\/)?(https:\/\/)?([\w\d-]+\.)+[\w-]+(\/[\d\w-.\/?%&=]*)?$/'){
        $v = strtolower(trim($value));
        if(empty($v))
            return false;
        return preg_match($match,$v);
    }




