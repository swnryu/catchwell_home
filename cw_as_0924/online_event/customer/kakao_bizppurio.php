<?

//20221117 bizppurio add
function httpsPost($Url, $json_data, $headers)
{
    $DEBUG = 0;
   // Initialisation
   $ch=curl_init();
   // Set parameters
   //curl_setopt($ch, CURLOPT_FORBID_REUSE, 1); 
   //curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
   curl_setopt($ch,CURLOPT_NOSIGNAL, 1);
   curl_setopt($ch, CURLOPT_VERBOSE, true);
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);    
   curl_setopt($ch, CURLOPT_URL, $Url);
   // Return a variable instead of posting it directly
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   // Active the POST method
   //curl_setopt($ch, CURLOPT_POST, 1) ;
   // Request
   curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
   // execute the connexion
   $result = curl_exec($ch);
   // Close it
   if($DEBUG)
   {
        echo 'Request :';
        echo '<pre>';
        print_r($headers);
        echo '<br>';
        echo '<br>';
        print_r(curl_getinfo($ch));//모든정보출력
        echo '<br>';
        echo '<br>';
        print_r(json_decode($result));
        curl_close($ch);
        echo '</pre>';
    }
   return $result;
}


function getToken()
{
    $DEBUG = 0;
    //토큰 얻어오기 -----------------------------------------
    $IDPASS = base64_encode("catchwellota:catchwellota4!");
    $headers[0]="Authorization:Basic $IDPASS"; 
    $headers[1]="Content-Type:application/json"; 
    $headers[2]="charset=utf-8";
    
    $url = 'https://dev-api.bizppurio.com/v1/token';
    $Response = httpsPost($url, NULL, $headers);
    $Ret = ( json_decode($Response));
    $token = $Ret->accesstoken;
    if($DEBUG)
    {
    echo '<pre>';
    print_r( "--token--" );
    echo '<br>';
    echo '<br>';
    print_r( $token);
    echo '</pre>';
    }
    return $token;
}

?>