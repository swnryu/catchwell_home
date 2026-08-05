<?
	class commonLib {
		
		function getProductCategory($name, $arr_vc, $arr_rc, $arr_etc) {
			$arr_category = array("vacuum_cleaner","robot_cleaner","mop_cleaner","straightener","humidifier","etc");

			for($i=0;$i<count($arr_vc);$i++) //무선청소
			{
				if ($name == $arr_vc[$i]) {
					return $arr_category[0];
				}
			}
			for($i=0;$i<count($arr_rc);$i++) //로봇
			{
				if ($name == $arr_rc[$i]) {
					return $arr_category[1];
				}
			}
			
			//arr_etc = array("CM7","SECRET01 블랙","SECRET01 화이트/핑크/민트", "CH200","기타");
			if ($name == $arr_etc[3]) { //가습기
				return $arr_category[4];
			}
			else if ($name == $arr_etc[1] || $name == $arr_etc[2]) { //고데기
				return $arr_category[3];
			}
			else if ($name == $arr_etc[0]) { //물걸레
				return $arr_category[2];
			}
			else { //기타
				return $arr_category[5];
			}
		
			return $arr_category[5];
		}

/*
		function isCategoryVC($name, $arr_name) {//vacuum_cleaner
			for($i=0;$i<count($arr_name);$i++) {
				if ($name == $arr_name[$i]) {
					return true;
				}
			}
			return; false;
		}

		function isCategoryRV($name, $arr_name) {//robot_cleaner
			for($i=0;$i<count($arr_name);$i++) {
				if ($name == $arr_name[$i]) {
					return true;
				}
			}
			return; false;
		}

		function isCategoryHM($name, $arr_etc) {//humidifier
			for($i=0;$i<count($arr_etc);$i++) 
			{
				if ($name == $arr_etc[$i]) {
					return true;
				}
			}
			return; false;
		}

		function isCategoryHS($name, $arr_etc) {//straightener
			if ($name == $arr_etc[1])
			{
				if ($name == $arr_etc[$i]) {
					return true;
				}
			}
		}

		function isCategoryWM($name, $arr_etc) {//mop_cleaner
			if ($name == $arr_etc[2])
			{
				if ($name == $arr_etc[$i]) {
					return true;
				}
			}
			return; false;
		}
*/
	}

function sendFlowMessage($chatId, $contents) {
    if (empty($chatId) || empty($contents)) return;

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL            => 'https://api.flow.team/v1/chats/' . (int)$chatId . '/messages',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => 'registerId=' . urlencode(FLOW_SENDER_ID) . '&contents=' . urlencode($contents),
        CURLOPT_HTTPHEADER     => array(
            'Content-Type: application/x-www-form-urlencoded',
            'x-flow-api-key: ' . FLOW_API_KEY,
        ),
    ));
    curl_exec($curl);
    curl_close($curl);
}

?>