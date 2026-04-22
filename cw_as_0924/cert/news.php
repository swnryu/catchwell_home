<?php

function fetch_news($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $data = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "<p>cURL 에러: " . curl_error($ch) . "</p>";
    }
    curl_close($ch);
    return $data;
}

// Daum 뉴스 URL
$url = 'https://news.daum.net/';

// HTML 가져오기
$html_data = fetch_news($url);

if ($html_data === FALSE) {
    echo "<p>뉴스 페이지를 불러오는 데 실패했습니다.</p>";
    exit;
}

// HTML 파싱
$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($html_data);
libxml_clear_errors();

$xpath = new DOMXPath($doc);

// 주요 뉴스 항목 선택
$news_items = $xpath->query('//div[contains(@class, "item_issue")]//a[contains(@class, "link_txt")]');

if ($news_items->length == 0) {
    echo "<p>뉴스 아이템을 찾을 수 없습니다.</p>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daum 뉴스 - 주요 뉴스</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0 auto;
            max-width: 800px;
            padding: 20px;
            overflow: hidden;
        }
        #news-container {
            overflow: hidden;
            position: relative;
            height: 50px;
            line-height: 50px;
            white-space: nowrap;
        }
        .marquee {
            display: inline-block;
            white-space: nowrap;
            animation: marquee 30s linear infinite;
        }
        .news-item {
            display: inline-block;
            padding: 0 2em;
        }
        .news-item h2 {
            font-size: 1.5em;
            display: inline;
        }
        .news-item a {
            text-decoration: none;
            color: black;
        }
        .news-item a:hover {
            text-decoration: underline;
        }
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>
<body>
    <h1>Daum 뉴스 - 주요 뉴스</h1>
    <div id="news-container">
        <div class="marquee">
            <?php
            foreach ($news_items as $item) {
                $title = $item->nodeValue;
                $link = $item->getAttribute('href');

                echo "<div class='news-item'>";
                echo "<h2><a href='" . htmlspecialchars($link) . "'>" . htmlspecialchars($title) . "</a></h2>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
</body>
</html>
