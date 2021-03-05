<?php


namespace App\Http\Controllers;


class IndexController extends Controller
{
    public function buildCsv()
    {

        // 输出Excel文件头，可把user.csv换成你要的文件名
        header ( 'Content-Type: application/vnd.ms-excel' );
        header ( 'Content-Disposition: attachment;filename="订单数据.csv"' );
        header ( 'Cache-Control: max-age=0' );
        // 打开PHP文件句柄，php://output 表示直接输出到浏览器
        $fp = fopen ( 'php://output', 'a' );

        $head = array ('订单号','','订单名称','','业务ID','','渠道ID','','渠道类型','','产品线名称','','原始订单号','','订单金额',
            '','From值','','订单时间','','收款合同号','','渠道名称','','付款合同号','','供应商名称','','运营平台','','产品类型',
            '','记账时间','','渠道成本比例','','渠道成本','','应收账款','','结算比例','','应付结算','','是否已回款','','是否已提批次',
            '','备注');
        foreach ( $head as $i => $v ) {
            // CSV的Excel支持GBK编码，一定要转换，否则乱码
            $head [$i] = iconv ( 'utf-8', 'gbk', $v );
        }
        // 将数据通过fputcsv写到文件句柄


        fputcsv ( $fp, $head );
    }
}
