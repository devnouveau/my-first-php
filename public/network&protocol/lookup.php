<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Quote From NASDAQ</title>
</head>
<body>
<?php 

$symbol = 'GOOG';
echo '<h1>Stock Quote for '.$symbol.'</h1>';

// 데이터 불러오기
/*
$url = 'http://download.finance.yahoo.com/d/quotes.csv' .
    '?s='.$symbol.'&e=.csv&f=sl1d1t1c1ohgv';
*/

$url = 'https://query1.finance.yahoo.com/v7/finance/download/'.$symbol.'?period1=1603850849&period2=1635386849&interval=1d&events=history&includeAdjustedClose=true';
if (!($contents = file_get_contents($url))) { // file_get_contents모든 파일을 string으로 읽어오기
    die('Failed to open '.$url);
}

// 볼러온 데이터 추출
// 
list($symbol, $quote, $date, $time) = explode(',', $contents);
$date = trim($date, '"');
$time = trim($time, '"');

echo '<p>'.$symbol.' was last sold at : '.$quote.'</p>';
echo '<p>Quote current as of '.$date.' at '.$time.'</p>';

echo '<p>This imformation retrieved from <br /><a href"'.$url.'">'.$url.'</a></p>';




?>

</body>
</html>