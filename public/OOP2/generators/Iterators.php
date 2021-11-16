<?php 

// 아래와 같이 이터레이터를 직접 구현하는 것은 번거로움.
// 그래서 iterator를 구현한 generator를 사용
class NumberIterator implements Iterator
{
  private $i;
  
  public function __construct($start, $end, $step =1)
  {
    $this->start = $i = $start;
    $this->end = $end;
    $this->step = $step; 
  }

  public function rewind() 
  {
    $this->i = 0;
  }

  public function valid() 
  {
    return $this->i <= $this->end;
  }

  public function current() 
  {
    return $this->i;
  }

  public function key() 
  {
    return $this->i;
  }
  
  public function next() 
  {
    return $this->i + $this->step;
  }
}

foreach (new NumberIterator(0, 10) as $number) {
  var_dump($number);
}

?>