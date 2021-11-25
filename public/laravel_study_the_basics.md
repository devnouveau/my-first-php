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

    //web미들웨어 그룹은 자동으로 RouteServiceProvider에 의해 routes/web.php에 적용됨
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
### 9.1.2. 드라이버 사전준비사항

## 9.2. 세션 사용
### 9.2.1. 데이터 조회
### 9.2.2. 데이터 저장
### 9.2.3. 데이터 임시저장
### 9.2.4. 데이터 삭제
### 9.2.5. 세션 ID 다시 생성

## 9.3. 사용자 정의 세션 드라이버 추가
### 9.3.1. 드라이버 구현
### 9.3.2. 드라이버 등록


# 10. Validation

# 11. Error Handling

# 12. Logging