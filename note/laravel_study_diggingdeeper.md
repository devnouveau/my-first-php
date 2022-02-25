# 1. Artisan console
## 1.1. 시작하기
- Artisan console : laravel의 CLI
    ```bash
    $ php artisan list # Artisan 명령어 목록
    $ php artisan help 명령어명 # 명령어에 대한 도움말 얻기
    ```
### 1.1.1. Tinker(REPL)
- Tinker : laravel의 REPL
- PsySH패키지로 구동됨
- Tinker사용시 Eloquent ORM, job, event 등을 CLI로 조작가능
- Tinker환경에 진입
    ```bash
    $ php artisan tinker
    ```
- Tinker설정파일 구성
    ```bash
    $ php artisan vendor:publish --provider="Laravel\Tinker\TinkerServiceProvider"
    ```
- 명령어 화이트리스트 : 구동가능한 명령어 목록지정
    ```php
    // tinker.php의 commands배열
    'commands' => [
        // App\Console\Commands\ExampleCommand::class,
    ],
    ```
- 별칭 블랙리스트
    - 일반적으로 Tinker는 클래스에 자동으로 alias지정
    - 일부 클래스는 alias지정하지 않게 함
        ```php
        // tinker.php의 dont_alias배열
        'dont_alias' => [
            App\User::class,
        ],
        ```

## 1.2. 명령어 작성하기
app/Console/Commands디렉토리에 고유한 명령어 저장가능
### 1.2.1. 명령어 생성
- app/Console/Command에 명령어 클래스 생성
    ```bash
    $ php artisan make:command SendEmails
    ```
### 1.2.2. 명령어 구조
- 생성된 명령어 클래스는 $signature, $description,handle()메소드(명령 실행시 호출됨)로 구성
    ```php
    namespace App\Console\Commands;

    use App\DripEmailer;
    use App\User;
    use Illuminate\Console\Command;

    class SendEmails extends Command
    {

        protected $signature = 'email:send {user}';
        protected $description = 'Send drip e-mails to a user';
        public function __construct()
        {
            parent::__construct();
        }
        public function handle(DripEmailer $drip)
        {
            $drip->send(User::find($this->argument('user')));
        }
    }
    ```
### 1.2.3. 클로저 명령
- app/Console/Kernel.php에서 routes/console.php파일 로드
    ```php
    protected function commands()
    {
        require base_path('routes/console.php'); 
    }
    ```
    - routes/console.php
        -  HTTP라우트 정의 X. 그러나 콘솔기반의 entry point(route)를 정의함
        - Artisan::command() 메소드로 클로저 기반 라우트 정의 가능
            ```php
            Artisan::command('email:send {user}', function (DripEmailer $drip, $user) { // 의존성 타입힌트가능
                $drip->send(User::find($user))->describe('description'); // description 추가가능
            });
            ```
        - 클로저는 기본 명령 인스턴스에 바인딩 (명령 클래스에서 접근할 수 있는 모든 헬퍼 메소드에 접근 가능)



## 1.3. 입력값들 정의
- signature속성을 통해 사용자입력값에 대한 설명을 작성가능
- signature속성 : 커맨드에서 사용할 입력값 이름, 인자, 옵션을 라우트와 비슷한 문법으로 정의할 수 있음
### 1.3.1. 인자들
- 사용자로부터 전달받을 인자,옵션은 {}로 감싸줌
    ```php
    // 필수인자인 경우
    protected $signature = 'email:send {user}';
    // 옵셔널한 인자인 경우
    protected $signature = 'email:send {user?}';
    // 옵셔널한 인자이며, 기본값 지정하는 경우 
    protected $signature = 'email:send {user=foo}'; 
    ```
### 1.3.2. 옵션들
- {--queue} : boolean 스위치처럼 동작함. 지정시 옵션값은 true, 미지정시 옵션값은 false
    ```php
    protected $signature = 'email:send {user} {--queue}';
    ```
- {--queue=} : 옵션값을 사용자가 지정하는 경우
    ```php
    protected $signature = 'email:send {user} {--queue=}';

    // 옵션에 기본값 할당시(옵션값 미전달시 기본값 사용)
    protected $signature = 'email:send {user} {--queue=default}'
    ```
- {--Q|queue} : 옵션에 단축키 지정시 옵션이름 앞에 단축어와 구분자 |를 추가
    ```php
    protected $signature = 'email:send {user} {--Q|queue}'
    ```

### 1.3.3. 배열입력
- 인자, 옵션을 배열형태로 입력받기를 원하는 경우 * 문자 사용
    ```php
    // 인자
    protected $signature = 'email:send {user*}';
    
    // 옵션
    protected $signature = 'email:send {user} {--id=*}';
    ```
- 명령 입력시 
    ```bash
    # 인자
    $ php artisan email:send foo bar # ['foo','bar']로 전달됨

    # 옵션
    $ php artisan email:send --id=1 --id=2
    ```
    

### 1.3.4. 설명입력
구분자 :를 사용하여 인자와 옵션에 대한 설명을 지정함
```php
protected $signature = 'email:send
                        {user : The ID of the user}
                        {--queue= : Whether the job should be queued}';
```
## 1.4. 명령 입출력
### 1.4.1. 입력 조회
```php
public function handle()
{
    /********* 인자 조회 *********/
    $userId = $this->argument('user'); // user인자 조회
    $arguments = $this->arguments(); // 모든 인자를 배열로 조회


    /********* 옵션 조회 *********/
    $queueName = $this->option('queue'); // quere인자값 조회
    $options = $this->options(); // 모든 옵션값을 배열로 조회


    // 인자, 옵션값 미존재시 null반환
}
```
### 1.4.2. 입력 프롬프트
```php
public function handle()
{
    // ask() : 사용자에게 질문을 표시하고 입력을 받아 명령어로 전달
    $name = $this->ask('What is your name?');

    // secret() : ask()와 유사. but 사용자 입력값을 화면에 미표시
    $password = $this->secret('What is the password?');

    // confirm() : 단순 y/n확인용. 기본값으로 false반환. y나 yes입력시 true반환
    if ($this->confirm('Do you wish to continue?')) {}

    // anticipate() : 입력가능한 값들 제공하여 자동완성. 제시된 값 외의 값도 입력가능
    $name = $this->anticipate('What is your name?', ['Taylor', 'Dayle']);
    // anticipate()에 두번째 인자로 클로저 전달
    // 클로저는 입력값을 받아서 자동완성을 위한 옵션 배열을 반환
    $name = $this->anticipate('What is your name?', function ($input) {});

    // choice() : 미리 주어진 선택지 중에 선택하게 하는 경우. 사용자는 배열의 인덱스를 선택. 기본값 지정가능
    $name = $this->choice('What is your name?', ['Taylor', 'Dayle'], $defaultIndex);
}
```


### 1.4.3. CLI에 출력하기
```php
public function handle()
{
    /****** 콘솔 출력을 위한 메소드 : line, info, comment, question, error ******/

    $this->info('Display this on the screen');
    // 녹색텍스트. 사용자에게 정보 제시.

    $this->error('Something went wrong!');
    // 적색텍스트. 에러출력

    $this->line('Display this on the screen');
    // 무색텍스트. 


    /****** 테이블 레이아웃 ******/
    $headers = ['Name', 'Email'];
    $users = App\User::all(['name', 'email'])->toArray();
    $this->table($headers, $users);


    /****** 프로그래스 바 - 진행률 표시줄 ******/
    $users = App\User::all();
    $bar = $this->output->createProgressBar(count($users));
    $bar->start();
    foreach ($users as $user) {
        $this->performTask($user);
        $bar->advance();
    }
    $bar->finish();
}
```

## 1.5. 명령어 등록하기
- app/Console/Kernel.php에서 명령어 등록
    ```php
    protected function commands()
    {
        // load()메소드를 통해 app/Console/Commands 내의 모든 명령어들이 아티즌에 자동 등록
        $this->load(__DIR__.'/Commands');
        $this->load(__DIR__.'/MoreCommands');

        // ...
    }

    // 클래스 이름 추가하여 수동으로 명령어 등록하는 것도 가능
    protected $commands = [
        Commands\SendEmails::class
    ];
    ```

## 1.6. 프로그래밍방식으로 명령 실행
- Artisan::call() 메소드 사용
    ```php
    /** 명령어 혹은 명령어클래스, 명령어 파라미터배열을 전달 **/
    Route::get('/foo', function () {
        $exitCode = Artisan::call('email:send', [ 
            'user' => 1, '--queue' => 'default'
        ]); 
    });
    $exitCode = Artisan::call('email:send', [
        'user' => 1, '--id' => [5, 13] //옵션값을 배열로 전달가능
    ]);
    $exitCode = Artisan::call('migrate:refresh', [
        '--force' => true, // 옵션값이 boolean인 경우
    ]);

    /** 명령어 자체를 전달**/
    Artisan::call('email:send 1 --queue=default'); 
    ```
- Artisan::queue() 메소드 사용
    ```php
    Route::get('/foo', function () {
        Artisan::queue('email:send', [
            'user' => 1, '--queue' => 'default'
        ]);
    });
    ```
    ```php
    /** 커넥션, 큐 지정도 가능 **/
    Artisan::queue('email:send', [
        'user' => 1, '--queue' => 'default'
    ])->onConnection('redis')->onQueue('commands');    
    ```
    - queue workers를 통해 백그라운드로 명령어가 실행 
    - 미리 큐설정, 큐리스너실행이 되어 있어야 함

### 1.6.1. 다른 명령어에서 명령호출
```php
// 명령어 클래스에서 
public function handle()
{
    // call()메소드 사용하여 다른 명령어 호출
    // call()을 callSilent()로 대체시 출력없이 명령실행
    $this->call('email:send', [  
        'user' => 1, '--queue' => 'default'
    ]);
}
```




# 2. 브로드캐스팅
## 2.1. 시작하기
- 브로드캐스팅
    - 1:N 통신모델. 한 네트워크의 모든 개체와 통신
    - 소켓에 대해 브로드캐스팅 활성화 -> 브로드 캐스트 주소로 데이터 전송
- 웹 소켓을 통해 서버 데이터 변경시(지속적인 폴링을 통해 데이터 변경확인) 메세지를 웹소켓 연결로 전송하여 클라이언트에 의해 처리되도록 함
- 라라벨 이벤트는 채널(공개/비공개에 따라 구독시 인증,승인 필요)을 통해 브로드캐스트
### 2.1.1. 설정하기
- ```config/broadcasting.php```에서 브로드캐스트 드라이버 설정(Pusher Channels, Redis, 디버깅 용도의 log 드라이버, null드라이버)

- 브로드캐스트 서비스 프로바이더
    - ```config/app.php``` - providers배열에서 App\Providers\BroadcastServiceProvider를 사용할 수 있도록 설정
    - 브로드캐스트 인증 라우트와 콜백을 등록할 수 있게 해줌

- CSRF 토큰
    ```php
    <meta name="csrf-token" content="{{ csrf_token() }}">
    ```


### 2.1.2. 드라이버 사전준비사항
- Pusher Channels사용시
    1. Pusher PHP SDK 설치
    2. config/broadcasting.php 설정 파일에서 Channels 인증정보설정
    3. resources/js/bootstrap.js에서 라라벨 Echo인스턴스 초기화시 pusher를 브로드 캐스터로 지정
    (라라벨 에코 : 채널 구독. 라라벨에 의해 브로드캐스트되는 이벤트를 수신하기 쉽게 해주는 자바스크립트 라이브러리)
