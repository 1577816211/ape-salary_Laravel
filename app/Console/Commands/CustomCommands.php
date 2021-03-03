<?php


namespace App\Console\Commands;


use App\GameRecord;
use App\Users;
use Illuminate\Console\Command;
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



    public function demo()
    {
        $data = QueryList::get('https://www.baidu.com/s?wd=QueryList', null, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
                'Accept-Encoding' => 'gzip, deflate, br',
            ]
        ])->rules([
            'title' => ['h3', 'text'],
            'link' => ['h3>a', 'href']
        ])
            ->range('.result')
            ->queryData();

        print_r($data);
    }
}
