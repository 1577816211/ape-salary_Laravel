<?php


namespace App\Http\Controllers;


use App\Exceptions\UserException;
use App\Library\Common;
use App\Library\Util;
use App\System;
use App\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $password = md5(md5($password));
        $res = (new System())->where('key', '=', $username)->where('value', '=', $password)->value('id');
        if (!$res) return $this->fail(UserException::E(UserException::LOGIN_FAIL));

        return $this->success(['token' => $password]);
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
        $saleId = $request->get('saleId') ?: 0;  //销售Id
        if ($role == 1 && !$saleId) return $this->fail(UserException::E(UserException::REGISTER_FAIL_SALE_ID_NOT_EMPTY));
        if ($role == 2 && $saleId) return $this->fail(UserException::E(UserException::NO_NEED_SALE_ID));
        //校验手机号
        if (!Util::checkTel($phone)) return $this->fail(UserException::E(UserException::PHONE_FORMAT_ERROR));
        //校验银行卡号
        if (!Util::checkBankCardAccount($bankCardAccount)) return $this->fail(UserException::E(UserException::BANK_ACCOUNT_FORMAT_ERROR));
        $unionNumber = $request->get('unionNumber') ?: '';    //联行号(非必填，默认'')
        $serialNumber = $request->get('serialNumber') ?: '';  //流水号(非必填，默认'')

        $time = time();
        $data = [
            'sale_id' => $saleId,
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
        $time = $request->get('time');
        $limit = Util::$limit;
        $offset = $request->get('offset');
        if (!$offset) return $this->fail(UserException::T([UserException::PARAMS_NOT_EMPTY, ['name' => $offset]]));
        $offset = ($offset - 1) * $limit;

        $userModel = new Users();
        $totalData = $userModel->count('id');

        if ($time) {
            $endTime = $time + 86399;
            $staffArray = $userModel
                ->whereBetween('updated_at', [$time, $endTime])
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->toArray();
        } else {
            $staffArray = $userModel
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->toArray();
        }

        $pages = $totalData % $limit == 0 ? $totalData / $limit : intval($totalData / $limit) + 1;
        $this->page($pages);
        $res = [];

        foreach ($staffArray as $staff) {
            $res[] = [
                'id' => $staff['id'],
                'username' => $staff['username'],
                'phone' => $staff['phone'],
                'bankName' => $staff['bank_name'],
                'bankCardAccount' => $staff['bank_card_account'],
                'role' => $staff['role'],
                'basicSalary' => $staff['basic_salary'],
                'bonus' => $staff['bonus'],
                'totalSalary' => $staff['basic_salary'] + $staff['bonus'],
                'updated_at' => $staff['updated_at']
            ];
        }
        return $this->success($res);
    }


    /**
     * 员工详情
     * @param Request $request
     * @return false|string
     */
    public function staffDetail(Request $request)
    {
        $id = $request->get('id');
        if (!$id) return $this->fail(UserException::E([UserException::PARAMS_NOT_EMPTY, ['name' => $id]]));
        $type = $request->get('type');

        $fields = ['id', 'sale_id', 'username', 'phone', 'bank_name', 'bank_card_account', 'union_number', 'serial_number', 'role', 'basic_salary', 'bonus', 'is_notice'];
        $staffDetail = (new Users())->where('id', '=', $id)->select($fields)->first()->toArray();
        $staffDetail['total_salary'] = $staffDetail['basic_salary'] + $staffDetail['bonus'];
        $staffDetail['sale_id'] = $staffDetail['sale_id'] ?: '';

        if ($type == 'detail') {
            $noticeMap = [0 => '不通知', 1 => '通知'];
            $staffDetail['is_notice'] = $noticeMap[$staffDetail['is_notice']];
            return $this->success($staffDetail);
        } elseif ($type = 'edit') {
            $roleMap = ['销售' => 1, '技术' => 2];
            $staffDetail['role'] = $roleMap[$staffDetail['role']];
            return $this->success($staffDetail);
        }



    }


    /**
     * 编辑员工信息
     * @param Request $request
     * @return false|string
     */
    public function editStaff(Request $request)
    {
        $id = $request->get('id');
        if (!$id) return $this->fail(UserException::E([UserException::PARAMS_NOT_EMPTY, ['name' => $id]]));
        $phone = $request->get('phone');
        $bankName = $request->get('bankName');  //开户行名称
        $bankCardAccount = $request->get('bankCardAccount');    //银行卡号
        $role = $request->get('role');  //职位
        $basicSalary = $request->get('basicSalary');    //底薪
        if (!$phone || !$bankName || !$bankCardAccount || !$role || !$basicSalary) {
            return $this->fail(UserException::E(UserException::RED_ASTERISK_MEANS_REQUIRED));
        }
        $saleId = $request->get('saleId') ?: 0;  //销售Id
        if ($role == 1 && !$saleId) return $this->fail(UserException::E(UserException::SALE_ID_NOT_EMPTY)); //销售需要销售ID
        if ($role == 2 && $saleId) return $this->fail(UserException::E(UserException::NO_NEED_SALE_ID));    //技术不需要销售ID

        //校验手机号
        if (!Util::checkTel($phone)) return $this->fail(UserException::E(UserException::PHONE_FORMAT_ERROR));
        //校验银行卡号
        if (!Util::checkBankCardAccount($bankCardAccount)) return $this->fail(UserException::E(UserException::BANK_ACCOUNT_FORMAT_ERROR));
        $isNotice = $request->get('isNotice');      //是否需要短信通知
        $unionNumber = $request->get('unionNumber') ?: '';    //联行号(非必填，默认'')
        $serialNumber = $request->get('serialNumber') ?: '';  //流水号(非必填，默认'')
        $bonus = $request->get('bonus') ?: 0;    //奖金

        $staffInfo = (new Users())->where('id', '=', $id)->first();
        if (!$staffInfo) return $this->fail(UserException::E(UserException::USER_NOT_EXISTS));
        $staffInfo = $staffInfo->toArray();

        $roleMap = ['销售' => 1, '技术' => 2];

        //收集修改后的数据，进行修改
        $data = [];
        if ($phone != $staffInfo['phone']) {
            $data['phone'] = $phone;
        } elseif ($bankName != $staffInfo['bank_name']) {
            $data['bank_name'] = $bankName;
        } elseif ($bankCardAccount != $staffInfo['bank_card_account']) {
            $data['bank_card_account'] = $bankCardAccount;
        } elseif ($role != $roleMap[$staffInfo['role']]) {
            $data['role'] = $role;
        } elseif ($basicSalary != $staffInfo['basic_salary']) {
            $data['basic_salary'] = $basicSalary * 100;
        } elseif ($unionNumber != $staffInfo['union_number']) {
            $data['union_number'] = $unionNumber;
        } elseif ($serialNumber != $staffInfo['serial_number']) {
            $data['serial_number'] = $serialNumber;
        } elseif ($bonus != $staffInfo['bonus']) {
            $data['bonus'] = $bonus * 100;
        } elseif ($isNotice != $staffInfo['is_notice']) {
            $data['is_notice'] = $isNotice;
        }

        if ($saleId != $staffInfo['sale_id']) {
            //检测该销售ID是否存在
            $checkSaleId = (new Users)->where('sale_id', '=', $saleId)->value('sale_id');
            if ($checkSaleId) return $this->fail(UserException::E(UserException::EDIT_SALE_ID_ERROR));
            $data['sale_id'] = $saleId;
        }

        if (!$data) return $this->success();

        $data['updated_at'] = time();

        $res = (new Users())->where('id', '=', $id)->update($data);
        if (!$res) return $this->fail(UserException::E(UserException::SYSTEM_BUSY));
        return $this->success();
    }
}
