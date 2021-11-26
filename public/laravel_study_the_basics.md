# 1. Routing
## 1.1. Basic Routing
```php
// 기본적인 라라벨 라우트
Route::get('foo', function () { // URI, Closure 파라미터
    return 'Hello World';
});
```

### 기본 라우트 파일
- 모든 라우트는 라우트 디렉토리의 라우트파일에 정의되어 있으며, 라우트파일은 프레임워크에 의해 자동으로 로드됨
- ```routes/web.php```에 정의된 routes
    - web middleware group에 할당되어 있음.
    - 여기에 정의된 라우트는 브라우저에서 URL로 접근하게 되어 있음.
- ```routes/api.php```에 정의된 routes
    - api middleware group에 할당되어 있음.
    - stateless
    - ```RouteServiceProvider```에 의해 라우트 그룹 내 중첩되어 정의됨
    - ```/api```URI prefix가 자동으로 적용
    - ```RouteServiceProvider```클래스를 수정하여 prefix,라우터그룹 옵션을 변경가능

### 라우터 메소드
- 다양한 http메소드에 응답하도록 설정가능
    ```php
    Route::get($uri, $callback);
    Route::post($uri, $callback);
    Route::put($uri, $callback);
    Route::patch($uri, $callback);
    Route::delete($uri, $callback);
    Route::options($uri, $callback);
    ```
- 복수의 http메소드에 응답하도록 설정하는 경우 
    ```php
    Route::match(['get', 'post'], '/', function () {});
    Route::any('/', function () {});
    ```

### CSRF Protection
- web route file 정의된 POST, PUT, DELETE 라우트를 가리키는 HTML form은  CSRF 토큰필드를 반드시 포함해야 함.(누락시 요청 거절)
    ```html
    <form method="POST" action="/profile">
        @csrf 
        ...
    </form>
    ```
    
### 1.1.1. Redirect Routes
- Route::redirect()메소드로 간단하게 리다이렉트처리
    ```php
    Route::redirect('/here', '/there');
    ```
    ```php
    // Route::redirect에서 기본 302 아닌 다른 상태코드를 반환하려 할 때
    Route::redirect('/here', '/there', 301);
    ```
    ```php
    Route::permanentRedirect('/here', '/there'); //permanentRedirect 은 301상태코드 반환
    ```

### 1.1.2. View Routes
- Route::view 메소드로 뷰 반환
    ```php
    Route::view('/welcome', 'welcome');
    Route::view('/welcome', 'welcome', ['name' => 'Taylor']); // 세번째 인자는 view에 전달할 데이터
    ```

## 1.2. Route Parameter
- 파라미터는 라우트 콜백/컨트롤러로 주입
### 1.2.1. 필수 파라미터
```php
Route::get('user/{id}', function ($id) {
    return 'User '.$id;
});

// 복수 파라미터
Route::get('posts/{post}/comments/{comment}', function ($postId, $commentId) {
});
```

### 1.2.2. 선택 파라미터
```php
Route::get('user/{name?}', function ($name = null) { // 파라미터이름뒤 ? // 변수 기본값 할당 필수
    return $name;
});
Route::get('user/{name?}', function ($name = 'John') {
    return $name;
});
```

### 1.2.3. 정규표현식 제약
```php
// where 메소드로 Route파라미터 포맷제한.
Route::get('user/{name}', function ($name) {
})->where('name', '[A-Za-z]+'); 

Route::get('user/{id}', function ($id) {
})->where('id', '[0-9]+');

Route::get('user/{id}/{name}', function ($id, $name) {
})->where(['id' => '[0-9]+', 'name' => '[a-z]+']);
```
- 글로벌 제약
    ```php
    // RouteServiceProvider의 boot()내에서, 라우트의 pattern메소드를 이용해 항상 파라미터 포맷 제한이 적용되도록 함.
    public function boot()
    {
        Route::pattern('id', '[0-9]+'); // id파라미터명을 사용하는 모든 라우트들에 자동으로 적용
        parent::boot();
    }
    ```
- 인코딩된 슬래시
    ```php
    Route::get('search/{search}', function ($search) {
        return $search; 
    })->where('search', '.*');
    ```
    
## 1.3. 이름이 지정된 라우트 
- URL생성, 리다이렉션 용이
- 라우트정의에 name메소드 체이닝하여 이름 지정
    ```php
    Route::get('user/profile', function () {
    })->name('profile'); 
    ```
- 컨트롤러 액션에도 라우트 이름 지정 가능
    ```php
    Route::get('user/profile', 'UserProfileController@show')->name('profile');
    ```
### 1.3.1. 이름이 지정된 라우트들에 대한 URL생성
- 전역 route함수를 통해 url,리다이렉션 생성시 라우트이름 사용
    ```php
    // url 생성
    $url = route('profile'); // 라우트이름 사용
    // 리다이렉트 생성
    return redirect()->route('profile');
    ```
    ```php
    Route::get('user/{id}/profile', function ($id) {
    })->name('profile');

    $url = route('profile', ['id' => 1]); // 2번째 인자는 자동으로 올바른 위치에 삽입됨
    // $url = route('profile', ['id' => 1, 'photos' => 'yes']); // 2번째 인자 배열에 값 추가시. /user/1/profile?photos=yes 으로 전달됨
    ```
- 현재의 라우트 검사하기
    ```php
    public function handle($request, Closure $next)
    {
        if ($request->route()->named('profile')) { // 라우트 이름 검사
        }
        return $next($request);
    }
    ```
## 1.4. 라우트 그룹
- 라우트 그룹 지정시, 미들웨어, 네임스페이스같은 라우트 속성 공유가능
- 라우트 속성 배열은 Route::group메소드의 첫번째 인자로 전달
- 그룹 중첩시 속성을 상위그룹과 병합
### 1.4.1. 미들웨어 
```php
// 그룹에 미들웨어 할당하기
// 미들웨어는 배열에 나열된 순서대로 실행됨
Route::middleware(['first', 'second'])->group(function () {
    Route::get('/', function () {
    });
    Route::get('user/profile', function () {
    });
});
```
### 1.4.2. 네임스페이스 
```php
// 컨트롤러들에 동일한 네임스페이스 할당하기
// 기본적으로 RouteServiceProvider는 App\Http\Controllers네임스페이스를 접두사로 지정하지 않아도 컨트롤러가 등록되도록 함(네임스페이스 그룹 내에서 라우트 파일 로드)
Route::namespace('Admin')->group(function () { 
    // App\Http\Controllers
    // Controllers Within The "App\Http\Controllers\Admin" Namespace
});
```
### 1.4.3. 서브 도메인 라우팅
```php
// 서브도메인 일부를 추출하여 라우트 파라미터로 할당
// 루트 도메인 라우트 등록 전에 서브 도메인 라우트를 등록해야 함
// (루트 도메인 라우트가 동일한 URI라우트를 가진 서브 도메인 라우트를 덮어쓰지 않도록)
Route::domain('{account}.myapp.com')->group(function () {
    Route::get('user/{id}', function ($account, $id) { // {account} 부분을 파라미터로 받음
    });
});
```
### 1.4.4. 라우트 Prefix
```php
// 특정 URI를 접두어로 지정
Route::prefix('admin')->group(function () {
    Route::get('users', function () {
        // Matches The "/admin/users" URL
    });
});
```
### 1.4.5. 라우트 이름 Prefix
```php
// 라우트이름에 접두사 붙이기 (접두사 뒤에 .을 붙여야 함)
Route::name('admin.')->group(function () {
    Route::get('users', function () {
    })->name('users'); // Route assigned name "admin.users"...
});
```
## 1.5. 라우트 모델 바인딩
- 라우트/컨트롤러 액션에 자동으로 모델 인스턴스를 주입할 수 있게 해줌
### 1.5.1. 묵시적 바인딩
```php
// 라우트/컨트롤러 액션에서 정의된 라우트 세그먼트({user})와
// App\User Eloquent모델로 타입힌트된 변수($user)가 일치하면
// 일치하는 ID값을 가진 모델 인스턴스를 주입해준다.
// (api/users/1로 요청시 db에서 id값이 1인 User데이터를 찾아 인스턴스로 주입)
// (매칭되는 모델 인스턴스 없을 경우 404응답 생성)
Route::get('api/users/{user}', function (App\User $user) {
    return $user->email;
});
```
- 키 이름 변경
    ```php
    // 모델 클래스 찾을 때 id와 다른 DB컬럼을 사용하는 모델을 바인딩하고 싶다면, Eloquent모델의 getRouteKeyName 메소드를 재지정
    public function getRouteKeyName()
    {
        return 'slug';
    }
    ```
### 1.5.2. 명시적 바인딩
```php
// RouteServiceProviders클래스의 boot()메소드에서 명시적으로 모델 바인딩 정의
public function boot()
{
    parent::boot();
    Route::model('user', App\User::class); // 주어진 파라미터에 대한 모델클래스를 명시적으로 지정
}
```
```php
Route::get('profile/{user}', function (App\User $user) {
});
```
- 의존성 해결 로직 커스터마이징
    - 방법1
        ```php
        public function boot()
        {
            parent::boot();

            // bind()에 전달되는 클로저에는 URI세그먼트 값이 전달되고,
            // 라우트에 주입돼야 하는 클래스 인스턴스를 반환.
            Route::bind('user', function ($value) { 
                return App\User::where('name', $value)->firstOrFail();
            });
        }
        ```
    - 방법2
        ```php
        // Eloquent 모델에서 resolveRouteBinding()을 오버라이딩
        public function resolveRouteBinding($value) // URI세그먼트값 받음
        {
            return $this->where('name', $value)->firstOrFail(); //라우트에 삽입돼야 할 클래스 인스턴스 반환
        }
        ```

## 1.6. 대체 라우트
- 요청과 일치하는 라우트가 없을 때 실행 (일반적으로는 exception handler에 의해 404페이지 렌더링)
- routes/web.php에서 fallback라우트 정의시 web미들웨어 그룹의 모든 미들웨어가 라우트에 적용 
- 대체라우트는 마지막 라우트로 등록돼야 함 
```php
Route::fallback(function () {
});
```

## 1.7. Rate 제한
```php
// 라우트 접속을 제한하는 throttle미들웨어 지정
// 인증된 유저가 라우트 그룹에 1분당 60번까지 접속하도록 제한하는 경우
Route::middleware('auth:api', 'throttle:60,1')->group(function () {
    Route::get('/user', function () {
    });
});
```
- 동적 Rate 제한
    ```php
    // 인증된 유저 모델의 rate_limit속성으로 접속 제한하는 경우
    Route::middleware('auth:api', 'throttle:rate_limit,1')->group(function () {
        Route::get('/user', function () {
        });
    });
    ```
