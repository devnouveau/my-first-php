<?php
/**
 *  객체 비교
 */
$class1 = new stdClass();
$class1->sayHello = 'hello';
$class2 = new stdClass();
$class2->sayHello = 'hello';

// var_dump($class1 == $class2);
// var_dump($class1 === $class2); // 메모리 주소값까지 비교



/**
 *  객체 복사
 */
// // 1. $class3이 $class1의 주소를 참조하도록 함 // 단순객체복사
// // $class3 = $class1 = <Object Id> 
// $class3 = $class1; // 위와 동일 // 
// $class3->sayHello = 'hello';
// var_dump($class1->sayHello); 
// // $class3의 속성값이 변경되면 $class1의 속성값도 
// // 동일하게 변경된 것으로 출력됨


// // 2. $class3과 $class1이 동일한 주소를 참조하도록 함 // 참조
// // ($class3, $class1) = <Object Id>
// $class3 =& $class1; // 위와 동일
// $class3 = 'hello'; 
// var_dump($class1); // 같은 주소를 참조하고 있기 때문에 class1의 값도 변경된것을 확인


// 3. 얕은 복사
$class3 = clone $class1; //clone 자체는 얕은 복사. 
var_dump($class3 === $class1); // false 
// 주소값이 서로 다름 
// 그럼 깊은복사 아님? 왜 얕은복사라 하지? 
// 아래 예시에서는 주소값이 다르면 깊은 복사라는데?


// clone키워드를 사용해도 객체 내부의 객체는 주소값이 그대로 복사... 왤까?
$array1 = new ArrayObject([1, 2, new ArrayObject([3, 4])]); 
$array2 = clone $array1; 
var_dump($array1 === $array2); // false
var_dump($array1[2] === $array2[2]); // true



// 4. Shallow Copy 얕은 복사 vs Deep Copy 깊은 복사
class MyArrayObject implements ArrayAccess, IteratorAggregate
{
  private $container = [];

  public function __constrtuct($array)
  {
    $this->container = $array;
  }

  public function offsetSet($offset, $value)
  {
    $this->container[$offset] = $value;
  }

  public function offsetExists($offset)
  {
    return isset($this->container[$offset]);
  }
}



?>