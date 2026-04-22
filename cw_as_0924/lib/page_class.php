<?
class Page {
	function poll( $totalPage, $totalList, $listScale, $pageScale, $startPage, $prexImgName, $nextImgName) {
		if( $totalList > $listScale ) {
			if( $startPage+1 > $listScale*$pageScale ) {
				$prePage = $startPage - $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$prePage);
				echo "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?poll_data=$mv_data' onfocus=this.blur()>$prexImgName</a>&nbsp;&nbsp;";
			}
			for( $j=0; $j<$pageScale; $j++ ) {
				$nextPage = ($totalPage * $pageScale + $j) * $listScale;
				$pageNum = $totalPage * $pageScale + $j+1;
				if( $nextPage < $totalList ) {
					if( $nextPage!= $startPage ) {
						$mv_data=$this->encode("startPage=".$nextPage);
						echo "<a href='$_SERVER[PHP_SELF]?poll_data=$mv_data' onfocus=this.blur()> $pageNum </a>";	
					} else {
						echo "<font color='#000000'>&nbsp;<b>$pageNum</b>&nbsp;</font>";
					}
				}
			}
			if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
				$nNextPage = ($totalPage+1) * $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$nNextPage);
				echo "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?poll_data=$mv_data' onfocus=this.blur()>$nextImgName</a>&nbsp;&nbsp;";
			}
		}
		if( $totalList <= $listScale) {
			echo "<b><font color='#000000'>1</font></b>";
		}
	}


	function bbs( $code, $cate, $table, $totalPage, $totalList, $listScale, $pageScale, $startPage, $prexImgName, $nextImgName, $search_item=0, $search_order="") {
		if( $totalList > $listScale ) {
			if( $startPage+1 > $listScale*$pageScale ) {
				$prePage = $startPage - $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$prePage."&code=".$code."&cate=".$cate."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
				echo "<a href='$_SERVER[PHP_SELF]?bbs_data=$mv_data' onfocus=this.blur() class='paging-arrow'><i class='material-icons'>&#xE314;</i></a>&nbsp;";
			}
			for( $j=0; $j<$pageScale; $j++ ) {
				$nextPage = ($totalPage * $pageScale + $j) * $listScale;
				$pageNum = $totalPage * $pageScale + $j+1;
				if( $nextPage < $totalList ) {
					if( $nextPage!= $startPage ) {
						$mv_data=$this->encode("startPage=".$nextPage."&code=".$code."&cate=".$cate."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
						echo " <a href='$_SERVER[PHP_SELF]?bbs_data=$mv_data' onfocus=this.blur()>$pageNum</a> ";
					} else {
						echo " <a href='javascript:;' class='cur'>$pageNum</a> ";
					}
				}
			}
			if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
				$nNextPage = ($totalPage+1) * $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$nNextPage."&code=".$code."&cate=".$cate."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
				echo "&nbsp;<a href='$_SERVER[PHP_SELF]?bbs_data=$mv_data' onfocus=this.blur() class='paging-arrow'><i class='material-icons'>&#xE315;</i></a>";
			}
		}
		if( $totalList <= $listScale) {
			echo "<a href='javascript:;' class='cur'>1</a>";
		}
	}

		function bbs2( $code, $table, $totalPage, $totalList, $listScale, $pageScale, $startPage, $prexImgName, $nextImgName, $search_item=0, $search_order="") {
		if( $totalList > $listScale ) {
			if( $startPage+1 > $listScale*$pageScale ) {
				$prePage = $startPage - $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$prePage."&code=".$code."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
				echo "&nbsp;<a href='$_SERVER[PHP_SELF]?bbs_data=$mv_data' onfocus=this.blur() class='bbs-num-bt'>$prexImgName</a>&nbsp;";
			}
			for( $j=0; $j<$pageScale; $j++ ) {
				$nextPage = ($totalPage * $pageScale + $j) * $listScale;
				$pageNum = $totalPage * $pageScale + $j+1;
				if( $nextPage < $totalList ) {
					if( $nextPage!= $startPage ) {
						$mv_data=$this->encode("startPage=".$nextPage."&code=".$code."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
						echo " <a href='$_SERVER[PHP_SELF]?bbs_data=$mv_data' onfocus=this.blur() class='bbs-num-bt'>$pageNum</a> ";
					} else {
						echo " <a href='#' class='bbs-num-bt'>$pageNum</a> ";
					}
				}
			}
			if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
				$nNextPage = ($totalPage+1) * $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$nNextPage."&code=".$code."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
				echo "&nbsp;<a href='$_SERVER[PHP_SELF]?bbs_data=$mv_data' onfocus=this.blur() class='bbs-num-bt' >$nextImgName</a>&nbsp;";
			}
		}
		if( $totalList <= $listScale) {
			echo "<a href='#' class='bbs-num-bt'></a>";
		}
	}


	function goods( $part_idx, $table, $totalPage, $totalList, $listScale, $pageScale, $startPage, $prexImgName, $nextImgName, $search_item=0, $search_order="") {
		if( $totalList > $listScale ) {
			if( $startPage+1 > $listScale*$pageScale ) {
				$prePage = $startPage - $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$prePage."&part_idx=".$part_idx."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
				echo "<li><a href='$_SERVER[PHP_SELF]?goods_data=$mv_data' onfocus=this.blur()><span aria-hidden='true'>&laquo;</span></a></li>";
			}

			for( $j=0; $j<$pageScale; $j++ ) {
				$nextPage = ($totalPage * $pageScale + $j) * $listScale;
				$pageNum = $totalPage * $pageScale + $j+1;
				if( $nextPage < $totalList ) {
					if( $nextPage!= $startPage ) {
						$mv_data=$this->encode("startPage=".$nextPage."&part_idx=".$part_idx."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
						echo "<li><a href='$_SERVER[PHP_SELF]?goods_data=$mv_data' onfocus=this.blur()>$pageNum</a></li>";
					} else {
						echo "<li class='active'><a href='#none'>$pageNum</a></li>";
					}
				}
			}
			if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
				$nNextPage = ($totalPage+1) * $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$nNextPage."&part_idx=".$part_idx."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
				echo "<li><a href='$_SERVER[PHP_SELF]?goods_data=$mv_data' onfocus=this.blur()><span aria-hidden='true'>&raquo;</span></a></li>";
			}
		}
		if( $totalList <= $listScale) {
			echo "<li class='active'><a href='#none' >1</a></li>";
		}
	}


	function goods_zzim( $code, $part_idx, $table, $totalPage, $totalList, $listScale, $pageScale, $startPage, $prexImgName, $nextImgName, $search_item=0, $search_order="") {
		if( $totalList > $listScale ) {
			if( $startPage+1 > $listScale*$pageScale ) {
				$prePage = $startPage - $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$prePage."&code=".$code."&part_idx=".$part_idx."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
				echo "<li><a href='$_SERVER[PHP_SELF]?goods_data=$mv_data' onfocus=this.blur()><span aria-hidden='true'>&laquo;</span></a></li>";
			}

			for( $j=0; $j<$pageScale; $j++ ) {
				$nextPage = ($totalPage * $pageScale + $j) * $listScale;
				$pageNum = $totalPage * $pageScale + $j+1;
				if( $nextPage < $totalList ) {
					if( $nextPage!= $startPage ) {
						$mv_data=$this->encode("startPage=".$nextPage."&code=".$code."&part_idx=".$part_idx."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
						echo "<li><a href='$_SERVER[PHP_SELF]?goods_data=$mv_data' onfocus=this.blur()>$pageNum</a></li>";
					} else {
						echo "<li class='active'><a href='#none'>$pageNum</a></li>";
					}
				}
			}
			if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
				$nNextPage = ($totalPage+1) * $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$nNextPage."&code=".$code."&part_idx=".$part_idx."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
				echo "<li><a href='$_SERVER[PHP_SELF]?goods_data=$mv_data' onfocus=this.blur()><span aria-hidden='true'>&raquo;</span></a></li>";
			}
		}
		if( $totalList <= $listScale) {
			echo "<li class='active'><a href='#none' >1</a></li>";
		}
	}


	function gongu( $table, $totalPage, $totalList, $listScale, $pageScale, $startPage, $prexImgName, $nextImgName, $search_item=0, $search_order="") {
		if( $totalList > $listScale ) {
			if( $startPage+1 > $listScale*$pageScale ) {
				$prePage = $startPage - $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$prePage."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
				echo "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?gongu_data=$mv_data' onfocus=this.blur()>$prexImgName</a>&nbsp;&nbsp;";
			}

			for( $j=0; $j<$pageScale; $j++ ) {
				$nextPage = ($totalPage * $pageScale + $j) * $listScale;
				$pageNum = $totalPage * $pageScale + $j+1;
				if( $nextPage < $totalList ) {
					if( $nextPage!= $startPage ) {
						$mv_data=$this->encode("startPage=".$nextPage."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
						echo "<a href='$_SERVER[PHP_SELF]?gongu_data=$mv_data' onfocus=this.blur()>[$pageNum]</a>";
					} else {
						echo "<b><font color='#000000'>&nbsp;$pageNum&nbsp;<b></b></font></b>";
					}
				}
			}
			if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
				$nNextPage = ($totalPage+1) * $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$nNextPage."&table=".$table."&search_item=".$search_item."&search_order=".$search_order);
				echo "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?gongu_data=$mv_data' onfocus=this.blur()>$nextImgName</a>&nbsp;&nbsp;";
			}
		}
		if( $totalList <= $listScale) {
			echo "<b><font color='#000000'>1</font></b>";
		}
	}

	function member( $totalPage, $totalList, $listScale, $pageScale, $startPage, $prexImgName, $nextImgName, $search_item=0, $search_order="", $order_chk=0, $order_list=0, $search_01=0, $search_02=0, $search_03=0) {
		if( $totalList > $listScale ) {
			if( $startPage+1 > $listScale*$pageScale ) {
				$prePage = $startPage - $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$prePage."&search_item=".$search_item."&search_order=".$search_order."&order_chk=".$order_chk."&order_list=".$order_list."&search_01=".$search_01."&search_02=".$search_02."&search_03=".$search_03);
				echo "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?mem_data=$mv_data' onfocus=this.blur()>$prexImgName</a>&nbsp;&nbsp;";
			}

			for( $j=0; $j<$pageScale; $j++ ) {
				$nextPage = ($totalPage * $pageScale + $j) * $listScale;
				$pageNum = $totalPage * $pageScale + $j+1;
				if( $nextPage < $totalList ) {
					if( $nextPage!= $startPage ) {
						$mv_data=$this->encode("startPage=".$nextPage."&search_item=".$search_item."&search_order=".$search_order."&order_chk=".$order_chk."&order_list=".$order_list."&search_01=".$search_01."&search_02=".$search_02."&search_03=".$search_03);
						echo "<a href='$_SERVER[PHP_SELF]?mem_data=$mv_data' onfocus=this.blur()>[$pageNum]</a>";
					} else {
						echo "<b><font color='#000000'>&nbsp;$pageNum&nbsp;<b></b></font></b>";
					}
				}
			}
			if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
				$nNextPage = ($totalPage+1) * $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$nNextPage."&search_item=".$search_item."&search_order=".$search_order."&order_chk=".$order_chk."&order_list=".$order_list."&search_01=".$search_01."&search_02=".$search_02."&search_03=".$search_03);
				echo "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?mem_data=$mv_data' onfocus=this.blur()>$nextImgName</a>&nbsp;&nbsp;";
			}
		}
		if( $totalList <= $listScale) {
			echo "<b><font color='#000000'>1</font></b>";
		}
	}

	function review( $totalPage, $totalList, $listScale, $pageScale, $startPage, $prexImgName, $nextImgName, $search_item=0, $search_order="") {
		if( $totalList > $listScale ) {
			if( $startPage+1 > $listScale*$pageScale ) {
				$prePage = $startPage - $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$prePage."&search_item=".$search_item."&search_order=".$search_order);
				echo "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?review_data=$mv_data' onfocus=this.blur()>$prexImgName</a>&nbsp;&nbsp;";
			}

			for( $j=0; $j<$pageScale; $j++ ) {
				$nextPage = ($totalPage * $pageScale + $j) * $listScale;
				$pageNum = $totalPage * $pageScale + $j+1;
				if( $nextPage < $totalList ) {
					if( $nextPage!= $startPage ) {
						$mv_data=$this->encode("startPage=".$nextPage."&search_item=".$search_item."&search_order=".$search_order);
						echo "<a href='$_SERVER[PHP_SELF]?review_data=$mv_data' onfocus=this.blur()>[$pageNum]</a>";
					} else {
						echo "<b><font color='#000000'>&nbsp;$pageNum&nbsp;<b></b></font></b>";
					}
				}
			}
			if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
				$nNextPage = ($totalPage+1) * $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$nNextPage."&search_item=".$search_item."&search_order=".$search_order);
				echo "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?review_data=$mv_data' onfocus=this.blur()>$nextImgName</a>&nbsp;&nbsp;";
			}
		}
		if( $totalList <= $listScale) {
			echo "<b><font color='#000000'>1</font></b>";
		}
	}

	function my_point( $totalPage, $totalList, $listScale, $pageScale, $startPage, $prexImgName, $nextImgName) {
		if( $totalList > $listScale ) {
			if( $startPage+1 > $listScale*$pageScale ) {
				$prePage = $startPage - $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$prePage);
				echo "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?point_data=$mv_data' onfocus=this.blur()>$prexImgName</a>&nbsp;&nbsp;";
			}

			for( $j=0; $j<$pageScale; $j++ ) {
				$nextPage = ($totalPage * $pageScale + $j) * $listScale;
				$pageNum = $totalPage * $pageScale + $j+1;
				if( $nextPage < $totalList ) {
					if( $nextPage!= $startPage ) {
						$mv_data=$this->encode("startPage=".$nextPage);
						echo "<a href='$_SERVER[PHP_SELF]?point_data=$mv_data' onfocus=this.blur()>[$pageNum]</a>";
					} else {
						echo "<b><font color='#000000'>&nbsp;$pageNum&nbsp;<b></b></font></b>";
					}
				}
			}
			if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
				$nNextPage = ($totalPage+1) * $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$nNextPage);
				echo "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?point_data=$mv_data' onfocus=this.blur()>$nextImgName</a>&nbsp;&nbsp;";
			}
		}
		if( $totalList <= $listScale) {
			echo "<b><font color='#000000'>1</font></b>";
		}
	}


	function trade( $trade_stat, $table, $totalPage, $totalList, $listScale, $pageScale, $startPage, $prexImgName, $nextImgName, $search_item_chk=0, $search_mem_item=0, $search_trade_item=0, $search_order="", $search_day="", $search_day_str="") {
		if( $totalList > $listScale ) {
			if( $startPage+1 > $listScale*$pageScale ) {
				$prePage = $startPage - $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$prePage."&trade_stat=".$trade_stat."&table=".$table."&search_item_chk=".$search_item_chk."&search_mem_item=".$search_mem_item."&search_trade_item=".$search_trade_item."&search_order=".$search_order."&search_day=".$search_day."&search_day_str=".$search_day_str);
				echo "<li><a href='$_SERVER[PHP_SELF]?trade_data=$mv_data' onfocus=this.blur()><span aria-hidden='true'>&laquo;</span></a></li>";
			}

			for( $j=0; $j<$pageScale; $j++ ) {
				$nextPage = ($totalPage * $pageScale + $j) * $listScale;
				$pageNum = $totalPage * $pageScale + $j+1;
				if( $nextPage < $totalList ) {
					if( $nextPage!= $startPage ) {
						$mv_data=$this->encode("startPage=".$nextPage."&trade_stat=".$trade_stat."&table=".$table."&search_item_chk=".$search_item_chk."&search_mem_item=".$search_mem_item."&search_trade_item=".$search_trade_item."&search_order=".$search_order."&search_day=".$search_day."&search_day_str=".$search_day_str);
						echo "<li><a href='$_SERVER[PHP_SELF]?trade_data=$mv_data' onfocus=this.blur()>$pageNum</a></li>";
					} else {
						echo "<li class='active'><a href='#none'>$pageNum</a></li>";
					}
				}
			}
			if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
				$nNextPage = ($totalPage+1) * $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$nNextPage."&trade_stat=".$trade_stat."&table=".$table."&search_item_chk=".$search_item_chk."&search_mem_item=".$search_mem_item."&search_trade_item=".$search_trade_item."&search_order=".$search_order."&search_day=".$search_day."&search_day_str=".$search_day_str);
				echo "<li><a href='$_SERVER[PHP_SELF]?trade_data=$mv_data' onfocus=this.blur()><span aria-hidden='true'>&raquo;</span></a></li>";
			}
		}
		if( $totalList <= $listScale) {
			echo "<li class='active'><a href='#none' >1</a></li>";
		}
	}

	function my_trade( $table, $totalPage, $totalList, $listScale, $pageScale, $startPage, $prexImgName, $nextImgName) {
		if( $totalList > $listScale ) {
			if( $startPage+1 > $listScale*$pageScale ) {
				$prePage = $startPage - $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$prePage."&trade_stat=".$trade_stat."&table=".$table);
				echo "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?trade_data=$mv_data' onfocus=this.blur()>$prexImgName</a>&nbsp;&nbsp;";
			}

			for( $j=0; $j<$pageScale; $j++ ) {
				$nextPage = ($totalPage * $pageScale + $j) * $listScale;
				$pageNum = $totalPage * $pageScale + $j+1;
				if( $nextPage < $totalList ) {
					if( $nextPage!= $startPage ) {
						$mv_data=$this->encode("startPage=".$nextPage."&trade_stat=".$trade_stat."&table=".$table);
						echo "<a href='$_SERVER[PHP_SELF]?trade_data=$mv_data' onfocus=this.blur()> $pageNum </a>";
					} else {
						echo "<font color='#000000'>&nbsp;<b>$pageNum</b>&nbsp;</font>";
					}
				}
			}
			if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
				$nNextPage = ($totalPage+1) * $listScale * $pageScale;
				$mv_data=$this->encode("startPage=".$nNextPage."&trade_stat=".$trade_stat."&table=".$table);
				echo "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?trade_data=$mv_data' onfocus=this.blur()>$nextImgName</a>&nbsp;&nbsp;";
			}
		}
		if( $totalList <= $listScale) {
			echo "<b><font color='#000000'>1</font></b>";
		}
	}

	
	
	
	
	
	
	
	// base64_encode로 엔코딩
	function encode($data) {
		return base64_encode($data)."||";
	}
		
	// $dateStr	: 날자 datetime 형
	// $day			: 시간
	// $imgStr		: 이미지
	function bbsNewImg( $dateStr, $h, $imgStr ) {
		$h		=	60 * 60 * $h;
		$today		=	time();
		$year		=	substr($dateStr, 0, 4);
		$mon		=	substr($dateStr, 5, 2);
		$day		=	substr($dateStr, 8, 2);
		$hour		=	substr($dateStr, 11, 2);
		$minute	=	substr($dateStr, 14, 2);
		$second	=	substr($dateStr, 17, 2);
		$regiDay	=	mktime($hour, $minute, $second, $mon, $day, $year);
		if($regiDay > ($today - $h)) {
			$img		= $imgStr; 
		} else {
			$img		= ""; 
		}
		return $img;
	}

	// $base		: 기본 숫자
	// $value		: 초과 숫자
	// $imgStr		: 이미지
	function bbsCoolImg( $base, $value, $imgStr ) {
		if( $base < $value) {
			$img		= $imgStr;
		} else {
			$img		= "";
		}
		return $img;
	}

	function newImg( $dateStr, $div=1, $img_num=1 ) {
		$img		= "";
		$div			=	60 * 60 * $div;
		$today		=	time();
		$year		=	substr($dateStr, 0, 4);
		$mon		=	substr($dateStr, 5, 2);
		$day		=	substr($dateStr, 8, 2);
		$hour		=	substr($dateStr, 11, 2);
		$minute	=	substr($dateStr, 14, 2);
		$second	=	substr($dateStr, 17, 2);
		$regiDay	=	mktime($hour, $minute, $second, $mon, $day, $year);

		if($regiDay > ($today - $div)) {
			if( $img_num==1 ){ 
				$img		= "<img src='../admin/images/new1.gif' align='absmiddle' border='0'>";
			} else if( $img_num==2 ){
				$img		= "<img src='../admin/images/new2.gif' align='absmiddle' border='0'>";
			} else if( $img_num==3 ){
				$img		= "<img src='../admin/images/new3.gif' align='absmiddle' border='0'>";
			}
		} else {
			$img		= ""; 
		}
		return $img;
	}
	function hitImg( $base, $value, $img_num=1 ) {
		$img		= "";
		if( $base < $value) {
			if( $img_num==1 ){ 
			$img		= "<img src='../admin/images/hit1.gif' align='absmiddle' border='0'>";
			} else if( $img_num==2 ){
			$img		= "<img src='../admin/images/hit2.gif' align='absmiddle' border='0'>";
			} else if( $img_num==3 ){
			$img		= "<img src='../admin/images/hit3.gif' align='absmiddle' border='0'>";
			}
		} else {
			$img		= "";
		}
		return $img;
	}
}
$page = new Page();
?>