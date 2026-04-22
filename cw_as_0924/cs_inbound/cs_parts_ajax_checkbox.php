<?
include('../header.php'); 

$mod	= M_INBOUND;	
$menu	= S_PARTS_LIST;

$dbname	= $_POST['dbname'];
$name	= $_POST['name'];
$idx	= $_POST['idx'];
$val	= $_POST['val'];



if($name=="status")
{
	for($i=0;$i<count($idx);$i++) 
	{
		if($dbname=="cs_shipping_parts")
		{
			//완료처리
			$db->update($dbname, "status=1 where idx='$idx[$i]' and status=0 ");

			//LOG
			$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='cs_parts_edit', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx[$i] $ADMIN_NAME'");
		} 
	}
}
else if($name=="delivery_excel_download")
{
	require_once '../PHPExcel/Classes/PHPExcel.php';
	require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';
	
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
	->setCellValue('I1', '접수번호');   

	$objPHPExcel->getActiveSheet()
	->getStyle('A1:I1')
	->getFill()
	->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
	->getStartColor()
	->setARGB('FFd0d0d0');

	
	$colIdx = 2;
	for($i=0;$i<count($idx);$i++) 
	{	
		$row = $db->object($dbname, "where idx='$idx[$i]'");

		$customer_phone = $row->customer_phone; 
		$reg_num = sprintf("P%s-%d", date("ymd", strtotime($row->reg_datetime)), $row->idx); 

//		$parts_name = str_replace("(V)","",$row->parts_name.$row->parts_name_ex);  //20211213
//		$parts_name = rtrim($parts_name,";");
		$parts_name = str_replace("(V)","",$row->parts_name.$row->parts_name_ex);  //20211213
		$parts_name = rtrim($parts_name,";");
		$parts_name = str_replace(";", ",    ", $parts_name);

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue(sprintf("A%s", $colIdx), $row->product_name." - ".$parts_name)
		->setCellValue(sprintf("B%s", $colIdx), 1)
		->setCellValue(sprintf("C%s", $colIdx), $row->customer_name)
		->setCellValueExplicit(sprintf("D%s", $colIdx), $customer_phone, PHPExcel_Cell_DataType::TYPE_STRING)
		->setCellValue(sprintf("E%s", $colIdx), '')
		->setCellValue(sprintf("F%s", $colIdx), $row->customer_addr." ".$row->customer_addr_detail)
		->setCellValue(sprintf("G%s", $colIdx), $row->delivery_memo)
		->setCellValue(sprintf("H%s", $colIdx), '')
		->setCellValue(sprintf("I%s", $colIdx), $reg_num); //yymmdd-nnn

		$colIdx++;
	}

	$fileName = 'CS부품출고_'.date("Ymd");

    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$fileName.'.xlsx"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

	$objWriter->save("../temp/".$fileName.'.xlsx');

	// disconnect
	$objPHPExcel->disconnectWorksheets();
	$objPHPExcel->garbageCollect();
	
	unset( $objWriter, $objPHPExcel );
	
	$temp_fileName = $fileName.'.xlsx';
	$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='excel_dl_parts', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$temp_fileName' ");

}

include('../footer.php');
?>