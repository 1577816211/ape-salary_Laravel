<?php


namespace App\Http\Controllers;


use App\Exceptions\UserException;
use App\Library\Util;
use App\System;
use App\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

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


    /**
     * 添加员工
     * @param Request $request
     * @return false|string
     * @throws \Throwable
     */
    public function addStaff(Request $request)
    {
        $name = $request->get('username');
        $phone = $request->get('phone');
        $bankName = $request->get('bankName');  //开户行名称
        $bankCardAccount = $request->get('bankCardAccount');    //银行卡号
        $role = $request->get('role');  //职位
        $basicSalary = $request->get('basicSalary');    //底薪
        if (!$name || !$phone || !$bankName || !$bankCardAccount || !$role || !$basicSalary) {
            return $this->fail(UserException::E(UserException::RED_ASTERISK_MEANS_REQUIRED));
        }
        //校验手机号
        if (!Util::checkTel($phone)) return $this->fail(UserException::E(UserException::PHONE_FORMAT_ERROR));
        //校验银行卡号
        if (!Util::checkBankCardAccount($bankCardAccount)) return $this->fail(UserException::E(UserException::BANK_ACCOUNT_FORMAT_ERROR));
        $unionNumber = $request->get('unionNumber') ?: '';    //联行号(非必填，默认'')
        $serialNumber = $request->get('serialNumber') ?: '';  //流水号(非必填，默认'')

        $time = time();
        $data = [
            'username' => $name,
            'phone' => $phone,
            'bank_name' => $bankName,
            'bank_card_account' => $bankCardAccount,
            'bank_code' => $bankName == '中国建设银行' ? '01' : '02',
            'union_number' => $unionNumber,
            'serial_number' => $serialNumber,
            'role' => $role,
            'basic_salary' => $basicSalary * 100,   //单位：元->分
            'created_at' => $time,
            'updated_at' => $time,
        ];

        try {
            DB::beginTransaction();
            (new Users())->insert($data);
            DB::commit();
            return $this->success();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            throw $throwable;
        }
    }


    /**
     * 员工列表
     * @param Request $request
     * @return false|string
     */
    public function staffList(Request $request)
    {
        $staffArray = (new Users())->get()->toArray();

        $res = [];

        foreach ($staffArray as $staff) {
            $res[] = [
                'id' => $staff['id'],
                'username' => $staff['username'],
                'phone' => $staff['phone'],
                'bankName' => $staff['bank_name'],
                'bankCardAccount' => $staff['bank_card_account'],
                'role' => $staff['role'],
                'basicSalary' => $staff['basic_salary'] . '元',
                'bonus' => $staff['bonus'] ?: 0 .'元',
                'updated_at' => $staff['updated_at']
            ];
        }
        return $this->success($res);
    }
}
