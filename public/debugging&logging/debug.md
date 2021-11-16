# PHP Debug

## 화면에 데이터 출력하기 
```php
$arr = array(1,2,3,'four');
echo var_dump($arr); // 데이터 타입 출력
echo print_r($arr);
```

## 액세스 로그 확인
- nginx.conf 파일(가상호스트 설정도 확인할 것)에서 로깅 설정
  ```
  access_log /var/log/nginx/homestead.study-access.log;
  error_log  /var/log/nginx/homestead.study-error.log error;
  ```
- 액세스 로그 확인 명령어
  ```bash
  $ tail - f /var/log/nginx/access.log
  # 사이트 접근할 때마다 로깅내용 확인 가능
  ```
  


## xdebug 사용 (homestead, VScode 사용시)
- [참고링크1](https://matttonks11.medium.com/xdebug-laravel-homestead-php-8-vscode-e6067875431b)
- [참고링크2](https://tighten.co/blog/debugging-configure-xdebug-and-laravel-homestead-and-vs-code-and-phpunit/)
- VSCODE에 PHP IntelliSense, PHP Debug extensions 설치
- xdebug설정파일 찾아서 수정하기
  ```bash
  # xdebug 사용하도록 설정
  $ xon
  # xdebug 사용되고 있는지 확인
  $ php -v

  # client_host 확인
  $ netstat -rn | grep "^0.0.0.0 " | cut -d " " -f10
  # netstat command not found일 경우 설치
  $ sudo apt-get update
  $ sudo apt-get install net-tools

  # xdebug설정파일 찾기/수정
  $ php --ini | grep xdebug
  $ sudo vi /etc/php/8.0/cli/conf.d/20-xdebug.ini

  #아래와 같이 수정
  zend_extension=xdebug.so
  xdebug.mode = debug
  xdebug.discover_client_host = yes
  xdebug.start_with_request = yes
  xdebug.client_host = 10.0.2.2 
  xdebug.client_port = 9003
  xdebug.idekey = VSCODE
  xdebug.max_nesting_level = 512

  # 수정후 fpm재시작
  $ sudo service php7.2-fpm restart
  ```

- VSCODE 설정
  php debug, php IntelliSense extension 설치
  VSCODE debug 탭 - 셀렉트 박스 - add config - PHP:listen for debug - launch.json설정파일
  
  ```json
  // 아래 항목과 같이 수정
  {
    // Use IntelliSense to learn about possible attributes.
    // Hover to view descriptions of existing attributes.
    // For more information, visit: https://go.microsoft.com/fwlink/?linkid=830387
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for XDebug",
            "type": "PHP",
            "request": "launch",
            "port": 9000
        },
        {
            "name": "Launch currently open script",
            "type": "php",
            "request": "launch",
            "program": "${file}",
            "cwd": "${fileDirname}",
            "port": 9003,
            "pathMappings": {
                "/home/vagrant/laravel": "C:\\Users\\cho03\\laravel",
            }
        },
        {
            "name": "Listen for XDebug on Homestead",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/home/vagrant/laravel": "C:\\Users\\cho03\\laravel",
            }
        }
    ]
  }
  ```
- 원하는 파일, 원하는 라인에 breakpoint 찍기
- 디버그 탭의 셀렉트박스에서 listen for xdebug 선택하고, 디버그시작버튼(재생버튼) 누르기
- 해당 페이지로 접속하면, breakpoint에 걸림