<?
//20230331  delete add 

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', FALSE);
ini_set('display_startup_errors', FALSE);

include("../def_inc.php");
include("../common_lib.php");

//$mod	= M_AS;	
//$menu	= isset($_GET['from'])?$_GET['from']:S_AS_NEW;


include("../header.php");

$table = "cs_online_event";
$query = "";

$idx = isset($_POST['idx']) ? $_POST['idx'] : "";
$fn = isset($_POST['fn']) ? $_POST['fn'] : "";


$return_url	= "online_event.php";//20230331
//$return_url = $_SERVER["REQUEST_URI"];//"management_id.php";
/*
echo "0 return_url: ". $return_url."<br/>\n";//20230331 추가 및 주석제거 
echo "0 query: ". $query."<br/>\n";
echo "0 idx: " .$idx."<br/>\n";
echo "0 fn: " .$fn."<br/>\n";
*/


$lib = new commonLib();


if ($idx!="")
{
    if ($fn == 'delete')
    {
        echo "1 idx: " .$idx."<br/>\n";
        echo "1 fn: " .$fn."<br/>\n";
        //$query = "delete from $table where admin_userid='$userid' ";
        //DELETE FROM table_name WHERE some_column = some_value 
        $query = "delete from $table where idx='$idx' ";
        //$db->delete($db_name, "where idx=$_POST[idx]");

        $result=mysqli_query($db->db_conn, $query);
        if ($result==false)
        {
            $tools->errMsg('데이터베이스 업데이트 오류가 발생하였습니다. 관리자에게 문의하세요.(2)'); 
        }

    }
}


//$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "memberlist.php";

$tools->alertJavaGo("삭제 되었습니다.", $return_url);



/*
if($_POST['isdel']=="y") { //remove

    include("online_as_backup.php"); //20220214

    if( $db->delete($db_name, "where idx=$_POST[idx]"))
    {
        if ($menu==S_AS_REGISTERING)	{$return_url.="?state=".ST_REGISTERING;}
        else if ($menu==S_AS_REGDONE)	{$return_url.="?state=".ST_REG_DONE;}
        else if ($menu==S_AS_FIXDONE)	{$return_url.="?state=".ST_FIX_DONE;}
        else if ($menu==S_AS_COMPLETED)	{$return_url.="?state=".ST_AS_COMPLETED;}
        else if ($menu==S_AS_REPORT)	{$return_url="online_as_report.php";}
        else							{$return_url.="?state=".ST_REGISTERING;}
        
        //관리자 로그
        $db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='del_reg', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$reg_num $customer_name $customer_phone'");

        $tools->alertJavaGo("삭제하였습니다.", $return_url);
    }
} 
*/


include('../footer.php');
?>