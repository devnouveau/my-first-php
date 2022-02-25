# 1. 블레이드 템플릿
## 1.2. 시작하기 (블레이드 템플릿이란?)
- 라라벨에서 제공하는 템플릿 엔진
- 뷰에서 PHP코드 작성 가능
- .blade.php
- resources/views에 저장

## 1.3. 템플릿 상속
- 레이아웃 정의
  - 레이아웃을 하나의 블레이드 뷰로 정의하여 
  - 동일한 레이아웃 유지 가능
  ```php
  <html>
      <head>
          <title>App Name - @yield('title')</title> 
      </head>
      <body>
          @section('sidebar') // @section : 내용정의 
              This is the master sidebar.
          @show // @show : @endsection과 달리 즉시 섹션 생성

          <div class="container">
              @yield('content') // @yield : section의 컨텐츠를 나타냄

              //  @yield('content', View::make('view.name')); // 섹션 미정의시 두번째 인자인 기본값을 렌더링함
          </div>
      </body>
  </html>
  ```

- 레이아웃 확장(상속)
  ```php
  <!-- Stored in resources/views/child.blade.php -->

  @extends('layouts.app') // @extends : 하위 페이지가 상속받는 레이아웃 명시

  @section('title', 'Page Title')

  @section('sidebar')
      @parent

      <p>This is appended to the master sidebar.</p>
  @endsection // @endsection : @show와 달리 섹션 정의만 함

  @section('content')
      <p>This is my body content.</p>
  @endsection
  ```


## 1.4. 컴포넌트 & 슬롯
- 컴포넌트
  ```php
  <!-- /resources/views/alert.blade.php -->

  <div class="alert alert-danger">
      <div class="alert-title">{{ $title }}</div>  // 컴포넌트에 주입될 내용
      {{ $slot }} // 컴포넌트에 주입될 내용
  </div>
  ```
- @component : 컴포넌트 구성
  ```php
  @component('alert', ['foo' => 'bar']) // 컴포넌트 이름, 전달할 데이터(생략가능)
      <strong>Whoops!</strong> Something went wrong!
  @endcomponent
  ```
  
- @slot : 컴포넌트 내 데이터 주입시 사용
  ```php
  @component('alert')
      @slot('title') // 이 안의 내용은 {{ $title }}에 주입됨
          Forbidden
      @endslot
      // @slot에 포함되지 않는 내용은 {{ $slot }} 에 주입됨

      You are not allowed to access this resource!
  @endcomponent
  ```
- 컴포넌트 별칭
  ```php
  use Illuminate\Support\Facades\Blade;
  //  AppServiceProvider 의 boot 메소드 내
  Blade::component('components.alert', 'alert'); // alert으로 별칭지정
  ```
  ```php
  @alert(['type' => 'danger']) // @alert별칭으로 사용가능 // 슬롯 없으면 ['type' => 'danger'] 생략
      You are not allowed to access this resource!
  @endalert
  ```

## 1.5. 데이터 표시하기
- 라우트에서 데이터 담아 뷰 반환
  ```php
  Route::get('greeting', function () {
      return view('welcome', ['name' => 'Samantha']);
  });
  ```
- 뷰에서 데이터 표시
  ```php
  Hello, {{ $name }} // 자동으로 htmlspecialchars 적용(XSS방지)
  Hello, {!! $name !!}. // htmlspecialchars 적용방지
  Hello, {{ time() }} // PHP코드도 들어갈 수 있음
  ```
- JSON 렌더링
  - 방법1. json_encode()
    ```php
    <script>
        var app = <?php echo json_encode($array); ?>;
    </script>
    ```
  - 방법2. @json()블레이드 지시어
    ```php
    <script>
        var app = @json($array);
        var app = @json($array, JSON_PRETTY_PRINT);
    </script>
    ```
    - @json()은 Vue컴포넌트, data-*속성 시딩시 유용
      ```php
      <example-component :some-prop='@json($array)'></example-component>
      ```