- Redis 브로드캐스터 사용시
    -  PECL을 통해 phpredis PHP 확장모듈을 설치 or Composer를 통해 Predis 라이브러리 설치 필요
    - 레디스와 웹소켓 서버를 페어링
        - Socket.IO와 페어링시 
            1. Socket.IO 자바스크립트 클라이언트 라이브러리를 인클루드(npm 사용)
            2. socket.io 커넥터와 host로 Echo를 초기화 
            3. 호환되는 Socket.IO 서버를 실행
- 큐를 사용하므로 큐설정이 되어 있어야 함

## 2.2. 컨셉 개요
### 2.2.1. 예제 애플리케이션
1. 이벤트발생
2. ShouldBroadcast 인터페이스
    - broadcastOn() : 이벤트 전송될 채널을 반환
3. 채널 인증(비공개채널인 경우)
    - routes/channels.php 인증규칙 정의
    - Broadcast::channel() 메소드에 인증된 사용자, 채널명 및 채널 구독을 위한 인증여부를 반환하는 콜백을 인자로 전달하며 호출
4. 이벤트 브로드캐스트 수신
    - 클라이언트 측 js에서 이벤트 수신
    - Echo의 private() : 비공개 채널 수신
    - Echo의 listen() : ShippingStatusUpdated 이벤트 수신

    
## 2.3. 브로드캐스트 이벤트 정의
- Illuminate\Contracts\Broadcasting\ShouldBroadcast 인터페이스 구현
### 2.3.1. 브로드캐스트 이름
- 기본적으로 이벤트 클래스명으로 이벤트를 브로드캐스팅
- 이벤트에 broadcastAs()로 브로드캐스트 이름 재정의 가능
    - 리스너 등록 필요
### 2.3.2. 브로드캐스트 데이터
- 이벤트의 public 속성은 자동으로 시리얼라이즈,브로드캐스트,클라이언트에서 접근 가능
- broadcastWith() 에서 데이터 조작가능
### 2.3.3. 브로드캐스트 큐
- 기본적으로  queue.php에서 설정한 큐에 브로드캐스트 이벤트 저장됨
- 이벤트클래스 - broadcastQueue속성정의하여 큐 재설정가능
    - ShouldBroadcast대신 ShouldBroadcastNow인터페이스 구현 필요
### 2.3.4. 조건부 브로드캐스트 
이벤트 클래스 - broadcastWhen() 정의



## 2.4. 채널승인
- 비공개 채널인 경우 사용자가 채널 구독 가능한지 검사
    - 채널명을 포함하여 HTTP요청을 생성 -> 애플리케이션에서 검사
    - Laravel Echo를 통해 자동으로 요청생성 가능
    - 응답(승인)라우트는 직접 정의해야 함
### 2.4.1. 승인 라우트 정의
- BroadcastServiceProvider에서 Broadcast::routes() 호출
     -  /broadcasting/auth(승인요청을 처리하는 라우트)가 등록됨
     - 자동으로 라우트를 WEB미들웨어 그룹에 위치시킴
        - 속성 커스텀시 Broadcast::routes()에 속성배열 전달
### 2.4.2. 승인 콜백 정의
- routes/channels.php 파일 - Broadcast::channel()에 승인 처리 콜백 전달
- 모델 바인딩시 콜백의 파라미터로 타입힌트
- Broadcast::channel()에 세번째 인자로 인증가드 지정하여 콜백에 인증가드가 적용되게 할 수 있음

### 2.4.3. 채널 클래스 정의
- 채널에 승인 콜백대신 채널클래스 사용가능
    1. Artisan make:channel 명령어로 채널클래스 생성
        -  App / Broadcasting에 채널 클래스 생성됨
    2. routes/channels.php에 생성된 채널 등록
    3. 채널클래스의 join()에 해당 채널 인증에 관련된 로직을 작성



## 2.5. 이벤트 브로드캐스팅 
- event() 함수로 이벤트 발생시킴
    - 이벤트가 ShouldBroadcast인터페이스를 통해 표시되었음을 알리고 브로드 캐스트를 위해 이벤트를 큐에 저장 
### 2.5.1. 
- event() 대신 broadcast() 사용가능
- toOthers() 브로드 캐스트 수신자에서 현재 사용자를 제외시킴



## 2.6. 브로드캐스트 수신
### 2.6.1. 라라벨 에코 설치
1. npm으로 에코 설치. Pusher Channels 브로드캐스터사용시 pusher-js도 설치.
2. js에서 에코인스턴스 생성 (resources/js/bootstrap.js 파일 하단에 생성하는 것을 권장)

### 2.6.2. 이벤트 리스닝
```javascript
Echo.channel('orders') // channel() : 채널 인스턴스 받기
    .listen('OrderShipped', (e) => { // listen() : 특정이벤트 수신 // private() : 비공개 채널 이벤트 수신
        console.log(e.order.name);
    });
```
### 2.6.3. 채널 나가기
```javascript
Echo.leaveChannel('orders');
Echo.leave('orders'); // 현재채널과 연관된 비공개채널도 나가려면 
```
### 2.6.4. 네임스페이스
- 에코는 자동으로 이벤트가 App\Events 네임스페이스에 위치하고 있다고 가정
- namespace 임의지정가능
    - 에코 초기화시 namespace속성으로 지정
    - 이벤트 구독시 지정
        ```javascript
        Echo.channel('orders')
        .listen('.Namespace\\Event\\Class', (e) => {
            // 정규화된 클래스명시 
        });
        ```
## 2.7. 프레젠스 채널
- 비공개 채널을 누가 구독하고 있는지 알려주는 기능 추가해줌