- 게스트 / 인증된 사용자의 Rate제한
    ```php
    // 1분당 접속제한수가 게스트는 10 인증된 사용자는 60인 경우
    Route::middleware('throttle:10|60,1')->group(function () {
    })
    // 'throttle:10|rate_limit,1' 로 동적 rate제한과 조합해서 사용가능
    ```
## 1.8. Form 메소드 Spoofing
- HTML form에서 PUT, PATCH, DELETE액션을 지원하지 않음. 
- PUT, PATCH, DELETE로 지정된 라우트 호출하려면 _method필드를 지정해야 함
- 방법1. html의 hidden input 
    ```php
    <form action="/foo/bar" method="POST">
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
    </form>
    ```
- 방법2. @method 블레이드 지시어
    ```php
    <form action="/foo/bar" method="POST">
        @method('PUT')
        @csrf
    </form>
    ```
## 1.9. 현재 라우트에 액세스하기
```php
// 현재 요청을 처리하는 라우트 정보 가져오기
$route = Route::current();
$name = Route::currentRouteName();
$action = Route::currentRouteAction();
```

# 2. Middleware  
## 2.1. 개요
- HTTP 요청을 간편하게 필터링 (사용자 인증여부에 따른 리다이렉트/요청처리, CORS를 위한 응답헤더 추가, request 로깅 등...)
- HTTP 요청이 애플리케이션에 도달하기 전에 반드시 통과해야 하는 일종의 단계(layers)
- app/Http/Middleware디렉토리 내 존재
## 2.2. 미들웨어 정의
- 아티즌 make:middleware명령어 사용
    ```bash
    $ php artisan make:middleware CheckAge # app/Http/Middleware 디렉토리 안에 CheckAge 클래스 생성
    ```
- 미들웨어는 서비스 컨테이너를 통해 처리 (미들웨어 생성자에 필요한 의존성 입력가능)
- Before & After 미들웨어
    ```php
    // 미들웨어를 'http요청 처리 전'에 실행할 때 
    namespace App\Http\Middleware;

    use Closure;

    class BeforeMiddleware // http요청 처리 전 실행되는 미들웨어
    {
        public function handle($request, Closure $next)
        {
            // Perform action

            return $next($request); // 미들웨어 처리후 http요청 처리
        }
    }
    ```
    ```php
    // 미들웨어를 'http요청 처리 후'에 실행할 때 
    namespace App\Http\Middleware;

    use Closure;

    class AfterMiddleware // http요청 처리 후 실행되는 미들웨어
    {
        public function handle($request, Closure $next)
        {
            $response = $next($request); //

            // Perform action

            return $response; // 미들웨어 처리 후 http응답 처리
        }
    }
    ```

## 2.3. 미들웨어 등록
- app/Http/Kernel.php - Kernel클래스에 미들웨어 속성을 등록
### 2.3.1. 글로벌 미들웨어
- 모든 http request에 대해 미들웨어를 작동시키는 경우
- Kernel클래스의 $middleware 속성에 미들웨어 등록

### 2.3.2. 라우트에 미들웨어 지정
- 미들웨어를 특정 라우트에만 할당시키는 경우
- Kernel클래스의 $routeMiddleware 속성에 미들웨어 추가 (기본적으로 라라벨에 포함된 미들웨어 목록이 존재함)
    ```php
    protected $routeMiddleware = [
        // 미들웨어 이름(key) => 미들웨어 클래스
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
    ```
- Kernel에 미들웨어 등록 후, 라우트에 미들웨어 지정
    ```php
    Route::get('admin/profile', function () {
    })->middleware('auth'); // middleware('미들웨어이름')메소드로 미들웨어 지정
    // ->middleware('first', 'second'); // 복수의 미들웨어 지정가능
    ```
    ```php
    use App\Http\Middleware\CheckAge;

    Route::get('admin/profile', function () {
    })->middleware(CheckAge::class); // 전체클래스 이름으로 미들웨어 지정가능
    ```

### 2.3.3. 미들웨어 그룹
- 복수의 미들웨어를 하나의 이름으로 묶어서 라우트에 할당하는 경우
- Kernel클래스의 $middlewareGroups속성 추가(기본적으로 라라벨은 web, api미들웨어 그룹 제공)
    ```php
    protected $middlewareGroups = [
        // 미들웨어그룹이름 => [미들웨어 클래스들]
        'web' => [  
            //web미들웨어 그룹은 자동으로 RouteServiceProvider에 의해 routes/web.php에 적용됨
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'auth:api',
        ],
    ];
    ```
- Kernel에 미들웨어 그룹 등록 후, 라우트에 미들웨어 지정
    ```php
    // 1. middleware 그룹이름으로 미들웨어 등록
    Route::get('/', function () {
    })->middleware('web'); // -> middleware(['web', 'subscribed']);
    // 2. group()메소드의 첫번째 인자로 미들웨어 배열 넘겨주는 방식
    Route::group(['middleware' => ['web', 'subscribed']], function () {
    });
    // 3. middelware()메소드의 인자로 그룹이름 배열넘겨주고, group()메소드 체이닝해서 라우터 그룹에 할당하는 방식
    Route::middleware(['web', 'subscribed'])->group(function () { 
    });
    ```


### 2.3.4. 미들웨어 순서
- 미들웨어의 실행 순서를 지정하는 경우
- Kernel클래스의 $middlewarePriority로 우선순위 지정
    ```php
    // 미들웨어(글로벌 미들웨어 제외)의 우선순위
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
    ```

## 2.4. 미들웨어 파라미터
- 미들웨어 클래스의 메소드에서 인자를 받음
    ```php
    namespace App\Http\Middleware;

    use Closure;

    class CheckRole // CheckRole 미들웨어
    {
        /**
         * Handle the incoming request.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Closure  $next
         * @param  string  $role
         * @return mixed
         */
        // 미들웨어가 파라미터를 전달받음 ($next인자 다음에 전달)
        public function handle($request, Closure $next, $role)
        {
            // 인증된 사용자가 적절한 role을 가지고 있는지 확인하여 리다이렉트 or 요청처리
            if (! $request->user()->hasRole($role)) {
                // Redirect...
            }

            return $next($request);
        }

    }
    ```
- 라우트 정의시 전달되는 미들웨어 파라미터 지정
    ```php
    Route::put('post/{id}', function ($id) { 
    })->middleware('role:editor'); // '미들웨어 이름:인자' 
    // 복수 지정가능 ->middleware('role:editor,2,3');
    ```

## 2.5. 종료시 동작하는 미들웨어
- Http response 전송 후의 작업 수행
- 미들웨어에서 terminate()메소드 정의 (FastCGI사용하는 웹서버에서 응답 전송 후 terminate() 자동 호출)
    ```php
    namespace Illuminate\Session\Middleware;

    use Closure;

    class StartSession
    {
        public function handle($request, Closure $next)
        {
            return $next($request);
        }

        // terminate()메소드는 request, response를 인자로 전달받는 구조
        // terminate() 호출시 서비스 컨테이너를 통해 새로운 미들웨어 인스턴스 생성. 
        // handle(), termainate()에서 동일한 미들웨어 인스턴스 사용을 위해서는 singleton()메소드로 미들웨어 인스턴스 등록.
        public function terminate($request, $response)
        {
            // Store the session data...
        }
    }
    ```


# 3. CSRF Protection
## 3.1. 개요
- CSRF; Cross-Site Request Fogery 사이트 간 요청 위조 : 인증된 사용자를 대신해 승인되지 않은 커맨드를 악의적으로 활용
- 라라벨은 활성화된 사용자 세션마다 CSRF토큰을 자동으로 생성 (request가능한 인증된 사용자임을 확인하는 데 사용)
- HTML의 form에 hidden CSRF토큰필드를 포함시켜 request를 검증하도록 함
    ```php
    <form method="POST" action="/profile">
        @csrf // 블레이드 지시어
        ...
    </form>
    ```
- web미들웨어 그룹의 VerifyCsrfToken미들웨어가 자동으로 request에 포함된 csrf토큰이 세션에 저장된 토큰과 일치하는지 확인함
- CSRF 토큰 & Javascript
    - 모든 request에 CSRF토큰을 자동으로 추가하도록 javascript라이브러리를 사용할 수 있음
    - resources/js/bootstrap.js에서 제공되는 Axios HTTP 라이브러리 
        - 암호화된 XSRF-TOKEN 쿠키의 값을 사용하여, X-XSRF-TOKEN 헤더를 자동으로 전송

## 3.2. 특정 URI 제외시키기
- CSRF 보호에서 특정 URI는 제외시키는 경우 (e.g. 결제모듈 실행)
- 특정 URI제외 방법
    - routes/web.php의 모든 라우트에 적용되는 web 미들웨어 그룹(VerifyCsrfToken미들웨어가 속한 곳) 외부에 제외하고 싶은 라우트를 위치시킴 (VerifyCsrfToken미들웨어가 적용되지 않도록.)
    - VerifyCsrfToken 미들웨어의 $except 속성에 URI추가
        ```php
        namespace App\Http\Middleware;

        use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

        class VerifyCsrfToken extends Middleware
        {
            /**
            * The URIs that should be excluded from CSRF verification.
            *
            * @var array
            */
            protected $except = [
                'stripe/*',
                'http://example.com/foo/bar',
                'http://example.com/foo/*',
            ];
        }
        ```
    - 테스트 실행시에는 CSRF미들웨어가 자동으로 비활성화

## 3.3. X-XSRF-Token  / X-CSRF-Token
### 3.3.1. X-CSRF-TOKEN
- VerifyCsrfToken 미들웨어는 POST파라미터로 넘어오는 CSRF토큰을 체크하기도 하고, X-CSRF-TOKEN request header를 체크하기도 함
    ```php
    <!-- X-CSRF-TOKEN request header 체크를 위해 먼저 HTML meta 태그에 토큰을 저장-->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    ```
    ```php
    <!-- meta 태그 생성 후 jQuery 등 라이브러리를 이용해 자동으로 모든 헤더에 토큰 추가 가능 -->
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    ```
### 3.3.2. X-XSRF-TOKEN
- 라라벨이 XSRF-TOKEN쿠키에 현재 CSRF토큰을 저장함. 
- 암호화된 XSRF-TOKEN쿠키는 프레임워크에 의해 생성된 response마다 포함됨
- X-XSRF-TOKEN request header를 지정하기 위해 XSRF-TOKEN쿠키를 사용하면 됨

# 4. Controllers  
## 4.1. 개요
- 어플리케이션 요청에 대한 처리로직을 routes.php파일 외에 별도의 컨트롤러 클래스를 통해서 구성 가능
- 컨트롤러 클래스를 구성해 http 요청에 대한 그룹 지정
- 컨트롤러는 app/Http/Controllers 디렉토리에 저장

## 4.2. 기본적인 컨트롤러
### 4.2.1. 컨트롤러 정의
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;

