<?
session_start();
include('../def_inc.php');
include('../common.php');
require('../check_session.php');

$name = isset($_POST['name']) ? $_POST['name'] : '';
$idx  = isset($_POST['idx'])  ? $_POST['idx']  : array();

// 승인하기 — 팀장(PERMISSION_ALL)만 가능
if ($name == "approve") {
    if ($PERMISSION != PERMISSION_ALL) {
        echo json_encode(array('ok' => false, 'msg' => '권한 없음'));
        exit;
    }
    $approved_by = mysqli_real_escape_string($db->db_conn, $ADMIN_NAME);
    for ($i = 0; $i < count($idx); $i++) {
        $db->update("cs_sponsored_orders",
            "status=1, approved_at=NOW(), approved_by='$approved_by'
             where idx=" . (int)$idx[$i] . " and status=0");
        $db->insert("admin_log",
            "userid='$_SESSION[ADMIN_USERID]', contents='cs_sponsored_approve',
             ip='$_SERVER[REMOTE_ADDR]', udate=now(),
             comment='" . (int)$idx[$i] . " $ADMIN_NAME'");
    }
}
// 삭제 — 쓰기 권한 필요
elseif ($name == "delete") {
    if (($PERMISSION & PERMISSION_CS) != PERMISSION_CS) {
        echo json_encode(array('ok' => false, 'msg' => '권한 없음'));
        exit;
    }
    for ($i = 0; $i < count($idx); $i++) {
        $db->delete("cs_sponsored_orders", "where idx=" . (int)$idx[$i]);
        $db->insert("admin_log",
            "userid='$_SESSION[ADMIN_USERID]', contents='cs_sponsored_del',
             ip='$_SERVER[REMOTE_ADDR]', udate=now(),
             comment='" . (int)$idx[$i] . " $ADMIN_NAME'");
    }
}

echo json_encode(array('ok' => true));
?>
