<?php


namespace App\Exceptions;


class UserException extends BaseException
{
    const CODE_PRE = '1001';

    const CODE_MAP = [
        '0000' => '系统繁忙，请稍后重试',
        '0001' => '登陆失败，用户名或密码错误',
        '0002' => '注册失败，该用户已存在',
        '0003' => '登陆过期，请重新登陆！',
        '0004' => '验证码发送频率过快，请稍后再试',
        '0005' => '此设备已被绑定，请先解绑后尝试',
        '0006' => '手机号码已存在',
        '0007' => '验证码输入错误',
        '0008' => '登陆失败，该用户不存在',
        '0009' => '修改用户名失败，该用户名已存在',
        '0010' => '已达到当日最大的发送次数',
        '0011' => '邮箱格式错误',
        '0012' => '用户名或密码不能为空',
        '0013' => '验证码发送失败，请稍后尝试',
        '0014' => '验证码已过期',
        '0015' => '注册失败，请稍后再试',
        '0016' => 'token验证失败',
        '0017' => '参数: {$name} 不能为空',
        '0018' => '验证码和新密码不能为空',
        '0019' => '修改密码失败，原密码不正确',
        '0020' => '修改昵称失败，游戏昵称不能为空',
        '0021' => '该用户不存在',
        '0022' => '该数据不存在',
        '0023' => '修改信息失败，不能提交空信息',
        '0024' => '红色星号是必填项',
        '0025' => '手机号码格式错误',
        '0026' => '银行卡号格式错误',
        '0027' => '添加用户失败，销售ID不能为空',
        '0028' => '修改销售ID失败，该销售已存在',
        '0029' => '销售ID不能为空',
        '0030' => '操作失败，技术不需要填写销售ID'
    ];
    const SYSTEM_BUSY = '0000';
    const LOGIN_FAIL = '0001';
    const USER_EXIST = '0002';
    const SESSION_EXPIRE = '0003';
    const SMS_CAPTCHA_FREQ_TOO_FAST = '0004';
    const DEVICE_BOUND = '0005';
    const PHONE_EXISTS = '0006';
    const SMS_CAPTCHA_ERROR = '0007';
    const NO_EXISTS_USER = '0008';
    const EDIT_USERNAME_ERROR = '0009';
    const SEND_SMS_MAX_LIMIT = '0010';
    const EMAIL_FORMAT_ERROR = '0011';
    const USERNAME_OR_PASSWORD_NOT_EMPTY = '0012';
    const CAPTCHA_CODE_SEND_ERROR = '0013';
    const CAPTCHA_CODE_IS_EXPIRE = '0014';
    const REGISTER_FAIL = '0015';
    const TOKEN_FAIL = '0016';
    const PARAMS_NOT_EMPTY = '0017';
    const CAPTCHA_CODE_AND_NEW_PWD_NOT_EMPTY = '0018';
    const EDIT_PASSWORD_ERROR = '0019';
    const EDIT_NICKNAME_ERROR = '0020';
    const USER_NOT_EXISTS = '0021';
    const DATA_NOT_EXISTS = '0022';
    const EDIT_USER_INFO_ERROR = '0023';
    const RED_ASTERISK_MEANS_REQUIRED = '0024';
    const PHONE_FORMAT_ERROR = '0025';
    const BANK_ACCOUNT_FORMAT_ERROR = '0026';
    const REGISTER_FAIL_SALE_ID_NOT_EMPTY = '0027';
    const EDIT_SALE_ID_ERROR = '0028';
    const SALE_ID_NOT_EMPTY = '0029';
    const NO_NEED_SALE_ID = '0030';
}
