# Laravel Architecture Concept


## 1. Request Lifecycle

### 1.1. Life Cycle Overview

#### 1.1.1. 시작점은 index.php
- 라라벨 어플리케이션에 대한 모든 요청은 public/index.php 파일이 시작점
- 컴포저에 의해 생성된 **오토로더 로딩**
- bootstrap/**app.php**를 불러옴 (라라벨이 어플리케이션 인스턴스를 생성)
- **서비스컨테이너** 인스턴스 생성
#### 1.1.2. HTTP / Console Kernels
- 들어온 요청은 요청타입에 따라 각각의 HTTP 커널이나 콘솔커널로 보내짐
- HTTP 커널 (app/Http/Kernel.php)
    - 요청 실행 전 처리 될 것들을 정의
        - Kernel 클래스의 bootstrappers 배열 : 에러핸들링  및 로깅설정, 앱환경 탐지 등
        - Illuminate\Foundation\Http\Kernel 클래스를 상속
    - 미들웨어 설정
        - 미들웨어 : 세션읽고 쓰기, CSRF토큰 인증 등을 처리
    - handle 메소드 정의
        - handle 메소드 : request를 받고 response를 리턴

#### 1.1.3 Service Providers     
- 라라벨 부트스트랩 과정에서 가장 중요
- 커널 부트스트랩에 의해 로드

- 모든 provider에서 register()가 호출되고, register후에는 boot()가 호출됨
- db, queue, validation, routing 등 프레임워크 **컴포넌트들을 부팅**시키는 역할
- 라라벨 프레임워크의 모든 기능 설정 및 초기화

- config/app.php의 providers배열에서 설정
- 기본 ServiceProvider의 위치는 app/Providers
- AppServiceProvider에서 부트스트랩, 서비스컨테이너 바인딩을 추가 할 수 있음

#### 1.1.3 Router : Dispatch Request
- 어플리케이션 boot()되고 모든 service provider들이 register()되면, 
Request는 라우터처리를 위해 전달됨
- 라우터 : 라우팅, request를 컨트롤러에 전달, 라우트에 지정된 미들웨어 실행



## 2. Service Container
### 2.1. 역할
#### 2.1.1. 의존성주입 및 관리
생성자나 setter메소드로 의존성이 주입됨

### 2.2. 바인딩
#### 2.2.1. 기본적인 바인딩
- **서비스 프로바이더 내에서** 등록
    ($this->app으로 컨테이너 인스턴스에 접근가능)
- 간단한 바인딩
    ```php
    // bind()사용. 원하는 클래스 인스턴스 반환하는 Closure 등록하여 바인딩.
    $this->app->bind('HelpSpot\API', function ($app) { 
        return new \HelpSpot\API($app->make('HttpClient')); 
    });
    ```
- singleton()
    ```php
    // 한 번만 의존성 해결
    // 컨테이너의 다른 부분에서 호출시 동일한 객체 인스턴스 반환
    $this->app->singleton('HelpSpot\API', function ($app) {
        return new HelpSpot\API($app->make('HttpClient'));
    });
    ```
- instance() 
    ```php
    // 이미 존재하는 객체의 인스턴스를 컨테이너에 바인딩
    $api = new HelpSpot\API(new HttpClient);
    $this->app->instance('HelpSpot\API', $api);
    ```
- primitive value 주입
    ```php
    $this->app->when('App\Http\Controllers\UserController')
          ->needs('$variableName')
          ->give($value);
    ```
#### 2.2.2. 인터페이스에 구현객체 바인딩
```php
$this->app->bind(
    'App\Contracts\EventPusher', // 인터페이스
    'App\Services\RedisEventPusher' // 인터페이스의 구현객체
);
```
```php
// EventPusher인터페이스의 구현객체 필요시  
// 서비스컨테이너가 RedisEventPusher의존성 주입
use App\Contracts\EventPusher;
public function __construct(EventPusher $pusher)
{
    $this->pusher = $pusher;
}
```
#### 2.2.3. 문맥에 따른 조건적 바인딩
```php
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\VideoController;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

// 각각의 컨트롤러가 같은 인터페이스의 서로다른 구현체에 의존하는 경우
$this->app->when(PhotoController::class)
        ->needs(Filesystem::class)
        ->give(function () {
            return Storage::disk('local');
        });
$this->app->when([VideoController::class, UploadController::class])
        ->needs(Filesystem::class)
        ->give(function () {
            return Storage::disk('s3');
        });
```

#### 2.2.4. 태깅
- 특정 카테고리 전체 바인딩이 필요한 경우
    ```php
    // report구현체 바인딩
    $this->app->bind('SpeedReport', function () {
    });
    $this->app->bind('MemoryReport', function () {
    });
    // report구현체 태깅
    $this->app->tag(['SpeedReport', 'MemoryReport'], 'reports');
    ```
    ```php
    $this->app->bind('ReportAggregator', function ($app) {
        return new ReportAggregator($app->tagged('reports'));
    });
    ```

#### 2.2.5. 바인딩확장
```php
// extends() 메소드는 리졸브된 서비스의 의존성을 수정하게 해준다.
$this->app->extend(Service::class, function ($service, $app) {
    return new DecoratedService($service);
});
```
### 2.3. 의존성 resolve
#### 2.3.1. make()
- 컨테이너 밖에서 의존성 resolve를 할 떄
    ```php
    $api = $this->app->make('HelpSpot\API'); // resolve할 클래스/인터페이스이름
    ```
    ```php
    // $app 변수에 접근이 불가한 위치인 경우
    $api = resolve('HelpSpot\API');
    ```
    ```php
    // 컨테이너를 통해 리졸브가 불가한 의존성일 경우
    $api = $this->app->makeWith('HelpSpot\API', ['id' => 1]);
    ```
#### 2.3.2. 자동주입
- 가장 많이 사용되는 방법
- 클래스 생성자에 타입힌트를 선언하여 컨테이너가 의존성을 해결할 수 있게 함
    ```php
    ...
    use App\Users\Repository as UserRepository;
    ...
    public function __construct(UserRepository $users) // 
    {
        $this->users = $users;
    }
    ```
- queued jobs의 handle 메소드에도 종속성을 입력 가능



### 2.4. 컨테이너 이벤트
- 서비스 컨테이너는 객체의 의존성해결을 수행할 때마다 이벤트를 발생시킴
- resolving()메소드로 이벤트에 대응
    ```php
    $this->app->resolving(function ($object, $app) { // 콜백에 의존성해결된 객체가 전달
        // 의존성이 해결된 어떤 객체에든 추가적으로 적용될 내용들...
    });

    $this->app->resolving(HelpSpot\API::class, function ($api, $app) {
        // 특정객체 "HelpSpot\API"가 의존성이 해결된 경우 적용될 내용들...
    });
    ```

### 2.5. 컨테이너 인스턴스에 접근 (PSR-11)
```php
use Psr\Container\ContainerInterface;
Route::get('/', function (ContainerInterface $container) { // 서비스컨테이너는 PSR-11 ContainerInterface인터페이스를 구현한 것이기 때문에, 해당 인터페이스를 타입힌트하여 컨테이너 인스턴스에 접근가능
    $service = $container->get('Service'); 
    //
});
// 식별자 reslove 불가시 예외 thrown
// Psr\Container\NotFoundExceptionInterface : 식별자 바인드되지 않은 경우
// Psr\Container\ContainerExceptionInterface : 식별자 바인드 되었으나 resolve 불가시
```


## 3. Service Providers
### 3.1. 개요 
- 라라벨 코어서비스는 서비스프로바이더를 통해서 부트스트래핑
- bootstrapped : 서비스컨테이너에 바인딩 등록, 이벤트리스너/라우트/미들웨어 등이 등록되는 것
- 서비스프로바이더 클래스는 실제로 필요할 때만 애플리케이션에 로드 (deffered providers)

### 3.2. 서비스 프로바이더 작성
#### 3.2.1. register()
- 서비스컨테이너 바인딩만 해야 함
    ```php
    namespace App\Providers;

    use Illuminate\Support\ServiceProvider;
    use Riak\Connection;

    class RiakServiceProvider extends ServiceProvider 
    // 모든 서비스프로바이더는 Illuminate\Support\ServiceProvider를 상속
    {
        public function register()
        {   // 서비스프로바이더에서는 $this->app으로 서비스 컨테이너에 접근
            $this->app->singleton(Connection::class, function ($app) {
                return new Connection(config('riak'));
            });
        }
    }
    ```
- 서비스 프로바이더가 동일한 바인딩 여러개를 등록하는 경우
    ```php
    // bindings, singletons 속성
    ...
    public $bindings = [ // 모든 컨테이너 바인딩이 등록됨
        ServerProvider::class => DigitalOceanServerProvider::class,
    ];

    ...
    public $singletons = [ // 모든 싱글톤 컨테이너가 등록됨
        DowntimeNotifier::class => PingdomDowntimeNotifier::class,
        ServerToolsProvider::class => ServerToolsProvider::class,
    ];
    ``` 
#### 3.2.2. boot()
- 서비스프로바이더 등록이후 호출 (서비스 액세스 가능)
    ```php
    namespace App\Providers;
    use Illuminate\Support\ServiceProvider;
    class ComposerServiceProvider extends ServiceProvider
    {
        public function boot() // 뷰 컴포저 등록
        {
            view()->composer('view', function () {
                //
            });
        }
    }
    ```
- boot()메소드 의존성 주입
    ```php
    use Illuminate\Contracts\Routing\ResponseFactory;
    public function boot(ResponseFactory $response) // 타입힌트로 의존성 주입
    {
        $response->macro('caps', function ($value) {
        });
    }
    ```

### 3.3. 서비스 프로바이더 등록 
- config/app.php의 providers배열에 등록

### 3.4. deffered providers
- 라라벨은 deffered providers의 서비스목록과 클래스명을 컴파일 및 저장해둠
- 의존성 해결 필요시에만 서비스프로바이더 로드
    ```php
    namespace App\Providers;
    use Illuminate\Contracts\Support\DeferrableProvider;
    use Illuminate\Support\ServiceProvider;
    use Riak\Connection;
    class RiakServiceProvider extends ServiceProvider implements DeferrableProvider
    // DeferrableProvider를 구현
    {
        public function register() // 서비스컨테이너 바인딩 등록
        {
            $this->app->singleton(Connection::class, function ($app) {
                return new Connection($app['config']['riak']);
            });
        }
        public function provides() // provides() 메소드 정의()
        {
            return [Connection::class]; // 바인딩이 등록된 서비스컨테이너 리턴
        }
    ```

## 4. Facades
### 4.1. 개요
- 서비스 컨테이너에서 사용가능한 클래스들에 대한 "정적" 인터페이스를 제공 (일종의 정적 프록시 역할)
- 간결한 문법, 테스트용이성, 코드유연성 유지
    ```php
    use Illuminate\Support\Facades\Cache;
    Route::get('/cache', function () { 
        return Cache::get('key');
    });
    ```
### 4.2. 언제사용?
- 의존성 주입, 테스트를 쉽게 해줌
#### 4.2.1. 파사드 VS 의존성주입
- 의존성주입 : 주입된 클래스 구현체 변경 가능 -> 테스트를 위해 모킹객체, 스텁 주입 가능.(일반적으로 정적클래스메소드에서는 불가함)
- 파사드 : 다이나믹 메소드를 사용하여 테스트 가능
    ```php
    use Illuminate\Support\Facades\Cache;

    Route::get('/cache', function () {
        return Cache::get('key');
    });   
    ```
    ```php
    // Cache::get메소드가 인자와 함께 호출되었는지를 테스트하는 코드
    use Illuminate\Support\Facades\Cache;
    public function testBasicExample()
    {
        Cache::shouldReceive('get')
            ->with('key')
            ->andReturn('value');

        $this->visit('/cache')
            ->see('value');
    }
    ```
#### 4.2.2. 파사드 VS 헬퍼함수
- 헬퍼함수 : 뷰파일생성, 이벤트 발생, job실행, http응답 반환 등 공통 작업 수행 함수 파사드와 일치하는 동작을 수행.
    ```php
    return View::make('profile'); // 파사드 호출
    return view('profile'); // 헬퍼함수 호출
    ```
### 4.3. 동작방식
#### 4.3.1. Facade Class
- 컨테이너 객체에 액세스하는 방법을 제공하는 클래스
- Illuminate\Support\Facades\Facade을 상속
- Facade기본클래스는 __callStatic()매직메소드를 사용해, 파사드에 대한 호출을 컨테이너에서 의존성이 해결된 객체로 전달.
    ```php
    namespace App\Http\Controllers;
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Cache; // Cache파사드사용
    class UserController extends Controller
    {
        public function showProfile($id)
        {
            $user = Cache::get('user:'.$id); // 의존성이 해결된 객체로 캐시 서비스의 구현체에 전달
            return view('profile', ['user' => $user]);
        }
    }
    ```
    ```php
    class Cache extends Facade // Facade를 상속
    {
        protected static function getFacadeAccessor() { return 'cache'; } // 서비스컨테이너의 바인딩 이름 반환. cache라는 이름의 바인딩객체를 찾아 메소드를 호출하게됨 (이 경우 get 메소드)
    }
    ```
### 4.4. 실시간 파사드
- 모든 클래스를 파사드처럼 취급하게 해줌
    ```php
    namespace Tests\Feature;
    use App\Podcast;
    // 리얼타임파사드의 사용
    use Facades\App\Contracts\Publisher; // Facades클래스네임 뒤의 인터페이스/클래스네임을 사용하여, 서비스 컨테이너에서 의존성을 해결. 
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Tests\TestCase;
    class PodcastTest extends TestCase
    {
        use RefreshDatabase;
        public function test_podcast_can_be_published()
        // 파라미터로 Publisher인스턴스를 전달하지 않아도 됨
        {
            $podcast = factory(Podcast::class)->create();
            Publisher::shouldReceive('publish')->once()->with($podcast);
            $podcast->publish();
        }
    }
    ```
### 4.5. 파사드 클래스 목록

## 5. Contracts 
### 5.1. 개요
- 프레임워크의 코어 서비스들을 정의한 인터페이스들의 모음
- 각 Contracts에는 상응하는 구현체가 있음
- 모든 Contracts는 다른 패키지에 의존하지 않는 단일 패키지
#### 5.1.1. Contracts VS Facades
- contracts는 의존성을 명시적으로 정의 (패키지 빌드시 권장)
- facade는 서비스컨테이너 외부에서 타입힌트, contracts의존성 없이 라라벨을 쉽게 사용할 수 있게 함
### 5.2. Contracts 사용시기
#### 5.2.1. 느슨한 결합
```php
namespace App\Orders;
class Repository
{
    protected $cache;
    public function __construct(\SomePackage\Cache\Memcached $cache) //특정캐시 구현체와 결합
    { 
        $this->cache = $cache;
    }
    ...
}
```
```php
namespace App\Orders;
use Illuminate\Contracts\Cache\Repository as Cache; // contracts 사용
class Repository
{
    protected $cache;
    public function __construct(Cache $cache) // 생성자의 타입힌트가 단순인터페이스. 특정캐시 구현체에 구속되지 않음. 
    {
        $this->cache = $cache;
    }
}
```
#### 5.2.2. 단순함
- 모든 서비스들이 인터페이스로 보기좋게 정의되어, Contract들이 프레임워크의 기능들에 대한 간결한 도큐먼트의 역할
- 가독성, 유지보수성 향상

### 5.3. Contract 사용법
```php
namespace App\Listeners;
use App\Events\OrderWasPlaced;
use App\User;
use Illuminate\Contracts\Redis\Factory;
class CacheOrderInformation
{
    /**
     * The Redis factory implementation.
     */
    protected $redis;

    /**
     * Create a new event handler instance.
     *
     * @param  Factory  $redis
     * @return void
     */
    public function __construct(Factory $redis) // 타입힌트로 contract구현체 얻기. 이벤트리스너가 리졸브될 때, 서비스컨테이너가 타입힌트를 읽고 적절한 값을 주입함.
    {
        $this->redis = $redis;
    }

    /**
     * Handle the event.
     *
     * @param  OrderWasPlaced  $event
     * @return void
     */
    public function handle(OrderWasPlaced $event)
    {
        //
    }
}
```




















