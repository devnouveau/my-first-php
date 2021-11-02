<?php
echo phpinfo();
// $locale="en_US.utf8";
@$locale = $_GET['lang'] ? $_GET['lang'] : "en_US.utf8";
putenv("LC_ALL=".$locale);
echo setlocale(LC_ALL, $locale) ? 'true':'false';

$domain='messages';
bindtextdomain($domain, "./locale"); 
bind_textdomain_codeset($domain, 'UTF-8'); 
textdomain($domain); 
?>
<!DOCTYPE html>
<html>
   <title><?php echo gettext("WELCOME_TEXT"); ?></title>
<body>
   <h1><?php echo gettext("WELCOME_TEXT"); ?></h1>
   <h2><?php echo gettext("CHOOSE_LANGUAGE"); ?></h2>
   <ul>
      <li><a href="<?php echo $_SERVER['PHP_SELF']."?lang=en_US.utf8"; ?>">en_US</a></li>
	   <li><a href="<?php echo $_SERVER['PHP_SELF']."?lang=ko_KR.utf8"; ?>">ko_KR</a></li>
      <li><a href="<?php echo $_SERVER['PHP_SELF']."?lang=ja_JP.utf8"; ?>">ja_JP</a></li> 
   </ul>
</body>
</html>
<!--
locale 디렉토리 구조에 맞게 생성해줘야 함.

[ .php로 .po파일 생성 ]
$ xgettext -o ./locale/en_US/LC_MESSAGES/messages.po -n use_gettext.php

[ .po로 messages.mo 생성 ]
$ msgfmt messages.po -o messages.mo
MO(Machine Object)는 GNU gettext가 읽는 이진객체 데이터를 포함

[ setlocale 작동하지 않는 경우 ]
https://stackoverflow.com/questions/10909911/php-setlocale-has-no-effect

echo setlocale(LC_ALL, $locale) ? 'true':'false';
false반환시
$ locale -a로 서버에 설치된 언어 확인

[ 서버 언어 설치 ]
https://www.devmanuals.net/install/ubuntu/ubuntu-12-04-lts-precise-pangolin/install-language-pack-ko.html

$ sudo locale-gen ko_KR
$ sudo locale-gen ko_KR.UTF-8
$ sudo update-locale 

$ sudo apt-get update
$ sudo apt-get install language-pack-ko
$ locale-gen ko_KR.UTF-8
$ sudo vi /etc/profile

설치 후 재시작해야 함.... 
-->
