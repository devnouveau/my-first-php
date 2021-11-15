<!-- 함수와 객체의 비교 -->
<h1>Function</h1>
<?php
// 함수의 경우 입력값('data.txt')을 반복해서 받음
var_dump(is_file('data.txt'));
var_dump(is_dir('data.txt'));
var_dump(file_get_contents('data.txt'));
file_put_contents('data.txt', rand(1,100));
?>

<h1>Object</h1>
<?php
// 객체가 입력값을 저장해두고 재사용하고 있음
$file = new SplFileObject('data.txt');
var_dump($file->isFile());
var_dump($file->isDir());
var_dump($file->fread($file->getSize()));
$file->fwrite(rand(1,100));
 
$file2 = new SplFileObject('data2.txt');
var_dump($file2->isFile());
var_dump($file2->isDir());
var_dump($file2->fread($file2->getSize()));
$file2->fwrite(rand(1,100));
 
// SplFileObject : Class
// $file, $file2 : Instance
// isFile, isDir, fread : Method(function)
// data.txt, data2.txt : state
?>





<?php
// construct()
// 인스턴스 생성시 필수적으로 받아야 할 인자/ 처리내용을 지정할 수 있음

class MyFileObj {
  function __construct($fname) {
    $this->filename = $fname;
  }
  function isFile() {
    return is_file($this->filename);
  }
}
$file = new MyFileObj('data.txt'); //
// 생성자가 인자를 받지 않도록 작성된 경우
// $file = new MyFileObj();
// $file->filename = 'data.txt' // 별도로 멤버변수값을 지정하는 것을 누락할 수 있음

var_dump($file->isFile());
var_dump($file->filename);
?>