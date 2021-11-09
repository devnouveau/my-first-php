# Laravel 프로젝트 생성

## laravel 프로젝트 생성방법 1
```bash
$ composer create-project --prefer-dist laravel/laravel pmwd-chap29 
```
## laravel 프로젝트 생성방법 2
```bash
$ composer global require "laravel/installer" # 라라벨 명령행 도구 설치 (composer vendor의 bin디렉토리에 설치.)
$ laravel new pmwd-chap29 # 프로젝트 생성
```
### 웹서버에서 writable하게 해야 하는 서브 디렉토리
- ./storage/
- ./bootstrap/cache





# Laravel의 요청처리 사이클과 MVC 패턴

## 애플리케이션 프레임워크 커널
- http 웹요청/콘솔에서 명령을 실행된 것은 프레임워크 커널에 의해 처리
- http커널 : 로깅, 에러처리 등 모든 요청에 필요한 도구정의
- 미들웨어(애플리케이션 요청 받기 전 모든 요청이 반드시 실행해야 하는 작업) 정의
- ex.http세션데이터 처리, CSRF 방지처리 
- 서비스제공자를 로드/처리 
- kernel 클래스로 정의
- app/Http/Kernel.php (Illuminate\Foundation\Http\Kernel클래스로부터 상속)

## 서비스제공자 
- 애플리케이션의 핵심컴포넌트
- 프레임워크의 대부분의 기능을 애플리케이션에 제공
- 어떤기능이 있는지를 프레임워크에 알려줌
- 애플리케이션 구성파일 config/app.php의 providers 키에 정의됨
- 라이브러리 작성시, 그것의 서비스제공자 클래스를 생성한 후, 애플리케이션 구성파일에 정의를 추가해야 사용가능
- 라라벨 앱 생성시, app/Providers 디렉토리에 다양한 서비스 제공자 클래스들이 자동생성
- 요청 처리 전 애플리케이션 로직과는 별도로 필요한 리소스를 초기화할 수 있게 해줌






# laravel MVC 클래스
- 서비스제공자 : 요청을 라우터로 전달
- 라우터 : 전달받은 요청을 적합한 컨트롤러에 연결 및 전달
- 컨트롤러 : 요청처리에 필요한 데이터를 모델에 요구
- 모델 : 데이터를 컨트롤러에 반환
- 컨트롤러 : 뷰에게 모델과 데이터 전달
- 뷰 : 전달받은 데이터를 바탕으로 응답을 구성하여 클라이언트에 전송


## laravel 라우터
- 적합한 컨트롤러에 요청을 연결
- 일반적으로 app/Http/routes.php 파일에 정의 (특정 경로와 프로토콜을 컨트롤러에 연관시키는 내용을 정의. Route클래스에 대한 정적인 호출을 필요한 개수만큼 포함시켜야 함.)

## 경로 매개변수
- 생략가능한 매개변수 이름 끝에 ?추가
    ```php
    Route::get('articles/{id?}', function($id) {... 
    ``` 
- 매개변수 기본값 정의 가능
    ```php
    Route::get('articles/{id?}', function($id = 1) {... 
    ``` 
- where메소드로 매개변수 형식 지정
    ```php
    Route::get('articles/{id?}', function($section, $id) {
        return ...;
    })->where(['section' => '[A-Za-z]+', 'id' => '[0-9]+']);
    ``` 

## 경로그룹
- 두 개 이상의 경로가 동일한 기본 경로를 공유하는 경우 (ex. auth/view, auth/create)
- Route::group() 메소드사용 
    ```php
    Route::group(['prefix'=>'auth'], function() { // 여러타입의 경로그룹 정의 지원(prefix, middleware, namespace...)
        Route::get('view', function() {
            return ...;
        });
        Route::get('create', function() {
            return ...;
        });
    })
    ```

## 컨트롤러 사용
- 애플리케이션 로직 구성시 유용
- 관련있는 코드를 하나의 클레스에 넣어 app/Http/Controllers 디렉토리에 둠.
- 라라벨프로젝트 생성시 app\Http\Controllers\Controller 클래스(컨트롤러의 베이스 클래스 역할) 생성.
- 경로 지정방법
    ```php
    // 1.
    Route::get('/', 'MyController@myAction'); // 컨트롤러클래스명@해당클래스의public메소드
    
    // 2.uses키를 이용해 경로의 다른 속성도 지정가능
    Route::get('/',[
        'uses' => 'MyController@myAction'
    ]);
    ```
- 컨트롤러의 미들웨어를 해당 클래스의 생성자에서 지정 
- 컨트롤러에 의존모듈 추가
- Request객체 사용
    ```php
    namespace App\Http\Controllers;

    use Illuminate\Http\Request; 

    class MyController extends Controller {
        
        protected $_myTool; // MyTool서비스를 사용하는 서비스제공자를 생성하고, 컨트롤러에 MyTool의 인스턴스를 자동으로 추가한다고 가정
        
        public function __construct(MyTool $foo) {
            $this->midddleware('auth'); // 미들웨어 지정
            $this->_myTool = $foo; // MyTool 의존모듈 추가
        }
        
        public function myAction(Request $request) {
            $name = $request->input('name', 'John'); // Request::input()으로 리퀘스트 객체에 담긴 입력 데이터 사용
            return "name : $name";
        }
    }
    ```