- HTML Entity 인코딩
  ```php
  namespace App\Providers;

  use Illuminate\Support\Facades\Blade;
  use Illuminate\Support\ServiceProvider;

  class AppServiceProvider extends ServiceProvider
  {
      public function boot()
      {
          Blade::withoutDoubleEncoding(); // 블레이드, HTML요소 이중 인코딩 비활성화
      }
  }
  ```
### 1.5.1. 블레이드 & 자바스크립트 프레임워크
- 자바스크립트 프레임워크의 컬리 브레이스{{}}와, 블레이드의 중괄호를 구분시키기
  ```php
  <h1>Laravel</h1>
  //  위해,
  // 블레이드의 중괄호 표현을 유지해야 한다는 것을 @기호를 붙여서 표시
  Hello, @{{ name }}.
  ```
- @verbatim 내에선  @ 안붙여도 블레이드 중괄호로 인식
  ```php
  @verbatim
      <div class="container">
          Hello, {{ name }}. 
      </div>
  @endverbatim
  ```

## 1.6. 제어구조
### 1.6.1. 조건문
- php문장과 동일하게 동작
  ```php
  @if (count($records) === 1)
      I have one record!
  @elseif (count($records) > 1)
      I have multiple records!
  @else
      I don't have any records!
  @endif
  ```
- unless, isset, emtpy도 사용가능
  ```php
  @unless (Auth::check())
      You are not signed in.
  @endunless
  ```
  ```php
  @isset($records)
    // $records is defined and is not null...
  @endisset

  @empty($records)
      // $records is "empty"...
  @endempty
  ```
- 인증관련 지시어
  ```php
  @auth
      // The user is authenticated...
  @endauth

  @guest
      // The user is not authenticated...
  @endguest
  ```
  ```php 
  // admin auth 가드 사용시
  @auth('admin')
      // The user is authenticated...
  @endauth

  @guest('admin')
      // The user is not authenticated...
  @endguest
  ```
- @hasSection : 섹션이 내용을 갖고 있는지 확인
  ```php
  @hasSection('navigation')
      <div class="pull-right">
          @yield('navigation')
      </div>

      <div class="clearfix"></div>
  @endif
  ```   
### 1.6.2. 스위치구문
```php
@switch($i)
    @case(1)
        First case...
        @break

    @case(2)
        Second case...
        @break

    @default
        Default case...
@endswitch
```
### 1.6.3. 반복문
php문장과 동일하게 대응
```php
@for ($i = 0; $i < 10; $i++)
    The current value is {{ $i }}
@endfor
```
```php
@foreach ($users as $user)
    @if ($user->type == 1)
        @continue 
    @endif

    <li>{{ $user->name }}</li>

    @if ($user->number == 5)
        @break
    @endif
@endforeach 
```
```php
@forelse ($users as $user)
    <li>{{ $user->name }}</li>
@empty
    <p>No users</p>
@endforelse
```
```php
@while (true)
    <p>I'm looping forever.</p>
@endwhile
```
```php
@foreach ($users as $user)
    @continue($user->type == 1) // 조건식 포함가능

    <li>{{ $user->name }}</li>

    @break($user->number == 5)
@endforeach
```
### 1.6.4. 루프변수
반복문 인덱스, 몇 번째 반복인지에 대한 변수
```php
@foreach ($users as $user)
// $loop->index, count, even, odd 등 사용가능
    @if ($loop->first) // 첫번째 루프
        This is the first iteration.
    @endif

    @if ($loop->last) // 마지막 루프
        This is the last iteration.
    @endif

    <p>This is user {{ $user->id }}</p>
@endforeach
```
- 중첩된 반복문의 루프변수
  ```php
  @foreach ($users as $user)
      @foreach ($user->posts as $post)
          @if ($loop->parent->first) // parent속성을 통해 액세스
              This is first iteration of the parent loop.
          @endif
      @endforeach
  @endforeach
  ```
