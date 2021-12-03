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

### 2.1.1. 설정하기
### 2.1.2. 드라이버 사전준비사항

## 2.2. 컨셉 개요
### 2.2.1. 예제 애플리케이션

## 2.3. 브로드캐스트 이벤트 정의
### 2.3.1. 브로드캐스트 이름
### 2.3.2. 브로드캐스트 데이터
### 2.3.3. 브로드캐스트 큐
### 2.3.4. 브로드캐스트 조건

## 2.4. 승인채널
### 2.4.1. 승인 라우트 정의
### 2.4.2. 승인 콜백 정의
### 2.4.3. 채널 클래스 정의

## 2.5. 브로드캐스팅 이벤트
### 2.5.1. 

## 2.6. 브로드캐스트 수신
### 2.6.1. 라라벨 에코 설치
### 2.6.2. 이벤트 리스닝
### 2.6.3. 채널 나가기
### 2.6.4. 네임스페이스

## 2.7. 주둔 채널
### 2.7.1. 주둔 채널 승인하기






# 3. 캐시
# 4. 컬렉션

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
# 7. 헬퍼 함수
# 8. 메일
# 9. 알림
# 10. 패키지개발




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
    - Beanstalkd: pda/pheanstalk ~4.0
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


# 12. 작업스케줄링