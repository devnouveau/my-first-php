<?php
// 생성기

// 생성기 함수는 foreach루프에서 호출.//generator객체 생성
// 늦은실행. 배열과 달리 하나의 값만 메모리에 보존. 
// 메모리에 쉽게 넣기 어려운 큰 데이터 처리에 유용. 


function fizzbuzz($start, $end)
{
  $current = $start;
  while ($current <= $end) {
    if ($current%3 == 0 && $current%5 == 0) {
      yield "fizzbuzz";
    } else if ($current%3 == 0) {
      yield "fizz";
    } else if ($current%5 == 0) {
      yield "buzz";
    } else {
      yield $current; // yield문을 만나면 호출측(foreach루프)으로 실행제어를 넘김
    }
    $current++;
  }
}

foreach(fizzbuzz(1,20) as $number) {  // 생성기함수와 실행제어를 주고 받음.
  echo $number.'<br />'; 
}
?>
