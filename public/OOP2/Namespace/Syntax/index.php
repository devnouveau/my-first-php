<?php
/**
 * Namespaces
 */
// 모던 php의 오토로딩에서 중요한 개념
// global namespace 

// 주로 클래스, 함수 포함
// 파일당 1개의 네임스페이스 권장

// var_dump()는 글로벌네임스페이스의 함수
namespace A
{
  const MESSAGE = __NAMESPACE__; //네임스페이스의 이름

  class A
  {
    function foo()
    {
      return __METHOD__;
    }
  }


  function foo()
  {
    return __FUNCTION__;
  }

}


namespace A\B
{
    class A
    {
      public function foo()
      {
        return __METHOD__;
      }
    }
}

\var_dump('hello'); // 직접 글로벌 네임스페이스의 함수, 클래스를 직접지칭

namespace //이름없으면 글로벌 네임스페이스
{
  use A\A;
  use A\B\A as AB; // 별칭을 지정하여 같은 이름의 클래스 충돌 방지
  use function A\foo; // 네임스페이스의 함수 이용
  use const A\MESSAGE; // 네임스페이스의 상수 이용
  
  var_dump(MESSAGE); //
} 


?>