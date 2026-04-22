<?php
// OpenWeatherMap API 키
$apiKey = "7d0dca8d31f93e0a9e5e1bcd86e427aa"; // 여기에 API 키를 입력하세요
$cityId = "1897000"; // 강릉시의 도시 ID

// API URL
$apiUrl = "https://api.openweathermap.org/data/2.5/weather?id=$cityId&appid=$apiKey&units=metric&lang=kr";

// API 호출 및 응답 데이터 가져오기
$response = file_get_contents($apiUrl);
$weatherData = json_decode($response, true);

// 필요한 데이터 추출
$city = $weatherData['name'];
$temperature = $weatherData['main']['temp'];
$weatherDescription = $weatherData['weather'][0]['description'];
$highTemp = $weatherData['main']['temp_max'];
$lowTemp = $weatherData['main']['temp_min'];
$weatherIcon = $weatherData['weather'][0]['icon'];

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>날씨 정보</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f0f0;
            font-family: 'Arial', sans-serif;
        }
        .weather-container {
            text-align: center;
            background-image: url('path/to/your/background.jpg'); /* 배경 이미지 경로 */
            background-size: cover;
            background-position: center;
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        .temperature {
            font-size: 4em;
            margin: 0;
        }
        .city {
            font-size: 2em;
            margin: 0;
        }
        .condition {
            font-size: 1.5em;
            margin: 0;
        }
        .high-low {
            font-size: 1em;
            margin: 10px 0;
        }
        .forecast-container {
            background-color: rgba(0, 0, 0, 0.5);
            padding: 10px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .forecast-header, .forecast-hourly, .forecast-daily {
            margin: 10px 0;
        }
        .forecast-item {
            display: inline-block;
            width: 50px;
            text-align: center;
            color: white;
        }
        .forecast-hourly .forecast-item img, .forecast-daily .forecast-item img {
            width: 30px;
            height: 30px;
        }
    </style>
</head>
<body>
    <div class="weather-container">
        <div class="city"><?php echo $city; ?></div>
        <div class="temperature"><?php echo $temperature; ?>°</div>
        <div class="condition"><?php echo $weatherDescription; ?></div>
        <div class="high-low">최고: <?php echo $highTemp; ?>° 최저: <?php echo $lowTemp; ?>°</div>
        <div class="forecast-container">
            <div class="forecast-header">
                07:00~09:00에 강우 상태가, 18:00에 부분적으로 흐린 상태가 예상됩니다.
            </div>
            <div class="forecast-hourly">
                <div class="forecast-item">
                    <div>지금</div>
                    <img src="https://openweathermap.org/img/wn/<?php echo $weatherIcon; ?>@2x.png" alt="아이콘">
                    <div><?php echo $temperature; ?>°</div>
                </div>
                <!-- 추가 시간별 예보 데이터를 여기 추가할 수 있습니다 -->
            </div>
            <div class="forecast-daily">
                <div class="forecast-item">
                    <div>오늘</div>
                    <img src="https://openweathermap.org/img/wn/<?php echo $weatherIcon; ?>@2x.png" alt="아이콘">
                    <div><?php echo $lowTemp; ?>°</div>
                    <div><?php echo $highTemp; ?>°</div>
                </div>
                <!-- 추가 일별 예보 데이터를 여기 추가할 수 있습니다 -->
            </div>
        </div>
    </div>
</body>
</html>
