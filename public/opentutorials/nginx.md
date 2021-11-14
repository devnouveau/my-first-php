# NGINX
[open tutorials NGINX](https://opentutorials.org/module/384/3462) 참고


## 1. 설치
### 1.1. CGI Common Gateway Interface 
- 웹서버와 외부프로그램을 연결해주는 표준화된 프로토콜.
- 정적인 정보를 제공하는 웹서버의 한계를 극복함
- 웹서버로 요청이 들어왔을 때 외부프로그램을 호출하여 거기서 처리한 결과를 웹서버로 다시 받아 브라우저로 전송함.
- 하나의 request에 하나의 프로세스를 생성함 (부하 발생)

### 1.2. Fast CGI
- 만들어진 프로세스가 계속해서 새로운 요청을 처리 (부하 감소)

### 1.3. PHP-FPM; PHP FastCGI Process Manager
- PHP가 fastCGI모드로 동작하도록 해줌


## 2. NGINX 환경설정
- 설정파일 위치
    - /usr/local/nginx/conf (컴파일로 설치한 경우)
    - /etc/nginx (우분투 apt-get으로 설치한 경우)
    - /etc/nginx/conf.d/default.conf 
    - /etc/nginx/sites-available/default
- 설정파일별 역할 
    - nginx.conf : 메인설정파일
    - fcgi.conf : fcgi 환경설정 파일
    - sites-enabled : 활성화된 사이트들의 설정파일들이 위치 (아파치 virtural host설정에 해당)
    - sties-available : 비활성화된 사이트들의 설정 파일들이 위치
- 설정파일 위치 찾기
    - ``` $ sudo find / -name nginx.conf ```
- 설정 파일 수정 후 반영
    - ``` $ sudo service nginx reload ```
- 기본 설정 구분
    1. core모듈 설정
        - work_processes와 같이 지시자 설정파일 최상단에 위치하며 nginx의 기본동작방식 정의.
    2. http 블록
        - server, location의 루트블록 
        - http, server, location블록은 계층구조. 
        - 하위블록이 루트블록의 값을 상속. (상위블록내용은 하위블록의 기본값이 되고, 하위블록에서 선언된 지시어는 상위의 선언을 무시하고 적용)
        - http 블록을 여러 개 사용 가능하나, 관리상이슈로 한 번만 사용할 것을 권장.
    3. server 블록
        - 하나의 웹사이트 선언에 사용.
        - http://site1.com, http://site2.com을 하나의 서버로 동시에 운영하려고 할 때 사용.
        - 가상 호스팅 개념.
    4. location 블록
        - server블록 안에 등장하면서, 특정url을 처리하는 방법을 정의.
        - http://site1.com/course/1, http://site1.com/module/1의 접근요청을 다르게 처리하고 싶을 때 사용.
    5. events 블록
        - 네트워크 동작방법과 관련된 설정값 정의.
        - http, server, location과 상속관계 X.
        - 이벤트블록 지시어들은 이벤트 블록에서만 사용가능.
        


### 2.1. 권장환경설정 _ nginx.conf
- user : 워커프로세스권한을 지정. 보안을 위해 root가 아닌 다른 사용자를 생성하여 지정.
- worker_process : 요청을 처리하는 프로세스 수. cpu코어수만큼 지정하는 것을 권장.
- worker_connections : 몇 개의 접속을 동시에 처리할 것인가를 지정. 
    이 값과 worker_process의 값의 조합을 계산하여 하나의 머신이 처리할 수 있는 커넥션의 양을 산출.
- log_not_found : 존재하지 않는 파일에 대한 요청시 404에러를 에러로그파일에 기록할 것인지 여부.
- client_max_body_size : 업로드할 수 있는 용량의 크기 지정.


### 2.2. 가상호스트 - server블록
- 가상호스트 
    - host : 네트워크에 연결된 하나의 컴퓨터.
    - virtual host : 한대의 컴퓨터러 여러대의 컴퓨터가 존재하는 것처럼 동작하게 하는 것.
    - http://site1.com, http://site2.com로 접속했을 때, 둘다 같은 IP를 가르키면서 도메인에 따라 서로 다른 페이지를 서비스하게 할 수 있음
- 가상호스트 사용방법 
    - nginx.conf 파일의 server블록 사용
    - 하나의 호스트에서 복수서비스 운영시 include방법 사용을 권장
    - server_name 지시어 : 호스트명(주로 도메인)을 지정
- include
    - 별도의 파일에 설정을 기록해서 설정의 그룹핑, 재활용성 제고
