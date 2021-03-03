<?php


namespace App\Traits;

use Exception;

trait HttpJsonResponse
{
    private $reply = [
        'status' => 1,
        'info' => '操作成功!',
        'data' => null,
        'code' => 200,
        'paginate' => null
    ];

    protected function page($page)
    {
        $this->reply['paginate'] = $page;
        return $this;
    }

    protected function data($data)
    {
        $this->reply['data'] = $data;
        return $this;
    }

    protected function success($data = null, $info = null)
    {
        $this->reply['data'] = $data;
        if ($info) {
            $this->reply['info'] = $info;
        }
        return json_encode($this->reply, JSON_UNESCAPED_UNICODE);
    }

    protected function fail($info = '操作失败!', $code = 501)
    {
        if ($info instanceof Exception) {
            $code = $info->getCode();
            $info = $info->getMessage();
        }
        $this->reply['status'] = 0;
        $this->reply['code'] = $code;
        $this->reply['info'] = $info;
        return json_encode($this->reply, JSON_UNESCAPED_UNICODE);
    }
}
