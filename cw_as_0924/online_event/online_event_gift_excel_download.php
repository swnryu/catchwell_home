<?
session_start();
//20231011 신규생성

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
        //EXCEL START
        // create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $fileName = './temp/'.$_GET['filename']; //'./temp/배송리스트_event_'.date("Ymd");

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fileName);//filename = 저장되는 파일명을 설정합니다.
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

        //타일틀 셋팅
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '발주날짜')
                //->setCellValue('B1', '모델')
                ->setCellValue('B1', '사은품명')//20231011
                //->setCellValue('C1', '악세사리추가(수량으로 표기)')
                ->setCellValue('C1', '')//20230809
                //->setCellValue('D1', '')
                //->setCellValue('D1', '요청자')//20230809 요청자추가 담당자?
                ->setCellValue('D1', '핀번호')//20231011 요청자 없음으로 핀번호 추가
                ->setCellValue('E1', '수량')
                //->setCellValue('F1', '구매처(무시)')
                ->setCellValue('F1', 'idx')//20230809 idx 추가 
                ->setCellValue('G1', '주문번호')
                //->setCellValue('H1', '업체명')
                ->setCellValue('H1', '구매처')//20230913 업체명에서 구매처로 변경 요청 건 수정
                ->setCellValue('I1', '수령자명')   
                ->setCellValue('J1', '송장번호')//20231011   
                //->setCellValue('J1', '재출고송장번호')//2023009
                ->setCellValue('K1', '일반전화')   
                ->setCellValue('L1', '핸드폰')   
                ->setCellValue('M1', '주소')   
                ->setCellValue('N1', '배송메세지')              
                ->setCellValue('O1', '사방넷 주문번호') ;
                //->setCellValue('O1', '접수번호') ;


                /*
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
                */

        
        //타이틀 스타일
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
        
            /*
            $gift = $row->model_name.$row->gift;
            if ($row->gift == 'CW44_사은품')
            {
                $gift = $row->gift;
            }
            */
//20231011
//발주날짜, 사은품명, ,핀번호 , 수량, idx, 주문번호, 구매처, 수령자명, 송장번호, 일반전화, 핸드폰, 주소, 배송메세지, 사방넷주문번호
//db에 맞게 넣는다.


            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue(sprintf("A%s", $colIdx), PHPExcel_Shared_Date::PHPToExcel(date('Y-m-d')))//발주날짜
            //->setCellValue(sprintf("B%s", $colIdx), $row->model_name /*$row->model_name.$row->gift*/)//모델
            ->setCellValue(sprintf("B%s", $colIdx), $row->gift /*$row->model_name.$row->gift*/)//사은품 20231011

           //->setCellValue(sprintf("D%s", $colIdx), $row->pin_num /**/)//20231011 요청자(담당자) 없음으로 핀번호 변경
           ->setCellValueExplicit(sprintf("D%s", $colIdx), $row->pin_num, PHPExcel_Cell_DataType::TYPE_STRING)//20231011 요청자(담당자) 없음으로 핀번호 변경
            
            ->setCellValue(sprintf("E%s", $colIdx), 1)//수량

            //->setCellValue(sprintf("F%s", $colIdx), ''/*$row->event_name*/)//구매처?
            ->setCellValue(sprintf("F%s", $colIdx), $row->idx)//20230809 idx로 추가

           // ->setCellValueExplicit(sprintf("E%s", $rowIdx), $row['order_id'], PHPExcel_Cell_DataType::TYPE_STRING)  
            //->setCellValueExplicit(sprintf("G%s", $colIdx), $row->order_id, PHPExcel_Cell_DataType::TYPE_STRING)//주문번호 20230809 다운로드시 숫자 틀어짐
            ->setCellValueExplicit(sprintf("G%s", $colIdx), $row->oid, PHPExcel_Cell_DataType::TYPE_STRING)//주문번호 20231011 다운로드시 숫자 틀어짐

            //20230913 업체명에서 구매처로 변경 요청 건 수정
            ->setCellValue(sprintf("H%s", $colIdx), $row->shoppingmall)//구매처 20231011

            //->setCellValue(sprintf("I%s", $colIdx), $row->customer_name)//수령자명
            ->setCellValue(sprintf("I%s", $colIdx), $row->name)//수령자명

            //->setCellValue(sprintf("J%s", $colIdx), $row->exchange_tracking_number)//출고송장번호  송장번호를 넣어야할지 출고송장번호를 넣어할지 물어봐야함
            //->setCellValueExplicit(sprintf("J%s", $colIdx), $row->exchange_tracking_number, PHPExcel_Cell_DataType::TYPE_STRING)//출고송장번호  송장번호를 넣어야할지 출고송장번호를 넣어할지 물어봐야함
            ->setCellValueExplicit(sprintf("J%s", $colIdx), $row->tracking_num, PHPExcel_Cell_DataType::TYPE_STRING)//20231011 송장번호

            ->setCellValue(sprintf("K%s", $colIdx), '')//일반전화
            
            ->setCellValue(sprintf("L%s", $colIdx), $row->hp)//핸드폰 20231011 
            //->setCellValue(sprintf("M%s", $colIdx), $row->address)//주소
            
            ->setCellValue(sprintf("M%s", $colIdx), $row->add1 . " " . $row->add2)//주소 add1 add2 문자 합쳐 나오게 수정 20231011
            ->setCellValue(sprintf("N%s", $colIdx), ''/*$row->event_name*/)//배송메세지

            ->setCellValue(sprintf("O%s", $colIdx), $row->order_id_sabangnet);//20250515사방넷주소록

            //->setCellValue(sprintf("O%s", $colIdx), $idx[$i] );//접수번호



    

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