<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 'ape_users';

    protected $dates = ['updated_at', 'created_at'];

    protected $dateFormat = 'Y-m-d H:i:s';

    public $timestamps = false;


    /**
     * 底薪单位换算(分->元)
     * @param $value
     * @return float|int
     */
    public function getbasicSalaryAttribute($value)
    {
        return $value / 100;
    }


    /**
     * 奖金单位换算(分->元)
     * @param $value
     * @return float|int
     */
    public function getBonusAttribute($value)
    {
        return $value ? $value / 100 : 0;
    }


    public function getRoleAttribute($value)
    {
        if ($value == 1) {
            $value = '销售';
        } elseif ($value == 2) {
            $value = '技术';
        }

        return $value;
    }
}
