<?php 
    // php변수명은 앞에 $붙임.
    // $_GET, $_POST, $_REQUEST 배열은 폼데이터를 가짐 
    // $_POST는 POST전송의 form입력값의 배열
    // $_POST['tireqty']는 tireqty form field의 입력값을 가짐
    $tireqty = $_POST['tireqty']; 
    $oilqty = $_POST['oilqty'];
    $sparkqty = htmlspecialchars($_POST['sparkqty']);
    $address = preg_replace('/\t|\R/',' ',$_POST['address']);
    $document_root = $_SERVER['DOCUMENT_ROOT'];
    $date = date('H:i, jS F Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Bob's Auto Parts</h1>
    <h2>Order Results</h2>
    <?php 
        echo "<p>Order processed at ".date('H:i, jS F Y')."</p>";
        // echo "<p>Order processed at ";
        // echo date('H:i, jS F Y');
        // "</p>";
        echo "<p>your order is following</p>";

        $totalqty = 0;
        $totalamount  = 0.00;

        define('TIREPRICE', 100); // 상수 선언
        define('OILPRICE', 10);
        define('SPARKPRICE', 4);
        
        $totalqty = $tireqty + $oilqty + $sparkqty;
        echo "<p>Items ordered:".$totalqty."<br />";



        // htmlspecialchars()는 &,",',<,> 등을 html엔티티문자로 이스케이프하여 값이 html로 인식되지 않도록 함  
        // echo "$sparkqty spark plugs<br>"; // ""에 입력된 변수는 보간(변수의 값으로 대체)이 됨
        // echo '$sparkqty literal~<br>'; // ''에 입력된 변수는 리터럴(문자그대로)로 출력
        if($totalqty == 0) {
            echo "You did not order anything on the previous page<br />";
        } else {
            if($tireqty > 0) {
                echo htmlspecialchars($tireqty)." tires<br />";  
            }
            if($oilqty > 0) {
                echo htmlspecialchars($tireqty)." bottles of oil<br />";  
            }
            if($sparkqty > 0) {
                echo htmlspecialchars($tireqty)." spark plugs<br />";  
            }
        }


        $totalamount = $tireqty * TIREPRICE +
                        $oilqty * OILPRICE +
                        $sparkqty * SPARKPRICE;
        
        echo "Subtotal : $".number_format($totalamount, 2)."<br />";


        $taxrate = 0.10;
        $totalamount = $totalamount * (1 + $taxrate);
        echo "Total including tax: $".number_format($totalamount, 2)."</p>";



        echo "<p>Address to ship to is".$address."</p>";
    


        $find = $_POST['find'];
        switch($find) {
            case "a" :
                echo "<p>regular customer</p>";
                break;
            case "b" :
                echo "<p>customer referred by tv ads</p>";
                break;
            case "c" :
                echo "<p>customer referred by social media</p>";
                break;
            case "d" :
                echo "<p>customer referred by word of mouth</p>";
                break;
            default :
                echo "<p>we don't know how this customer found us.</p>";
                break;
        }
    


        $outputstring = $date."\t".$tireqty." tires \t".$oilqty." oil\t"
        .$sparkqty." spark plugs\t\$".$totalamount
        ."\t". $address."\n";

        // echo "$document_root";
        // 데이터 추가 - 1.파일 열기 (파일이 존재하지 않으면 에러 발생)
        @$fp = fopen("$document_root/orderprocess/orders/orders.txt", 'ab'); 
        //@는 에러억제 연산자, ad -> add(기존파일에 추가, 없으면 파일 생성), b - > binary data로 저장
        
        if(!$fp) { // 예외처리
            echo "<p><strong>your order could not be processed in this time. please try again later.</strong></p>";
            exit;
        }


        // 데이터 추가 - 2.파일 쓰기
        flock($fp, LOCK_EX); // (acquire an exclusive lock)
        fwrite($fp, $outputstring, strlen($outputstring)); // $fp에 $outputstring을, $outputstring의 길이만큼 쓰기
        flock($fp, LOCK_UN);

        // 데이터 추가 - 3.파일 닫기
        fclose($fp);

        echo "<p>Order written.</p>";
        

            
    ?>
</body>
</html>