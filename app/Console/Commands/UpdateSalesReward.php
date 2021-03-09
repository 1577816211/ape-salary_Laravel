<?php

namespace App\Console\Commands;

use App\Library\UpdateSalesReward as update;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateSalesReward extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-sales-reward';

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
        try {
            var_dump('开始执行脚本');
            (new update())->getSalesReward();
            var_dump('脚本执行完毕');
        } catch (\Throwable $throwable) {
            Log::warning('getSalesReward task exception', $throwable->getTrace());
        }
    }
}
