<?php


namespace App\Console\Commands;


use App\GameRecord;
use App\SalesReward;
use App\Users;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use QL\QueryList;

class CustomCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:name {name=unknown}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('进入脚本执行');
        $name =$this->argument('name');
        switch ($name) {
            case 'demo':
                $this->demo();
                break;
            default:
                var_dump('未知name--'. $name);
        }
    }

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

    public function demo()
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
