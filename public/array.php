<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
        // =========================== 숫자 인덱스 배열 ===============================
        // 배열을 명시적으로 생성
        $products = array('Tires', 'Oil', 'Spark Pulgs'); //array()는 함수가 아닌 언어 구성요소
        $products2 = ['Tires', 'Oil', 'Spark Pulgs'];
        
        // 배열을 미리 초기화/생성 할 필요없이, 배열선언과 동시에 요소할당 가능
        $newarray[0] = 'abc';

        // 범위를 배열로 만들기
        $numbers = range(1,10,2);
        $odds = range(1,10,2);
        $letters = range('a', 'z');

        // 배열 끝에 요소 추가 
        $products[3] = 'Fuses';

        // 루프에서 배열 사용
        for($i = 0; $i<3; $i++) {
            echo $products[$i]." ";
        }
        echo "<br />";
        foreach($products as $current) {
            echo $current." ";
        }
        echo "<br />============================================<br />";

        // =========================== 숫자 외 인덱스 배열 ===============================
        // 배열을 명시적으로 생성
        $prices = array('Tires'=>100, 'Oil'=>10, 'Spark Plugs'=>4); // 키값지정 / 요소값 할당
        // $prices = array('Tires'=>100); $prices['Oil'] = 10; $prices['Spark Plugs'] = 4; // 배열요소 추가
        
        // 배열을 미리 초기화/생성 할 필요없이, 배열선언과 동시에 요소할당 가능
        // $prices = $prices['Tires'] = 10; $prices['Oil'] = 10; $prices['Spark Plugs'] = 4; //

        // 루프에서 배열 사용
        foreach($prices as $key => $value) {
            echo $key." - ".$value."<br />";
        }
        echo "<br />";
        /* 
        //each() : php 7.2 deprecated
        while($element = each($prices)) {
            echo $element['key']."-".$element['value'];
            echo "<br />";
        }
        */
        // 배열합집합 $array1 + $array2 -> $array2의 요소들을 $array1에 추가. 
        // 중복되는 key값이 있으면 연산X

        ?>
</body>
</html>