# 1. 인증
## 1.1. 시작하기
- 라라벨 인증기능은 guards, provider로 구성
    - guards
        - request마다 어떻게 인증되는지 정의
        - session guard(세션스토리지, 쿠키로 상태유지)
        - token gruad(request로 전달되는 API토큰으로 인증)
    - provider
        - DB에서 사용자 찾는 방법 정의
        - 라라벨은 Eloquent, 쿼리빌더를 지원
- config/auth.php 인증옵션 설정
- 인증시스템 스캐폴딩
    - 라라벨ui 패키지, vue auth 스캐폴딩 설치하여 사용가능

### 1.1.1. DB 유의사항
- 기본적으로 Apa\User Eloquent모델 포함 (Eloquent인증드라이버와 함께 사용)
- 스키마 생성시 Password컬럼은 최소60자(기본값 255자)
- users 테이블은 remember_token컬럼을 포함해야 함


## 1.2. 빠르게 살펴보기
- App\Http\Controllers\Auth 네임스페이스의 인증컨트롤러(trait사용)를 기본으로 제공
    - RegisterController
    - LoginController
    - ForgotPasswordController : 암호재설정 링크생성
    - ResetPasswordController : 암호 재설정 로직

### 1.2.1. 라우팅 
- 인증시스템 스캐폴딩
    - 라라벨ui 패키지, vue auth 스캐폴딩 설치하여 사용가능
    - 레이아웃 뷰, 등록과 로그인 뷰, 모든 인증의 진입점을 위한 라우팅 기능, HomeController(로그인 후 대시보드 페이지 요청 컨트롤러)가 생성됨
        ```bash
        $ composer require laravel/ui "^1.0" --dev
        $ php artisan ui vue --auth   

        # 라라벨 애플리케이션 생성시 모든 인증 스캐폴딩 컴파일/설치
        # $ laravel new blog --auth 
        ```
### 1.2.2. 뷰
- 인증 스캐폴딩 생성시 
    - resources/views/auth디렉토리에 인증관련 뷰를 생성
    - resources/views/layouts 디렉토리 생성(Bootstrap CSS 사용, 커스텀 가능)

### 1.2.3. 인증하기 
- 기본으로 제공되는 로직을 사용하면 됨
- 리다이렉트 경로수정
    - 인증시 기본으로 /home URI로 리다이렉트
    - 리다이렉트 경로 커스터마이징
        1. redirectTo 속성 수정
            ```php
            // LoginController, RegisterController, ResetPasswordController 그리고 VerificationController
            protected $redirectTo = '/';
            ```
        2. RedirectIfAuthenticated 미들웨어의 handle 메소드수정
        - redirect URI생성로직 커스텀시
            ```php
            protected function redirectTo() // redirectTo() 메소드 정의
            {
                return '/path';
            }
            ```
- 인증에 사용하는 이름 지정
    - 기본으로 email필드 사용
    - 커스텀시 
        ```php
        public function username() // username() 메소드 수정
        {
            return 'username';
        }
        ```
- Guard 커스터마이징
    ```php
    // LoginController, RegisterController, ResetPasswordController의 guard()메소드 재정의
    use Illuminate\Support\Facades\Auth;
    protected function guard()
    {
        return Auth::guard('guard-name'); // guard객체 반환해야 함
    }
    ```
- validation, 스토리지 커스텀
    - RegisterController
        - 사용자등록시 필수 form항목(validator()) 커스터마이징
        - DB에 사용자 저장되는 로직(create()) 커스터마이징
### 1.2.4. 승인된 사용자 조회
- Auth파사드로 접근
    ```php
    use Illuminate\Support\Facades\Auth;

    // Get the currently authenticated user...
    $user = Auth::user();

    // Get the currently authenticated user's ID...
    $id = Auth::id();

    // 로그인여부 확인 
    // (특정라우트, 컨트롤러 접근가능 체크시에는 미들웨어를 사용하게 됨)
    if (Auth::check()) {
        // The user is logged in...
    }
    ```
- Illuminate\Http\Request 인스턴스로 접근
    ```php
    class ProfileController extends Controller
    {
        public function update(Request $request)
        {
            // $request->user() returns an instance of the authenticated user...
        }
    }
    ```

