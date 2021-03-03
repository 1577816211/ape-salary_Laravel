<?php


namespace App\Http\Controllers;


use App\Exceptions\UserException;
use App\System;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 管理员登录
     * @param Request $request
     * @return false|string
     */
    public function login(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');
        if (!$username || !$password) return $this->fail(UserException::E(UserException::USERNAME_OR_PASSWORD_NOT_EMPTY));

        $res = (new System())->where('key', '=', $username)->where('value', '=', md5(md5($password)))->value('id');
        if (!$res) return $this->fail(UserException::E(UserException::LOGIN_FAIL));

        return $this->success();
    }


    /**
     * 管理员更改密码
     * @param Request $request
     * @return false|string
     */
    public function changePwd(Request $request)
    {
        $username = $request->get('username');
        $oldPwd = $request->get('oldPwd');
        $newPwd = $request->get('newPwd');
        if (!$oldPwd) return $this->fail(UserException::E([UserException::PARAMS_NOT_EMPTY, ['name' => $oldPwd]]));
        if (!$newPwd) return $this->fail(UserException::E([UserException::PARAMS_NOT_EMPTY, ['name' => $newPwd]]));

        $adminInfo = (new System())->where('key', '=', $username)->where('value', '=', md5(md5($oldPwd)))->value('id');
        if (!$adminInfo) return $this->fail(UserException::E(UserException::EDIT_PASSWORD_ERROR));

        $res = (new System())->where('key', '=', $username)->update(['value' => md5(md5($newPwd))]);
        if (!$res) return $this->fail(UserException::E(UserException::SYSTEM_BUSY));

        return $this->success();
    }
}