### 1.6.5. 주석
```php
// HTML에 포함되지 않음
{{-- This comment will not be present in the rendered HTML --}}
```
### 1.6.6. PHP
뷰에서 php코드 그대로 사용시
```php
@php
    //
@endphp
```
## 1.7. 폼
### 1.7.1. CSRF 필드
laravel_study_the_basics에서 다룬 내용으로 생략
### 1.7.2. 메소드필드
laravel_study_the_basics에서 다룬 내용으로 생략
### 1.7.3. 유효성 검증 실패
laravel_study_the_basics에서 다룬 내용으로 생략

## 1.8. 하위 뷰파일 포함시키기
- 뷰 파일 내 뷰파일 포함
- 부모뷰의 변수는 하위 뷰에서도 사용가능
  ```php
  <div>
      @include('shared.errors')  // 하위 뷰 포함시키기 ()
      // @include('view.name', ['some' => 'data']) // 데이터 직접전달도 가능
      // @includeIf('view.name', ['some' => 'data']) // 존재하지 않을 수도 있는 하위뷰 포함시
      // @includeWhen($boolean, 'view.name', ['some' => 'data']) // 조건에 따라 포함시키기
      // @includeFirst(['custom.admin', 'admin'], ['some' => 'data']) // 존재확인된 첫번째 뷰 포함시키기

      // __DIR__와 __FILE__ 는 블레이드 뷰에서 사용금지. (컴파일된 캐시 뷰 경로 반환됨)


      <form>
          <!-- Form Contents -->
      </form>
  </div>
  ```
- Include 별칭지정
  ```php
  // resources/views/includes/input.blade.php
  <input type="{{ $type ?? 'text' }}">
  ```
  ```php
  // AppServiceProvider의 boot 메소드에서 별칭지정
  use Illuminate\Support\Facades\Blade;
  Blade::include('includes.input', 'input'); // resources/views/includes/input.blade.php에 input으로 별칭지정
  ```
  ```php
  @input(['type' => 'email']) // @별칭으로 include가능
  ```
### 1.8.1. 컬렉션을 뷰에서 렌더링하기
- 배열이나 컬렉션을 뷰에서 편하게 렌더링
- 반복문을 한줄로
```php
  @each('view.name', $jobs, 'job', 'view.empty') // 렌더링할 뷰의 부분, 배열혹은컬렉션, 반복값이 대입되는 변수명, 배열이 비었을 경우 렌더링될 뷰[생략가능]

  // 자식뷰에서는 사용불가
  ```
## 1.9. 스택
- 다른 뷰, 레이아웃에서 렌더링할 수 있게 스택에 푸시해둘 수 있음
  ```php
  @push('scripts')  // scripts스택에 푸시해둠 // 한 번 더 푸시하면 내용추가됨
      <script src="/example.js"></script>
  @endpush 

  @prepend('scripts') // 스택의 앞쪽에 추가로 푸시할 때
    This will be first...
  @endprepend
  ```
  ```php
  <head>
      <!-- Head Contents -->

      @stack('scripts') // 스택에 저장된 컨텐츠 렌더링
  </head>
  ```



## 1.10. 서비스 인젝션 @inject
```php
@inject('metrics', 'App\Services\MetricsService') // 서비스명, 주입할 서비스클래스/인터페이스명

<div>
    Monthly Revenue: {{ $metrics->monthlyRevenue() }}. // 서비스 사용
</div>
```

## 1.11. 블레이드 기능 확장하기 (커스텀 디렉티브)
- directive 메소드로 사용자정의 지시어 정의가능
- 블레이드 컴파일러는 사용자정의 지시어 발견시 정의된 콜백을 호출
```php
// AppServiceProvider 의 boot 메소드
public function boot()
{
    Blade::directive('datetime', function ($expression) { // @datetime디렉티브 발견시 콜백호출
        return "<?php echo ($expression)->format('m/d/Y H:i'); ?>";
    });
}
```

