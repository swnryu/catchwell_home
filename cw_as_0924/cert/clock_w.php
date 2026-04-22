<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>디지털 시계와 날씨</title>
    <style>
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f0f0;
        }
        #clock {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            font-size: 20vw;
            font-family: 'Arial', sans-serif;
            color: #333;
            position: relative;
        }
        #ampm {
            position: absolute;
            bottom: 5%;
            right: 5%;
            font-size: 4vw;
            color: #666;
        }
        #date {
            font-size: 3vw;
            margin-bottom: 2%;
            color: #333;
        }
        #weather-widget {
            position: absolute;
            top: 5%;
            left: 5%;
            font-size: 2vw;
            color: #333;
        }
        #openweathermap-widget-9 {
            width: 100%;
        }
    </style>
</head>
<body>
    <div id="clock">
        <div id="date"></div>
        <div id="time"></div>
        <div id="ampm"></div>
        <div id="weather-widget">
            <div id="openweathermap-widget-9"></div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const hours = now.getHours();
            const minutes = now.getMinutes();
            const seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';

            const formattedHours = hours % 12 || 12;
            const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
            const formattedSeconds = seconds < 10 ? '0' + seconds : seconds;

            const timeString = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
            const dateString = now.toLocaleDateString('ko-KR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                weekday: 'long'
            });

            document.getElementById('time').textContent = timeString;
            document.getElementById('ampm').textContent = ampm;
            document.getElementById('date').textContent = dateString;
        }

        setInterval(updateClock, 1000);
        updateClock();

        function updateWeatherWidget() {
            const widgetContainer = document.getElementById('openweathermap-widget-9');
            widgetContainer.innerHTML = ''; // 기존 위젯 내용 제거

            window.myWidgetParam ? window.myWidgetParam : window.myWidgetParam = [];
            window.myWidgetParam.push({
                id: 9,
                cityid: '1897000',
                appid: '7d0dca8d31f93e0a9e5e1bcd86e427aa',
                units: 'metric',
                containerid: 'openweathermap-widget-9',
            });

            (function() {
                var script = document.createElement('script');
                script.async = true;
                script.charset = "utf-8";
                script.src = "//openweathermap.org/themes/openweathermap/assets/vendor/owm/js/weather-widget-generator.js";
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(script, s);
            })();
        }

        updateWeatherWidget();
        setInterval(updateWeatherWidget, 600000); // 10분(600,000ms)마다 날씨 위젯 업데이트
    </script>
</body>
</html>
