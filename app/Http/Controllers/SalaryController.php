<?php


namespace App\Http\Controllers;


use App\Exceptions\UserException;
use App\System;
use App\Users;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\AlignFormatter;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Style;

class SalaryController extends Controller
{
    public function buildExcel(Request $request)
    {
        $filename = $request->get('filename');
        if (!$filename) return $this->fail(UserException::E(UserException::RED_ASTERISK_MEANS_REQUIRED));
//        $date = $request->get('date') ?: strtotime(date('Y-m'));

        $systemModel = new System();
        //付款方账户
        $payerBankName = $systemModel->where('key', '=', 'payer_bank_name')->value('value');
        $payerBankAccount = $systemModel->where('key', '=', 'payer_bank_account')->value('value');

        $fieldArray = ['id', 'username', 'phone', 'bank_name', 'bank_card_account', 'bank_code', 'union_number', 'serial_number', 'is_notice', 'basic_salary', 'bonus'];
        //将员工信息查询出来
        $staffArray = (new Users())->select($fieldArray)->get()->toArray();

        $newStaffArr = [];
        if ($staffArray) {
            foreach ($staffArray as $staff) {
                $staff['use'] = '工资';
                $staff['mark'] = '';
                $staff['addInfo']= '';
                $staff['totalSalary'] = $staff['basic_salary'] + $staff['bonus'];
                $staff['payerBankName'] = $payerBankName;
                $staff['payerBankAccount'] = $payerBankAccount;
                unset($staff['basic_salary']);
                unset($staff['bonus']);
                $newStaffArr[] = $staff;
            }

            $title = ['*序号', '*付款方客户账号', '*付款方账户名称', '*收款方行别代码
（01-本行 02-国内他行）', '*收款方客户账号', '*收款方账户名称', '收款方开户行名称',
                '收款方联行号', '客户方流水号', '*金额', '*用途', '备注', '是否短信通知收款人(0-不通知，1-通知，默认为0-不通知。)', '收款人手机号码', '短信通知附加信息'];
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            //表头
            //设置单元格内容
            $titCol = 'A';

            foreach ($title as $key => $value) {
                $sheet->getColumnDimension($titCol)->setWidth(18);  //设置列宽
                $sheet->getColumnDimension('M')->setWidth(15);  //设置列宽
                $sheet->getColumnDimension('N')->setWidth(15);  //设置列宽
                $sheet->getColumnDimension('O')->setWidth(10);  //设置列宽
                $sheet->getRowDimension(1)->setRowHeight(58);   //设置列高
                $sheet->getStyle('A1:O1')->getAlignment()->setWrapText(true);   //自动换行
                $sheet->getStyle('A1:O1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); //文本左右对齐
                $sheet->getStyle('A1:O1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER); //文本上下对齐
                $sheet->getStyle('A1:O1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor('ff8db4e2')->setARGB('ff8db4e2');    //单元格背景颜色
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'ff000000'],
                        ],
                    ],
                ];
                $sheet->getStyle('A1:O1')->applyFromArray($styleArray);
                // 单元格内容写入
                $sheet->setCellValue($titCol . '1', $value)->getStyle('A1:O1')->getFont()->setBold(true)->setName('宋体')->setSize(10);
                $titCol++;
            }

            $row = 2; // 从第二行开始
            foreach ($newStaffArr as $staff) {
                $sheet->getRowDimension($row)->setRowHeight(30);//设置列高
                $sheet->getStyle('A'.$row.':'.'O'.$row)->getFont()->setName('宋体')->setSize(10);
                $sheet->getStyle('A'.$row.':'.'O'.$row)->getAlignment()->setWrapText(true);     //自动换行
//                $sheet->getStyle('A'.$row.':'.'O'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); //文本左右对齐
                $sheet->getStyle('A'.$row.':'.'O'.$row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER); //文本上下对齐
                $sheet->getStyle('C'.$row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('E'.$row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('N'.$row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
                $sheet->setCellValue('A' . $row, $staff['id']);
                $sheet->setCellValue('B' . $row, $staff['payerBankAccount']);
                $sheet->setCellValue('C' . $row, $staff['payerBankName']);
                $sheet->setCellValue('D' . $row, $staff['bank_code']);
                $sheet->setCellValue('E' . $row, $staff['bank_card_account']);
                $sheet->setCellValue('F' . $row, $staff['username']);
                $sheet->setCellValue('G' . $row, $staff['bank_name']);
                $sheet->setCellValue('H' . $row, $staff['union_number']);
                $sheet->setCellValue('I' . $row, $staff['serial_number']);
                $sheet->setCellValue('J' . $row, $staff['totalSalary']);
                $sheet->setCellValue('K' . $row, $staff['use']);
                $sheet->setCellValue('L' . $row, $staff['mark']);
                $sheet->setCellValue('M' . $row, $staff['is_notice']);
                $sheet->setCellValue('N' . $row, $staff['phone']);
                $sheet->setCellValue('O' . $row, $staff['addInfo']);
                $row++;
            }


        }

        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename=' . $filename . '.xlsx');
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

        exit;

    }




    public function getSalesReward()
    {

    }



}