## 1.12. 커스텀 IF
- Blade:if()메소드로 빠르게 커스텀if지시어 정의가능
  ```php
  // AppServiceProvider 의 boot 메소드
  public function boot()
  {
      Blade::if('env', function ($environment) {
          return app()->environment($environment);
      });
  }  
  ```
  ```php
  @env('local')
      // The application is in the local environment...
  @elseenv('testing')
      // The application is in the testing environment...
  @else
      // The application is not in the local or testing environment...
  @endenv

  @unlessenv('production')
      // The application is not in the production environment...
  @endenv  
  ```




# 2. 다국어
## 2.1. 시작하기(라라벨의 다국어 기능이란?)
- 다국어에 대응할 수 있게 번역된 문자열을 사용할 수 있게 해줌
- resources/lang/언어명칭/messeges.php로 언어파일 저장
- 언어파일은 키-값 배열 반환
  ```php
  return [
      'welcome' => 'Welcome to our application'
  ];
  ```
### 2.1.1. Locale설정
- config/app.php에서 기본언어 설정
- App 파사드의 setLocale()로 설정
  ```php
  Route::get('welcome/{locale}', function ($locale) {
      App::setLocale($locale);
  });
  ```
- 문자열 못 찾을 경우 대체언어 설정 
  ```php
  // config.php
  'fallback_locale' => 'en',
  ```
- 현재 로케일 확인
  ```php
  $locale = App::getLocale();
  if (App::isLocale('en')) {
      //
  }
  ```

## 2.2. 다국어 문자값 정의
### 2.2.1. 짧은 키 사용
언어파일에서 키-값 배열을 반환하기에 키로 해당 문자열 사용가능
```php
// resources/lang/en/messages.php
return [
    'welcome' => 'Welcome to our application'
];
```
### 2.2.2. 다국어 문자열을 키로 사용
- 문자열 자체를 키로 하용하여 번역된 문자열을 사용가능
- JSON파일로 저장됨
  ```php
  // resources/lang/es.json
  {
      "I love programming.": "Me encanta programar."
  }
  ```

## 2.3. 다국어 문자 조회
- __() 헬퍼 함수로 조회가능
  ```php
  echo __('messages.welcome'); // messages.php 파일에서 'welcome'키로 조회
  echo __('I love programming.'); // JSON파일에서 'I love programming'키로 조회
  ```
- 블레이드 템플릿에서 조회
  ```php
  // escape처리는 별도로 해주어야 함
  {{ __('messages.welcome') }}
  @lang('messages.welcome')
  ```

### 2.3.1. 다국어 문자열의 파라미터 치환
- 문자열의 일부분 교체(placeholders) 가능
- 교체문자는 :로 시작
  ```php
  // messages.php에서 플레이스홀더 :name 정의
  'welcome' => 'Welcome, :name', 
  // 'welcome' => 'Welcome, :NAME', // Welcome, DAYLE // 플레이스홀더가 대문자면 변환문자도 대문자로 표기됨
  // 'goodbye' => 'Goodbye, :Name', // Goodbye, Dayle
  ```
  ```php
  // 다국어 문자열 조회시 __()함수의 두번째 인자로 플레이스홀더-변환문자 배열 전달
  echo __('messages.welcome', ['name' => 'dayle']);
  ```
### 2.3.2. 복수 표기
- 언어의 단수|복수형에 따른 표현을 다르게 사용가능
- 파이프로 나누어 정의
  ```php
  'apples' => 'There is one apple|There are many apples',
  'apples' => '{0} There are none|[1,19] There are some|[20,*] There are many', // 갯수에 따라 정의
  ```
- 갯수에 따라 메세지 표시
  ```php
  echo trans_choice('messages.apples', 10);
  ```
- 플레이스 홀더 속성 정의
```php
'minutes_ago' => '{1} :value minute ago|[2,*] :value minutes ago',
echo trans_choice('time.minutes_ago', 5, ['value' => 5]); // 플레이스홀더는 trans_choice의 세번째 인자 값으로 치환됨
```
## 2.4. 패키지 언어파일 재정의
- 언어파일을 제공하는 패키지들의 경우
- resources/lang/vendor/{locale}/{package} 디렉토리에 파일을 추가하여 다국어 재정의 가능
- skyrim/hearthfire패키지는 resources/lang/vendor/en/hearthfire/en/messages.php에 언어파일 추가






