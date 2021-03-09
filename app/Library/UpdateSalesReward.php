<?php


namespace App\Library;



use App\SalesReward;
use App\Users;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class UpdateSalesReward
{

    /**
     * 客户端授权
     * @param $data
     * @return string
     */
    protected function sign(&$data)
    {
        $key = env('APE_ADMIN_API_SERVER_SECRET_KEY');
        if (isset($data['sign'])) unset($data['sign']);
        ksort($data);
        $query = '';
        foreach($data as $k => $v) {
            $query .= "{$k}={$v}&";
        }
        $query .= "key={$key}";
        return strtoupper(md5($query));
    }


    /**
     * 获取并更新销售的提成
     *
     * @throws \Throwable
     */
    public function getSalesReward()
    {
        $saleIdArr = (new Users())->where('sale_id', '<>', 0)->pluck('sale_id', 'id')->toArray();
        if (!$saleIdArr) return;
        $date = date('Y-m', strtotime('-1 month'));
        $url = env('APE_ADMIN_API_SERVER');

        $client = new Client;

        $updateTime = time();

        $salesRewardModel = new SalesReward();
        try {
            DB::beginTransaction();
            foreach ($saleIdArr as $id => $saleId) {
                $data = [
                    'id' => $saleId
                ];
                $data['sign'] = $this->sign($data);
                //POST:form_params; GET:query
                $res = $client->request('GET', $url, [
                    'query' => $data
                ]);
                $data = json_decode($res->getBody()->getContents(), true)['data'] ?? [];

                foreach ($data as $k => $v) {
                    if ($date != $k) continue;
                    $v['total_reward'] = ($v['normal_reward'] * 0.1) + ($v['union_reward'] * 0.05);
                    $rewardArr = [
                        'updated_at' => $updateTime,
                        'normal_reward' => $v['normal_reward'],
                        'union_reward' => $v['union_reward'],
                        'total_reward' => $v['total_reward']
                    ];

                    //用户表给该销售更新提成
                    (new Users())->where('id', '=', $id)->update(['bonus' => $v['total_reward']]);

                    //查询提成记录id
                    $rewardRecordId = $salesRewardModel->where('sale_id', '=', $saleId)
                        ->where('created_at', '=', strtotime($date))
                        ->value('id');

                    //如果没有提成记录就创建，有则更新
                    if ($rewardRecordId) {
                        $salesRewardModel->where('id', '=', $rewardRecordId)->update($rewardArr);
                    } else {
                        $rewardArr['sale_id'] = $saleId;
                        $rewardArr['created_at'] = strtotime($date);
                        $salesRewardModel->insert($rewardArr);
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            throw $throwable;
        }
    }

}
