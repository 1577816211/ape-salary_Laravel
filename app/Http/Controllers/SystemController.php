<?php


namespace App\Http\Controllers;


use App\Exceptions\UserException;
use App\System;
use App\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{

    /**
     * 展示后台系统配置
     * @return false|string
     */
    public function getSystemConfig()
    {
        $systemModel = new System();
        $data['payerBankName'] = $systemModel->where('key' , '=', 'payer_bank_name')->value('value') ?: '';
        $data['payerBankAccount'] = $systemModel->where('key', '=', 'payer_bank_account')->value('value') ?: '';
        return $this->success($data);
    }


    /**
     * 修改后台系统配置
     * @param Request $request
     * @return false|string
     * @throws \Throwable
     */
    public function editSystemConfig(Request $request)
    {
        $payerBankName = $request->get('payerAccountName');
        if (!$payerBankName) return $this->fail(UserException::E([UserException::PARAMS_NOT_EMPTY, ['name' => $payerBankName]]));
        $payerBankAccount = $request->get('payerAccount');
        if (!$payerBankAccount) return $this->fail(UserException::E([UserException::PARAMS_NOT_EMPTY, ['name' => $payerBankAccount]]));
        $bonus = $request->get('bonus');
        $bonus = $bonus ? $bonus * 100 : 0;

        $systemModel = new System();

        try {
            DB::beginTransaction();
            $systemModel->where('key', '=', 'payerAccountName')->update(['value' => $payerBankName]);
            $systemModel->where('key', '=', 'payerAccount')->update(['value' => $payerBankAccount]);
            if ($bonus) {
                (new Users())->where('role', '=', 2)->increment('bonus', $bonus);
            }
            DB::commit();
            return $this->success();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            throw $throwable;
        }
    }
}
