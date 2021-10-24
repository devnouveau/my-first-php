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

        // 다차원 배열
        $categories = array(
                            array(array('CAR_TIR', 'Tires', 100),
                                array('CAR_OIL', 'Oil', 10),
                                array('CAR_SPK', 'Spark Pulgs', 4)),
                            array(array('VAN_TIR', 'Tires', 120),
                                    array('VAN_OIL', 'Oil', 12),
                                    array('VAN_SPK', 'Spark Pulgs', 5)),
                            array(array('TRK_TIR', 'Tires', 150),
                                    array('TRK_OIL', 'Oil', 15),
                                    array('TRK_SPK', 'Spark Pulgs', 6))
                            );
        
        for($layer = 0; $layer < 3; $layer++) {
            echo 'Layer'.$layer."<br />";
            for($row = 0; $row <3; $row++) {
                for($column = 0; $column < 3; $column++) {
                    echo ' | '.$categories[$layer][$row][$column];
                }
                echo ' |<br />';
            }
        }




        // =========================== 배열 정렬 ===============================
        // sort(), 값 기준 정렬 asort(), 키 기준 정렬 ksort()
        // 내림차순 정렬 rsort(), arsort(), krsort()
        // sort함수 두 번째 매개변수 SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LACALE_STRING, SORT_NATURAL, SORT_FLAG_CASE
        // 다차원배열 정렬, 여러배열 정렬시 array_multisort(). 기본적으로 배열의 첫 번쨰 요소 값 기준 오름차순정렬
        // 키가 문자열 : 그냥 정렬, 키가 숫자 : 인덱스 재생성

        // 무작위 정렬 suffle(), 역순정렬된 배열의 복사본반환 array_reverse(), array_push(), array_pop()


        // =========================== 배열 함수 ===============================

        // 배열 순환 함수 (배열 포인터를 이동 후 배열요소 반환)
        // each(), current(), reset(), end(), next(), pos(), prev()
        
        // 배열요소 역순출력
        $arrayforrprnt = array(1, 2, 3);
        $arrayforrprntval = end($arrayforrprnt);
        while($arrayforrprntval) {
            echo "$arrayforrprntval ";
            $arrayforrprntval = prev($arrayforrprnt); 
        }
        echo "<br />";
        
        
        // 배열 각 요소에 함수 적용 
        // array_walk()

        // 배열 요소의 수 반환 함수
        // count(), sizeof(), 
        // array_count_values() : 배열의 고유값을 키로, 해당 고유값의 갯수를 값으로 하는 요소들의 배열을 반환

        // 배열의 키로 스칼라 변수를 추출하는 함수
        $arrforextract = array('Tires'=>100, 'Oil'=>10, 'Spark Plugs'=>4);
        extract($arrforextract);
        echo "$Tires $Oil <br />"; // 100 10
        extract($arrforextract, EXTR_PREFIX_ALL, 'prefix');
        echo "$prefix_Tires $prefix_Oil"; // 100 10

    ?>
</body>
</html>