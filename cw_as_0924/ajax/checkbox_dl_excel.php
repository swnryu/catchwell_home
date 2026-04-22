<?
include('../header.php'); 
header("Content-type: text/html; charset=utf-8");

$dbname	= $_POST['dbname'];
$name	= $_POST['name'];
$idx	= $_POST['idx'];
$val	= $_POST['val'];

include ('../PHPExcel/Classes/PHPExcel.php');
include ('../PHPExcel/Classes/PHPExcel/IOFactory.php');



/***********************************************************************************************************/

if($name=="export2excel") {
	
	// create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	
	$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
	$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
	$objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
	$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
	
	// Add data
    if ($val==ST_FIX_DONE) { //발송용 송장 
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '품목명')
        ->setCellValue('B1', '박스수량')
        ->setCellValue('C1', '이름')
        ->setCellValue('D1', '전화번호')
        ->setCellValue('E1', '전화번호2')
        ->setCellValue('F1', '주소')
        ->setCellValue('G1', '배송메시지')
        ->setCellValue('H1', '운송장번호')
        ->setCellValue('I1', '고객주문번호');   

        $objPHPExcel->getActiveSheet()
        ->getStyle('A1:I1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB('FFd0d0d0');
    
	} 
	else { //회수용 송장
		if (USE_DELIVERY_EPOST) {
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);

            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '발송인명')
            ->setCellValue('B1', '우편번호')
            ->setCellValue('C1', '발송인우편번호주소')
            ->setCellValue('D1', '발송인상세주소')
            ->setCellValue('E1', '발송인전화')
            ->setCellValue('F1', '발송인이동통신')
            ->setCellValue('G1', '상품명')
            ->setCellValue('H1', '상품코드')
            ->setCellValue('I1', '상품모델')
            ->setCellValue('J1', '사이즈')
            ->setCellValue('K1', '색상')
            ->setCellValue('L1', '수량')
            ->setCellValue('M1', '주문번호')
            ->setCellValue('N1', '요금납부방법')
            ->setCellValue('O1', '배송메시지')
            ->setCellValue('P1', '비고')
            ->setCellValue('Q1', '반품수거메시지');
    
            $objPHPExcel->getActiveSheet()
            ->getStyle('A1:Q1')
            ->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
			->setARGB('FFd0d0d0');  
		}
		else {
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', '품목명')
			->setCellValue('B1', '박스수량')
			->setCellValue('C1', '이름')
			->setCellValue('D1', '전화번호')
			->setCellValue('E1', '주소')
			->setCellValue('F1', '배송메세지')
			->setCellValue('G1', '운송장번호')
			->setCellValue('H1', '고객주문번호');
	
			$objPHPExcel->getActiveSheet()
				->getStyle('A1:H1')
				->getFill()
				->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
				->getStartColor()
				->setARGB('FFd0d0d0');
		}		
	}
	
	
	$colIdx = 2;
	for($i=0;$i<count($idx);$i++) {	

		if($dbname=="as_parcel_service"){
			$row = $db->object($dbname, "where idx='$idx[$i]'");
			
		}


		//export to excel 
		//	$tmpN = str_pad( $intN, 5, "0", STR_PAD_LEFT );
			
		if ( ($row->customer_phone != "") && 
				(strlen($row->customer_phone)==10 || strlen($row->customer_phone)==11) 
			) {
				$customer_ph1 = substr($row->customer_phone,0,3);
				$customer_ph3 = substr($row->customer_phone,-4);
				$customer_ph2 = substr($row->customer_phone,3,(strlen($row->customer_phone)-7));
				$customer_phone = $customer_ph1."-".$customer_ph2."-".$customer_ph3;
		}


		if ($customer_phone=="") {
			$customer_phone = $row->customer_phone; 
		}

        if ($row->process_state==ST_FIX_DONE) { //발송접수 

            //20220315
            $memo = $row->admin_memo;
            $memo = str_replace("(V)","",$memo);
            $memo = str_replace("(R)","",$memo);
            $memo = str_replace("(H)","",$memo);
            $memo = str_replace("(S)","",$memo);
            $memo = str_replace("(M)","",$memo);
            $memo = str_replace("[ETC]","",$memo);
            
            $delivery_msg = $row->parcel_memo_return;
            $delivery_msg .= '(자사몰AS접수)';
            $delivery_msg .= " ";
            $delivery_msg .= $memo;
            $delivery_msg = $tools->getSubstring($delivery_msg, 100);

            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue(sprintf("A%s", $colIdx), $row->product_name)
            ->setCellValue(sprintf("B%s", $colIdx), 1)
            ->setCellValue(sprintf("C%s", $colIdx), $row->customer_name)
            ->setCellValueExplicit(sprintf("D%s", $colIdx), $customer_phone, PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValue(sprintf("E%s", $colIdx), '')
            ->setCellValue(sprintf("F%s", $colIdx), $row->customer_addr_return." ".$row->customer_addr_detail_return)
            ->setCellValue(sprintf("G%s", $colIdx), $delivery_msg /*$row->parcel_memo_return.'(자사몰AS접수)'*/)
            ->setCellValue(sprintf("H%s", $colIdx), '')
            ->setCellValue(sprintf("I%s", $colIdx), $row->reg_num);
        }
		else if ($row->process_state==ST_REGISTERING||ST_DC) { //회수접수
            if (USE_DELIVERY_EPOST) {
                $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue(sprintf("A%s", $colIdx), $row->customer_name)
                ->setCellValue(sprintf("B%s", $colIdx), sprintf("%05d", $row->customer_zipcode))
//              ->setCellValue(sprintf("B%s", $colIdx), $row->customer_zipcode)
                ->setCellValue(sprintf("C%s", $colIdx), $row->customer_addr)
                ->setCellValue(sprintf("D%s", $colIdx), $row->customer_addr_detail)
                ->setCellValueExplicit(sprintf("E%s", $colIdx), $customer_phone, PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit(sprintf("F%s", $colIdx), $customer_phone, PHPExcel_Cell_DataType::TYPE_STRING)
//              ->setCellValue(sprintf("F%s", $colIdx), '')
                ->setCellValue(sprintf("G%s", $colIdx), '전자제품'.'(자사몰AS접수)')
                ->setCellValue(sprintf("H%s", $colIdx), '')
                ->setCellValue(sprintf("I%s", $colIdx), '')
                ->setCellValue(sprintf("J%s", $colIdx), '')
                ->setCellValue(sprintf("K%s", $colIdx), '')
                ->setCellValue(sprintf("L%s", $colIdx), '1')
                ->setCellValue(sprintf("M%s", $colIdx), $row->reg_num)
                ->setCellValue(sprintf("N%s", $colIdx), '')
                ->setCellValue(sprintf("O%s", $colIdx), $row->parcel_memo)
                ->setCellValue(sprintf("P%s", $colIdx), '')
                ->setCellValue(sprintf("Q%s", $colIdx), '');
            }
			else {
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue(sprintf("A%s", $colIdx), $row->product_name)
				->setCellValue(sprintf("B%s", $colIdx), 1)
				->setCellValue(sprintf("C%s", $colIdx), $row->customer_name)
				->setCellValueExplicit(sprintf("D%s", $colIdx), $customer_phone, PHPExcel_Cell_DataType::TYPE_STRING)
				->setCellValue(sprintf("E%s", $colIdx), $row->customer_addr." ".$row->customer_addr_detail)
				->setCellValue(sprintf("F%s", $colIdx), $row->parcel_memo.'(자사몰AS접수)')
				->setCellValue(sprintf("G%s", $colIdx), '')
				->setCellValue(sprintf("H%s", $colIdx), $row->reg_num);
			}
        }
					
		$colIdx++;
	}

//	$fileName = 'AS_'.date("Ymd").'.xlsx';
	$fileName = 'AS_'.date("Ymd");

    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$fileName.'.xlsx"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');
//	header("Content-Encoding: utf-8");

    // If you're serving to IE over SSL, then the following may be needed
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

   /* 
	// redirect output to a client's web browser
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment; filename=$fileName');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');
	header("Content-Encoding: utf-8");

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
*/

	//$objWriter->save('php://output');
	$objWriter->save("../temp/".$fileName.'.xlsx');
	
	// disconnect
	$objPHPExcel->disconnectWorksheets();
	$objPHPExcel->garbageCollect();
	
	unset( $objWriter, $objPHPExcel );

    $temp_fileName = $fileName.'.xlsx';
    $db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='excel_dl_as', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$temp_fileName' ");
    
//	$result['success']	= true;
//	$result['data'] = $fileName;
}



include('../footer.php');
?>