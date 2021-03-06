<?php


namespace App\Library;


class Util
{
    /**
     * 分页
     * @var int
     */
    static public $limit = 10;


    /**
     * 校验手机号
     * @param $tel
     * @return bool
     */
    static public function checkTel($tel)
    {
        if (empty($tel)) return false;

        $preg = "/^1[3456789]\d{9}$/";
        if (!preg_match($preg, $tel)) {
            return false;
        }

        return true;
    }


    /**
     * 校验银行卡号
     * @param $number
     * @return bool
     */
    static public function checkBankCardAccount($number)
    {
        if (empty($number)) return false;

        $preg = "/^([1-9]{1})(\d{14}|\d{18})$/";
        if (!preg_match($preg, $number)) {
            return false;
        }

        return true;
    }
}