### 1.2.5. 라우트보호 (라우트 접근제한)
- Illuminate\Auth\Middleware\Authenticate의 auth미들웨어 사용
    -  라우트에 미들웨어 설정
        ```php
        Route::get('profile', function () {
            // Only authenticated users may enter...
        })->middleware('auth');
        ```
    - 컨트롤러 클래스 생성자에서 미들웨어 호출
        ```php
        public function __construct()
        {
            $this->middleware('auth');

            // 인증 gurad(auth.php의 gurads배열의 키)를 api로지정시
            // $this->middleware('auth:api');
        }
        ```
- 미인증시 리다이렉트
    - app/Http/Middleware/Authenticate.php 파일의 redirectTo() 수정
        ```php
        protected function redirectTo($request)
        {
            // 기본으로 login라우트로 리다이렉트
            return route('login'); 
        }
        ```

### 1.2.6. 비밀번호 확인 
- 특정영역 접근시 암호입력 요청하게 함
- password.confirm미들웨어 사용
    ```php
    Route::get('/settings/security', function () {
        // Users must confirm their password before continuing...
    })->middleware(['auth', 'password.confirm']);
    ```

### 1.2.7. 로그인횟수 제한
- LoginController사용시 Illuminate\Foundation\Auth\ThrottlesLogins 트레이트를 이용해 제한하게 됨

## 1.3. 수동으로 사용자인증
- Auth 파사드 사용
    ```php
    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;

    class LoginController extends Controller
    {
        public function authenticate(Request $request)
        {
            $credentials = $request->only('email', 'password');
            // attempt() 인증성공여부 반환
            if (Auth::attempt($credentials)) { 

            // if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => 1])) { 
            // 부가 조건 체크가능

            // if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) { 
            // 세번째인자로 인증의 무기한 유지 여부를 bool값 전달(users테이블이 remember_token 컬럼을 가지고 있어야 함)

                // Authentication passed...
                return redirect()->intended('dashboard');
                // intended()는 원래 액세스하려던 URL로 리다이렉트 시킴
            }
        }
    }
    ```
- 특정 Gurad인스턴스 접근
    ```php
    // admin guard는 auth.php의 guard에 지정되어야 함
    if (Auth::guard('admin')->attempt($credentials)) {
        //
    }
    ```
- 로그아웃
    ```php
    Auth::logout();
    ```
### 1.3.1. 사용자 기억하기
Auth 파사드 사용 부분 참고
### 1.3.2. 그외 인증메소드
- 사용자 인스턴스로 인증
    ```php
    // Illuminate\Contracts\Auth\Authenticatable contract구현
    // (App\User모델이 구현하고 있음)
    Auth::login($user);
    // Login and "remember" the given user...
    Auth::login($user, true);

    // 가드 인스턴스 지정하는 경우
    Auth::guard('admin')->login($user);
    ```
- ID로 인증
    ```php
    Auth::loginUsingId(1); // 사용자의 primary key를 전달받음

    // Login and "remember" the given user...
    Auth::loginUsingId(1, true);
    ```
- 사용자 인증 한번만 하기
    ```php
    if (Auth::once($credentials)) { // 세션, 쿠키사용 안함
        //
    }
    ```

## 1.4. HTTP 기본인증
- auth.basic 미들웨어 사용
- 인증자격 증명할 것을 요구받음
- 기본으로 사용자의 email을 username으로 사용
    ```php
    Route::get('profile', function () {
        // Only authenticated users may enter...
    })->middleware('auth.basic');
    ```

### 1.4.1. 상태를 유지하지 않는 HTTP기본인증
- onceBasic메소드 호출하는 미들웨어 정의
    ```php
    namespace App\Http\Middleware;

    use Illuminate\Support\Facades\Auth;

    class AuthenticateOnceWithBasicAuth
    {
        public function handle($request, $next)
        {
            return Auth::onceBasic() ?: $next($request);
        }
    }
    ```
- 라우트 미들웨어 등록 및 라우트에 미들웨어를 추가
    ```php
    Route::get('api/user', function () {
        // Only authenticated users may enter...
    })->middleware('auth.basic.once');
    ```