## 요청 데이터 액세스 
- 입력데이터에 접근
    ```php
        // example.com/myaction?name=Joe 요청시
        public function myAction(Request $request) {
            $name = $request->input('name', 'John'); // input(데이터의 키/변수명, 기본값)
            return "name : $name";
        }
        // 입력데이터가 배열일 때. (JSON형식도 적용가능.Content-Type을 application/json으로 지정해야 함.)
        ['mykey'][0]['name']: // 다차원배열
        public function myAction(Request $request) {
            if(!request->has('name')) { // 리퀘스트객체에 변수가 존재하는지 검사
                return ...;
            }
            $name = $request->input('myarray.mykey.0.name', 'John'); // 입력된 배열데이터$myarray 액세스
            return "name : $name";
        }
    ```
- 다양한 방식으로 associative array반환 
    ( Request::all(), Request::only(), Request::except() )
- 파일 검사
    ```php
        public function myAction(Request $request) {
            if(!$request->hasFile('photo')) {
                return 'no file';
            }
            $file = $request->file('photo');
            if(!file->isValid()) {
                return 'Invalid file';
            }
            $file->move('/destination/path/for/photos/on/server');
            return "upload accepted";
        }
    ``` 

## 뷰 사용하기
- 구현방법
    resources/views 디렉토리에 뷰 템플릿 생성 ->
    뷰 컴포넌트로 뷰 인스턴스(뷰에 포함된 모든 변수들의 값을 가짐) 생성
    ```php
        Route::get('/', function()) {
            // resources/views/welcome.php뷰템플릿을 뷰로 만드는 경우
            // 첫번째 매개변수는 뷰이름(.php확장자를 제외한 것), 두번째매개변수는 뷰에서 사용되는 값
            return view('welcome', ['pageTitle' => 'Welcome!!']); 
        }
        // 서브디렉토리의 뷰 참조시
        Route::get('/', function()) {
            // resources/views/mycontroller/index.php 참조시
            return view('mycontroller.index', ['pageTitle' => 'Welcome!!']); // .으로 디렉토리와 뷰 구분
        }


        // 전역 뷰 변수 (boot()같은 서비스제공자에 두어야 함)
        view()->share('key', 'value');
    ```

## 블레이드 템플릿
- 뷰 템플릿 엔진
- 템플릿 상속, 섹션정의 가능
- .blade.php 파일사용
- views()함수로 참조
- @extense, @section, @stop, @show 등의 directive사용
- {{$var}} 와 같이 변수 출력가능 ( {{!!$var!!}} : $var값에 htmlentities()를 적용하지 않고 출력)



## laravel 모델
- Eloquent로 구현
- ORM 사용
- 구현방법
    1. DB생성
    2. config\database.php 에서 DB연결 설정
        (connections 키 아래에 정의되어 있음)
    ```php
        'connections'=> [
            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', 'localhost'), // env() : 시스템환경변수를 사용
                'database' => env('DB_HOST', 'dbdb'),
                'username' => env('DB_USERNAME', 'name'),
                'password' => env('DB_PASSWORD', 'passwd'),
                .....
            ]
        ]
    ```
    3. Eloquent 모델 생성 (스키마 정의를 DB마이그레이션으로 처리)
    ``` bash
    # artisan으로 마이그레이션 클래스 생성 
    # app\database\migrations에 위치하는 마이그레이션 PHP스크립트 생성 후 파일이름 제일 앞에 타입스탬프 붙임
    $ php artisan migrate:make create_author_books_schema 
    $ php artisan migrate # 미적용 마이그레이션을 찾아 app\database\migrations를 검색
    ```
- 모델 클래스는 app\Models디렉토리에 저장하는 것을 권장
- 모델클래스 간 관계
    ```php
    // author와 Book이 1:N 관계일 때

    // App\Models\Author.php
    namespace App\Models;
    class Author extends \Eloquent { #authors테이블의 모델클래스
        public function books() {
            return $this->hasMany('Book'); // 다수의 Book객체를 반환
        }
    }
    // App\Models\Book.php
    namespace App\Models;
    class Book extends \Eloquent { #books테이블의 모델클래스
        public function author() {
            return $this->belongsTo('Author'); // 속해있는 Author객체를 반환
        }
    }
    ```
- Eloquent로 DB액세스
- 데이터 변경시 모델객체의 기본키 필드값이 null이면 추가, null이 아니면 변경으로 처리
    ```php
    // 데이터 저장
    $myModel = new \App\MOdels\Author();
    $myModel->name = "김하나";
    $myModel->save();
    // 데이터 변경
    $myModel = new \App\MOdels\Author();
    $myModel->id = 2; // 기본키 id열의 값이 2인 행의 데이터 변경
    $myModel->name = "김두울";
    $myModel->save();
    ```
- 모델객체 메소드
    -  Model::find($key), Model::where($column, $comparison, $value), Model::whereNull($column), Model::whereRaw($conditional, $bindings)..
    ```php
    // Model객체::all()은 테이블의 모든 행 반환
    $authors = \App\Models\Authors::all(); 
    ```
    - all(), find()외 메소드는 쿼리의 생성/실행 구분 (where()메서드 하나만 호출시 결과반환불가)
    - where()로 생성된 쿼리를 실제로 실행하고 결과를 가져오려면 get(), first()메소드 사용
    ```php
    $query = Author::where('name', 'LIKE', '%ohn%')
                    ->where('name', 'LIKE', '%shall%')
    $result = $query->get();
    $result = $query->first();
    ```
- 조인 JOIN
    ```php
    $author = Author::find(1);
    $PHPBooks = $author->books()->where('title', 'LIKE', '%PHP%')->get();
    ```