class UserController extends Controller 
// 라라벨 기본 컨트롤러 클래스를 상속하면 미들웨어를 컨트롤러 액션에 연결하는 데 사용하는 middleware() 등의 편리한 메소드 사용가능
{
    public function show($id)
    {
        return view('user.profile', ['user' => User::findOrFail($id)]);
    }
}
```
```php
// 컨트롤러 액션에 라우트 지정가능
// 요청 URI가 user/{id}와 일치할 때 UserController의 show메소드가 호출되며, {id}로 들어온 파라미터도 전달
Route::get('user/{id}', 'UserController@show');
```

### 4.2.2. 컨트롤러 & 네임스페이스
- 라우트 정의시 컨트롤러의 전체 네임스페이스 지정할 필요 없음
- 기본적으로 RouteServiceProvider는 App\Http\Controllers네임스페이스를 접두사로 지정하지 않아도 컨트롤러가 등록되도록 함 (네임스페이스 그룹 내에서 라우트 파일 로드)
- 라우트 정의시 App\Http\Controllers 뒤의 클래스이름만 지정하면 됨
```php
Route::get('foo', 'Photos\AdminController@method');
// App\Http\Controllers\Photos\AdminController의 method호출
```


### 4.2.3. 단일동작 컨트롤러
- 단일액션만을 처리하는 컨트롤러에는 하나의  __invoke()메소드 정의
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;

class ShowProfile extends Controller
{
    public function __invoke($id)
    {
        return view('user.profile', ['user' => User::findOrFail($id)]);
    }
}
```
```php
// 단일동작 컨트롤러에 대한 라우트 정의시 함수 @액션 없이 클래스명만 지정 
Route::get('user/{id}', 'ShowProfile'); 
```
- artisan 명령어로 단일동작 컨트롤러 생성가능
```bash
# make:controller에 --invokable옵션 사용
php artisan make:controller ShowProfile --invokable
```


## 4.3. 컨트롤러 미들웨어 지정하기
1. 미들웨어를 컨트롤러 라우트에 할당
    ```php
    Route::get('profile', 'UserController@show')->middleware('auth');
    ```
2. 생성자에서 미들웨어 지정시 더욱 편리함
    ```php
    class UserController extends Controller
    {
        public function __construct()
        {
            $this->middleware('auth');
            
            // 특정 메소드에 대해 미들웨어 사용을 제한할 수 있음
            $this->middleware('log')->only('index');
            $this->middleware('subscribed')->except('store');
        }
    }
    ```
3. Closure사용하여 미들웨어 지정
    ```php 
    // 컨트롤러 클래스 내에서 클로저를 사용해 미들웨어 지정
    // 전체 미들웨어 클래스를 정의할 필요없이 단일 컨트롤러에 미들웨어 할당하는 편리한 방법
    $this->middleware(function ($request, $next) {
        //...
        return $next($request);
    });
    ```


## 4.4. 리소스 컨트롤러
- 리소스 라우팅 : 일반적인 CRUD처리 경로를 한 줄의 코드로 컨트롤러에 할당
    1. resource controller 생성
        ```bash
        $ php artisan make:controller PhotoController --resource
        # app/Http/Controllers/PhotoController.php파일을 생성
        # PhotoController는 각 resource에 해당하는 메소드를 가지게 됨
        ```
    2. 컨트롤러 라우트 정의
        ```php
        // resourceful 라우트
        Route::resource('photos', 'PhotoController');
        // photo를 구성하는 RESTful 액션에 대한 라우트가 설정됨
        ```
        ```php
        // 한 번에 여러개의 리소스 컨트롤러 라우트 등록가능
        Route::resources([
            'photos' => 'PhotoController',
            'posts' => 'PostController'
        ]);
        ``` 
        ```php 
        /** resourceful 컨트롤러/라우트에 의해 구성되는 액션들 **/
        /*
        Verb | URI | Action | Route | Name
        ----------------------------------
        GET	| /photos | index | photos.index
        GET	| /photos/create | create | photos.create
        POST | /photos | store | photos.store
        GET | /photos/{photo} | show | photos.show
        GET | /photos/{photo}/edit | edit | photos.edit
        PUT/PATCH | /photos/{photo} | update | photos.update
        */
        ```
- 리소스 모델 지정하기
    - 라우트 모델 바인딩을 사용하고 있고, 리소스컨트롤러의 메소드에 모델인스턴스에 대한 타입힌트를 하려는 경우
    ```bash
    # 리소스 컨트롤러 생성시 --model옵션 사용
    $ php artisan make:controller PhotoController --resource --model=Photo
    ```
