<?php


namespace App\Http\Controllers;


use App\System;
use App\Users;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function buildExcel(Request $request)
    {
        $systemModel = new System();
        //付款方账户
        $payerBankName = $systemModel->where('key', '=', 'payer_bank_name')->value('value');
        $payerBankAccount = $systemModel->where('key', '=', 'payer_bank_account')->value('value');

        $fieldArray = ['id', 'username', 'phone', 'bank_name', 'bank_card_account', 'bank_code', 'union_number', 'serial_number', 'is_notice', 'total_salary'];
        //将员工信息查询出来
        $staffArray = (new Users())->select($fieldArray)->get()->toArray();

        $newStaffArr = [];
        if ($staffArray) {
            foreach ($staffArray as $staff) {
                $staff['payerBankName'] = $payerBankName;
                $staff['payerBankAccount'] = $payerBankAccount;
                $newStaffArr[] = $staff;
            }

            $title = ['序号', '付款方客户账号', '付款方账户名称', '收款方行别代码', '收款方客户账号', '收款方账户名称', '收款方开户行名称',
                '收款方联行号', '客户方流水号', '金额', '用途', '备注', '是否短信通知收款人', '收款人手机号码', '短信通知附加信息'];
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            //表头
            //设置单元格内容
            $titCol = 'A';
            foreach ($title as $key => $value) {
                // 单元格内容写入
                $sheet->setCellValue($titCol . '1', $value);
                $titCol++;
            }

            $dataArr = [
//                'id' => array_column($staff, 'id'),
            ];
//            dd($dataArr);
            foreach ($newStaffArr as $staff){
                $dataArr[] = [
//                    'id' => array_column($staff, 'id'),
//                    'username' => $staff['username'],
//                    'phone' => $staff['phone'],
//                    'bank_name' => $staff['bank_name'],
//                    'bank_card_account' => $staff['bank_card_account'],
//                    'bank_code' => $staff['bank_code'],
//                    'union_number' => $staff['union_number'],
//                    'serial_number' => $staff['serial_number'],
//                    'is_notice' => $staff['is_notice'],
//                    'total_salary' => $staff['total_salary'],
//                    'payerBankName' => $staff['payerBankName'],
//                    'payerBankAccount' => $staff['payerBankAccount']
                ];
//                dd($staff);
//                $proviceName = $one['name'];
//                foreach ($one['sub'] as $subOne){
//                    $dataArr[$proviceName][] = [
//                        'city' => $subOne['name'],
//                        'code' => $subOne['city']
//                    ];
//                }
            }

            var_dump('aa');
            dd($dataArr);

            $row = 2; // 从第二行开始
            foreach ($dataArr as $provice => $item) {
                $dataColA = 'A';
                $dataColB = 'B';
                $dataColC = 'C';
                $sheet->setCellValue($dataColA . $row, $provice);
                foreach ($item as $value) {
                    $sheet->setCellValue($dataColB . $row, $value['city']);
                    $sheet->setCellValue($dataColC . $row, $value['code']);
                    $row++;
                }
        }


        }

        $filename = '';
        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="地区.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        $writer->save('http://' . $request->server('HTTP_HOST') . '/excel/' . $filename);
        exit;
    }
}
