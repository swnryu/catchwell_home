<?
session_start();
/*
include ("../common.php");
require ("../check_session.php");
include("event_def.php");

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';
*/
include('../header.php'); 
include ('../PHPExcel/Classes/PHPExcel.php');
include ('../PHPExcel/Classes/PHPExcel/IOFactory.php');

header("Content-type: text/html; charset=utf-8");

const FORMAT_CODE_USER_DATE = 'mm"월" dd"일"';

$dbname	= $_POST['dbname'];
$name	= $_POST['name'];
$idx	= $_POST['idx'];
$val	= $_POST['val'];

//발주날짜, 모델, 악세사리추가(수량으로 표기), , , 구매처(무시), 주문번호, 업체명, 수령자명, 송장번호, 일반전화, 핸드폰, 주소, 배송메세지, 사방넷주문번호

if($name=="export2excel") {

        // create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $fileName = './temp/'.$_GET['filename']; //'./temp/배송리스트_event_'.date("Ymd");

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fileName);
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // header("Content-Encoding: utf-8");

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        $styleArray = array(
                'font'  => array(
                    'bold'  => false,
                    'color' => array('rgb' => '000000'),
                    'size'  => 10,
                    'name'  => '맑은고딕'
                ));
        $objPHPExcel->getDefaultStyle()->applyFromArray($styleArray);

        //header : A~O
        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(18);
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(8);
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);

        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(65);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '발주날짜')
                ->setCellValue('B1', '모델')
                ->setCellValue('C1', '악세사리추가(수량으로 표기)')
                ->setCellValue('D1', '')
                ->setCellValue('E1', '수량')
                ->setCellValue('F1', '구매처(무시)')
                ->setCellValue('G1', '주문번호')
                ->setCellValue('H1', '업체명')
                ->setCellValue('I1', '수령자명')   
                ->setCellValue('J1', '송장번호')   
                ->setCellValue('K1', '일반전화')   
                ->setCellValue('L1', '핸드폰')   
                ->setCellValue('M1', '주소')   
                ->setCellValue('N1', '배송메세지')   
                ->setCellValue('O1', '접수번호') ;
        
        $objPHPExcel->getActiveSheet()->getStyle(sprintf("A%s:O%s", 1, 1))->applyFromArray(array(
                'font'  => array(
                        'bold'  => true
                )
                ));

        $objPHPExcel->getActiveSheet()->getStyle(sprintf("A%s:O%s", 1, 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aad2d2d2");
        $objPHPExcel->getActiveSheet()->setAutoFilter('A1:O1');


        
        //data
	$colIdx = 2;
	for($i=0;$i<count($idx);$i++) {	
		
            $row = $db->object($dbname, "where idx='$idx[$i]'");
        
            $gift = $row->model_name.$row->gift;
            if ($row->gift == 'CW44_사은품')
            {
                $gift = $row->gift;
            }

            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue(sprintf("A%s", $colIdx), PHPExcel_Shared_Date::PHPToExcel(date('Y-m-d')))
//            ->setCellValue(sprintf("A%s", $colIdx), PHPExcel_Shared_Date::PHPToExcel(strtotime(date('Y-m-d'), time())))
            ->setCellValue(sprintf("B%s", $colIdx), $gift /*$row->model_name.$row->gift*/)
            ->setCellValue(sprintf("E%s", $colIdx), 1)
            ->setCellValue(sprintf("F%s", $colIdx), $row->event_name)
            ->setCellValue(sprintf("G%s", $colIdx), ''/*$row->order_id*/)
            ->setCellValue(sprintf("H%s", $colIdx), '사은품'/*$row->market_name*/)
            ->setCellValue(sprintf("I%s", $colIdx), $row->customer_name)
            ->setCellValue(sprintf("K%s", $colIdx), $row->customer_phone)
            ->setCellValue(sprintf("L%s", $colIdx), $row->customer_phone)
            ->setCellValue(sprintf("M%s", $colIdx), $row->customer_addr." ".$row->customer_addr_detail)
            ->setCellValue(sprintf("N%s", $colIdx), ''/*$row->event_name*/)
            ->setCellValue(sprintf("O%s", $colIdx), $idx[$i] );
            

            $objPHPExcel->getActiveSheet(0)->getStyle(sprintf("A%s", $colIdx))->getNumberFormat()->setFormatCode('mm월dd일');
            //$objPHPExcel->getActiveSheet(0)->getStyle(sprintf("A%s", $colIdx))->getNumberFormat()->setFormatCode('mm-dd');

            $colIdx++;

        }


        //save
        $objWriter->save($fileName);

        // disconnect
        $objPHPExcel->disconnectWorksheets();
        $objPHPExcel->garbageCollect();

        unset( $objWriter, $objPHPExcel );

        $db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='excel_dl_ev', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$fileName' ");
        
}

?>