- 스푸핑 폼 함수 (html form이 만들 수 없는 PUT, PATCH, DELETE요청을 우회하여 요청 - ## 1.8. Form 메소드 Spoofing 참조)


### 4.4.1. Resource라우트의 일부만 지정하기
```php
// 리소스 컨트롤러 액션 중 특정 액션을 제한하여 라우트 정의
Route::resource('photos', 'PhotoController')->only([
    'index', 'show'
]);
Route::resource('photos', 'PhotoController')->except([
    'create', 'store', 'update', 'destroy'
]);
```
- API 리소스 라우트 (HTML템플릿을 표시하는 create,edit 라우트를 제외)
    ```php
    Route::apiResource('photos', 'PhotoController');
    ```
    ```php
    Route::apiResources([ // 복수의 api리소스컨트롤러에 대한 라우트 정의
        'photos' => 'PhotoController',
        'posts' => 'PostController'
    ]);
    ```
    ```php
    # artisan의 make:controller의 --api옵션으로 API 리소스 컨트롤러 생성
    $ php artisan make:controller API/PhotoController --api
    ```


### 4.4.2. 중첩된 Resources
- e.g. 사진리소스는 사진에 첨부된 다수의 코멘트를 가질 수 있음. 
- 리소스컨트롤러 중첩을 위해 라우트 경로 선언시 dot.을 사용
```php
Route::resource('photos.comments', 'PhotoCommentController');
// photos/{photos}/comments/{comments} URI로 접근가능
```


### 4.4.3. 리소스 라우트이름 지정하기
- 기본으로 정의된 리소스 컨트롤러의 각 액션에 대한 라우트 이름('resourceful 컨트롤러에 의해 구성되는 액션들' 참고)을 names()메소드로 재지정할 수 있음
```php
Route::resource('photos', 'PhotoController')->names([
    'create' => 'photos.build'
]);
```

### 4.4.4. 리소스 라우트 파라미터이름 지정하기
- 기본으로 정의된 리소스 파라미터명('resourceful 컨트롤러에 의해 구성되는 액션들' 참고)을 parameters메소드로 덮어쓰기 할 수 있음
```php
Route::resource('users', 'AdminUserController')->parameters([
    'users' => 'admin_user' // 리소스이름 => 파라미터이름
]);
// show액션 라우트가 /users/{admin_user} URI를 생성
```

### 4.4.5. 리소스 URI의 지역화(다국어 동사처리)
- Route::resource는 영어 동사형태로 된 리소스 URI를 구성('resourceful 컨트롤러에 의해 구성되는 액션들' 참고)
- AppServiceProvider 파일의 boot()메소드 내에서 리소스 URI의 액션동사를 localization 가능
```php
use Illuminate\Support\Facades\Route;

public function boot()
{
    Route::resourceVerbs([
        'create' => 'crear',
        'edit' => 'editar',
    ]);
}
// 위 설정에 따라 리소스라우트는 /fotos/crear, /fotos/{foto}/editar URI를 생성 
```

### 4.4.6. 리소스 컨트롤러 라우트에 추가하기
```php
// 리소스 컨트롤러에 추가 라우팅 구성시 Route::resource 호출 이전에 등록되어야 함
Route::get('photos/popular', 'PhotoController@method');
Route::resource('photos', 'PhotoController');
```


## 4.5. 의존성 주입 & 컨트롤러
### 4.5.1. 생성자 주입
```php
namespace App\Http\Controllers;

use App\Repositories\UserRepository;

class UserController extends Controller
{
    /**
     * The user repository instance.
     */
    protected $users;

    /**
     * Create a new controller instance.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(UserRepository $users) // 생성자에 타입힌트를 주어 컨트롤러에서 사용할 인스턴스 주입
    {
        $this->users = $users;
    }
}
```
### 4.5.2. 메소드 주입
```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request) // 액션 메소드에 타입힌트를 주어 메소드에서 사용할 Illuminate\Http\Request의 인스턴스를 주입
    { 
        $name = $request->name;
        //
    }
}
```
- 컨트롤러 액션 메소드가 의존성 타입힌트와 라우트 파라미터를 함께 받도록 하기
    ```php
    // 컨트롤러 메소드가  입력값을 받도록 라우트를 정의
    Route::put('user/{id}', 'UserController@update');
    ```
    ```php
    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    class UserController extends Controller
    {
        /**
         * Update the given user.
         *
         * @param  Request  $request
         * @param  string  $id
         * @return Response
         */
        public function update(Request $request, $id) // 메소드에 타입힌트를 하면서 라우트 파라미터에도 액세스 가능
        {
            //
        }
    }
    ```

## 4.6. 라우트 캐싱
- 라우트 캐시 사용시 라우트 등록시 소요 시간이 감소
- 컨트롤러 라우트에서 사용가능 (클로저 기반 라우트에서는 동작 X)
- 라우트 캐시생성 artisan 명령어
    ```bash
    $ php artisan route:cache
    # 실행시 캐시된 라우트 파일이 모든 요청에 로드
    # 새로운 라우트 추가시 라우트 캐시를 새로 생성해야 함 (배포 중 route:cache 명령 실행으로 간단하게 생성가능)
    ```
- 라우트 캐시 제거 artisan 명령어
    ```php
    $ php artisan route:clear
    ```

# 5. Requests
## 5.1. Request 액세스하기
```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request) // Request 타입힌트하여 의존성 주입해야 액세스 가능
    {
        $name = $request->input('name');

        //
    }
}
```
- 의존성 주입 & 라우트 파라미터
    ```php
    Route::put('user/{id}', 'UserController@update'); // 라우트 파리미터를 받는 경우
    ```
    ```php
    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    class UserController extends Controller
    {
        public function update(Request $request, $id) // Illuminate\Http\Request를 타입힌트 하면서 동시에 라우트 파라미터 {id}에 접근가능
        {
        }
    }
    ```
- 라우트 클로저를 통해서 Request 액세스하기
    ```php
    use Illuminate\Http\Request;

    Route::get('/', function (Request $request) { // 라우트 클로저에서도 Illuminate\Http\Request를 타입힌트하여 의존성 주입 가능
    });
    ```


### 5.1.1. Request 경로 & 메소드
Illuminate\Http\Request 인스턴스는 http request 검사를 위한 다양한 메소드 제공하며 Symfony\Component\HttpFoundation\Request 클래스를 상속함

- Request 경로 조회하기
    ```php
    // request의 path메소드는 request의 경로정보를 반환
    $uri = $request->path(); 
    // http://domain.com/foo/bar요청시 foo/bar를 반환
    ```
    ```php
    // is메소드는 요청이 특정 패턴에 상응하는지 확인
    if ($request->is('admin/*')) { 
        //
    }
    ```
- Request URI 조회하기
    ```php
    // Without Query String...
    $url = $request->url(); // http://domain.com/foo/bar

    // With Query String...
    $url = $request->fullUrl(); // http://domain.com/foo/bar?var=1
    ```
- Request HTTP 메소드(verb) 조회하기
    ```php
    $method = $request->method(); // 요청 메소드 확인
    if ($request->isMethod('post')) { // 특정 메소드와 상응하는지 확인
    }
    ```

### 5.1.2. PSR-7 Requests
- PSR-7 표준 : request, response를 포함한 http메세지들에 대한 인터페이스 지정
- 라라벨 request대신 PSR-7요청의 인스턴스 사용가능
    - 사용방법
        1. 라이브러리 설치
            ```bash
            # Symfony HTTP Message Bridge 컴포넌트 사용하여
            # 라라벨의 요청과 응답을 PSR-7에 맞는 구현체로 변환하게 됨
            $ composer require symfony/psr-http-message-bridge
            $ composer require zendframework/zend-diactoros
            ```
        2. 라우트클로저 or 컨트롤러 메소드에 타입힌트하여 PSR-7 Request얻기
            ```php
            use Psr\Http\Message\ServerRequestInterface;

            Route::get('/', function (ServerRequestInterface $request) { // ServerRequestInterface PSR-7 request인스턴스 주입
                // 반환된 PSR-7 response 인스턴스는 자동으로 라라벨 response 인스턴스로 변환됨
            });
            ```


## 5.2. 입력값 Trim & 일반화 처리
### 글로벌 미들웨어 TrimStrings, ConvertEmptyStringsToNull        
    - App\Http\Kernel클래스의 미들웨어 설정 참고
    - 요청으로 유입되는 문자필드를 자동으로 trim 및 공백필드 null변환
    - 비활성화를 위해서는 $middleware속성에서 해당 미들웨어 제거

## 5.3. 입력값 조회
### 모든 입력값 조회
```php
$input = $request->all(); // all() : 모든 입력데이터를 배열로 조회
```

### 입력값 조회
request verb에 상관없이, Illuminate\Http\Request 인스턴스에서 모든 사용자 입력에 접근 가능하도록 하는 input() 메소드 (쿼리스트링으로 전달된 값도 접근가능)
- input() 기본 사용
    ```php
    $name = $request->input('name');
    ```
    ```php
    $name = $request->input('name', 'Sally'); // 입력값 없이 전달된 경우 두번째 인자를 기본값으로 반환
    ```
- 배열 input값이 전달되는 경우
    ```php
    $name = $request->input('products.0.name'); // 배열에 접근하기 위해 dot. 표기법 사용
    $names = $request->input('products.*.name');
    ```
- 모든 입력값을 연관배열로 검색하기
    ```php
    $input = $request->input(); // 인자없이 input() 호출
    ```

### 쿼리 스트링에서만 입력값 조회
- query() 기본사용
    ```php
    $name = $request->query('name');
    ```
    ```php
    $name = $request->query('name', 'Helen'); // 해당 쿼리 스트링값 없이 전달된 경우 두번째 인자를 기본값으로 반환
    ```
- 모든 쿼리 스트링값을 연관배열로 검색하기
    ```php
    $input = $request->query(); // 인자없이 query() 호출
    ```    
### 동적 속성을 통한 입력값 조회
- Illuminate\Http\Request인스턴스의 동적속성 : e.g. form의 필드
    ```php
    $name = $request->name; // form의 'name' 필드값에 접근가능
    ```
- 동적속성 사용시 request payload 내 파라미터 값을 먼저 찾고, 없으면 라우트 파라미터의 필드를 찾게 됨.

### JSON 입력 값 조회
JSON요청 전달시, 헤더속성 Content-Type이 application/json으로 지정되어 있다면 input메소드로 JSON데이터에 접근가능 
```php
$name = $request->input('user.name'); // dot.으로 JSON배열 접근 가능
```

### 입력 데이터의 한 부분 조회
```php
// 입력데이터 중 반환할 부분 제한하기
$input = $request->only(['username', 'password']);
$input = $request->only('username', 'password');
$input = $request->except(['credit_card']);
$input = $request->except('credit_card');
```

### 입력값이 존재하는지 확인
```php
// request에 특정값이 존재하는지 확인하는 has()
if ($request->has('name')) { 
}
// has()에 배열이 인자로 주어지면 주어진 모든 값이 존재하는지 확인
if ($request->has(['name', 'email'])) {
}
// hasAny()는 지정값이 하나라도 존재하면 true반환
if ($request->hasAny(['name', 'email'])) {
}
// missing()은 주어진 키가 request에 없는지 확인
if ($request->missing('name')) {
}
```



### 5.3.1. 이전 request입력값 저장/확인하기
- request입력값을 다음 request에서도 유지할 수 있음
- 유효성 검사 오류 감지 후 폼을 다시 채울 때 사용
- 유효성 검사 기능들이 자동으로 이 기능을 호출
- 방법
    1. 입력값을 세션에 임시 저장하기
        - Illuminate\Http\Request클래스의 flash() 메소드
            ```php
            $request->flash(); // 현재 request입력값을 세션에 저장

            // 현재 request입력값의 일부분을 세션에 임시 저장
            // pw같은 민감정보를 제외시킬 때 유용
            $request->flashOnly(['username', 'email']);
            $request->flashExcept('password');
            ```
        - 입력값을 임시저장한 후 리다이렉트하기
            ```php
            return redirect('form')->withInput();
            // 리다이렉트시 withInput()으로 입력값을 세션에 임시저장하도록 간단하게 메소드 체이닝
            return redirect('form')->withInput( 
                $request->except('password') // 일부값은 제외하고 임시저장
            );
            ```
    2. 이전request에서 저장된 입력값 조회하기
        - old()메소드로 세션에 저장된 입력데이터 꺼내기
            ```php
            $username = $request->old('username'); 
            ```
        - 블레이드 템플릿에서 글로벌 old 헬퍼함수 사용
            ```php
            <input type="text" name="username" value="{{ old('username') }}">
            ```



### 5.3.2. 쿠키
- Request에서 쿠키 조회하기
    - 라라벨에서 생성된 모든 쿠키는 인증코드와 함께 암호화됨(쿠키변조시 유효하지 않은 것으로 간주)
    - request에서 쿠키 값 가져오기
        ```php
        // Illuminate\Http\Request 인스턴스에서 cookie 메소드 사용
        $value = $request->cookie('name');
        ```
    - Cookie 파사드 사용하여 쿠키값 가져오기
        ```php
        use Illuminate\Support\Facades\Cookie;
        $value = Cookie::get('name');
        ```
- Response에 쿠키 추가하기
    - Illuminate\Http\Response인스턴스에 cookie메소드를 사용해 쿠키 추가
        ```php
        return response('Hello World')->cookie(
            'name', 'value', $minutes // 쿠키명, 쿠키값, 유효시간(분)
        );
        ```
        ```php
        return response('Hello World')->cookie(
            'name', 'value', $minutes, $path, $domain, $secure, $httpOnly // php setcookie메소드에 제공되는 인자들과 동일한 의미를 갖는 인자들 사용가능
        ); 
        ```
- 쿠키 인스턴스 생성하기
    - Symfony\Component\HttpFoundation\Cookie 인스턴스를 생성해두고, 나중에 response 인스턴스에 추가시키거나/시키지 않을 수도 있음
        ```php
        // cookie헬퍼함수사용하여 쿠키 인스턴스 생성
        $cookie = cookie('name', 'value', $minutes);
        // response에 쿠키 추가 
        return response('Hello World')->cookie($cookie); 
        ```


## 5.4. 파일처리
### 5.4.1. 업로드된 파일 조회 
- 업로드 파일 인스턴스(PHP의 SplFileInfo클래스를 상속한 
    // Illuminate\Http\UploadedFile클래스의 인스턴스) 얻기
    ```php
    // Illuminate\Http\Request 인스턴스의 file()로 업로드된 파일에 액세스
    // file()은 PHP의 SplFileInfo클래스를 상속한 
    // Illuminate\Http\UploadedFile클래스의 인스턴스를 반환하며, 파일상호작용 메소드들을 제공
    $file = $request->file('photo');

    // Illuminate\Http\Request 인스턴스의 동적속성(form 필드)로 업로드된 파일에 액세스
    $file = $request->photo; 

    // request가 file을 갖고 있는지 확인
    if ($request->hasFile('photo')) { 
    }
    ```

- 업로드 성공 확인
    ```php 
    // file()에서 반환된 UploadedFile인스턴스의 isValid()를 사용해 파일존재여부/업로드파일의 이상여부 확인 가능
    if ($request->file('photo')->isValid()) {
    }
    ```

- 파일 경로 & 확장자
    ```php 
    $path = $request->photo->path(); // UploadedFile의 전체경로 반환
    $extension = $request->photo->extension(); // UploadedFile의 확장자 반환
    ```


### 5.4.2. 업로드된 파일 저장
- UploadedFile클래스 store()
    - 업로드된 파일을 로컬 파일 시스템이나 클라우드 스토리지 디스크에 이동시킴
    - 루트 디렉토리를 기준으로 파일이 저장될 경로 전달받음 (파일명은 미포함해야 함. 파일명은 고유 ID로 생성.)
        ```php
        $path = $request->photo->store('images'); // 파일저장경로
        $path = $request->photo->store('images', 's3'); // 파일저장경로, 파일저장 디스크이름
        ```
    - 파일명을 임의 지정시 
        ```php
        // storeAs()메소드에 경로, 파일명, 디스크명을 인자로 넘겨줌
        $path = $request->photo->storeAs('images', 'filename.jpg');
        $path = $request->photo->storeAs('images', 'filename.jpg', 's3');
        ```

## 5.5. 신뢰할 수 있는 프록시 설정
- TLS / SSL 인증서가 적용된 로드밸런서 뒤에서 애플리케이션 실행시,
애플리케이션에서 HTTPS링크가 생성되지 않는 경우 발생
- 애플리케이션이 포트80 로드밸런서에서 전송되는 트래픽 뒤에 위치해서 HTTPS링크를 생성해야 함을 알지 못하기 때문
- App\Http\Middleware\TrustProxies 미들웨어로 신뢰할 수 있는 로드밸런서 or 프록시설정, 신뢰해야 할 프록시헤더 설정 가능 ($proxies 속성배열에 설정)
    ```php
    namespace App\Http\Middleware;

    use Fideloper\Proxy\TrustProxies as Middleware;
    use Illuminate\Http\Request;

    class TrustProxies extends Middleware
    {
        /**
        * The trusted proxies for this application.
        *
        * @var array
        */
        protected $proxies = [
            '192.168.1.1',
            '192.168.1.2',
        ];
        // protected $proxies = '*'; // 모든 프록시 신뢰하기
        // 클라우드 로드밸런서 사용시 실제 로드밸런서 IP를 알기 어려우므로 모든 프록시를 신뢰하도록 함

        /**
        * The headers that should be used to detect proxies.
        *
        * @var string
        */
        protected $headers = Request::HEADER_X_FORWARDED_ALL;
    }
    ```

# 6. Responses
## 6.1. Responses 생성
- 문자열 & 배열 response
    - 문자열 반환과 response
        ```php
        Route::get('/', function () {
            return 'Hello World'; // 라우트/컨트롤러에서 문자열 반환시, 프레임워크가 자동으로 문자열을 HTTP response로 변환
        });
        ```
    - 배열 반환과 JSON Response
        ```php
        Route::get('/', function () {
            return [1, 2, 3]; // 라우트/컨트롤러에서 배열 반환시, 프레임워크가 자동으로 배열을 JSON response로 변환
        });
        ```
    - 라우트/컨트롤러에서 Eloquent컬렉션 반환시에도 JSON response로 변환
- Response 객체  
    - 라우트에서 Illuminate\Http\Response 인스턴스나 views도 반환 가능
    - Response인스턴스는 Symfony\Component\HttpFoundation\Response클래스를 구현하며, HTTP response 생성위한 메소드 제공 (response상태코드, 헤더 변경 등 가능)
    ```php
    Route::get('home', function () {
        return response('Hello World', 200) // response객체반환
                    ->header('Content-Type', 'text/plain');
    });
    ```
### 6.1.1. Responses에 헤더추가
```php
// response돌려주기 전에 메소드체이닝으로 header추가 가능
return response($content)
            ->header('Content-Type', $type)
            ->header('X-Header-One', 'Header Value')
            ->header('X-Header-Two', 'Header Value');
```
```php
return response($content)
            ->withHeaders([ // withHeaders()로 response에 추가할 헤더의 배열 지정 가능
                'Content-Type' => $type,
                'X-Header-One' => 'Header Value',
                'X-Header-Two' => 'Header Value',
            ]);
```

- Cache Control 미들웨어
    ```php
    // cache.headers 미들웨어는 라우트그룹에 cache control헤더를 빠르게 설정
    // etag 지시어 지정시 응답내용의 MD5해시가 자동으로 ETag식별자로 설정
    Route::middleware('cache.headers:public;max_age=2628000;etag')->group(function() {
        Route::get('privacy', function () {
            // ...
        });
        Route::get('terms', function () {
            // ...
        });
    });
    ```
### 6.1.2. Responses에 쿠키추가
```php
// cookie()메소드 체이닝
return response($content)
                ->header('Content-Type', $type) 
                ->cookie('name', 'value', $minutes); // cookie생성하여 response에 추가
```
```php
// Cookie 파사드의 queue 사용
// response가 브라우저로 전달되기 전에 쿠키가 추가됨
Cookie::queue(Cookie::make('name', 'value', $minutes));
Cookie::queue('name', 'value', $minutes);
```

### 6.1.3. 쿠키 & 암호화
- 라라벨에서 생성되는 쿠키는 암호화 및 서명적용되어 클라이언트에서 수정확인 불가
- 암호화 비활성화
    ```php
    // app/Http/Middleware디렉토리 내 
    // App\Http\Middleware\EncryptCookies미들웨어의 $except속성 설정
    protected $except = [
        'cookie_name',
    ];
    ```


## 6.2. Redirect
- Illuminate\Http\RedirectResponse의 인스턴스
- 리다이렉트를 위한 헤더를 포함하고 있음
- RedirectResponse 인스턴스 생성 방법
    - redirect() 헬퍼함수 사용
        ```php
        Route::get('dashboard', function () {
            return redirect('home/dashboard');
            // 
        });
        ```
    - back() 헬퍼함수 사용
        ```php
        Route::post('user/profile', function () {
            // Validate the request...
            return back()->withInput();
            // 이전 위치로 리다이렉트하면서, 현재 request의 입력값을 세션에 저장
            // back() 함수는 세션을 사용하기 때문에, 라우트 호출이 web미들웨어 그룹 내에 있거나 세션미들웨어를 적용해야 함
        });
        ```
### 6.2.1. 이름이 지정된 라우트로 리다이렉트
```php
return redirect()->route('login'); // redirect()가 인자없이 호출될 때는 Illuminate\Routing\Redirector인스턴스 반환
// route메소드사용하여 이름이 지정된 라우트에 대한 RedirectResponse 생성 가능

return redirect()->route('profile', ['id' => 1]);
// 라우트가 인자를 받는 경우 // URI: profile/{id}
```
- Eloquent 모델을 통한 파라미터 채우기
```php
return redirect()->route('profile', [$user]);
// 라우트 인자를 Eloquent모델로 채울 때 
// URI: profile/{id} // $user의 id가 자동으로 추출되어 전달됨
// Eloquent 모델의 getRouteKey()를 오버라이드하면 id가 아닌 다른 값을 라우트 파라미터에 저장할 수 있음
```

### 6.2.2. 컨트롤러 액션으로 리다이렉트
```php
// action메소드에 컨트롤러@액션명 전달
return redirect()->action('HomeController@index');

// 컨트롤러 라우트에 파라미터가 필요한 경우 
return redirect()->action(
    'UserController@profile', ['id' => 1] // 두번째인자로 전달
);
```
### 6.2.3. 외부 도메인으로 리다이렉트
```php
return redirect()->away('https://www.google.com');
// away()메소드를 호출해서 추가적인 URL 인코딩, 유효성 검사와 확인 과정 없이 RedirectResponse생성가능
```

### 6.2.4. 세션의 임시데이터와 함께 리다이렉트
- RedirectResponse 인스턴스 생성하고, 데이터를 세션에 임시저장
    ```php
    Route::post('user/profile', function () {
        return redirect('dashboard')->with('status', 'Profile updated!');
    });
    ```
- 리다이렉션 이후 세션에 임시저장된 데이터 표시
    ```php
    // 블레이드 문법 사용
    @if (session('status')) // 세션에 저장된 데이터 표시
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
    ```


## 6.3. 기타 Responses 타입들
- contract response
    - response 헬퍼 함수가 인자 없이 호출되면, Illuminate\Contracts\Routing\ResponseFactory contract의 구현체가 반환

### 6.3.1. View Responses
```php
return response()
            ->view('hello', $data, 200) // view헬퍼함수 사용
            ->header('Content-Type', $type);
```
### 6.3.2. JSON Responses
- json()메소드
    -  자동으로 Content-Type 헤더를 application/json으로 설정
    - PHP json_encode 함수를 사용하여 주어진 배열을 JSON으로 변환
        ```php
        return response()->json([
            'name' => 'Abigail',
            'state' => 'CA'
        ]);
        ```
- JSONP response 생성
    - json메소드와 withCallback메소드 조합       
        ```php
        return response()
            ->json(['name' => 'Abigail', 'state' => 'CA'])
            ->withCallback($request->input('callback'));
        ```

### 6.3.3. File Downloads
- download()메소드 
    - 주어진 경로에 해당하는 파일을 다운로드하게 하는 response를 생성
    - download(경로, 파일이름, http헤더 배열)
        ```php
        return response()->download($pathToFile);
        return response()->download($pathToFile, $name, $headers);
        return response()->download($pathToFile)->deleteFileAfterSend();
        ```
- Symfony HttpFoundation클래스
    - 파일다운로드 관리
    - ASCII형식의 파일이름 지정해야 함
- 스트리밍 다운로드
    - 동작의 결과를 저장하지 않고 바로 다운로드가능한 응답으로 반환하는 경우
    - streamDownload(콜백, 파일이름, 헤더배열); 사용
    ```php
    return response()->streamDownload(function () {
        echo GitHub::api('repo')
                    ->contents()
                    ->readme('laravel', 'laravel')['contents'];
    }, 'laravel-readme.md');
    ``` 
### 6.3.4. File Responses
- file() 메소드
    - 파일다운로드 없이 브라우저에 이미지, pdf 같은 파일을 표시
        ```php
        return response()->file($pathToFile);
        return response()->file($pathToFile, $headers); // (파일경로,헤더배열)
        ```
## 6.4. Response 매크로
```php
namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseMacroServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 커스텀 Response정의를 위해 Response파사드의 macro메소드 사용
        Response::macro('caps', function ($value) {
            return Response::make(strtoupper($value));
        }); // macro(매크로명, 클로저); 
        
    }
}
```
```php
// 매크로 등록된 클로저는 response 헬퍼함수를 통해 ResponseFactory 구현객체에서 호출
return response()->caps('foo'); // 매크로명 caps로 클로저 호출?
```



# 7. Views
## 7.1. 뷰 생성하기
- 뷰파일 형태
    ```php
    <html>
        <body>
            <h1>Hello, {{ $name }}</h1>
        </body>
    </html>
    ```
- resources/views디렉토리에 뷰파일 위치
- view헬퍼 사용하여 view 반환
    ```php
    // 뷰파일이 resources/views/greeting.blade.php로 저장되어 있는 경우
    Route::get('/', function () {
        return view('greeting', ['name' => 'James']); 
        // view(뷰파일명, 뷰에서 사용할 데이터)
    });
    ```
- 중첩된 뷰 디렉토리
    ```php
    // resources/views/admin/profile.blade.php 로 디렉토리 중첩된 경우
    return view('admin.profile', $data); // dot.으로 디렉토리 구분
    ```
- 뷰파일 존재여부 확인
    ```php
    // View Facades의 exist사용
    use Illuminate\Support\Facades\View;
    if (View::exists('emails.customer')) {
        //
    }
    ```
- 먼저 사용가능한 뷰 파일 사용하기
    ```php
    // first 메소드
    return view()->first(['custom.admin', 'admin'], $data);
    ```
    ```php
    // View Facades의 first사용
    use Illuminate\Support\Facades\View;
    return View::first(['custom.admin', 'admin'], $data);
    ```

## 7.2. 뷰에 데이터 전달하기
- view()헬퍼함수의 인자로 연관배열 전달
    ```php
    return view('greetings', ['name' => 'Victoria']); 
    // 데이터는 키/값 으로 구성된 배열이어야 함
    // 뷰 내에서 echo $key;와 같이 키에 해당하는 값에 액세스 가능
    ```
- with메소드를 사용하여 개별 데이터 추가
    ```php
    return view('greeting')->with('name', 'Victoria'); // 키/값
    ```
### 7.2.1. 모든 뷰파일에서 데이터 공유하기
- View 파사드의 share()메소드 사용
- 서비스프로바이더의 boot메소드에 구성해놓아야 함
    ```php
    namespace App\Providers;

    use Illuminate\Support\Facades\View;

    class AppServiceProvider extends ServiceProvider
    {
        //...
        public function boot()
        {
            View::share('key', 'value'); // a모든 뷰파일에서 데이터를 공유할 수 있도록 함
        }
    }
    ```
## 7.3. 뷰 컴포저
- 뷰 컴포저 : 뷰 렌더링시 호출되는 콜백 or 클래스메소드
- 뷰 렌더링할 때마다 전달할 데이터가 있다면 해당 로직을 한 곳에서 구성할 수 있게 해줌
- 뷰 컴포저 구성
    1. 서비스 프로바이더에서 뷰 컴포저 구성
        ```php
        namespace App\Providers;

        use Illuminate\Support\Facades\View;
        use Illuminate\Support\ServiceProvider;

        class ViewServiceProvider extends ServiceProvider // ServiceProvider를 통해 뷰 컴포저 구성
        {
            //...
            public function boot()
            {
                // View 파사드를 사용하여 Illuminate\Contracts\View\Factory contract구현체에 액세스
                View::composer( 
                    'profile', 'App\Http\View\Composers\ProfileComposer'
                    // profile뷰가 렌더링 될 때마다 ProfileComposer@compose메소드가 실행
                    // 뷰 컴포저 디렉토리는 임의로 자유럽게 구성 가능
                );
                View::composer('dashboard', function ($view) {
                    //
                });
            }
        }
        ```
    2. config/app.php의 providers배열에 해당 서비스 프로바이더 추가
    3. 뷰 컴포저 클래스 정의
        ```php
        namespace App\Http\View\Composers;

        use App\Repositories\UserRepository;
        use Illuminate\View\View;

        class ProfileComposer // 뷰 컴포저 클래스 
        {
            /**
            * The user repository implementation.
            *
            * @var UserRepository
            */
            protected $users;

            /**
            * Create a new profile composer.
            *
            * @param  UserRepository  $users
            * @return void
            */
            public function __construct(UserRepository $users) // 뷰컴포저에서 필요한 객체를 타입힌트
            {
                // Dependencies automatically resolved by service container...
                $this->users = $users;
            }

            /**
            * Bind data to the view.
            *
            * @param  View  $view
            * @return void
            */
            public function compose(View $view) // 
            {
                $view->with('count', $this->users->count());
            }
            // 뷰 렌더링 전 뷰컴포저의 compose메소드가 Illuminate\View\View 인스턴스와 함께 호출되고, 데이터전달을 위해 with()메소드 사용가능
        }
        ```
- 뷰 컴포저를 다수의 뷰에 적용
    ```php
    View::composer(
        ['profile', 'dashboard'], // 다수의 뷰 배열전달
        'App\Http\View\Composers\MyViewComposer'
    );

    View::composer('*', function ($view) { // 모든 뷰에 뷰컴포저 적용
    });
    ```
- 뷰 크리에이터
    ```php
    // 뷰컴포저와 유사하지만, 뷰 크리에이터는 뷰가 렌더링되기를 기다리지 않고 인스턴스화 된 다음에 바로 실행
    View::creator('profile', 'App\Http\View\Creators\ProfileCreator');
    ```

# 8. URL Generation
## 8.1. 개요
## 8.2. 기본 내용
### 8.2.1. 기본 URL 생성
- url()헬퍼함수로 URL생성
- 자동으로 현재 request의 스키마와 호스트를 사용
```php
$post = App\Post::find(1);
echo url("/posts/{$post->id}"); 
// http://example.com/posts/1
```
### 8.2.2. 현재 URL 액세스
- url()헬퍼함수에 경로미지정시 Illuminate\Routing\UrlGenerator 인스턴스 반환
    ```php
    // Get the current URL without the query string...
    echo url()->current();

    // Get the current URL including the query string...
    echo url()->full();

    // Get the full URL for the previous request...
    echo url()->previous();
    ```
    ```php
    use Illuminate\Support\Facades\URL;

    echo URL::current(); // URL Facade로도 액세스 가능
    ```

## 8.3. 이름이 지정된 라우트 URL

- route() 헬퍼함수 사용
    ```php
    // Route::get('/post/{post}', function () {
    // })->name('post.show'); 
    // 아래와 같이 사용가능

    echo route('post.show', ['post' => 1]);
    // http://example.com/post/1
    // 라우트 이름 지정하여 사용시 라우트에 정의된 실제 URl에 구애받지 않고 URL생성가능 
    // (라우트 URL변경되어도 route함수 호출 한 곳을 수정할 필요 없음)
    ```
    - Eloquent모델의 기본키 사용하여 URL생성
        ```php
        // route() 헬퍼함수는 자동으로 모델 post의 기본키를 추출하여 사용하게 됨
        echo route('post.show', ['post' => $post]);
        ```
    - 복수의 파라미터를 가진 라우트 URL생성
        ```php
        Route::get('/post/{post}/comment/{comment}', function () {
            //
        })->name('comment.show');

        echo route('comment.show', ['post' => 1, 'comment' => 3]);

        // http://example.com/post/1/comment/3
        ```
### 8.3.1. Signed URLs
- Signed URLs
    - signed hash가 쿼리 스트링 뒤에 추가되어, URL 생성 이후 수정되지 않았음을 확인 가능
    - 공개적으로 액세스 가능하지만, 조작으로부터 보호가 필요한 경우 유용
- 이름이 지정된 named 라우트에 서명이 적용된 signed URL을 쉽게 생성가능
    ```php
    // URL 파사드의 signedRoute메소드 사용 가능
    use Illuminate\Support\Facades\URL;
    return URL::signedRoute('unsubscribe', ['user' => 1]);
    ```
    ```php
    // temporarySignedRoute 메소드를 사용해 임시생성 후 만료되는 signed URL 생성 가능
    use Illuminate\Support\Facades\URL;
    return URL::temporarySignedRoute(
        'unsubscribe', now()->addMinutes(30), ['user' => 1]
    );
    ```
- Signed URLs에 대한 Request Validating
    - 방법1. 유입되는 request에 유효한 sign이 있는지 검증하기 위해 request에 hasValidSignature() 메소드를 호출함
        ```php
        use Illuminate\Http\Request;

        Route::get('/unsubscribe/{user}', function (Request $request) {
            if (! $request->hasValidSignature()) { // Signed URLs에 대한 Request Validating
                abort(401);
            }
            // ...
        })->name('unsubscribe');
        ```
    - 방법2. 라우트에 ValidateSignature 미들웨어를 지정
        ```php
        // HTTP 커널의 routeMiddleware 배열에 미들웨어를 등록
        protected $routeMiddleware = [
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        ];
        ```
        ```php
        // 라우트에 Illuminate\Routing\Middleware\ValidateSignature 미들웨어를 지정
        Route::post('/unsubscribe/{user}', function (Request $request) {
            // ...
        })->name('unsubscribe')->middleware('signed');
        ```

## 8.4. 컨트롤러 액션 URL
- action() 함수 : 주어진 컨트롤러 액션에 대한 URL생성
    ```php
    $url = action('HomeController@index'); 
    ```
    ```php
    use App\Http\Controllers\HomeController;
    $url = action([HomeController::class, 'index']); // callable 배열 문법을 통해 액션을 참조하도록 함 // HomeController클래스의 index 메소드를 참조
    ```
    ```php
    $url = action('UserController@profile', ['id' => 1]); // 라우트 파라미터 인자 전달가능
    ```

## 8.5. 기본값
- 특정 URL 파라미터에 request 전 기본값 지정 가능
    ```php
    Route::get('/{locale}/posts', function () {
    })->name('post.index');
    ```
    ```php
    // 라우트 미들웨어에서 기본값 지정
    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Support\Facades\URL;

    class SetDefaultLocaleForUrls
    {
        public function handle($request, Closure $next)
        {
            //  URL::defaults 파사드 메소드를 이용하여, request마다 적용될 {locale} 파라미터의 기본값 설정
            URL::defaults(['locale' => $request->user()->locale]);
            return $next($request);
        }
    }
    ```


# 9. Session
## 9.1. 시작하기
### 9.1.1. 설정하기
- config/session.php로 설정파일이 저장
- 기본적으로 file세션 드라이버를 사용하도록 설정되어 있음 (실서비스시 memcached or redis 드라이버 사용 권장)
- session driver 설정옵션 (request에 따른 세션을 어디에 저장할지 정의)
    - file : storage/framework/sessions 디렉토리에 세션을 저장합니다.
    - cookie : 암호화된 쿠키를 사용하여 안전하게 세션을 저장할 것입니다.
    - database : 세션이 관계형 데이터베이스에 저장된다.
    - memcached / redis : 빠르고, 캐시를 기반으로한 memcached, redis 에 저장합니다.
    - array : 세션은 PHP 배열에 저장되며 세션이 지속되지 않습니다. (테스트 진행시 사용)
### 9.1.2. 드라이버 사전준비사항
- Database 세션 드라이버 사용시
    - 세션 저장 테이블 생성코드 (migration클래스내 정의)
        ```php
        // Schema파사드의 create()메소드 사용하여 테이블 생성
        Schema::create('sessions', function ($table) {
            $table->string('id')->unique();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity');
        });
        ```
    - 마이그레이션 실행
        ```bash
        $ php artisan session:table
        $ php artisan migrate
        ```

- Redis 세션 드라이버 사용시
    - PECL을 통해 PhpRedis 확장모듈 설치 or Composer로 predis/predis 패키지 (~ 1.0) 설치해야 사용가능
    - session 설정 파일 내 connection옵션을 통해 세션에서 사용할 Redis연결 지정가능
## 9.2. 세션 사용
### 9.2.1. 세션 데이터 조회
- 세션데이터 조작 방법1. Request인스턴스 사용
    ```php
    namespace App\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;

    class UserController extends Controller
    {
        public function show(Request $request, $id) // Request의존성주입
        {
            // reqeust를 통해 세션에 접근

            /******** get() : key로 세션값 접근 ********/
            $value = $request->session()->get('key'); // get메소드의 두 번째 인자로 키 존재하지 않을 경우 사용할 기본값 지정 가능(클로저도 지정가능)
            

            /******** all() : 모든 세션 데이터 조회 ********/
            $data = $request->session()->all(); // 
            
            /******** has() : 세션아이템 존재여부 확인 ********/
            if ($request->session()->has('users')) {
            } // 아이템존재여부와 아이템 값의 null여부 검사

            /******** exists() : 세션아이템 존재여부 확인 ********/
            if ($request->session()->exists('users')) {
            } // 아이템존재여부만 검사(null값이어도 true)
        }
    }
    ```
- 세션데이터 조작 방법2. session()헬퍼함수 사용
    ```php
    Route::get('home', function () {
        // Retrieve a piece of data from the session...
        $value = session('key'); // 키에해당하는 세션 값 반환

        // Specifying a default value... 
        $value = session('key', 'default');

        // Store a piece of data in the session...
        session(['key' => 'value']); // 키=>값 배열로 전달시 세션에 데이터 저장
    });
    ```
- 두 방법 모두 테스트케이스에서 assertSessionHas() 메소드를 이용해 테스트 가능


### 9.2.2. 세션 데이터 저장
- 방법1. Request인스턴스 사용
    ```php
    // put()메소드로 세션데이터 저장
    $request->session()->put('key', 'value'); 

    // 세션에 저장된 배열에 값 추가
    $request->session()->push('user.teams', 'developers');

    // 세션에서 아이템 가져오면서 삭제
    $value = $request->session()->pull('key', 'default');
    ```

- 방법2. session()헬퍼함수 사용
    ```php
    session(['key' => 'value']);
    ```



### 9.2.3. 세션 데이터 임시저장
- 다음 request에서만 사용하기 위한 값을 세션에 임시저장
    ```php
    $request->session()->flash('status', 'Task was successful!');
    ```
- 임시데이터를 좀 더 오래 유지하는 경우
    ```php
    $request->session()->reflash();

    // 특정 임시 데이터만을 유지
    $request->session()->keep(['username', 'email']);
    ```
### 9.2.4. 세션 데이터 삭제
    ```php
    // 세션에서 특정 데이터(들) 삭제
    $request->session()->forget('key');
    $request->session()->forget(['key1', 'key2']);

    $request->session()->flush(); // 세션에서 모든 데이터 삭제
    ```
### 9.2.5. 세션 ID 다시 생성
- 세션 fixation 방지를 위해 재생성
- 세션ID 수동으로 재생성시 (라라벨 LoginController는 인증과정에서 세션ID 자동으로 재생성)
    ```php
    $request->session()->regenerate();
    ```

## 9.3. 사용자 정의 세션 드라이버 추가
### 9.3.1. 드라이버 구현
```php
namespace App\Extensions; // SessionHandler를 다른 디렉토리에 생성하는 것도 가능
class MongoSessionHandler implements \SessionHandlerInterface
// 사용자정의 세션드라이버는 SessionHandlerInterface를 구현해야 함
{
    /******* 구현해야 할 메소드 *******/ 
    public function open($savePath, $sessionName) {}
    // 일반적으로 파일기반의 세션저장 시스템에서 사용. 라라벨이 file 세션드라이버제공하기 때문에 비어있는 형태로 구성가능
    public function close() {} 
    // 대부분의 드라이버에서 필요X
    
    public function read($sessionId) {}
    // 주어진 $sessionId에 해당하는 세션데이터를 직렬화한 문자열 반환(라라벨이 알아서 처리)
    public function write($sessionId, $data) {}
    // 세션데이터 문자열을 db 등에 저장(라라벨이 알아서 처리)

    public function destroy($sessionId) {} // 주어진 데이터 삭제
    public function gc($lifetime) {} // $lifetime보다 오래된 세션데이터 제거. 메소드비워둠
}
```
### 9.3.2. 드라이버 등록
- 세션 드라이버 구현 후 ServiceProvider에서 드라이버 등록 필요
```php
namespace App\Providers;

use App\Extensions\MongoSessionHandler;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }
    public function boot()
    {
        Session::extend('mongo', function ($app) {
            // Return implementation of SessionHandlerInterface...
            return new MongoSessionHandler;
        });
        // 드라이버 추가시 Session파사드의 extend() 사용
    }
}
```

# 10. Validation
## 10.1. 시작하기
라라벨은 http request의 유효성 검사를 위해 ValidatesRequests Trait를 사용함
## 10.2. form 유효성검사 예제
### 10.2.1. 라우트 정의하기
```php 
// routes/web.php는 웹미들웨어 그룹이 적용되어 ShareErrorsFromSession미들웨어로 자동으로 에러페이지와 연결되게 됨
Route::get('post/create', 'PostController@create'); //  글 작성페이지
Route::post('post', 'PostController@store'); // 글 등록처리
```

### 10.2.2. 컨트롤러 생성하기
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
    * Show the form to create a new blog post.
    *
    * @return Response
    */
    public function create()
    {
        return view('post.create');
    }

    /**
    * Store a new blog post.
    *
    * @param  Request  $request
    * @return Response
    */
    public function store(Request $request)
    {
        // Validate and store the blog post...
    }
}
```

### 10.2.3. 유효성 검사 로직 작성하기
- Illuminate\Http\Request 객체의 validate()메소드 사용
- 유효성 검사 통과시 다음로직 정상실행
- 미통과시 exception thrown 하고 오류 response and 리다이렉트
- (ajax reqeust의 경우 HTTP 422코드로 JSON응답생성)
```php
class PostController extends Controller
{
    //...
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            /** 유효성 검사 규칙을 validate()의 인자로 전달 **/
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',

            /** 아래와 같이 배열로 전달하는 것도 가능 **/
            //'title' => ['required', 'unique:posts', 'max:255'],
            //'body' => ['required'],

            /** bail규칙으로 유효성 검사 처음 실패시 검사 중단하도록 하기 **/
            //'title' => 'bail|required|unique:posts|max:255',
            //'body' => 'required',

            /** 중첩된 파라미터에 대한 유효성 검사규칙 **/
            //'title' => 'required|unique:posts|max:255',
            //'author.name' => 'required', // dot.으로 구분
            //'author.description' => 'required',
        ]);

        // The blog post is valid...
    }
}
```

### 10.2.4. 유효성 검사 에러 표시하기
- 유효성 검사 에러는 세션에 임시저장
- 세션에 $errors가 저장되어 있다면 뷰에 자동으로 연결
- $errors는 web미들웨어 그룹에 의해 제공되는 Illuminate\View\Middleware\ShareErrorsFromSession미들웨어에 의해 뷰와 연결 
- ShareErrorsFromSession미들웨어 지정시 $errors변수 항상 사용가능
- $errors변수는 Illuminate\Support\MessageBag의 인스턴스
    ```php
    <!-- /resources/views/post/create.blade.php -->
    <h1>Create Post</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <!-- Create Post Form -->
    ```
- @error지시어
    ```php
    <label for="title">Post Title</label>
    <input id="title" type="text" class="@error('title') is-invalid @enderror">
    @error('title') // 속성에 대한 유효성검사 오류메시지가 있는지 빠르게 확인 가능
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    ```


### 10.2.5. 유효성검사 규칙 옵션 필드에 대한 주의사항
```php
// TrimStrings, ConvertEmptyStringsToNull 미들웨어가 글로벌미들웨어로 적용되기 때문에, null을 허용할 필드는 nullable을 표기해줘야 함 
$request->validate([
    'title' => 'required|unique:posts|max:255',
    'body' => 'required',
    'publish_at' => 'nullable|date', // 값이 null이거나 date타입
]);
```




## 10.3. Form Request 유효성 검사
- 폼리퀘스트는 컨트롤러 메소드 호출 전에 유효성검사를 수행함
- form request클래스는 라라벨의 베이스 request클래스를 상속
### 10.3.1. Form Request클래스 생성하기
- 복잡한 유효성 검사를 위해 유효성검사 로직을 가진 form request클래스를 생성
    ```bash
    $ php artisan make:request StoreBlogPost
    ```
- App/Http/Requests 디렉토리에 form request클래스가 저장
    ```php
    public function rules() // form request클래스의 유효성검사규칙메소드
    {
        return [ // 유효성검사규칙 반환
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
        ];
    }
    ```
- 유효성 검사 규칙 실행
    - 컨트롤러 메소드에 폼리퀘스트 객체를 타입힌트
    - 폼리퀘스트는 컨트롤러 메소드 호출 전에 유효성검사를 수행
    ```php
    // 컨트롤러 메소드
    public function store(StoreBlogPost $request) 
    {
        // The incoming request is valid...

        // Retrieve the validated input data...
        $validated = $request->validated(); 
        // 여기엔 유효성검사 로직이 별도로 포함되지 않음
    }
    ```
- Form Request 에 After 후킹 추가하기
    ```php
    /**
    * Configure the validator instance.
    *
    * @param  \Illuminate\Validation\Validator  $validator
    * @return void
    */   
    public function withValidator($validator)
    // withValidator()는 생성된 validator객체를 전달받음
    // validator instance 생성 후, 유효성 검사 수행 전에
    // validator의 메소드를 호출할 수 있게 해줌
    {
        $validator->after(function ($validator) { 
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field', 'Something is wrong with this field!');
            }
        });
    }
    ```

### 10.3.2. Form Requests 사용자 승인
- Form Requests의 authorize()메소드
    ```php
    public function authorize()
    {
        // 인증된 사용자가 리소스에 대한 권한이 있는지 확인가능
        $comment = Comment::find($this->route('comment'));

        return $comment && $this->user()->can('update', $comment);
        // 현재 인증된 사용자에 액세스하기 위해 user()메소드 사용가능 (모든 form request클래스는 라라벨의 베이스 request클래스를 상속하기 때문)
    }
    ```
    ```php
    Route::post('comment/{comment}'); 
    // authorize()메소드가 false리턴시 403 http응답 반환, 컨트롤러 메소드 실행되지 않음
    // 다른 인증로직을 사용하려면 authorize()에서 true리턴하도록 하면됨
    ```
### 10.3.3. 에러 메세지 사용자 정의하기
```php
public function messages()
{
    return [ // from request의 에러메시지를 커스터마이징
        'title.required' => 'A title is required',
        'body.required'  => 'A message is required',
    ];
}
```

### 10.3.4. 유효성 검사 속성 사용자 정의하기
```php
public function attributes()
{
    return [ // 유효성 검사 메시지의 :attribute 속성이름을 커스텀
        'email' => 'email address',
    ];
}
```

## 10.4. Validators 수동으로 생성하기
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function store(Request $request)
    {
        // Validator 파사드의 make메소드로 새로운 validator인스턴스 생성하기 
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
        ]); // Validator::make(유효성검사대상데이터, 검사규칙)


        // 리다이렉트 로직을 매뉴얼하게 추가 
        if ($validator->fails()) { // 검사 실패확인
            return redirect('post/create') 
                        ->withErrors($validator) // 에러msg를 세션에 임시저장(flash)
                        ->withInput(); // 현재 입력데이터와 함께 리다이렉트 
        }

        // Store the blog post...
    }
}
```

