# PHP Session

### 세션 제어
#### 세션
- 세션 : 로그인 ~ 로그아웃할 때까지.
논리적인 연결
- 트랜잭션 : 세션 연결 중 클라이언트 -서버 간 데이터 교환
- HTTP 통신에서 하나의 세션 진행 중 여러번의 물리적 연결 (클라이언트 소켓 생성 후 서버 소켓과 연결) 발생
- 세션유지를 위해 세션 ID사용
- 시션 시작시 서버에서 세션ID생성 - 클라이언트의 브러우저에서 이를 쿠키로 저장 후 이후 연결시 서버에 전송 - 서버에서 같은 클라이언트의 요청인지 알 수 있음
- 슈퍼글로벌 변수 $_SESSION

#### 쿠키
- 클라이언트에 저장
- HTTP 헤더에 포함시켜 전송