# 3. Javascript & CSS 스캐폴딩
## 3.1. 시작하기
- 라라벨에서는 Bootstrap, React, Vue같은 자바스크립트 라이브러리/프레임워크를 사용할 수 있는 토대(Scaffolding)를 마련해줌
- NPM을 이용해 프론트엔드 패키지를 설치하여 사용
- 각 스캐폴딩은 laravel/ui Composer패키지에 있음
- 설치방법 
  - Composer로 통해 라라벨 ui패키지 설치
    ```php
    composer require laravel/ui --dev
    ```
  - artisan으로 프론트엔드 스캐폴딩 설치
    ```php
    // Generate basic scaffolding...
    php artisan ui bootstrap
    php artisan ui vue
    php artisan ui react

    // Generate login / registration scaffolding...
    php artisan ui bootstrap --auth
    php artisan ui vue --auth
    php artisan ui react --auth
    ```
- 프론트엔드 스캐폴딩 삭제
  ```bash
  $ php artisan preset none # none옵션 : SASS파일, js유틸제외한 부트스트랩/Vue 스캐폴딩 제거
  ```
## 3.2. CSS에서의 사용
- 라라벨 Mix가 SASS/Less를 컴파일하는 API제공
- laravel/ui 패키지, 프론트엔드 스캐폴딩 설치시 package.json파일은 bootstrap패키지를 포함하게 됨
- CSS 컴파일 전 NPM으로 프론트엔드 의존성 설치
  ```bash
  $ npm install
  ```
- 라라벨 Mix를 이용해 SASS를 CSS로 컴파일 (public/css디렉토리에 위치하게 됨)
  ```bash
  $ npm run dev # webpack.mix.js의 명령어를 실행(resources/sass/app.scss 컴파일)
  ```

## 3.3. Javascript에서의 사용
- 라라벨은 Vue 스캐폴딩 제공
- 라라벨 Mix가 js컴포넌트를 하나의 js파일로 컴파일

- NPM으로 package.json에 정의된 js의존성 설치
  ```bash
  $ npm install
  ```
- assets 컴파일 : app.js파일을 컴파일 (public/js디렉토리에 위치하게 됨)
  ```bash
  $ npm run dev # webpack.mix.js의 명령어를 webpack이 실행(resources/js/app.js 컴파일)
  ```
  - app.js파일 
    - resources/js/bootstrap.js을 로드
    - Vue, Axios, jQuery, 그외 자바스크립트 의존성을 설치하고 구동


### 3.3.1. Vue 컴포넌트 작성
- 프론트엔드 스캐폴딩 생성시 resources/js/components디렉토리에 ExampleComponent.vue 컴포넌트가 추가됨
- app.js에 컴포넌트가 등록되어 있음
  ```php
  Vue.component(
      'example-component',
      require('./components/ExampleComponent.vue').default
  );  
  ```
- 블레이드템플릿에서 뷰 컴포넌트 등록하기
  ```php
  @extends('layouts.app')

  @section('content')
      <example-component></example-component> // 컴포넌트 등록
  @endsection
  ```
### 3.3.2. React 사용하기
- Vue 스캐폴딩을 React 스캐폴딩으로 교체가능
  ```bash
  $ composer require laravel/ui --dev # laravel/ui패키지 설치

  $ php artisan ui react # 리액트 기본 스캐폴딩 설치

  // Generate login / registration scaffolding...
  $ php artisan ui react --auth  # 리액트 인증 스캐폴딩 설치
  ```


## 3.4. Preset 추가
- Preset : 런타임에 UiCommand 클래스에 메소드를 추가하게 해줌
- service provider에서 preset macro를 선언가능
  ```php
  use Laravel\Ui\UiCommand;
  UiCommand::macro('nextjs', function (UiCommand $command) { // nextjs의 메소드를 UiCommand클래스에 추가하는 코드

      // Scaffold your frontend...
  });
  ```
  ```bash
  $ php artisan ui nextjs
  ```