### 10.4.1. 자동으로 리다이렉트하기
```php
Validator::make($request->all(), [
    'title' => 'required|unique:posts|max:255',
    'body' => 'required',
])->validate(); // Validator 파사드를 이용해 validator객체를 생성하는 경우에도, 라라벨 기본 Redirect객체의 validate()함수처럼 자동으로 유효성검사 미통과시 자동 리다이렉트 시켜주는 validate()사용가능
```

### 10.4.2. 이름이 지정된 Error Bags
- 에러 메시지가 저장되는 Error Bags에 이름 지정 가능
- 이름 지정시 한 페이지의 여러 form 중 특정 form에 맞는 에러 메시지 조회가능
    ```php
    return redirect('register')
            ->withErrors($validator, 'login'); 
            // withErrors의 두번째 인자로 Error Bags의 이름을 전달
            // $validator 객체 생성시 request로부터 특정 form데이터를 받아오기 때문에
    ```
    ```php
    {{ $errors->login->first('email') }} // MessageBag인스턴스에 지정된 이름으로 접근
    ```
### 10.4.3. 유효성 검사 이후에 후킹하기
- Form Request의 after 후킹은 validator객체 생성 후, 유효성 검사 전에 수행. Validator파사드로 생성된 validator객체의 after 후킹은 유효성 검사 이후에 수행?
    ```php
    $validator = Validator::make(...);

    $validator->after(function ($validator) { // 유효성검사 이후에 호출될 콜백
        if ($this->somethingElseIsInvalid()) {
            $validator->errors()->add('field', 'Something is wrong with this field!');
        }
    });

    if ($validator->fails()) {
        //
    }
    ```

