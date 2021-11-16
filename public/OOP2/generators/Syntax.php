<?php
/**
 * generator
 */
// getnerator는 iterator 인터페이스의 구현
function gen() { 
  yield 1;
  yield 2;
  yield 3;
}

// 제너레이터 사용
// $gen = gen();
// var_dump($gen->current()); //1
// $gen->next();
// var_dump($gen->current()); //2
// foreach ($gen as $numbers)
// {
//   var_dump($number);
// }


// 제너레이터를 내부에서 부르기
function gen2() {
  yield 1;
  yield from gen();
  yield 2;
}
foreach (gen2() as $numbers)
{
  var_dump($number);
}




// key/value로 제너레이터 사용
function gen3() 
{
  yield 'message' => 'hello';
}
foreach (gen3() as $key=>$value)
{
  var_dump($number);
}



// 데이터를 전달하여 제너레이터 사용
function gen4()
{
  $data = yield;
  yield $data;
}
$gen4 = gen4();
var_dump($gen4->send('hello'));
var_dump($gen4->current());




// 제너레이터를 사용하는 이유 (메모리 효율성)
function __range($start, $end, $step = 1)
{
  for($i = 0; $i <= $end; $i +=$step) {
    yield $i;
  }
}

$s = microtime(true);

foreach(__range(0, 100000) as $number) {} // generator
//foreach(range(0, 100000) as $number) {} // buit-in function

var_dump(microtime(true) -$s, memory_get_peak_usage());
// float(0.013192176818847656) int(1477848) // generator (메모리를 더욱 효율적으로 사용)
// float(0.015245199203491211) int(5606568) // buit-in function

?>