# 4. ASSETS 컴파일
## 4.1. 시작하기
라라벨 Mix가 CSS, JS의 webpak빌드를 위한 API를 제공함
## 4.2. 설치 & 설정
1. Node.js 설치
2. 라라벨 Mix설치(package.json생성)

## 4.3. Mix실행
```bash
# Run all Mix tasks...
$ npm run dev
# Run all Mix tasks and minify output...
$ npm run production


# 파일 변경사항 감지시 자동으로 재컴파일하도록 함
$ npm run watch
# 파일 변경사항 감지해도 자동으로 재컴파일 하지 않음
$ npm run watch-poll
```

## 4.4. 스타일시트에 적용하기
- webpack.mix.js
  - assets컴파일에 대한 내용을 담고 있음 (메소드 체이닝 형태)
  - Webpack을 래핑한 가벼운 설정
### 4.4.1. Less
```php
// app.less를 public/css/app.css로 컴파일
mix.less('resources/less/app.less', 'public/css')
    .less('resources/less/admin.less', 'public/css');  // 여러개 동시에 컴파일

// css파일이름 임의지정하여 컴파일
mix.less('resources/less/app.less', 'public/stylesheets/styles.css');

// 기본 Less플러그인 옵션 오버라이드
mix.less('resources/less/app.less', 'public/css', { // 세번째 인자로 옵션전달
    strictMath: true
});
```
### 4.4.2. Sass
```php
// Sass를 CSS로 컴파일
mix.sass('resources/sass/app.sass', 'public/css')
    .sass('resources/sass/admin.sass', 'public/css/admin'); // 여러개 동시에 컴파일

// 기본 Node-Sass플러그인 옵션 오버라이드
mix.sass('resources/sass/app.sass', 'public/css', { // 세번째 인자로 옵션전달
    precision: 5
});
```
### 4.4.3. Stylus
```php
// stylus를 CSS로 컴파일
mix.stylus('resources/stylus/app.styl', 'public/css');
```

### 4.4.4. PostCSS
- CSS변환툴
- Autoprefixer 플러그인 설치하여 모든 CSS에 prefix자동적용가능
```php
mix.sass('resources/sass/app.scss', 'public/css')
    .options({
        postCss: [
            require('postcss-css-variables')()
        ]
    });
```

### 4.4.5. 일반적인 CSS
```php
mix.styles([ // styles() : 복수의 CSS를 하나의 파일로 연결해서 붙임
    'public/css/vendor/normalize.css',
    'public/css/vendor/videojs.css'
], 'public/css/all.css');
```

### 4.4.6. URL Processing
- webpack의 상대경로 URL처리
  - CSS컴파일시 스타일시트 내에서 url()호출을 재작성/최적화함
    - Sass
      ```css
      .example {
          background: url('../images/example.png');
      }
      ```
    - 라라벨 Mix와 Webpack은 example.png 파일을 찾아 이를 public/images 폴더에 복사하고, 생성된 스타일 시트 안에서 url()을 재작성
    - 컴파일된 CSS
      ```css
      .example { // example.png파일 복사 및 url() 재작성됨
          background: url(/images/example.png?d41d8cd98f00b204e9800998ecf8427e);
      }
      ```
    - 기존 폴더 존재시 (파일이 복사되지 않도록) URL재작성동작 비활성화가능
      ```php
      mix.sass('resources/app/app.scss', 'public/css')
      .options({
          processCssUrls: false // 비활성화
      });
      ```
### 4.4.7. 소스맵
개발자도구에서 디버깅 정보 확인할 수 있게 해줌 (컴파일 성능은 저하)
```php
mix.js('resources/js/app.js', 'public/js')
    .sourceMaps(); // 소스맵활성화 
```