## 10.5.에러 메세지 작업하기
Validator인스턴스의 errors()메소드 호출시, MessageBag인스턴스 반환받아 오류 메시지를 편리하게 처리 가능
- 하나의 필드에 대한 첫 번째 에러메세지 얻기
    ```php
    $errors = $validator->errors();
    echo $errors->first('email'); // first()
    ```
- 하나의 필드에 대한 모든 에러메세지 얻기
    ```php
    foreach ($errors->get('email') as $message) { // foreach get()
    }

    // 배열형태의 form필드인 경우
    foreach ($errors->get('attachments.*') as $message) {
    } // attachments 배열 필드의 *모든 아이템
    ```
- 모든 필드에 대한 모든 에러메세지 얻기
    ```php
    foreach ($errors->all() as $message) { // foreach all()
        //
    }
    ```
- 하나의 필드에 대해 에러메세지 존재여부 확인
    ```php
    if ($errors->has('email')) { // has()
        //
    }
    ```
### 10.5.1. 사용자 정의 에러 메세지
- 방법1. Validator::make()에 세번째 인자로 에러메세지 전달하기
    ```php
    $messages = [
        'required' => 'The :attribute field is required.',
    ];
    $validator = Validator::make($input, $rules, $messages);
    ```
    - :attribute 플레이스홀더는 유효성검사대상 필드이름으로 대체됨
    - 사용가능한 플레이스홀더들
        ```php
        $messages = [ 
            'same'    => 'The :attribute and :other must match.',
            'size'    => 'The :attribute must be exactly :size.',
            'between' => 'The :attribute value :input is not between :min - :max.',
            'in'      => 'The :attribute must be one of the following types: :values',
        ];
        ```
    - 특정 필드에 대해서만 커스텀 오류 메세지 지정
        ```php
        $messages = [
            'email.required' => 'We need to know your e-mail address!',
        ]; // dot. 표기법으로 특정필드, 특정규칙에 메세지 지정가능
        ```

