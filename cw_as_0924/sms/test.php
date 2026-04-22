<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>링크 미리보기</title>
<script>
function openPopup(url, params, x, y) {
    // GET 매개변수를 포함한 URL 생성
    var queryString = Object.keys(params).map(key => key + '=' + params[key]).join('&');
    var fullUrl = url + '?' + queryString;

    // 팝업 창을 엽니다.
    var popup = window.open(fullUrl, '미리보기', 'width=600,height=400,left=' + x + ',top=' + y);

    // 팝업이 차단되었을 경우 알림을 표시합니다.
    if (!popup || popup.closed || typeof popup.closed == 'undefined') {
        alert('팝업이 차단되었습니다. 팝업 차단을 해제하고 다시 시도하세요.');
    }

    return popup; // 팝업 창 객체를 반환합니다.
}

function showPopupOnMouseover(link, url, params) {
    var popup; // 팝업 창 객체를 저장할 변수

    link.addEventListener('mouseover', function(event) {
        // 링크의 위치를 기준으로 팝업의 위치를 계산합니다.
        var rect = link.getBoundingClientRect();
        var x = rect.right + window.pageXOffset;
        var y = rect.bottom + window.pageYOffset;

        popup = openPopup(url, params, x, y);
    });

    link.addEventListener('mouseout', function() {
        // 마우스가 떠났을 때 팝업 창을 닫습니다.
        if (popup && !popup.closed) {
            popup.close();
        }
    });
}
</script>
</head>
<body>

<?php
// 전달할 변수 설정
$var1 = 'value1';
$var2 = 'value2';
?>

<a href="#" id="popupLink">마우스를 가져가면 미리보기가 열립니다.</a>

<script>
    var link = document.getElementById('popupLink');
    showPopupOnMouseover(link, 'preview.php', {var1: '<?php echo $var1; ?>', var2: '<?php echo $var2; ?>'});
</script>

</body>
</html>