### 2.7.1. 프레젠스 채널 승인하기
- 프레젠스 채널은 비공개 채널이기 때문에 프레젠스에 접근하기 위해 사용자는 승인을 받아야 함(채널 승인관련 내용 ## 2.4. 채널승인 참조)

### 2.7.2. 프레젠스 채널에 들어가기
```javascript
Echo.join(`chat.${roomId}`) // PresenceChannel(listen()을 노출)인스턴스를 반환
    .here((users) => { // 현재 채널 구독중인 다른 모든 사용자들의 배열 수신
    })
    .joining((user) => { // 새 사용자가 채널 구독시 실행
        console.log(user.name);
    })
    .leaving((user) => { // 사용자가 채널을 떠날 때 실행
        console.log(user.name);
    });
```
### 2.7.3. 프레젠스 채널에 브로드캐스트
- 이벤트의 broadcastOn()에서 PresenceChannel인스턴스를 반환하여 이벤트를 프레젠스 채널에 브로드 캐스트


## 2.8. 클라이언트 이벤트
- 라라벨 애플리케이션이 아닌 다른 클라이언트에 이벤트를 브로드캐스트하는 경우 
    ```javascript
    Echo.private('chat')
        .whisper('typing', { //클라이언트 이벤트를 브로드 캐스트
            name: this.user.name
        }); // 타이핑 중인 것을 다른 사용자에 알릴 때
        .listenForWhisper('typing', (e) => { // 클라이언트이벤트 수신
            console.log(e.name);
        });
    ```
## 2.9. 알림
```javascript
Echo.private(`App.User.${userId}`)
    .notification((notification) => { // notification()으로 브로드캐스트 이벤트 수신
        console.log(notification.type);
    });
```



# 3. 캐시
## 3.1. 설정하기
- 라라벨이 캐시 API를 제공
- config/cache.php에서 캐시 드라이버 지정 
- Memcached 나 Redis 같은 캐시시스템 지원 (기본은 file캐시 드라이버)


### 3.1.1. 드라이버 사전 준비사항
- DB스키마 구성
    - Schema::create()
    - php artisan cache:tableArtisan로도 스키마 마이그레이션 생성가능

- Memcached 드라이버 사용시
    - Memcached PECL 패키지 설치
    - config/cache.php 설정 파일안에서 Memcache서버 지정
- Redis 드라이버 사용시
    - PECL을 통해서 PhpRedis PHP Extension 설치 or
    컴포저로 predis/predis패키지 설치

## 3.2. 캐시 사용법
### 3.2.1. 캐시 인스턴스 획득하기
- contracts로 접근
    - Illuminate\Contracts\Cache\Factory : 정의된 모든 캐시 드라이버 제공
    - Illuminate\Contracts\Cache\Repository : cache설정 파일에서 기본으로 설정된 캐시드라이버의 구현체
- Cache 파사드로 접근
    - Cache::get('key')
    - store() : 여러개의 캐시 스토어에 접근 가능
        ```php
        Cache::store('redis')->get('stores 배열에 들어 있는 store 중 하나');
        ```
### 3.2.2. 캐시 아이템 조회
```php
$value = Cache::get('key');
$value = Cache::get('key', 'default');
$value = Cache::get('key', function () {
    return DB::table(...)->get();
});
if (Cache::has('key')) {
}
Cache::increment('key');
Cache::increment('key', $amount);
Cache::decrement('key');
Cache::decrement('key', $amount);

// users아이템 없으면 DB에서 기본값 가져와서 저장
$value = Cache::remember('users', $seconds, function () {
    // rememberForever() : 영원히 기억
    return DB::table('users')->get();
});

$value = Cache::pull('key');// 조회하고 삭제(아이템 없으면 null)
```
### 3.2.3. 캐시 아이템 저장하기
```php
// 아이템 저장
Cache::put('key', 'value', $seconds); 
Cache::put('key', 'value', now()->addMinutes(10));
Cache::put('key', 'value'); // 아이템 무기한 저장

// 아이템 존재하지 않으면 저장
Cache::add('key', 'value', $seconds); 

// 아이템 영구저장 (자동만료되지 않음)
Cache::forever('key', 'value');
```
### 3.2.4. 캐시 아이템 삭제하기
```php
// 특정캐시 삭제
Cache::forget('key');
// TTL 지정하여 삭제
Cache::put('key', 'value', 0);
Cache::put('key', 'value', -5);
// 전체캐시 삭제
Cache::flush();
```
### 3.2.5. 원자 잠금장치 (Atomic-locks)
- Atomic-locks : 경쟁조건에 대한 걱정없이 분산된 lock을 설정할 수 있게 함
    - Laravel Forge : Atomic-locks를 사용해 한 번에 하나의 원격작업만 서버에서 실행되도록 함
- 사용시 전제조건
    - memcached, dynamodb 또는 redis 캐시 드라이버사용
    - 모든 서버는 동일한 중앙캐시 서버와 통신
- Cache::lock() 메소드 사용
    ```php
    Cache::lock('foo')->get(function () {
        // Lock acquired indefinitely 
        // and automatically released...
    });
    ```
- 프로세스 간 잠금관리
    - 요청 중에 잠금을 획득하고 해당 요청으로 인해 대기상태가 된 작업이 끝날 때 잠금을 해제하려 할 때
        - 잠금범위가 지정된 소유자 토큰을 대기중인 작업에 전달
        - 작업이 토큰을 이용해 잠금을 다시 인스턴스화
            ```php
            // Within Controller...
            $podcast = Podcast::find($id);
            $lock = Cache::lock('foo', 120); // 캐시 잠금
            if ($result = $lock->get()) {
                ProcessPodcast::dispatch($podcast, $lock->owner()); // 소유자 토큰을 job에 전달
            }

            // Within ProcessPodcast Job...
            // 토큰을 확인해 잠금해제
            Cache::restoreLock('foo', $this->owner)->release();
            ```
            ```php
            // 현재 소유자를 무시하고 잠금해제시 
            Cache::lock('foo')->forceRelease();
            ```


### 3.2.6. 캐시 헬퍼 함수
```php
cache(['key' => 'value'], $seconds);
cache(['key' => 'value'], now()->addMinutes(10));
cache()->remember('users', $seconds, function () {
    return DB::table('users')->get();
}); // cache() : Illuminate/Contracts/Cache/Factory구현 인스턴스 반환 (모든 캐싱 메소드 호출가능)
```



## 3.3. 캐시 태그
- 캐시태그 : 다수의 캐시 아이템을 태그로 묶어 관리하게 해줌
- file과 database드라이버에서는 지원불가
### 3.3.1. 태그가 추가된 캐시 아이템 저장하기
```php
// tags(태그배열)->put(캐시key, 캐시value, 캐시유효시간)
Cache::tags(['people', 'artists'])->put('John', $john, $seconds);
Cache::tags(['people', 'authors'])->put('Anne', $anne, $seconds);
```
### 3.3.2. 태그로 캐시 아이템 엑세스하기
```php
$john = Cache::tags(['people', 'artists'])->get('John');
$anne = Cache::tags(['people', 'authors'])->get('Anne');
```
### 3.3.3. 태그가 추가된 캐시 아이템 삭제하기
```php
// 'people' or 'artists' 로 태그된 John, Anne아이템 모두 삭제
Cache::tags(['people', 'authors'])->flush();
Cache::tags('authors')->flush(); // authors로 태그된 Anne만 삭제
```
## 3.4. 사용자 정의 캐시 드라이버 추가하기
### 3.4.1. 드라이버 작성하기
1. Illuminate\Contracts\Cache\Store contract 구현
2. 각 메소드 구현

### 3.4.2. 드라이버 등록하기
1. 서비스프로바이더에서 Cache::extend()로 등록 
    - App\Providers\AppServiceProvider의 boot()에서
    - extension 을 제공하는 고유한 서비스 프로바이더의 boot()에서
        ```php
        public function boot()
        {
            // extend(드라이버명,Illuminate\Cache\Repository를 반환하는 클로저)
            Cache::extend('mongo', function ($app) {  // $app은 서비스컨테이너인스턴스
                return Cache::repository(new MongoStore);
            });
        }
        ```
2. config/cache.php에서 driver옵션을 추가한 드라이버명으로 변경


## 3.5. 이벤트
- 캐시 동작시 특정 코드가 실행되기를 원할 때 사용
- 캐시 이벤트 리스너를 등록해야 함
    ```php
    // EventServiceProvider에 구성
    protected $listen = [
        'Illuminate\Cache\Events\CacheHit' => [ 
            'App\Listeners\LogCacheHit',
        ], // 이벤트 => 리스너

        // ...
    ];
    ```




# 4. 컬렉션
## 4.1. 시작하기
- Illuminate\Support\Collection 클래스
    - 배열 데이터 사용을 위한 wrapper를 제공
    - 편리한 매핑, 배열 조작을 위한 메소드 체이닝 가능
    - 컬렉션 인스턴스는 immutable (컬렉션 메소드는 새로운 컬렉션 인스턴스 반환)
### 4.1.1. 컬렉션 생성하기
- collect() 헬퍼함수를 이용하여 생성
    ```php
    $collection = collect([1, 2, 3]); 
    // Illuminate\Support\Collection 인스턴스 반환
    ```
### 4.1.2. 컬렉션 확장(상속)하기
- 런타임에 Collection클래스에 메소드를 추가할 수 있음(macroable함)
- 서비스 프로바이더에서 추가 
    ```php
    use Illuminate\Support\Str;
    // Collection 클래스에 toUpper() 메소드 추가
    Collection::macro('toUpper', function () {
        return $this->map(function ($value) {
            return Str::upper($value);
        });
    });

    $collection = collect(['first', 'second']);

    $upper = $collection->toUpper();

    // ['FIRST', 'SECOND']
    ```
## 4.1. 사용 가능한 메소드
//...
## 4.2. Higher Order Messages
- 컬렉션 인스턴스 내 각 요소에 공통된 작업 수행시 사용
- 컬렉션 인스턴스의 동적속성에 접근가능
- 사용가능한 컬렉션 메소드가 정해져 있음
    ```php
    $users = User::where('group', 'Development')->get();
    return $users->sum->votes; // $users 컬렉션의 전체 투표수 확인
    ```

## 4.3. 지연 컬렉션-Lazy Collections
### 4.3.1. 시작하기
- 메모리 사용량을 적게 유지하면서도 매우 큰 데이터 셋을 처리할 수 있게 함
- PHP의 generators를 이용함
    ```php
    use App\LogEntry;
    use Illuminate\Support\LazyCollection;

    // LazyCollection 생성하여 로그를 파싱
    // LazyCollection::make()에 php제너레이터 함수를 전달
    LazyCollection::make(function () {
        $handle = fopen('log.txt', 'r');

        while (($line = fgets($handle)) !== false) {
            yield $line; // yield키워드를 포함시켜 제너레이터 함수로 사용
        }
    })->chunk(4)->map(function ($lines) { // chunk로 분할하여 후
        return LogEntry::fromLines($lines); // 로그의 한 라인만 메모리에 로드하여 작업
    })->each(function (LogEntry $logEntry) {
        // Process the log entry...
    });
    ```
    ```php
    /*
    $users = App\User::all()->filter(function ($user) {
        return $user->id > 500;
    });
    */
    // LazyCollection 인스턴스를 반환하는 쿼리빌더의 cursor() :
    // DB에서 단 하나의 쿼리만 실행, 한 번에 하나의 Eloquent모델만 메모리에 로드.
    // filter() 콜백 반복할 때까지 실행되지 않음
    $users = App\User::cursor()->filter(function ($user) {
        return $user->id > 500;
    });
    foreach ($users as $user) {
        echo $user->id;
    }
    ```
### 4.3.2. 열거형 Contract
- Collection클래스에서 사용가능한 메소드들은 대부분
LazyCollection클래스에서도 사용가능
- Illuminate\Support\Enumerable contract에 구현


# 5. 이벤트 (& 리스너)
## 5.1. 시작하기
- 라라벨의 이벤트는 옵저버 구현을 제공하여 이벤트리스닝을 할 수 있게 해줌
- 이벤트클래스는 보통 app/Events에 저장
- 리스너클래스는 보통 app/Listeners에 저장
- 하나의 이벤트는 서로 의존하지 않는 리스너들을 가질 수 있음
(애플리케이션 레이어들이 서로 의존하지 않게 할 수 있음) 

## 5.2. 이벤트 & 리스너 등록하기
- EventServiceProvider의 listen속성에 등록
- 이벤트 (key) - 리스너 (value) 의 배열을 가짐
    ```php
    protected $listen = [
        'App\Events\OrderShipped' => [
            'App\Listeners\SendShipmentNotification',
        ],
    ];
    ```

### 5.2.1. 이벤트 & 리스너 생성하기
- artisan명령어로 EventServiceProvider에 포함된 이벤트,리스너 클래스를 생성
    ```bash
    $ php artisan event:generate
    ```


### 5.2.2. 수동으로 이벤트 등록하기
- EventServiceProvider의 listen배열 대신 boot()메소드 내에 클로저 기반 이벤트를 수동으로 등록 
    ```php
    public function boot()
    {
        parent::boot();
        Event::listen('event.name', function ($foo, $bar) {
        }); // 이벤트 이름, 전체 이벤트 데이터배열을 인자로 받음
        
        // 복수의 이벤트를 리슨하는 경우
        //Event::listen('event.*', function ($eventName, array $data) {
        });
    }
    ```


### 5.2.3. 이벤트 discovery
- 라라벨이 이벤트를 자동으로 discovery할 수도 있음
- EventServiceProvider클래스에서 이벤트 discovery 활성화 후 사용가능
    ```php
    public function shouldDiscoverEvents()
    {
        return true; // 오버라이드해서 변경가능
    }
    ```
- Listeners디렉토리를 검색하여 이벤트, 리스너를 자동으로 등록
    - 검사디렉토리 변경시
        ```php
        //EventServiceProvider클래스
        protected function discoverEventsWithin()
        {
            return [
                $this->app->path('Listeners'), // 오버라이드해서 변경가능
            ];
        }
        ```
- handle로 시작하는 리스너 클래스 메소드 발견시, 해당 메소드를 타입힌트된 이벤트에 대한 이벤트리스너로 등록함
    ```php
    use App\Events\PodcastProcessed;
    class SendPodcastProcessedNotification
    {
        public function handle(PodcastProcessed $event)
        { // PodcastProcessed에 대한 이벤트리스너로 등록됨
        }
    }
    ```
## 5.3. 이벤트 정의
- 이벤트 클래스는 별도의 로직을 가지지 않음
    ```php
    namespace App\Events;

    use App\Order;
    use Illuminate\Queue\SerializesModels;

    class OrderShipped
    {
        use SerializesModels; 
        // 이벤트가 사용하는 속성. 
        // 이벤트 객체가 serialize() 될 경우
        // Eloquent모델도 serialize하게됨

        public $order;

        public function __construct(Order $order)
        {
            $this->order = $order;
        }
    }
    ```



## 5.4. 리스너 정의
- 리스너클래스의 handle메소드
    - 이벤트 인스턴스를 전달받음
    - 이벤트 대응하기 위한 액션을 구성하는 곳
    - false반환시 이벤트가 다른 리스너에 전달되는 것을 중단



## 5.5. Queue로 이벤트리스너 처리하기
- 이메일발송, HTTP request생성 같은 오래 걸리는 작업 수행시 유용
- Queue로 이벤트리스너 처리
    - 리스너클래스가 ShouldQueue인터페이스를 구현하도록 추가
        ```php
        namespace App\Listeners;

        use App\Events\OrderShipped;
        use Illuminate\Contracts\Queue\ShouldQueue;

        class SendShipmentNotification implements ShouldQueue
        {
        }
        ```
- 리스너가 이벤트를 통해 호출시, 
queue를 이용하는 이벤트 dispatcher에 의해 자동으로 queue에 저장됨

- 큐 커넥션과 큐 이름 커스터마이징 하기
    - 리스너 클래스에 $connection, $queue 또는 $delay 속성을 정의
- 조건부 대기열
    - shouldQueue()메소드를 정의하여 조건에 따라 Queueing여부를 결정가능

### 5.5.1. 수동으로 큐에 접근하기
- use Illuminate\Queue\InteractsWithQueue 
- InteractsWithQueue트레이트의 delete(), release()메소드를 통해 큐에 수동으로 액세스 가능

### 5.5.2. 실패한 job처리하기
- 큐를 통한 리스너가 queue worker에 정읜 재시도 횟수 초과시
- failed() 메소드 호출 (Exception을 인자로 받음) 






## 5.6. 이벤트 처리
- event() 호출하여 이벤트리스너에 이벤트인스턴스를 전달
    ```php
    class OrderController extends Controller 
    {
        public function ship($orderId)
        {
            $order = Order::findOrFail($orderId);
            event(new OrderShipped($order));
        }
    }
    ```



## 5.7. 이벤트 Subscribers
- 단일 Subscriber클래스 내에서 복수의 이벤트 핸들러 정의를 가능하게 함
### 5.7.1. 이벤트 Subscribers 작성
- subscribe() 메소드를 통해 event dispatcher인스턴스를 전달받음
- event dispatcher에서 listen()메소드를 호출하여 event listener 등록
```php
namespace App\Listeners;

class UserEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function handleUserLogin($event) {}

    /**
     * Handle user logout events.
     */
    public function handleUserLogout($event) {}

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Illuminate\Auth\Events\Login', // 이벤트
            'App\Listeners\UserEventSubscriber@handleUserLogin' // 리스너
        );

        $events->listen(
            'Illuminate\Auth\Events\Logout',
            'App\Listeners\UserEventSubscriber@handleUserLogout'
        );
    }
}
```
### 5.7.2. 이벤트 Subscribers 등록
- EventServiceProvider에 subscriber를 등록 ($subscribe 속성)
    ```php
    class EventServiceProvider extends ServiceProvider
    {
        // ... 
        protected $subscribe = [
            'App\Listeners\UserEventSubscriber',
        ];
    }
    ```

# 6. 파일스토리지
## 6.1. 시작하기
- 라라벨의 파일 시스템
    - Flysystem패키지 기반의 추상화된 파일 시스템 제공
    - 각 시스템에 대한 동일한 API를 사용하여 스토리지를 쉽게 변경가능
## 6.2. 설정하기
- config/filesystems.php에서 설정
- 각 디스크에 대한 스토리지 드라이버, 스토리지 위치 지정가능
### 6.2.1. Public 디스크 
- 누구나 접근가능한 디스크
- local 드라이버 사용
- storage/app/public에 파일 저장
- 웹에서의 접근을 위해서는
    - public/storage에 대해 storage/app/public 으로 심볼릭링크를 생성해야 함
        ```bash
        $ php artisan storage:link
        ```
    - asset() 헬퍼로 파일URL생성
        ```php
        asset('storage/file.txt');
        ```

### 6.2.2. 로컬 드라이버
- config/filesystems.php에 설정된 root를 기준으로 파일이 조작됨
- 기본 root directory는 storage/app
    ```php
    Storage::disk('local')->put('file.txt', 'Contents');
    // storage/app/file.txt에 저장됨
    ```
- 권한
    - config/filesystems.php에서 directory, file에 대한 권한 수정가능


### 6.2.3. 드라이버 사용시 준비사항
- 필요한 컴포저 패키지
    - SFTP 사용시 : league/flysystem-sftp ~1.0
    - Amazon S3 사용시 : league/flysystem-aws-s3-v3 ~1.0
- 캐싱된 어댑터 사용시 : league/flysystem-cached-adapter ~1.0를 추가로 설치

### 6.2.4. 캐싱
- 디스크옵션 설정시 각 디스크에 cache지시어 추가
    ```php
    's3' => [
        'driver' => 's3',

        // Other Disk Options...

        'cache' => [
            'store' => 'memcached',
            'expire' => 600,
            'prefix' => 'cache-prefix',
        ],
    ],
    ```

## 6.3. Disk 인스턴스 획득하기
- Storage파사드 사용
    ```php
    use Illuminate\Support\Facades\Storage;
    
    /*기본 설정된 디스크에 접근*/
    Storage::put('avatars/1', $fileContents)

    /* 특정 디스크에 접근 */
    Storage::disk('s3')->put('avatars/1', $fileContents);
    ```
- 
## 6.4. 파일 조회하기
```php
$contents = Storage::get('file.jpg'); // 파일내용반환
$exists = Storage::disk('s3')->exists('file.jpg'); //존재확인
$missing = Storage::disk('s3')->missing('file.jpg'); //누락확인
```
### 6.4.1. 파일 다운로드
```php
//  Storage::download() : 다운로드를 수행하는 response 생성
return Storage::download('file.jpg');
return Storage::download('file.jpg', $filename, $headers);
```
### 6.4.2. 파일 URL
- 파일 url생성 메소드
    ```php
    // url()
    use Illuminate\Support\Facades\Storage;
    $url = Storage::url('file.jpg'); // 파일url 반환
    // s3드라이버 사용시 - 전체 URL반환
    // 로컬 드라이버 사용시 - 6.2.1. Public 디스크 내용 참조


    // temporaryUrl()은 임시URL생성
    $url = Storage::temporaryUrl(
        'file.jpg',
        now()->addMinutes(5),
        ['ResponseContentType' => 'application/octet-stream']
    );
    ```
- 로컬 URL호스트 커스텀
    ```php
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'), 
        'url' => env('APP_URL').'/storage', // url옵션 변경
        'visibility' => 'public',
    ],
    ```
### 6.4.3. 파일의 메타 데이터
- Storage::size('file.jpg');
- Storage::lastModified('file.jpg'); // 마지막에 파일이 업데이트되었을 때의 UNIX 타임 스탬프값

## 6.5. 파일 저장하기
- 기본 저장 메소드
    ```php
    use Illuminate\Support\Facades\Storage;

    /* 파일이 내용을 디스크에 저장 */
    Storage::put('file.jpg', $contents);
    /* 파일시스템의 스트림을 사용 */
    Storage::put('file.jpg', $resource);
    ```

- 자동 스트리밍
    - Illuminate\Http\File 혹은 Illuminate\Http\UploadedFile 인스턴스를 인자로 받아 자동으로 명시한 위체 파일을 스트림 처리함
        ```php
        use Illuminate\Http\File;
        use Illuminate\Support\Facades\Storage;

        // Automatically generate a unique ID for file name...
        Storage::putFile('photos', new File('/path/to/photo')); 
        // 저장경로 반환됨

        // Manually specify a file name...
        Storage::putFileAs('photos', new File('/path/to/photo'), 'photo.jpg');


        // visibility지정가능
        Storage::putFile('photos', new File('/path/to/photo'), 'public');
        ```
- 기타
    ```php
    // 내용추가
    Storage::prepend('file.log', 'Prepended Text');
    Storage::append('file.log', 'Appended Text');

    // 복사/이동
    Storage::copy('old/file.jpg', 'new/file.jpg');
    Storage::move('old/file.jpg', 'new/file.jpg');
    ```
### 6.5.1. 파일 업로드
- 
    ```php
    class UserAvatarController extends Controller
    {
        public function update(Request $request)
        {
            /* request의 store()메소드 호출시 */
            // store()에는 경로를 인자로 주어야 함
            $path = $request->file('avatar')->store('avatars');
            // 파일이름 지정
            $path = $request->file('avatar')->storeAs(
                'avatars', $request->user()->id
            );
            // 디스크지정
            $path = $request->file('avatar')->store(
                'avatars/'.$request->user()->id, 's3'
            );


            /* Storage 파사드의 putFile 메소드를 호출시 */
            $path = Storage::putFile('avatars', $request->file('avatar'));
            // 파일이름지정
            $path = Storage::putFileAs(
                'avatars', $request->file('avatar'), $request->user()->id
            );

            return $path;
        }
    }
    ```
### 6.5.2. 파일 Visibility
```php
use Illuminate\Support\Facades\Storage;

/* 파일 저장시 지정 */
Storage::put('file.jpg', $contents, 'public');

/* 파일이 이미 저장된 경우 */
$visibility = Storage::getVisibility('file.jpg');
Storage::setVisibility('file.jpg', 'public');
```

## 6.6. 파일 삭제하기
```php
use Illuminate\Support\Facades\Storage;

Storage::delete('file.jpg');
Storage::delete(['file.jpg', 'file2.jpg']);
Storage::disk('s3')->delete('folder_path/file_name.jpg');
```


## 6.7. 디렉토리들
```php
use Illuminate\Support\Facades\Storage;

// 디렉토리 안의 모든 파일들 확인
$files = Storage::files($directory);
$files = Storage::allFiles($directory); // 하위디렉토리 목록포함


// 하위 디렉토리들 확인
$directories = Storage::directories($directory);
$directories = Storage::allDirectories($directory);// Recursive...

// 디렉토리 생성하기
Storage::makeDirectory($directory);

// 디렉토리 삭제하기
Storage::deleteDirectory($directory);
```


## 6.8. 사용자 정의 파일 시스템
- 사용자 정의 파일 시스템 구성을 위해 필요한 것 (e.g. 드롭박스 어댑터)
    1. FlySystem 패키지
    2. FlySystem 어댑터
        ```bash
        $ composer require spatie/flysystem-dropbox
        ```
    3. ServiceProvider
        ```php
        namespace App\Providers;

        use Illuminate\Support\ServiceProvider;
        use League\Flysystem\Filesystem;
        use Spatie\Dropbox\Client as DropboxClient;
        use Spatie\FlysystemDropbox\DropboxAdapter;
        use Storage;

        class DropboxServiceProvider extends ServiceProvider
        {
            public function register()
            {
            }
            public function boot()
            {
                // Storage::extend() 로 커스텀 드라이버 정의
                Storage::extend('dropbox', function ($app, $config) {
                    $client = new DropboxClient(
                        $config['authorization_token']
                    );

                    return new Filesystem(new DropboxAdapter($client));
                });
            }
        }
        ```
    4. 서비스프로바이더 등록 (config/app.php)
        ```php
        'providers' => [
            // ...
            App\Providers\DropboxServiceProvider::class,
        ];
        ```
    5. config/filesystems.php 에서 등록한 dropbox드라이버 사용
        



# 7. 헬퍼 함수



# 8. 메일
## 8.1. 시작하기
- 라라벨은 SwiftMailer를 통해 단순한 API 제공
- SMTP, Mailgun, Postmark, 아마존 SES, sendmail 메일드라이버 제공
### 8.1.1. 드라이버 사전 준비사항
- guzzlehttp 라이브러리
- config/mail.php에서 메일드라이버 설정

## 8.2. Mailables 생성하기
- mailable클래스로 라라벨에서 이메일발송처리 
- make:mail명령어로 mailable클래스 생성
    - app/Mail 디렉토리 내 mailable클래스가 위치하게 됨

## 8.3. Mailable클래스 작성하기
- build() 메소드 
    - mailable클래스설정
    - build() 메소드 내에서 from(), subject(), view(), attach() 같은 이메일 형태, 발송에 대해 설정하는 메소드 사용가능
### 8.3.1. 발송자 설정하기
```php
public function build()
{
    return $this->from('example@example.com')
                ->view('emails.orders.shipped');
}
```
### 8.3.2. View-뷰 설정하기
- 컨텐츠 렌더링시 사용할 템플릿을 지정
    ```php
    public function build()
    {
        return $this->view('emails.orders.shipped');
       //  ->text('emails.orders.shipped_plain'); // 텍스트 전용 이메일인 경우
    }
    ```
### 8.3.3. 뷰 데이터
- 뷰에 데이터 전달하는 방법1. 
    - mailable클래스 생성자에 데이터를 전달하여
    , public속성에 저장 (자동으로 뷰에서 사용가능)
- 뷰에 데이터 전달하는 방법2. 
    - mailable클래스 생성자에 데이터를 전달하는 것은 동일. private혹은 protected속성에 데이터를 저장에야 함.
    - build() 내에서 with() 메소드에 사용할 데이터를 전달(자동으로 뷰에서 사용가능)
        ```php
        public function build()
        {
            return $this->view('emails.orders.shipped')
                        ->with([
                            'orderName' => $this->order->name,
                            'orderPrice' => $this->order->price,
                        ]);
        }
        ```
### 8.3.4. 첨부파일
- attach(), attachFromStorage(), attachFromStorageDisk(), attachData()
### 8.3.5. 인라인 첨부
- 이메일 뷰 안에서 $message 메소드에 embed 메소드를 사용
    ```php
    <img src="{{ $message->embed($pathToImage) }}">
    ```
### 8.3.6. SwiftMailer 메세지 커스터미아징하기
- withSwiftMessage()


## 8.4. 마크다운 Mailables
- 메일 템플릿, 메일 컴포넌트 활용을 가능하게 함
- 원활한 렌더링, 반응형 HTML템플릿, 텍스트 자동생성 가능

### 8.4.1. 마크다운 Mailable 생성하기
- 마크다운 Mailable 클래스 생성
    ```bash
    $ php artisan make:mail OrderShipped --markdown=emails.orders.shipped
    ```
- markdown() 메소드 호출
    ```php
    public function build()
    {
        return $this->from('example@example.com')
                    ->markdown('emails.orders.shipped');
    }
    ```
### 8.4.2. 마크다운으로 메세지 작성하기
9.5.2. 메세지 작성하기 참조

### 8.4.3. 컴포넌트 커스터마이징
9.5.3. 컴포넌트 커스터마이징 하기 참조

## 8.5. 메일 발송
```php
class OrderController extends Controller
{
    public function ship(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        //...
        // Mail::to(메일주소, 사용자인스턴스하나, 사용자들 컬렉션) 메소드 사용
        // ( 자동으로 사용자 객체의 email, name 속성을 가져와서 이메일 수신자로 설정)
        // send(mailable인스턴스)메소드에 수신자 데이터전달하여 발송
        Mail::to($request->user())->send(new OrderShipped($order)); // mailable 클래스의 인스턴스를 send 메소드에 전달
    }
}
```
### 8.5.1. 큐를 통한 메일 처리
- Mail::queue(mailable인스턴스) 메소드 호출
    ```php
    $when = now()->addMinutes(10);

    Mail::to($request->user())
    ->cc($moreUsers)
    ->bcc($evenMoreUsers)
    ->queue(new OrderShipped($order));
    //->later($when, new OrderShipped($order)); // 지연발송시

    /* 지정된 큐로 작업 보내기 */
    $message = (new OrderShipped($order))
                    ->onConnection('sqs')
                    ->onQueue('emails');

    Mail::to($request->user())
        ->cc($moreUsers)
        ->bcc($evenMoreUsers)
        ->queue($message);
    ```
- mailable 클래스가 ShouldQueue를 구현하도록 하기(항상 큐를 통해 처리됨)
    ```php
    use Illuminate\Contracts\Queue\ShouldQueue;

    class OrderShipped extends Mailable implements ShouldQueue
    {
    }
    ```

## 8.6. Mailables 객체 렌더링
```php
$invoice = App\Invoice::find(1);
return (new App\Mail\InvoicePaid($invoice))->render(); // mailable 객체의 render() 메소드 호출하여 렌더링(객체 내용을 연산하여 문자형태로 반환)
```
### 8.6.1. 브라우저에서 Mailable 객체 미리보기
```php
Route::get('mailable', function () {
    $invoice = App\Invoice::find(1);
    return new App\Mail\InvoicePaid($invoice); // 라우트에서 mailabl 객체반환하면 브라우저에 렌더링되어 메일을 미리 볼 수 있음
});
```
## 8.7. Mailables 현지화
- locale(언어코드)메소드 사용
- 사용자 선호 언어
    - 사용자 모델 클래스가 HasLocalePreference contract를 구현하게 함

## 8.8. 메일 & 로컬 개발환경
- 개발환경에서 이메일 발송 비활성화 방법
    - 로그 드라이버 사용
    - 모든 메일의 수신자 고정 (config/mail.php의 to옵션)
    - Mailtrap서비스 사용
## 8.9. 이벤트
- 라라벨에서 이메일이 큐를 통하지 않고 바로 발송시 이벤트 발생
    - MessageSending 이벤트 (메시지 발송 전 발생)
    - MessageSent 이벤트 (메시지 발송 후 발생)
    - EventServiceProvider에서 이벤트 리스터 등록가능 ($listen 속성에 등록)







# 9. 알림
## 9.1. 시작하기
- 라라벨은 이메일, SMS(Nexmo를 통해 제공), 슬랙 등 다양한 드라이버 채널을 통해 알림을 발송하는 기능을 제공
## 9.2. 알림 생성
- 각 알림은 하나의 클래스로 표현
- 알림 클래스 생성 (app/Notifications디렉토리에 생성됨)
    ```bash
    $ php artisan make:notification InvoicePaid
    ```

## 9.3. 알림 발송
### 9.3.1. Notifiable trait 사용하기
```php
// Notifiable트레이트는 App\User모델에서 알림 발송시 사용됨
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable; // Notifiable트레이트 사용
}
```
```php
// Notifiable 트레이트
use App\Notifications\InvoicePaid;
$user->notify(new InvoicePaid($invoice)); // notify() 메소드로 알림을 발송
```

### 9.3.2. Notification 파사드 사용하기
```php
// 사용자 컬렉션과 같은 여러개의 알림 가능한 엔티티들에 대해서 알림을 발송하는데 유용
Notification::send($users, new InvoicePaid($invoice)); // Notification::send() 메소드로 알림 발송
```

### 9.3.3. 전달할 채널 지정하기
- via() 메소드에서 지정
    ```php
    public function via($notifiable) // notifiable인스턴스를 전달받음
    {
        return $notifiable->prefers_sms ? ['nexmo'] : ['mail', 'database'];
        //  mail, database, broadcast, nexmo, slack 채널을 통해 전달 가능
    }
    ```

### 9.3.4. Queue를 통한 Notifications 사용
- 알림전송은 시간 소요가 많음
- 응답속도를 높이기 위해 queue를 통해 알림 구현가능
    - queue설정 후 queue worker를 구동해야 함
    - ShouldQueue 인터페이스, Queueable 트레이트를 알림클래스에 추가해 큐를 통해 알림이 발송되도록 함
        ```php
        namespace App\Notifications;

        use Illuminate\Bus\Queueable;
        use Illuminate\Contracts\Queue\ShouldQueue;
        use Illuminate\Notifications\Notification;

        class InvoicePaid extends Notification implements ShouldQueue // ShouldQueue인터페이스 구현
        {
            use Queueable; // Queueable 트레이트 사용
            //...
        }
        ```
        ```php
        // queue를 사용한 알림발송 가능
        $user->notify(new InvoicePaid($invoice)); 

        // 지연된 알림발송
        $when = now()->addMinutes(10);
        $user->notify((new InvoicePaid($invoice))->delay($when));
        ```
### 9.3.5. 필요시 대상을 지정한 Notifications

```php
// route() 메소드 체이닝
Notification::route('mail', 'taylor@example.com')
            ->route('nexmo', '5555555555')
            ->route('slack', 'https://hooks.slack.com/services/...')
            ->notify(new InvoicePaid($invoice));
```

## 9.4. 이메일을 통한 알림
### 9.4.1. 메세지 포맷 지정하기
- 알림 클래스에서 toMail() 메소드 정의
    ```php
    public function toMail($notifiable)
    {
        $url = url('/invoice/'.$this->invoice->id);

        /*  Illuminate\Notifications\Messages\MailMessage인스턴스 반환 */
        return (new MailMessage) 
                    ->greeting('Hello!')
                    ->line('One of your invoices has been paid!')
                    ->action('View Invoice', $url)
                    ->line('Thank you for using our application!');


        /* view()를 통해 알림이메일 렌더링되는 커스텀 템플릿사용 */
        return (new MailMessage)->view(
            'emails.name', ['invoice' => $this->invoice]
        );           


        /*  Mailable 인스턴스 반환 */
        // return (new Mailable($this->invoice))->to($this->user->email);


        /*  error()메소드 사용 */
        return (new MailMessage)
            ->error()
            ->subject('Notification Subject')
            ->line('...');
    }
    ```
### 9.4.2. Sender 커스터마이징
- 기본적으로 보내는 사람/주소는 config/mail.php 설정파일에 정의되어 있음
```php
public function toMail($notifiable)
{
    return (new MailMessage)
                ->from('test@example.com', 'Example') // from()메소드로 지정가능
                ->line('...');
}
```
### 9.4.3. 수신메일주소 설정하기
```php
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    // routeNotificationForMail()메소드를 알림 엔티티 클래스에 정의하여 수신이메일 주소 설정
    public function routeNotificationForMail($notification)
    {
        return $this->email_address;
    }
}
```
### 9.4.4. 제목 설정하기
```php
public function toMail($notifiable)
{
    return (new MailMessage)
                ->subject('Notification Subject') // 제목설정
                ->line('...');
}
```
### 9.4.5. 템플릿 설정하기
- 알림 템플릿 생성
    ```bash
    $ php artisan vendor:publish --tag=laravel-notifications
    ```
    - resources/views/vendor/notifications 디렉토리에 알림 템플릿 저장됨

### 9.4.6. 메일 알림 미리보기
```php
Route::get('mail', function () {
    $invoice = App\Invoice::find(1);

    return (new App\Notifications\InvoicePaid($invoice))
                ->toMail($invoice->user);
            // MailMessage 반환시 브라우저에 렌더링되어 실제 이메일을 미리보기 할 수 있음
});
```



## 9.5. 마크다운 이메일 알림
- 사전 작성된 메일알림 템플릿 활용가능
- 메세지 렌더링 원활, 반응형 HTML템플릿 사용, 일반텍스트 자동 생성 가능
### 9.5.1. 메세지 클래스 생성하기
- 아티즌 명령어를 통해 마크다운 템플릿으로 알림 클래스 생성
    ```bash
    $ php artisan make:notification InvoicePaid --markdown=mail.invoice.paid
    ```
### 9.5.2. 메세지 작성하기
- 블레이드 컴포넌트, 마크다운 문법을 조합하여 작성
    ```php
    @component('mail::message')
    # Invoice Paid

    Your invoice has been paid!

    @component('mail::button', ['url' => $url, 'color' => 'green'])
    View Invoice
    @endcomponent

    @component('mail::panel') // 주어진 텍스트 블럭 강조
    This is the panel content.
    @endcomponent

    @component('mail::table') // 테이블 컴포넌트는 HTML테이블로 변환됨
    | Laravel       | Table         | Example  |
    | ------------- |:-------------:| --------:|
    | Col 2 is      | Centered      | $10      |
    | Col 3 is      | Right-Aligned | $20      |
    @endcomponent


    Thanks,<br>
    {{ config('app.name') }}
    @endcomponent
    ```


### 9.5.3. 컴포넌트 커스터마이징 하기
- 마크다운 메일 컴포넌트를 resources/views/vendor/mail 디렉토리에 퍼블리싱
    ```bash
    $ php artisan vendor:publish --tag=laravel-mail
    # laravel-mail 에셋태그 지정
    ```
    - 생성된 html, text 디렉토리 내 컴포넌트를 커스텀 가능
- CSS 커스터마이징
    - 컴포넌트 export 후 resources/views/vendor/mail/html/themes 디렉토리에서 default.css파일 확인 가능
        - 마크다운알림의 html에서 css자동 적용
    - html/themes디렉토리에 css파일 추가 가능
        - mail 컴포넌트의 theme옵션을 새 테마의 이름과 동일하도록 수정
    - 개별 알림에 대한 테마 커스텀
        - toMail()메소드 내에서 theme()메소드 사용
            ```php
            public function toMail($notifiable)
            {
                return (new MailMessage)
                            ->theme('invoice')
                            ->subject('Invoice Paid')
                            ->markdown('mail.invoice.paid', ['url' => $url]);
            }
            ```


## 9.6. DB알림
- database 알림채널은 알림정보를 DB에 저장

### 9.6.1. 사전준비사항
- DB 준비
    - 알림을 저장할 db테이블 생성
    - 마이그레이션 생성
        ```php
        $ php artisan notifications:table
        ```
    - 마이그레이션 실행
        ```php
        $ php artisan migrate
        ```

### 9.6.2. 데이터베이스 알림 포맷 지정하기
- 알림클래스에서 toDatabase() 혹은 toArray()메소드 정의 필요
(toArray()를 브로드캐스팅시 사용하는 경우, 여기서는 toDatabase()를 정의해서 사용해야 함)
    ```php
    public function toArray($notifiable) // $notifiable받음
    {
        return [
            'invoice_id' => $this->invoice->id,
            'amount' => $this->invoice->amount,
        ]; // 순수 php배열을 반환해야 함
        // 반환된 배열은 JSON인코딩 되어 notification테이블의 data컬럼에 저장됨
    }
    ```
### 9.6.3. 알림에 엑세스하기
-  App\User모델에서 사용하는 Illuminate\Notifications\Notifiable트레이트는 notifications Eloquent 관계를 가짐(notifications 객체 리턴해줌)
    ```php
    $user = App\User::find(1);

    // notifications Eloquent에 접근 
    // 기본적으로 알림은 created_at 타임스탬프를 기준으로 정렬
    foreach ($user->notifications as $notification) { // 읽지 않은 알림만 조회시 unreadNotifications 사용
        echo $notification->type;
    }
    ```
### 9.6.4. 알림을 읽음 표시로 전환하기
- Illuminate\Notifications\Notifiable트레이트의 markAsRead()
    - 알림 테이블의 read_at컬럼을 업데이트함
        ```php
        $user = App\User::find(1);
        foreach ($user->unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        /* 위의 반복문 대신 아래와 같이 컬렉션에 바로 사용가능 */
        // $user->unreadNotifications->markAsRead();

        /* mass-update 쿼리 사용시 */
        $user = App\User::find(1);
        $user->unreadNotifications()->update(['read_at' => now()]);
        ```
- 테이블에서 알림 삭제
    ```php
    $user->notifications()->delete();
    ```




## 9.7. 알림 브로드캐스팅 
### 9.7.1. 사전 준비사항
- 라라벨 이벤트 브로드캐스팅 설정 (2. 브로드캐스팅 참고)

### 9.7.2. 브로드캐스팅 알림 포맷 지정하기
- 알림을 브로드캐스팅하기 위해 알림 클래스 내 toBroadcast() 정의
    ```php
    use Illuminate\Notifications\Messages\BroadcastMessage;

    public function toBroadcast($notifiable) // notifiable 엔티티 받음
    {
        return new BroadcastMessage([
            'invoice_id' => $this->invoice->id,
            'amount' => $this->invoice->amount,
        ]); // BroadcastMessage인스턴스 반환되고, 이는 JSON으로 인코딩되어 클라이언트로 브로드 캐스팅 됨
    }
    ```
    - toBroadcast() 메소드 없을 경우 toArray() 메소드가 브로드 캐스팅용 데이터 조회시 사용됨

- 브로드 캐스트 큐 설정
    ```php
    return (new BroadcastMessage($data))
                ->onConnection('sqs')
                ->onQueue('broadcasts');
    ```

### 9.7.3. 알림을 위한 리스너 (알림 수신)
- 알림 수신 채널
    - 비공개 채널에 브로드캐스팅되는 알림은 {notifiable}.{id} 컨벤션으로 포맷지정
    -  ID 가 1인 App\User 인스턴스의 알림은 App.User.1 비공개 채널에 브로드캐스팅
- 라라벨 에코 사용시 채널에서 notification() 헬퍼메소드로 알림 수신 가능
    ```php
    Echo.private('App.User.' + userId) // (알림수신채널)
        .notification((notification) => {
            console.log(notification.type);
        });
    ```
- 알림수신채널 커스텀
    -  notifiable entity (e.g. App\User)에서 receivesBroadcastNotificationsOn() 메소드를 정의
        ```php
        public function receivesBroadcastNotificationsOn()
        {
            return 'users.'.$this->id;
        }
        ```




        
## 9.8. SMS알림
- 라라벨에서는 Nexmo사용하여 SMS알림 전송

### 9.8.1. 사전준비사항
1. laravel/nexmo-notification-channel 컴포저 패키지 설치
    - nexmo/laravel 패키지도 설치됨
        - 자체설정파일포함
        - NEXMO_KEY 및 NEXMO_SECRET 환경 변수를 사용해 Nexmo 공개 및 비밀 키 설정
2. config/services.php 설정파일에 설정옵션 추가
    ```php
    'nexmo' => [
        'sms_from' => '15556666666', //  SMS 메세지가 전송되는 전화번호
        // Nexmo 설정 패널에서 애플리케이션의 전화번호를 생성해야 함
    ],
    ```

### 9.8.2. SMS 알림 포맷 지정하기
- 알림 클래스에서 toNexmo() 메소드 정의해야 함
    ```php
    public function toNexmo($notifiable) // notifiable엔티티 받음
    {
        // Illuminate\Notifications\Messages\NexmoMessage인스턴스 반환
        return (new NexmoMessage)
                    ->content('Your SMS message content');



        /** sms에 유니코드 문자 포함시 **/
        /*
        return (new NexmoMessage)
            ->content('Your unicode message')
            ->unicode(); // unicode() 메소드 호출 필요
        */
    }
    ```

### 9.8.3. Shortcode 알림 포맷 지정하기
- shortcode :  Nexmo 계정에서 미리 정의된 메시지 템플릿
- shortcode 지정
    ```php
    public function toShortcode($notifiable)
    {
        return [ 
            'type' => 'alert', // 알림 유형
            'custom' => [ // 사용자 정의 값
                'code' => 'ABC123',
            ];
        ];
    }
    ```


### 9.8.4. 발신자 번호 지정하기
- config/services.php에 지정된 발신자 번호와 다른 번호 사용시
- NexmoMessage인스턴스의 from() 메소드 사용

### 9.8.5. SMS 알림 라우팅 (수신자 번호 지정)
- 알림 사용할 엔티티에 routeNotificationForNexmo() 메소드 정의
     ```php
     namespace App;

    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;

    class User extends Authenticatable
    {
        use Notifiable;

        // Nexmo채널로 알림을 라우팅
        public function routeNotificationForNexmo($notification)
        {
            return $this->phone_number; // 수신번호 지정 //엔티티 (현재 user모델)의 정보 사용
        }
    }
     ```




## 9.9. slack 알림
### 9.9.1. 사전준비사항
- 슬랙 알림 채널 패키지 설치
    ```bash
    $ composer require laravel/slack-notification-channel
    ```
- 슬랙 팀에서 "Incoming Webhook" intergration설정 (슬랙 알림 전송시 사용할 URL을 제공해줌)

### 9.9.2. Slack 알림 포맷 지정하기
- 알림클래스에 toSlack() 메소드 정의
    ```php
    public function toSlack($notifiable) // notifiable 엔티티 받음
    {
        // Illuminate\Notifications\Messages\SlackMessage 인스턴스 반환
        return (new SlackMessage)
                ->from('Ghost', ':ghost:') // 발신자지정 (사용자이름, 이모지 식별자)
                ->to('#other') // 수신자지정 (채널 or 사용자이름)
                ->content('This will be sent to #other');
    }
    ```

### 9.9.3. 슬랙 첨부파일
```php
public function toSlack($notifiable)
{
    $url = url('/exceptions/'.$this->exception->id);

    return (new SlackMessage)
                ->error()
                ->content('Whoops! Something went wrong.')
                ->attachment(function ($attachment) use ($url) {
                    $attachment->title('Exception: File Not Found', $url)
                               ->content('File [background.jpg] was not found.');

                                /* // 테이블 스타일로 출력하기

                                ->fields([
                                                    'Title' => 'Server Expenses',
                                                    'Amount' => '$1,234',
                                                    'Via' => 'American Express',
                                                    'Was Overdue' => ':-1:',
                                                ]);
                                */

                                /* // 첨부파일 필드 중 마크다운이 포함되어 있는 경우
                                주어진 마크다운 텍스트를 파싱하여 출력되도록 함

                                ->markdown(['text']); 
                                */
                });
}
```
### 9.9.4. 슬랙 알림 라우팅 (수신자 설정)
- 알림엔티티에 routeNotificationForSlack() 메소드 정의
    ```php
    public function routeNotificationForSlack($notification)
    {
        return 'https://hooks.slack.com/services/...'; // 알림이 전송될 webhook URL(슬랙 팀 설정의 "Incoming Webhook" 서비스에서 생성) 반환
    }
    ```

## 9.10. 알림 현지화
- Illuminate\Notifications\Mailable클래스의 locale()메소드 사용
- 알림 양식 생성시 언어 변경, 생성완료 후 이전 언어로 돌아감
    ```php
    $user->notify((new InvoicePaid($invoice))->locale('es'));
    ```
- Notification 파사드 이용
    ```php
    Notification::locale('es')->send($users, new InvoicePaid($invoice));
    ```
- 사용자 선호 언어 설정
 - HasLocalePreference contract구현
    ```php
    use Illuminate\Contracts\Translation\HasLocalePreference;

    class User extends Model implements HasLocalePreference
    {
        /**
        * Get the user's preferred locale.
        *
        * @return string
        */
        public function preferredLocale()
        {
            return $this->locale;
        }
    }
    ```
    ```php
    $user->notify(new InvoicePaid($invoice)); // preferredLocale() 에서 지정한 로케일로 notify됨
    ```

## 9.11. 알림 이벤트
- 알림 전송시, Illuminate\Notifications\Events\NotificationSent 이벤트 발생
- 이벤트는 notifiable 엔티티와 알림인스턴스를 가지고 있음
- EventServiceProvider에서 이벤트리스너 등록
- event:generate아티즌 명령어로 리스너 클래스 생성가능
    - 리스너 클래스 내에서는 이벤트의 notifiable, notification, channel 속성에 접근 가능



## 9.12. 커스텀 알림 채널
- send() 메소드를 가진 클래스 정의
    - send()는 $notifiable 와 $notification를 전달 받아야 함
        ```php
        namespace App\Channels;

        use Illuminate\Notifications\Notification;

        class VoiceChannel
        {

            public function send($notifiable, Notification $notification) 
            {
                $message = $notification->toVoice($notifiable);

                // Send notification to the $notifiable instance...
            }
        }
        ```
2. 알림클래스의 via 메소드에서 클래스 이름을 반환
    ```php
    public function via($notifiable)
    {
        return [VoiceChannel::class];
    }
    ```



# 10. 패키지개발
## 10.1. 시작하기
- 패키지개발 : 라라벨에 기능을 추가하는 주요 방법

### 10.1.1. 파사드 사용의 주의사항
- contracts, facades 둘 다 테스트성은 동일
- 패키지는 일반적으로 testing helper에 접근 불가
- Orchestral Testbench패키지를 사용하여 일반적인 패키지 테스트 작성 가능

## 10.2. 패키지 Discovery를 위한 설정
- composer.json파일
    ```php
    "extra": {
        "laravel": {
            "providers": [ // 패키지 Discovery
                "Barryvdh\\Debugbar\\ServiceProvider"
            ],
            "aliases": {
                "Debugbar": "Barryvdh\\Debugbar\\Facade"
            },

            "dont-discover": [ // 패키지 Discovery에서 제외
                "barryvdh/laravel-debugbar"
            ]
        }
    },
    ```
    - extra섹션에 서비스프로바이더 정의
        - 패키지 인스톨시 config/app.php의 해당 서비스프로바이더가 providers옵션에 자동으로 추가됨
    - 등록할 facades도 정의 가능
        - 자동으로 파사드 등록됨



## 10.3. 서비스 프로바이더
- 서비스 프로바이더 : 라라벨과 패키지 사이의 접점
- 하나의 서비스 프로바이더는 서비스 컨테이너 바인딩에 대응
- 패키지의 뷰, 설정파일, 언어파일의 위치를 라라벨에 알려주는 역할
- Illuminate\Support\ServiceProvider를 상속받음 (register(), boot()메소드를 포함
- 베이스 ServiceProvider클래스 : 컴포저 패키지의 illuminate/support에 위치
    - 패키지에 필요한 의존성을 컴포저에 추가해야 함



## 10.4. Resources
### 10.4.1. 설정파일 퍼블리싱
1. 패키지 설정파일을 config디렉토리에 퍼블리싱
    ```php
    // 서비스프로바이더 클래스
    public function boot()
    {
        $this->publishes([ // publishes()로 패키지 설정파일 퍼블리싱
            __DIR__.'/path/to/config/courier.php' => config_path('courier.php'),
        ]);
    }
    ```
2. vendor:publish 명령어 실행시 파일들이 지정된 위치로 복사됨
    ```php
    $value = config('courier.option'); // 설정값 액세스 가능
    ```
- 패키지 기본설정
    - 패키지 설정 파일을 애플리케이션의 퍼블리싱된 설정 파일에 병합시
    - register()메소드 내 mergeConfigFrom()메소드 사용
        ```php
        public function register()
        {
            $this->mergeConfigFrom(
                __DIR__.'/path/to/config/courier.php', 'courier'
            );
        }
        ```

### 10.4.2. 마이그레이션 파일들
- loadMigrationsFrom() 메소드로 패키지의 마이그레이션 파일들을 라라벨이 로딩할 수 있도록 위치를 알려줌
    ```php
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/path/to/migrations'); /
    }
    ```
### 10.4.3. 라우트
- loadRoutesFrom() 메소드로 패키지의 라우트를 로딩
    ```php
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        // 라우트 캐싱여부를 자동으로 확인하여 로딩
    }
    ```
### 10.4.4. 언어 파일
- loadTranslationsFrom(언어파일 위치, 패키지이름)메소드로 패키지의 언어파일을 라라벨이 로드할 수 있게 해줌
    ```php
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/path/to/translations', 'courier');
    }
    ```
    - 패키지 언어파일 참조시
        ```php
        echo trans('courier::messages.welcome'); // package::file.line
        ```
- 언어파일 퍼블리싱하는 경우
    - resources/lang/vendor디렉토리로 퍼블리싱하는 경우 publishes() 메소드 사용
        ```php
        public function boot()
        {
            $this->loadTranslationsFrom(__DIR__.'/path/to/translations', 'courier');

            $this->publishes([
                __DIR__.'/path/to/translations' => resource_path('lang/vendor/courier'),
            ]);
        }
        ```
        - vendor:publish 명령어 실행시 패키지의 언어파일들이  퍼블리싱위치에 복사됨


### 10.4.5. 뷰 파일들
- loadViewsFrom() 메소드로 라라벨이 패키지의 뷰를 등록할 수 있게 뷰파일 위치를 라라벨에 알려줌
    ```php
    // 서비스프로바이더에서 뷰 경로 등록
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/path/to/views', 'courier'); // (뷰템플릿경로, 패키지이름)
    }
    ```
    - 패키지의 뷰 참조방법
        ```php
        Route::get('admin', function () {
            return view('courier::admin'); 
            // courier 패키지의 admin뷰 로드 (package::view문법)
        });
        ```
    - loadViewsFrom()사용시 뷰파일 로드를 위한 두개의 경로 등록
        1. resources/views/vendor/패키지이름 
        2. 사용자 지정 디렉토리
        - 먼저 1에 뷰가 있는지 확인하고, 없으면 loadViewsFrom()에서 지정된 뷰 디렉토리를 확인함
- 뷰 퍼블리싱하는 경우 
    - publishes() 메소드로 뷰파일을 resources/views/vendor에 복사
        ```php
        public function boot()
        {
            $this->loadViewsFrom(__DIR__.'/path/to/views', 'courier');

            $this->publishes([
                __DIR__.'/path/to/views' => resource_path('views/vendor/courier'),
            ]); // publishes(패키지의 뷰 경로, 퍼블리싱될 위치를 나타내는 배열)
        }
        ```
        - vendor:publish명령어 실행시 패키지 뷰 파일이 지정된 퍼블리싱 위치로 복사됨
    



## 10.5. 명령어
- boot() 메소드 내 commands()메소드를 사용하여 패키지의 아티즌 명령어를 등록
    ```php
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FooCommand::class,
                BarCommand::class,
            ]);
        }
    }
    ```

## 10.6. Public Assets
- js, css, 이미지 파일 등의 assets파일들을 public디렉토리로 퍼블리싱
    - 서비스 프로바이더의 publishes()메소드 사용
        ```php
        public function boot()
        {
            $this->publishes([
                __DIR__.'/path/to/assets' => public_path('vendor/courier'),
            ], 'public');
        }
        ```
    - vendor:publish 명령어 실행시 assest들이 퍼블리싱 위치로 복사됨
        ```bash
        $ php artisan vendor:publish --tag=public --force 
        # 패키지 업데이트시 assets를 덮어쓰도록 --force플래그 사용
        ```

## 10.7. Publishing File Groups
- 서비스 프로바이더의 publishes()메소드에서 그룹별 태그 지정
    ```php
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/package.php' => config_path('package.php')
        ], 'config'); // config 태그 지정

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'migrations'); // migrations 태그 지정
    }
    ```
- vendor:publish 실행시 태그를 참조하여 그룹별로 퍼블리싱 가능
    ```php
    $ php artisan vendor:publish --tag=config
    ```



# 11. 큐
## 11.1. 시작하기
- queue사용시 시간이 소요되는 job(e.g.이메일발송)을 나중에 처리(결과적으로 request를 더 빠르게 처리할 수 있음)
- 라라벨의 queue는 Beanstalk, Amazone SQS, Redis 등의 queue 사용을 위한 통일된 API 제공
- config/queue.php에서 설정. (null로 동작시 큐 비활성화
)
-  +) 라라벨 horizon을 통해서 redis queue 지원
### 11.1.1. connections vs queues
- config/queue.php의 connections 옵션
    - Amazon SQS, Beanstalk, Redis 등 각 서비스에 대한 개별 커넥션 정의
    - queue 속성을 포함 (job이 커넥션에 보내졌을 때의 기본 queue)
        ```php
        // connections속성에 지정된 기본 queue에 전달됨
        Job::dispatch();

        // This job is sent to the "emails" queue...
        Job::dispatch()->onQueue('emails');
        ```


### 11.1.2. 드라이버 주의사항 / 사전준비 사항
- DB queue 드라이버 사용시
    - job저장해둘 db 테이블 추가
        ```bash
        $ php artisan queue:table # 마이그레이션 생성
        $ php artisan migrate # db테이블 생성
        ```
- Redis queue 드라이버 사용시
    - config/database.php에서 Redis커넥션 설정필요
        ```php
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'default',
            'retry_after' => 90,
            'block_for' => 5,
            // block_for 옵션 : 드라이버가 작업루프를 반복하고 다시 Redis를 폴링하기 전에 job이 대기해야 하는 시간
            // 큐의 부하에 따라 조정
        ],
        ```
        - redis클러스터 사용시 key hash tag을 반드시 포함해야 함(queue가 동일한 해시 슬롯에 부여됨을 보호하는 목적)
- 다른 큐 드라이버의 사전준비사항
    - Amazon SQS: aws/aws-sdk-php ~3.0
    - Beanstalk: pda/pheanstalk ~4.0
    - Redis: predis/predis ~1.0 또는 phpredis PHP 확장 모듈

## 11.2. job생성
### 11.2.1. job class 생성
- 생성 명령어
    ```bash
    $ php artisan make:job ProcessPodcast
    ```
- queue job class는 app/Jobs에 생성 
- queue job class는 Illuminate\Contracts\Queue\ShouldQueue인터페이스(job이 queue를 통해 비동기적으로 실행되어야 함을 표현) 구현


### 11.2.2. job class 구조
```php
namespace App\Jobs;

use App\AudioProcessor;
use App\Podcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $podcast;

    public function __construct(Podcast $podcast)
    // Eloquent모델이 생성자에 전달
    // job 클래스의 SerializesModels트레이트에 의해 Eloquent 모델이 serialize되고, job처리시 unserialize됨
    // +) 바이너리데이터는 base64_encode 처리 후 전달 되어야 정상적으로 serialze 가능
    {
        $this->podcast = $podcast;
    }

    // queue에 의해 job처리시 handle()메소드 호출
    public function handle(AudioProcessor $processor)
    {
        // Process uploaded podcast...
    }
}
```
```php
// queue클래스의 handle()에의 객체주입하지 않고
// ServiceProvicder 내에서 객체주입하는 경우
// 서비스컨테이너의 bindMethod() 사용
use App\Jobs\ProcessPodcast;

$this->app->bindMethod(ProcessPodcast::class.'@handle', function ($job, $app) { // 콜백 내에서 handle메소드 호출가능
    return $job->handle($app->make(AudioProcessor::class));
});
```

### 11.2.3. job 미들웨어
- job미들웨어 사용시 대기중인 job실행을 중심으로 커스텀 로직을 래핑하여 job자체의 중복코드를 줄일 수 있음(e.g. 속도제한 코드를 handle()메소드마다 작성하지 않고 분리해둘 수 있음)
- job 미들웨어 작성 (파일위치는 임의로 지정가능)
    ```php
    namespace App\Jobs\Middleware;

    use Illuminate\Support\Facades\Redis;

    class RateLimited
    {
        public function handle($job, $next) // 처리중인 job과 job처리를 계속하기 위한 콜백을 인자로 받음
        {
            // job마다 공통적용되어야 할 로직들

            Redis::throttle('key')
                    ->block(0)->allow(1)->every(5)
                    ->then(function () use ($job, $next) {
                        // Lock obtained...

                        $next($job);
                    }, function () use ($job) {
                        // Could not obtain lock...

                        $job->release(5);
                    });
        }
    }    
    ```
- job 미들웨어 적용
    ```php
    use App\Jobs\Middleware\RateLimited;
    public function middleware() // job클래스에, job에서 사용할 미들웨어를 리턴해주는 middleware()클래스를 추가
    {
        return [new RateLimited];
    }
    ```    

## 11.3. job처리
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPodcast;
use Illuminate\Http\Request;

class PodcastController extends Controller
{
    public function store(Request $request)
    {
        // Create podcast...

        /** job클래스의 dispatch()메소드를 이용해 job처리가능 **/
        ProcessPodcast::dispatch($podcast); 

        /** delay()메소드를 이용해 job을 지연시켜 처리가능 **/
        ProcessPodcast::dispatch($podcast)
        ->delay(now()->addMinutes(10));

        /** dispatchNow()메소드를 이용해 job을 즉시(동기적으로) 반환가능 **/
        ProcessPodcast::dispatchNow($podcast); // job이 queue에 저장되지 않고 현재 프로세스 내에서 즉시 실행
    }
}
```

### 11.3.1. 지연시켜 처리
11.3. job처리 참조
### 11.3.2. 동기식 반환
11.3. job처리 참조


### 11.3.3. job 체이닝
- 기본 job 성공 뒤 순차적으로 실행할 대기job 목록 지정
    ```php
    // withChain()사용
    ProcessPodcast::withChain([ // 하나의 job실패시 나머지는 실행X
        new OptimizePodcast, 
        new ReleasePodcast
    ])->dispatch();
    ```
- 체이닝된 job에 기본 커넥션과 큐 지정
    ```php
    ProcessPodcast::withChain([
        new OptimizePodcast,
        new ReleasePodcast
    ])->dispatch()
    ->allOnConnection('redis')->allOnQueue('podcasts'); 
    // 각 job에서 커넥션, 큐 미지정시 이 설정이 기본으로 적용됨
    ```

### 11.3.4. queue, connection 커스텀
- dispatch할 때 queue,connection지정
    ```php
    namespace App\Http\Controllers;

    use App\Http\Controllers\Controller;
    use App\Jobs\ProcessPodcast;
    use Illuminate\Http\Request;

    class PodcastController extends Controller
    {
        public function store(Request $request)
        {
            /**** 특정 queue로 처리하도록 지정 ****/
            // (동일한 connection 내의 queue로만 지정할 수 있음) 
            ProcessPodcast::dispatch($podcast)->onQueue('processing');
            // ProcessPodcast job을 dispatch할 때 processing queue를 사용하도록 지정함 

            
            /**** 특정 connection으로 처리하도록 지정 ****/
            ProcessPodcast::dispatch($podcast)
                ->onConnection('sqs')
                ->onQueue('processing');

        }
    ```
- job클래스에서 connection지정
    ```php
    class ProcessPodcast implements ShouldQueue
    {
        public $connection = 'sqs';
    }
    ```

### 11.3.5. 최대 재시도횟수, 타임아웃시간 지정
- 작업 수행 최대 재시도 횟수 지정
    - artisan명령어로 지정
        ```bash
        $ php artisan queue:work --tries=3
        ```
    - job클래스에서 지정 (artisan명령어로 지정된 값보다 우선)
        ```php
        namespace App\Jobs;

        class ProcessPodcast implements ShouldQueue
        {
            public $tries = 5;
        }
        ```

- 시간기반 재시도 : job 시간초과 처리되는 기준시간 지정
    ```php
    // job class에 retryUntil() 메소드추가
    public function retryUntil()
    {
        return now()->addSeconds(5);
    }
    ```


- 타임아웃 : job 수행 최대시간 지정
    - artisan명령어로 지정
        ```bash
        $ php artisan queue:work --timeout=30 # --timeout옵션지정
        ```
    - job클래스에서 지정 (artisan명령어로 지정된 값보다 우선)
        ```php
        namespace App\Jobs;
        class ProcessPodcast implements ShouldQueue
        {
            public $timeout = 120;
        }
        ```


    
### 11.3.6. rate limiting (Redis server연결시 사용)
- 시간, 동시성에 따라 대기 job을 조정할 수 있음
- 대기 job이 rate limit이 있는 API와 상호작용하는 경우 유용
- throttle()메소드 사용
    ```php
    Redis::throttle('key')->allow(10)->every(60)->then(function () {
        // Job logic...
    }, function () {
        // Could not obtain lock...
        return $this->release(10); // queue에 release한 뒤 나중에 다시 시도
    });
    ```
- funnel()메소드 사용
    - 한 번에 하나의 worker를 통해 처리되도록 하는 job에서의 제한
    - key는 job을 식별하는 고유한 문자열(e.g.job클래스명, eloquent모델의 id)
        ```php
        Redis::funnel('key')->limit(1)->then(function () {
            // Job logic...
        }, function () {
            // Could not obtain lock...
            return $this->release(10);
        });
        ```


### 11.3.7. 에러핸들링
- job 처리 중 exception발생시 queue로 반환됨
- 최대 재시도 횟수만큼 다시 실행됨

## 11.4. Queueing Closure
- job을 queue 대신 Closure로 전달
- 요청주기를 벗어나 실행되야하는 빠르고 간단한 job에 적용
- dispatch시 Closure 코드 내용은 암호화/서명됨(전송 중 수정불가) 
    ```php
    $podcast = App\Podcast::find(1);
    dispatch(function () use ($podcast) { 
        $podcast->publish();
    });
    ```


## 11.5. Queue Worker 구동
- queue worker : queue의 job을 처리
- queue:work 명령어
    ```bash
    # queue:work 기본 명령어
    $ php artisan queue:work
    
    # connection, queue지정(config/queue.php의 커넥션명과 일치해야 함)
    $ php artisan queue:work redis
    $ php artisan queue:work redis --queue=emails
    # 단일 Job 처리
    $ php artisan queue:work --once
    # 대기중인 모든 작업 처리 및 종료
    $ php artisan queue:work --stop-when-empty 
    ```
    - 시작되고나면 수동중지 or 터미널 종료시까지 실행유지
    - 백그라운드 중단 방지를 위해서는 Supervisor같은 프로세스 모니터 사용
    - 메모리에 애플리케이션 상태를 저장 
    - 코드 변경시 queue worker재시작해야 함
    - queue:listen 명령은 코드 변경후에 수동재시작 필요없음.(효율은 떨어짐)


### 11.5.1. Queue우선순위 지정
```php
dispatch((new Job)->onQueue('high')); // config/queue.php에 지정한 queue우선순위를 low로 지정해도 onQueue('high')가 적용됨
```
```bash
# queue worker시작시 queue의 우선순위지정
$ php artisan queue:work --queue=high,low
```

### 11.5.2. Queue Worker & 배포
- 코드 변경(배포)시 queue worker재시작 필요
- queue재시작
    ```bash
    $ php artisan queue:restart
    ```
    - 현재 job 처리 종료 후 queue worker가 종료됨
- 자동으로 queue worker재시작하기 위해서는 Supervisor같은 프로세스 매니저를 사용
- 혹은 cache에 queue재시작 시그널을 저장(캐시드라이버 설정 필요)


### 11.5.3. job 만료 & 타임아웃
- job 만료
    - job 처리 후 queue에서 제거되기까지d의 시간이 만료되면 다시 queue에 투입
    - config/queue.php에서 queue 커넥션의 retry_after옵션을 지정
    (e.g. 90이면, job처리 완료 후 90초동안 미제거시 queue에 재투입)
- worker 타임아웃
    - child queue worker가 job을 얼마나 오래 처리하는지 지정
    - 타임아웃되면 먹통이 된 프로세스 제거
        ```bash
        $ php artisan queue:work --timeout=60
        ```
- Worker Sleep Duration
    ```bash
    $ php artisan queue:work --sleep=3 # 새로운 job처리시 delay 시간 지정
    ```

## 11.6. Supervisor(리눅스 프로세스 모니터)설정
- Supervisor 설치
    ```bash
    $ sudo apt-get install supervisor
    ```
- Supervisor 설정
    ```bash
    [program:laravel-worker]
    process_name=%(program_name)s_%(process_num)02d
    command=php /home/forge/app.com/artisan queue:work sqs --sleep=3 --tries=3 # sqs는 사용할 커넥션
    autostart=true
    autorestart=true
    user=forge
    numprocs=8 # Supervisor에 총 8 개의 queue:work 프로세스를 실행하고 이들을 모니터링. 프로세스가 죽어 있으면, 자동으로 재시작하도록
    redirect_stderr=true
    stdout_logfile=/home/forge/app.com/worker.log
    ```
    - /etc/supervisor/conf.d디렉토리에 .conf설정파일 존재
    - Supervisor가 어떻게 프로세스를 모니터링 할 것인지 지시하는 설정 파일을 원하는 수 만큼 생성

- Supervisor 시작
    ```bash
    $ sudo supervisorctl reread
    $ sudo supervisorctl update
    $ sudo supervisorctl start laravel-worker:*
    ```

## 11.7. 실패한 job처리
1. failed_jobs테이블/마이그레이션 생성
2. 최대 재시도 횟수 지정
3. 재시도 전 딜레이시간 지정
### 11.7.1. 실패한 job 정리
job 클래스에 failed()메소드 정의(job실패 Exception전달됨)
(job실패시 알림/ 실행액션 롤백 등 처리 가능)
### 11.7.2. 실패한 job 이벤트
- AppServiceProvider에 Queue::failing(이벤트콜백)메소드 추가
### 11.7.3. 실패한 job 재시도
1. queue:failed명령어로 failed_jobs에 추가된 실패jobs 조회
2. 실패 job정보를 바탕으로 job재시작 or job삭제

- 누락된 모델 무시
    - job처리 중 모델 삭제시 ModelNotFoundException발생
    - 이를 방지하기 위해 deleteWhenMissingModelsf를 true로 설정가능

- Job 이벤트
    - 로깅, 대시보드 통계 증가 등 작업
    - Queue파사드의 before(), after()메소드로 job실행 전 후 실행할 콜백 지정
    - AppServiceProvider등 서비스프로바이더에서 호출


# 12. 작업 스케줄링
## 12.1. 시작하기
- 기존 스케줄링
    - 서버에서 각 작업에 대한 Cron항목 생성 (ssh접속하여 추가함. 번거로움)
    - 작업 스케줄의 소스컨트롤 어려움
- 라라벨 명령어 스케줄러 : 편리 ! 기능풍부 !
- 라라벨 명령어 스케줄러를 호출하는 Cron항목을 서버에 추가해야 함 
    ```bash
    $ * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
    ```
    - schedule:run 실행시 라라벨이 스케줄에 포함된 작업을 계산/수행
    - 라라벨 Forge를 사용하여 Cron항목 관리하는 것도 가능


## 12.2. 스케줄 정의하기
- app/Console/Kernel.php 파일의 schedule()메소드에서 작업스케줄 정의
    ```php
    protected function schedule(Schedule $schedule)
    {
        /* 클로저 사용하여 스케줄링 */
        $schedule->call(function () {
            DB::table('recent_users')->delete();
        })->daily();

        /* invokable objects 사용하여 스케줄링 */
        $schedule->call(new DeleteRecentUsers)->daily();
        // 호출되는 객체는 __invoke()를 포함하는 간단한 클래스
    

        /* 아티즌 명령어, os명령어 스케줄링 */
        $schedule->command('emails:send Taylor --force')->daily();
        $schedule->command(EmailsCommand::class, ['Taylor', '--force'])->daily();

        /* Queued Jobs 스케줄링 */
        $schedule->job(new Heartbeat)->everyFiveMinutes();
        // Dispatch the job to the "heartbeats" queue...
        $schedule->job(new Heartbeat, 'heartbeats')->everyFiveMinutes();

        /* 쉘 명령어 스케줄링 */
        $schedule->exec('node /home/forge/script.js')->daily();
    }
    ```
### 12.2.1. 아티즌 명령어 스케줄링
### 12.2.2. Queued Jobs 스케줄링
### 12.2.3. 쉘 명령어 스케줄링
12.2. 스케줄 정의하기 참조

### 12.2.4. 스케줄링 주기 관련 옵션
//...
### 12.2.5. 타임존
-  예약 된 작업의 시간을 주어진 타임존 안에서 실행 되도록 지정

### 12.2.6. 작업의 중복 방지
- 스케줄에 등록된 작업들 중 동일한 작업이 이미 실행중일 때 중복실행 방지 
    ```php
    $schedule->command('emails:send')->withoutOverlapping();
    ```
### 12.2.7. 한 서버에서만 작업 실행하도록 하기
- 다수 서버사용시, 동일한 작업을 중복수행하지 않도록 수행서버 제외한 나머지 서버는 lock
    ```php
    // onOneServer();
    $schedule->command('report:generate')
                    ->fridays()
                    ->at('17:00')
                    ->onOneServer();
    ```
### 12.2.8. 백그라운드 작업
- 동시 예약된 다수의 명령은 순차적으로 실행됨
- 장시간 실행되는 명령 사용시 뒤에 실행되는 명령이 늦게 시작될 수 있음
- runInBackground() : 백그라운드에서 명령을 실행해 모든 명령이 동시에 실행되게 할 수 있음

### 12.2.9. 유지보수 모드
- evenInMaintenanceMode() :유지보수 중인 서버에서도 스케줄링작업 실행되도록 강제

## 12.3. 작업 출력
- sendOutputTo() : 결과 파일 보냄
- appendOutputTo() : 특정 파일에 출력을 더함
- emailOutputTo() : 이메일주소로 출력전달
- emailOutputOnFailure() : 명령 실패시 이메일주소로 출력전달

## 12.4. 작업 후킹
- before(), after() 메소드 사용시 작업 실행 전, 후에 특정 코드실행 가능
- onSuccess(), onFailure()
- URL Ping 실행
    - guzzleHTTP 라이브러리 설치 후 이용
        ```bash
        $ composer require guzzlehttp/guzzle
        ```
    - pingBefore($url)
    - thenPing($url)
    - pingBeforeIf($condition, $url)
    - thenPingIf($condition, $url)
    - pingOnSuccess($successUrl)
    - pingOnFailure($failureUrl);
