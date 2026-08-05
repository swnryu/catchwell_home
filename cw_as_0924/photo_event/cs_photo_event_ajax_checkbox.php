<?
include('../header.php');

$mod  = M_CANCELLATION;
$menu = S_PHOTO_EVENT_LIST;

$dbname = $_POST['dbname'];
$name   = $_POST['name'];
$idx    = $_POST['idx'];

if ($name == "status" && $dbname == "cs_photo_event_orders") {
    for ($i = 0; $i < count($idx); $i++) {
        $db->update($dbname, "status=1 where idx='" . (int)$idx[$i] . "' and status=0");
        $db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_photo_event_complete', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='" . (int)$idx[$i] . " $ADMIN_NAME'");
    }
} else if ($name == "delete" && $dbname == "cs_photo_event_orders") {
    for ($i = 0; $i < count($idx); $i++) {
        $db->delete($dbname, "where idx='" . (int)$idx[$i] . "'");
        $db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_photo_event_del', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='" . (int)$idx[$i] . " $ADMIN_NAME'");
    }
}

include('../footer.php');
?>