- 방법2. 언어 파일에 커스텀 메세지 지정하기
    ```php
    // resources/lang/xx/validation.php 언어파일의 custom배열에 지정
    'custom' => [
        'email' => [
            'required' => 'We need to know your e-mail address!',
        ],
    ],
    ```
    - 언어 파일에서 사용자 값 지정
        ```php
        $request->validate([
            'credit_card_number' => 'required_if:payment_type,cc'
        ]);
        // 검사 미통과시 메세지
        // The credit card number field is required when payment type is cc.
        ```
        ```php
        // validation.php 언어파일에서 실제유형값을 사용자 정의 값으로 변경가능 
        'values' => [
            'payment_type' => [
                'cc' => 'credit card'
            ],
        ],
        // 적용 후
        // 검사 미통과시 메세지
        // The credit card number field is required when payment type is credit card.
        ```
### 10.5.2. 사용가능한 유효성 검사 규칙
[docs 참조](https://laravel.kr/docs/6.x/validation#available-validation-rules)

## 10.6. 조건부로 규칙 추가하기
- 필드가 입력값 배열에 존재할때만 유효성 검사하기
    ```php
    $v = Validator::make($data, [ 
        'email' => 'sometimes|required|email', // sometimes 규칙 추가
    ]);
    ```
- 복잡한 조건부 유효성 검사
    ```php
    // validator 인스턴스 생성
    $v = Validator::make($data, [
        'email' => 'required|email',
        'games' => 'required|numeric',
    ]);
    ```
    ```php
    // 생성된 인스턴스에 조건부로 유효성 규칙 추가
    // sometimes(필드이름, 규칙, 클로저) 메소드 사용
    // 클로저가 true 반환시 규칙이 추가됨
    $v->sometimes('reason', 'required|max:500', function ($input) {
        // Closure에 전달된  $input은 Illuminate\Support\Fluent의 인스턴스
        return $input->games >= 100;
    });
    // 여러 필드에 한 번에 적용도 가능
    // $v->sometimes(['reason', 'cost'], 
    ```

## 10.7. 배열입력필드 유효성 검사
- 배열형태의 입력필드에 유효성 속성 지정시 dot.표기법 사용
    ```php
    $validator = Validator::make($request->all(), [
        'photos.profile' => 'required|image', // photos[profile] 필드에 유효성 규칙 적용
    ]);
    ```
    ```php
    $validator = Validator::make($request->all(), [
        'person.*.email' => 'email|unique:users',
        'person.*.first_name' => 'required_with:person.*.last_name',
    ]);
    ```
- validation.php언어 파일에서 배열형태 입력필드에 대한 오류 메세지 지정가능
    ```php
    'custom' => [
        'person.*.email' => [
            'unique' => 'Each person must have a unique e-mail address',
        ]
    ],
    ```



## 10.8. 사용자 정의 유효성 검사 규칙
### 10.8.1. Rule 객체 사용하여 커스텀 유효성검사 규칙 등록
Rule객체 : 커스텀 유효성 검사 규칙 객체
1. Rule 객체 생성 (artisan 명령어)
    ```bash 
    # app/Rules 디렉토리에 rule객체를 생성
    $ php artisan make:rule Uppercase 
    # 문자열이 대문자로 구성되었는지 확인하는 rule생성
    ```
2. 유효성 검사 동작 방식 정의 (passes(), message())
    ```php
    namespace App\Rules;

    use Illuminate\Contracts\Validation\Rule;

    class Uppercase implements Rule
    {
        /**
        * Determine if the validation rule passes.
        *
        * @param  string  $attribute
        * @param  mixed  $value
        * @return bool
        */
        public function passes($attribute, $value)
        {   // 속성명과 속성값을 받아, 유효성 규칙 검사하여 true/false반환
            return strtoupper($value) === $value;
        }

        /**
        * Get the validation error message.
        *
        * @return string
        */
        public function message()
        {
            // 유효성 검사 미통과시 메세지 반환
            return 'The :attribute must be uppercase.';
            
            // trans()헬퍼함수를 사용하여,
            // validation.php 언어 파일의 에러메세지 반환 가능

            // return trans('validation.uppercase');
    }
    ```
3. validator에 rule 객체 추가
    ```php
    use App\Rules\Uppercase; // Rule객체

    $request->validate([
        'name' => ['required', 'string', new Uppercase],
    ]);
    ```

### 10.8.2. 클로저 사용하여 커스텀 유효성검사 규칙 등록
사용자 정의 규칙 기능이 한 번만 필요한 경우 클로저 사용
```php
// validator생성시 유효성 규칙배열에서 정의
$validator = Validator::make($request->all(), [
    'title' => [
        'required',
        'max:255',
        function ($attribute, $value, $fail) { // 속성명, 값, 유효성 검사 미통과시 호출될 콜백
            if ($value === 'foo') {
                $fail($attribute.' is invalid.');
            }
        },
    ],
]);
```
### 10.8.3. 확장기능 사용하여 커스텀 유효성검사 규칙 등록
- Validator 파사드의 extend() 메소드 사용
    ```php
    namespace App\Providers;

    use Illuminate\Support\ServiceProvider;
    use Illuminate\Support\Facades\Validator;

    class AppServiceProvider extends ServiceProvider //ServiceProvider 내에서 사용
    {
        //...
        public function boot()
        {   
            // 사용자 정의 유효성 검사 규칙을 등록
            Validator::extend('foo', function ($attribute, $value, $parameters, $validator) {  // 클로저는 검사대상 필드명, 필드값, 검사규칙에 전달할 파라미터, Validator인스턴스를 인자로 받음
                return $value == 'foo';
            });


            // 클로저 대신 클래스명@메소드명을 전달하는 것도 가능
            // Validator::extend('foo', 'FooValidator@validate');
        }
    }
    ```
- 에러 메세지 정의하기
    - 추가 방법은 10.5.1. 사용자 정의 에러 메세지 참고
        ```php
        // 특정 유효성규칙속성에 대한 에러메세지 추가 
        "foo" => "Your input was invalid!",
        "accepted" => "The :attribute must be accepted.",
        ```
    - 에러메세지의 커스텀 플레이스홀더 정의
        ```php
        /**
         * Bootstrap any application services.
        *
        * @return void
        */
        public function boot()
        {
            Validator::extend(...);

            // Validator파사드의 replacer()메소드 호출
            Validator::replacer('foo', function ($message, $attribute, $rule, $parameters) {
                return str_replace(...);
            });
        }
        ```
- 묵시적 확장
    - 유효성검사를 받는 필드가 없거나 빈 문자열인경우 검사규칙시행불가
        ```php
        $rules = ['name' => 'unique:users,name'];
        $input = ['name' => ''];
        Validator::make($input, $rules)->passes(); // true
        ```
    - Validator::extendImplicit()메소드를 사용하여, 속성이 비었을 때도 규칙이 실행되도록 함 (속성값이 필요함을 내포시킴)
        ```php
        // Validator::extendImplicit()메소드로 묵시적 확장
        Validator::extendImplicit('foo', function ($attribute, $value, $parameters, $validator) {
            return $value == 'foo';
        });
        ```
- 묵시적 규칙 객체
    - 속성이 비어있을 때 규칙 객체를 실행하려면 Illuminate\Contracts\Validation\ImplicitRule 인터페이스를 구현해야 함



# 11. Error Handling

## 11.1. 시작하기

## 11.2. 설정하기

## 11.3. Exception Handler
### 11.3.1. Report 메소드
### 11.3.2. Render 메소드
### 11.3.3. Reportable & Renderable Exceptions

## 11.4. Http Exceptions
### 11.4.1. 커스텀-사용자 지정 HTTP 에러 페이지



# 12. Logging