## 1.5. 로그아웃
```php
use Illuminate\Support\Facades\Auth;
Auth::logout();
```
### 1.5.1. 다른 디바이스 세션 무효화
- 현재 접속한 디바이스의 세션만 유지하고 다른 디바이스에서는 로그아웃시키기
    ```php
    'web' => [ 
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        // 사용가능한 상태여야 함
    ],
    ```
    ```php
    use Illuminate\Support\Facades\Auth;
    Auth::logoutOtherDevices($password); // 사용자가 비밀번호를 입력해야 사용가능한 기능임
    ```


## 1.6. 소셜인증
Laravel Socialite추가
## 1.7. 사용자정의 Guards추가
```php
namespace App\Providers;

use App\Services\Auth\JwtGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    public function boot() // ServiceProvider의 boot()메소드 내에서 Auth::extend()를 호출하여 guards추가
    {
        $this->registerPolicies();

        Auth::extend('jwt', function ($app, $name, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\Guard...
            return new JwtGuard(Auth::createUserProvider($config['provider']));
        });
    }
}
```
```php 
// auth.php의 guards설정에서 guard정의
'guards' => [
    'api' => [ 
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

### 1.7.1. Closure형태의 Request Guards
```php
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

public function boot()
{
    $this->registerPolicies();

    // viaRequest(인증드라이버 이름, request를 받아 사용자인스턴스를 반환하는 클로저)
    Auth::viaRequest('custom-token', function ($request) {
        return User::where('token', $request->token)->first();
    });
}
```
```php
// auth.php의 guards설정에서 인증드라이버 설정
'guards' => [
    'api' => [
        'driver' => 'custom-token',
    ],
],
```


## 1.8. 사용자정의 User Provider추가
- 사용자 저장시 RDBMS를 사용하지 않는 경우
- Auth::provider()메소드로 사용자정의 User 프로바이더를 정의
    ```php
    namespace App\Providers;

    use App\Extensions\RiakUserProvider;
    use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
    use Illuminate\Support\Facades\Auth;

    class AuthServiceProvider extends ServiceProvider
    {
        public function boot()
        {
            $this->registerPolicies();

            Auth::provider('riak', function ($app, array $config) {
                // Return an instance of Illuminate\Contracts\Auth\UserProvider...
                return new RiakUserProvider($app->make('riak.connection'));
            });
        }
    }
    ```
- 사용자정의 provider와 guards를 정의 (auth.php) 
    ```php
    'providers' => [
        'users' => [
            'driver' => 'riak',
        ],
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],
    ```


### 1.8.1. 사용자 Provider Contract
Illuminate\Contracts\Auth\UserProvider의 구현체는 스토리지에서 Illuminate\Contracts\Auth\Authenticatable을 불러오는 것만 담당
- UserProvider Contract
    ```php
    namespace Illuminate\Contracts\Auth;
    interface UserProvider 
    {// UserProvider contract
        
        public function retrieveById($identifier); 
        // id에 상응하는 키를 받아 Authenticatable객체를 반환
        public function retrieveByToken($identifier, $token); 
        // remember_token에 저장된 토큰에 따라 사용자를 조회해 Authenticatable객체를 반환
        public function updateRememberToken(Authenticatable $user, $token); 
        // 새로운 token으로 remember_token필드를 업데이트
        public function retrieveByCredentials(array $credentials);
        // Auth::attempt()로 전달되는 인증정보배열로 저장소에서 사용자를 질의하여 Authenticatable객체를 반환 
        // 패스워드 확인,인증시 사용불가
        public function validateCredentials(Authenticatable $user, array $credentials);
        // 주어진 $user를 $credentials에 비교
    }
    ```
- Authenticatable Contract
    ```php
    namespace Illuminate\Contracts\Auth;

    interface Authenticatable
    {
        public function getAuthIdentifierName(); 
        // 사용자의 primary key 필드명 반환
        public function getAuthIdentifier();
        // 사용자의 primary key반환
        public function getAuthPassword();
        // 사용자의 해시된 pw반환
        public function getRememberToken();
        public function setRememberToken($value);
        public function getRememberTokenName();
    }
    ```
## 1.9. 이벤트
- 라라벨은 인증과정 중 다양한 이벤트를 발생시킴
- EventServiceProvider의 $listen배열에 이벤트 리스너 클래스 추가 

# 2. API인증
## 2.1. 시작하기
- 라라벨은  api가드의 토큰 드라이버를 사용해 API인증을 제공
- API인증을 위해 laravel Passport 사용을 추천

## 2.2. 설정하기
### 2.2.1. DB 사전준비
- users테이블에 api_token컬럼 추가
    ```php
    Schema::table('users', function ($table) {
        $table->string('api_token', 80)->after('password')
                            ->unique()
                            ->nullable()
                            ->default(null);
    });
    ```
- migrate 실행
## 2.3. 토큰생성
RegisterController 의 create()에서 api_token사용가능
```php
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
protected function create(array $data)
{
    return User::forceCreate([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'api_token' => Str::random(80),
    ]);
}
```

### 2.3.1. 토큰 Hashing하기
API 토큰을 해싱하여 저장하려는 경우 api카드에서 hash옵션을 설정
```php
// config/auth.php
'api' => [
    'driver' => 'token',
    'provider' => 'users',
    'hash' => true, // SHA-256 해싱 on
],
```
- 해싱된 토큰생성
    - API토큰 초기화/갱신 처리
        ```php
        namespace App\Http\Controllers;

        use Illuminate\Http\Request;
        use Illuminate\Support\Str;

        class ApiTokenController extends Controller
        {
            public function update(Request $request)
            {
                $token = Str::random(80); // 랜덤문자열 토큰 새로 생성

                $request->user()->forceFill([
                    'api_token' => hash('sha256', $token), // 토큰해싱
                ])->save(); // 해싱된 토큰복제본을 DB에 저장

                return ['token' => $token]; // JSON응답으로 토큰반환 (평문으로 반환하여 화면단에서 토큰을 조회할 수 있도록함)
            }
        }
        ``` 


## 2.4. 라우트 보호
API토큰 검증하는 인증가드 설정
```php
use Illuminate\Http\Request;