## 4.5. 자바스크립트에 적용하기
```php
mix.js('resources/js/app.js', 'public/js');
// es2015컴파일, 모듈번들링, .vue파일 컴파일, minification을 수행하게 됨
```
### 4.5.1. Vendor분할
- 어플리케이션 코드 변경시, 브라우저가 라이브러리는 새로 다운로드 받지 않도록(캐싱에 유리하도록) vendor.js파일로 분리
  ```php
  mix.js('resources/js/app.js', 'public/js')
      .extract(['vue']) // vendor.js파일로 추출할 라이브러리/모듈 배열을 인자로 전달
  ```
- extract() 후에 생성되는 파일들
  - public/js/manifest.js: The Webpack manifest runtime
  - public/js/vendor.js: Your vendor libraries
  - public/js/app.js: Your application code    
- 순서대로 파일을 로드해야 함
  ```php
  <script src="/js/manifest.js"></script>
  <script src="/js/vendor.js"></script>
  <script src="/js/app.js"></script>
  ```

### 4.5.2. React
```php
mix.react('resources/js/app.jsx', 'public/js'); // Babel플러그인 자동생성됨
```
### 4.5.3. Vanila JS
```php
mix.scripts([ // 바닐라js파일을 통합 및 minification
    'public/js/admin.js',
    'public/js/dashboard.js'
], 'public/js/all.js');
// mix.babel() 사용시 Babel 컴파일도 됨
```
### 4.5.4. 커스텀 webpack설정
- 라라벨 Mix는 webpack.config.js파일을 참조함
- 설정파일 변경가능
- 커스텀 설정 병합 : webpackConfig() 사용
  ```php
  mix.webpackConfig({ // webpack설정 포함하는 객체를 받음
      resolve: {
          modules: [
              path.resolve(__dirname, 'vendor/laravel/spark/resources/assets/js') 
          ]
      }
  });
  ```
- 커스텀 설정파일
  1. node_modules/laravel-mix/setup/webpack.config.js를 루트디렉토리에 복사
  2. package.json의 --config참조를 새로운 설정파일로 지정
  (업데이트시 webpack.config.js내역을 수동으로 커스텀파일에 병합해야 함)

## 4.6. 파일 & 디렉토리 복사
node_modules 디렉토리 내의 특정 asset을 public 폴더로 재배치해야 할 때 유용
```php
mix.copy('node_modules/foo/bar.css', 'public/css/bar.css');
mix.copyDirectory('resources/img', 'public/img');
```

## 4.7. 버전관리/ 캐시 갱신
새로운 assets를 강제로 로드하게 할 때 version() 메소드 사용
```php
mix.js('resources/js/app.js', 'public/js')
    .version(); // 자동으로 컴파일된 파일이름뒤에 hash값을 붙여 캐시갱신
```
```php
<script src="{{ mix('/js/app.js') }}"></script> 
// view파일에서 mix헬퍼함수를 호출하여 hash값이 붙은 파일명을 사용할 수 있음
```
- production환경에서만 버저닝하도록 지정가능
  ```php
  mix.js('resources/js/app.js', 'public/js');
  if (mix.inProduction()) {
      mix.version();
  }
  ```
## 4.8. Browersync 리로딩
- 파일 변경사항 감시하여 자동으로 변경사항을 브라우저에 반영하는 것
- browserSync활성화 코드
  ```php
  mix.browserSync('my-domain.test');  

  // Or...

  // https://browsersync.io/docs/options
  mix.browserSync({
      proxy: 'my-domain.test' // 프록시 전달 가능
  });
  ```
- ```npm run watch``` 명령어로 webpack의 dev서버 시작


## 4.9. 환경변수
- .env파일에 MIX_로 시작하는 키 사용시 환경변수를 Mix에 지정가능
  ```php
  MIX_SENTRY_DSN_PUBLIC=http://example.com
  ```
-  process.env객체를 통해 환경변수에 접근가능
  ```php
  process.env.MIX_SENTRY_DSN_PUBLIC
  ```
- watch 작업중 환경변수 변경시 재시작해야 함

## 4.10. Notification
- Mix가 번들링을 마치면 자동으로 알림 표시
- 알림 비활성화시 (e.g.프로덕션 서버에서 Mix사용시)
  ```php
  mix.disableNotifications();
  ```