// 토큰을 요구하는 라우트에 auth:api미들웨어 설정
Route::middleware('auth:api')->get('/user', function(Request $request) {
    return $request->user();
});
```

## 2.5. request시 토큰전달 방법
- 쿼리스트링
    ```php
    $response = $client->request('GET', '/api/user?api_token='.$token); 
    ```
- 요청 페이로드 : 폼 파라미터안에 API토큰 포함
    ```php
    $response = $client->request('POST', '/api/user', [
        'headers' => [
            'Accept' => 'application/json',
        ],
        'form_params' => [
            'api_token' => $token,
        ],
    ]);
    ```
- bearer token : Authorization요청헤더에 Bearer 토큰으로 API토큰을 제공가능
    ```php
    $response = $client->request('POST', '/api/user', [
        'headers' => [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ],
    ]);
    ```


# 3. Authorization 권한 확인
## 3.1. 시작하기
- 라라벨은 기본 권한확인 방법 제공
- gate, policy로 액션수행 권한 확인
    - gate : 간단한, 클로저 기반의 권한확인방식 (라우트와 유사)
    - policy : 특정 모델, 리소스에 대한 로직 정리 (컨트롤러와 유사)

## 3.2. Gate
### 3.2.1. 게이트 작성
- App\Providers\AuthServiceProvider의 boot()메소드 내에서 Gate파사드 사용
```php
public function boot()
{
    $this->registerPolicies();

    Gate::define('edit-settings', function ($user) {
        return $user->isAdmin;
    });

    Gate::define('update-post', function ($user, $post) { // 클로저의 첫번째 인자로 사용자 인스턴스, 두번째로 Eloquent모델
        return $user->id === $post->user_id;
    });

    Gate::define('update-post', 'App\Policies\PostPolicy@update');
    // Class@method 스타일의 콜백문자열 형태도 사용가능
}
```

### 3.2.2. 액션수행 권한 확인
```php
// 특정액션이 현재 인증된 사용자(라라벨이 자동으로 Gate클로저에 전달)에 allow/deny되었는지 확인
// App\Providers\AuthServiceProvider의 boot() 내
if (Gate::allows('edit-settings')) { //...
if (Gate::allows('update-post', $post)) { //...
if (Gate::denies('update-post', $post)) { //...

// 특정유저에 특정액션이 allow/deny되었는지 확인
if (Gate::forUser($user)->allows('update-post', $post)) { //...
if (Gate::forUser($user)->denies('update-post', $post)) { //...

// 한 번에 여러액션에 대한 인증가능
if (Gate::any(['update-post', 'delete-post'], $post)) { //...
if (Gate::none(['update-post', 'delete-post'], $post)) { //...
```
- 권한확인 or 예외처리
    ```php
    // 권한 없을 떄 자동으로 Illuminate\Auth\Access\AuthorizationException 발생시키기
    Gate::authorize('update-post', $post); // AuthorizationException은 자동으로 403응답으로 변환됨
    ```

- 추가 컨텍스트 제공
    - 권한 확인하는 게이트 메소드(allows, denies, check, any, none, authorize, can, cannot)와 Blade directives (@can, @cannot, @canany)에 두번째 인자로 배열을 전달 가능
    ```php
    Gate::define('create-post', function ($user, $category, $extraFlag) { // 두번째 인자 배열은 권한 결정시 추가 컨텍스트로 사용
        return $category->group > 3 && $extraFlag === true;
    });

    if (Gate::check('create-post', [$category, $extraFlag])) {
        // The user can create the post...
    }
    ```

### 3.2.3. 게이트 응답
- 권한 true/false확인 뿐만 아니라 메시지 포함한 응답 반환가능
- Illuminate\Auth\Access\Response반환
    ```php
    // App\Providers\AuthServiceProvider의 boot() 내

    use Illuminate\Auth\Access\Response;
    use Illuminate\Support\Facades\Gate;

    Gate::define('edit-settings', function ($user) { // 액션명, 콜백
        return $user->isAdmin
                    ? Response::allow()
                    : Response::deny('You must be a super administrator.');
    });


    // inspect메소드는 전체 권한 확인한 response 반환
    $response = Gate::inspect('edit-settings', $post);

    if ($response->allowed()) {
        // The action is authorized...
    } else {
        echo $response->message();
    }


    
    Gate::authorize('edit-settings', $post);
    // 권한 없으면 AuthorizationException 발생, 403http응답시킴
    ```



### 3.2.4. 게이트 체크로직 후킹
Gate::before(), Gate::after()메소드 사용하여 권한 체크로직의 전 후에 콜백을 실행 가능




## 3.3. Policy 생성
### 3.3.1. Polices 파일생성
- make:policy 아티즌 명령어로 Policy클래스파일 생성 (app/Policies 디렉토리에 생성)
```bash
$ php artisan make:policy PostPolicy # 빈 policy 클래스 생성
$ php artisan make:policy PostPolicy --model=Post # CRUD 메소드 포함된 policy클래스 생성
```
### 3.3.2. Polices 등록
```php
namespace App\Providers;

use App\Policies\PostPolicy;
use App\Post;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    // AuthServiceProvider는 policies 속성을 가짐
    // policies속성은 Eloquent모델과 그에 대한 policy를 매핑 
    protected $policies = [
        Post::class => PostPolicy::class, // Post모델의 액션에 권한 확인시 PostPolicy를 사용함
    ];

    public function boot()
    {
        $this->registerPolicies(); // policy 등록
    }
}
```
- Policy Auto-Discovery
    - 직접 policy모델 등록 안해도 됨
    - 모델, policy가 표준 라라벨 네이밍 규칙 따르는 경우 사용가능
    - policies위치 : 모델을 포함하는 디렉토리/Policies디렉토리
    - policy이름 : 모델이름Policy (모델이름에 Policy 접미사 붙음)
    - Policy Discovery로직 커스텀
        ```php
        //AuthServiceProvider의 boot()메소드에서 호출

        use Illuminate\Support\Facades\Gate;
        
        Gate::guessPolicyNamesUsing(function ($modelClass) {
            // return policy class name...
        });
        ```
    - AuthServiceProvider에 명시적으로 매핑된 모든 Policy는 auto-discovered Policy보다 우선시됨

## 3.4. Policies 작성
### 3.4.1. Policy() 메소드
```php
namespace App\Policies;

use App\Post;
use App\User;

class PostPolicy
{
    // Post객체 update시 권한확인할 로직 
    public function update(User $user, Post $post)
    {
        return $user->id === $post->user_id; 
        // 권한소유여부 boolean반환
    }
}
```
### 3.4.2. Poilcy response 응답
```php
use Illuminate\Auth\Access\Response;

public function update(User $user, Post $post) //인증된사용자, 모델
{
    // policy메소드에서 response반환
    return $user->id === $post->user_id
                ? Response::allow()
                : Response::deny('You do not own this post.');
}


$response = Gate::inspect('update', $post);

if ($response->allowed()) {
    // The action is authorized...
} else {
    echo $response->message();
}


Gate::authorize('update', $post);

// The action is authorized...
```



### 3.4.3. 모델없는 메소드
create() 같은 policy()메소드 정의시, 모델전달없이 인증된 사용자를 전달받도록 함
```php
public function create(User $user) {} // 인증된 사용자만 받음
```
### 3.4.4. 게스트 사용자
? optional 타입힌트나, User인자에 null을 기본값으로 지정하여 자동으로 false 반환하여 인증되지 않게 함
```php
class PostPolicy
{
    public function update(?User $user, Post $post) //
}
```
### 3.4.5. Policy필터
- Policy 내 모든 액션에 권한 부여 or 접근 비허용시 사용
- Policy클래스에 before(), after()메소드 정의
    ```php
    public function before($user, $ability) // Policy내 모든 메소드 호출 전 실행
    {
        if ($user->isSuperAdmin()) {
            return true; 
        }
    }
    ```

## 3.5. Policy를 사용하여 액션수행 권한 확인
### 3.5.1. User모델을 통해
- User 모델의 can(), cant()메소드 사용
    ```php
    // $post 모델을 인자로 받는 액션의 경우
    if ($user->can('update', $post)) { 
    }

    // 모델을 인자로 받지 않는 액션의 경우 (e.g. create())
    use App\Post;
    if ($user->can('create', Post::class)) { // 클래스이름은 액션 권한 확인시 어떤 policy를 사용할지를 결정
        // Executes the "create" method on the relevant policy...
    }
    ```
    - policy메소드 등록시 : 적절한 policy메소드 호출(여기서는 update)
    - policy메소드 미등록시 : 주어진 액션이름과 매칭되는 Gate를 호출(AuthServiceProvider에 등록되어 있음)

### 3.5.2. 미들웨어를 통해
권한을 확인하는 Illuminate\Auth\Middleware\Authorize미들웨어가 can으로 등록되어 있음
```php
// 모델을 필요로 하는 액션의 경우
use App\Post;
Route::put('/post/{post}', function (Post $post) {
    // The current user may update the post...
})->middleware('can:update,post'); // can:액션명,라우트파라미터


// 모델을 필요로 하지 않는 액션의 경우
Route::post('/post', function () {
    // The current user may create posts...
})->middleware('can:create,App\Post');
```
### 3.5.3. 컨트롤러 헬퍼를 통해
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function update(Request $request, Post $post)
    {
        // App\Http\Controllers\Controllers클래스 상속하는 컨트롤러에서 authorize()메소드 사용하여 권한 검사 가능
        $this->authorize('update', $post); // 액션명, 관련모델
        // $this->authorize('create', Post::class); // 모델이 필요없는 경우


    }
}
```
- 리소스 컨트롤러에서의 권한 확인
    - 생성자 안에서 authorizeResource() 사용
    - can미들웨어의 정의사항을 리소스컨트롤러 메소드에 추가하게 됨
        ```php
        namespace App\Http\Controllers;

        use App\Http\Controllers\Controller;
        use App\Post;
        use Illuminate\Http\Request;

        class PostController extends Controller
        {
            public function __construct()
            {
                $this->authorizeResource(Post::class, 'post'); // 모델클래스이름, 모델의 ID파라미터
            }
        }

        Controller Method |	Policy Method
        ----------------------------------
        index   |   viewAny
        show   |   view
        create   |   create
        store   |   create
        edit   |   update
        update   |   update
        destroy   |   delete
        ```


### 3.5.4. 블레이드 템플릿을 통해
- @can 과 @cannot지시어로 권한 검사
    ```php
    @can('update', $post)
        <!-- The Current User Can Update The Post -->
    @elsecan('create', App\Post::class)
        <!-- The Current User Can Create New Post -->
    @endcan

    @cannot('update', $post)
        <!-- The Current User Can't Update The Post -->
    @elsecannot('create', App\Post::class)
        <!-- The Current User Can't Create New Post -->
    @endcannot
    ```
- @if, @unless
    ```php
    @if (Auth::user()->can('update', $post))
        <!-- The Current User Can Update The Post -->
    @endif

    @unless (Auth::user()->can('update', $post))
        <!-- The Current User Can't Update The Post -->
    @endunless
    ```
- @canany
    ```php
    @canany(['update', 'view', 'delete'], $post)
        // The current user can update, view, or delete the post
    @elsecanany(['create'], \App\Post::class)
        // The current user can create a post
    @endcanany
    ```
- 모델을 필요로하지 않는 액션에 대한 권한검사
    ```php 
    @can('create', App\Post::class) 
        <!-- The Current User Can Create Posts -->
    @endcan

    @cannot('create', App\Post::class)
        <!-- The Current User Can't Create Posts -->
    @endcannot
    ```


# 4. Email 검증
## 4.1. 시작하기
- 라라벨은 이메일 검증하는 편리한 방법 제공
- 모델 준비사항
    - App\User 모델이 Illuminate\Contracts\Auth\MustVerifyEmail contract를 구현해야 함
        ```php
        namespace App;

        use Illuminate\Contracts\Auth\MustVerifyEmail;
        use Illuminate\Foundation\Auth\User as Authenticatable;
        use Illuminate\Notifications\Notifiable;

        // 
        class User extends Authenticatable implements MustVerifyEmail
        {
            use Notifiable;
        }
        ```

## 4.2. DB고려사항
- user테이블은 이메일주소 검증날짜 및 시간 컬럼 email_verified_at 을 포함해야 함
    - 라라벨 기본 users테이블 마이그레이션에는 이미 이 컬럼이 포함
    - DB마이그리에션만 실행하면 됨
        ```php
        php artisan migrate
        ```
## 4.3. 라우팅
- Auth\VerificationController 클래스는 검증링크를 보내고 이메일을 확인
- VerificationController에 대한 라우트 등록
    ```php
    Auth::routes(['verify' => true]);
    ```
### 4.3.1. 라우트 보호
- verified middleware를 사용하여 검증된 사용자만 라우트에 접근하도록 제한
    ```php
    Route::get('profile', function () {
        // Only verified users may enter...
    })->middleware('verified');
    ```
## 4.4. 뷰
- 검증에 필요한 뷰 생성
    ```bash
    $ composer require laravel/ui --dev
    $ php artisan ui vue --auth
    ```
- resources/views/auth/verify.blade.php 


## 4.5. 이메일 검증이후
1.2.3. 인증하기 참조


## 4.6. 이벤트
1.9. 이벤트






# 5. 암호화
## 5.1. 시작하기 
- 라라벨의 인크립터는 OpenSSL을 사용하여 AES-256, AES-128암호화를 제공
- 라라벨에서 암호화된 값은 MAC;message authentification code를 통해 서명되어 암호화 후 값 변경이 불가함

## 5.2. 설정하기
- config/app.php의 key옵션지정
    - key 생성
        ```bash
        $ php artisan key:generate # PHP의 안전한 랜덤 바이트 제너레이터 사용
        ```

## 5.3. Encryptor사용하기
- 하나의 값 암호화
    ```php
    namespace App\Http\Controllers;

    use App\Http\Controllers\Controller;
    use App\User;
    use Illuminate\Http\Request;

    class UserController extends Controller
    {
        public function storeSecret(Request $request, $id)
        {
            $user = User::findOrFail($id);

            $user->fill([
                'secret' => encrypt($request->secret), 
                // encrypt() 헬퍼함수로 하나의 값 암호화
            ])->save();
        }
    }
    ```
- Serialization 없이 암호화하기
    - 암호화시 데이터가 serialize됨 (serialize를 통해 객체,배열의 암호화가능)
    - 클라이언트에서 암호화된 값을 받으려면 unserialize 필요
    - Serialization없이 암/복호화하기
        ```php
        use Illuminate\Support\Facades\Crypt;
        $encrypted = Crypt::encryptString('Hello world.');
        $decrypted = Crypt::decryptString($encrypted);
        ```
- 값 복호화 하기
    ```php
    use Illuminate\Contracts\Encryption\DecryptException;

    try {
        $decrypted = decrypt($encryptedValue); // 복호화
    } catch (DecryptException $e) {
        // MAC 일치하지 않는 경우 DecryptException 발생
    }
    ```




# 6. hashing
## 6.1. 시작하기
- 라라벨의 Hash파사드는 Bcrypt와 Argon2해싱드라이버를 제공
- 라라벨의 LoginController, RegisterController를 사용하는 경우 컨트롤러에서 자동으로 Bcrypt방식을 사용함
## 6.2. 설정하기
- config/hashing.php에 기본 해싱드라이버가 설정되어 있음

## 6.3. 기본적인 사용법
- Hash::make() 호출하여 패스워드 해싱
    ```php
    namespace App\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;

    class UpdatePasswordController extends Controller
    {
        public function update(Request $request)
        {
            $request->user()->fill([ 
                'password' => Hash::make($request->newPassword)
            ])->save();
        }
    }
    ```
- Bcrypt Work Factor 조정하기
    - Work Factor 조정으로 하드웨어 성능 향상, 해싱시간 단축
        ```php
        $hashed = Hash::make('password', [
            'rounds' => 12 // rounds옵션으로 work factor조정
        ]);
        ```
- Argon2 Work Factor 조정하기
    ```php
    $hashed = Hash::make('password', [
        'memory' => 1024,
        'time' => 2,
        'threads' => 2,
    ]);
    ```
- 패스워드에 대한 해시 확인하기
    ```php
    // Hash::check() 메소드로 주어진 해시값과 일치하는지 확인
    if (Hash::check('plain-text', $hashedPassword)) { // 
    ```
- 패스워드의 리해싱이 필요한지 확인하기
    ```php
    // Hash::needsRehash (패스워드 해싱 후 해시의 work factor가 수정되었는지 확인)
    if (Hash::needsRehash($hashed)) {
        $hashed = Hash::make('plain-text');
    }
    ```


# 7. 패스워드 재설정
## 7.1. 시작하기
- 라라벨은 패스워드 분실 이메일 전송 및 패스워드 재설정 수행 방법을 제공함
- 사용자모델이 Illuminate\Notifications\Notifiable트레이트를 사용하고 있어야 함
## 7.2. DB고려사항
- App\User모델이 Illuminate\Contracts\Auth\CanResetPassword contract를 구현하고 있는지 확인 (라라벨은 기본적으로 구현하고 있음)
- 패스워드 재설정 토큰을 저장하는 테이블 생성 
    - 라라벨에 기본적으로 마이그레이션 파일이 database/migrations디렉토리에 포함돼있음
    - ```php artisan migrate``으로 DB마이그레이션만 실행
## 7.3. 라우팅
- Auth\ForgotPasswordController : pw재설정 링크 이메일 전송
- Auth\ResetPasswordController : 패스워드 재설정 로직
- 패스워드 재설정에 필요한 모든 라우트는 laravel/ui 컴포저 패키지를 사용하여 생성
    ```bash
    $ composer require laravel/ui --dev
    $ php artisan ui vue --auth
    ```
## 7.4. 뷰
- laravel/ui 컴포저 패키지설치하여
- resources/views/auth/passwords에 뷰파일 생성

- 라우트, 뷰 정의 후 브라우저에서 /password/reset 주소로 액세스 가능

## 7.5. 패스워드 재설정 이후 처리사항
- 기본적으로 재설정이후 /home으로 리다이렉션
- ResetPasswordController 의 redirectTo 속성에서 리다이렉션 주소 변경가능

## 7.6. 커스터마이징
- 인증 Guard 커스터마이징
    - auth.php에서 guards 정의
    - ResetPasswordController의 guard메소드를 오버라이딩하여 어떤 guard를 사용할지 결정
        ```php
        use Illuminate\Support\Facades\Auth;
        protected function guard()
        {
            return Auth::guard('guard-name');
        }
        ```
- 패스워드 브로커 커스터마이징
    - auth.php에서 브로커(사용자의 암호 재설정시 사용)들을 정의
    -  ForgotPasswordController 와 ResetPasswordController의 broker 메소드를 오버라이딩 하여 어떤 브로커를 사용할지 결정
        ```php
        use Illuminate\Support\Facades\Password;
        public function broker()
        {
            return Password::broker('name');
        }
        ```
- 패스워드 재설정 이메일 커스터마이징하기
    - User모델의 sendPasswordResetNotification()메소드 오버라이딩
    ```php
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token)); 
        // 선택한 알림클래스로 알림 전송 가능
    }
    ```