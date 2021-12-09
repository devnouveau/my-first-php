# 1. 시작하기
## 1.1. 시작하기
- 설정은 phpunit.xml
- tests디렉토리 : Feature, Unit
- 대부분의 테스트는 하나의 메소드에 포커스
- 
## 1.2. 환경
- 테스팅시 array드라이버에 세션,캐시 자동설정(테스트 중 세션,캐시 데이터 유지되지 않음)
- 프로젝트 루트에 .env.testing파일 생성
    - PHPUnit테스트 실행시, --env=testing 옶션 실행시 .env오버라이드
## 1.3. 테스트 생성 & 실행
- 테스트 케이스 생성 artisan 명령어
    ```bash
    // Create a test in the Feature directory...
    $ php artisan make:test UserTest

    // Create a test in the Unit directory...
    $ php artisan make:test UserTest --unit
    ```
- 실행
    phpunit커맨드로 실행

# 2. HTTP 테스트
## 2.1. 시작하기
### 2.1.1.요청-Request 헤더 커스터마이징하기
```php
public function testBasicExample()
{
    $response = $this->withHeaders([
        'X-Header' => 'Value',
    ])->json('POST', '/user', ['name' => 'Sally']);

    $response
        ->assertStatus(201)
        ->assertJson([
            'created' => true,
        ]);
}
```

### 2.1.2. 응답 디버깅



## 2.2. 세션 / 인증
```php
class ExampleTest extends TestCase
{
    public function testApplication()
    {
        // actingAs() 헬퍼메소드는 사용자 인증, gurad(생략가능)지정
        // withSession() 으로 배열을 세션에 저장
        $response = $this->actingAs($user, 'api') // 사용자, 인증가드지정
                        ->withSession(['foo' => 'bar'])
                        ->get('/');
    }
}
```
## 2.3. JSON API 테스팅
```php
public function testBasicExample()
{
    $response = $this->postJson('/user', ['name' => 'Sally']);

    $response
        ->assertStatus(201)
        ->assertJson([
            'created' => true,
        ])
        ->assertExactJson([
            'created' => true,
        ])
        ->assertJsonPath('team.owner.name', 'foo')
```
-  json, getJson, postJson, putJson, patchJson, deleteJson, optionsJson 메소드

## 2.4. 파일 업로드 테스트하기
- fake()로 더미 파일/이미지 생성하여 테스트
    ```php
    UploadedFile::fake()->image('avatar.jpg', $width, $height)->size(100);
    UploadedFile::fake()->create('document.pdf', $sizeInKilobytes);
    ```
## 2.5. 사용가능한 Assertions
### 2.5.1. 응답 Assert 메소드들
- 응답상태, 쿠키/세션 데이터 확인 등 
### 2.5.2 인증 Assert 메소드들
- 인증여부, 인증정보 유효성 확인 등





# 3. 콘솔 테스트
## 3.1. 시작하기
사용자 입력을 요구하는 콘솔 애플리케이션 테스트를 위한 API
## 3.2. Input / Output 
- expectsQuestion , expectsOutput, assertExitCode 메소드를 통해 사용자 입력값 mocking, 예상되는 종료코드 및 텍스트 지정




# 4. 브라우저 테스트 (라라벨 Dusk 패키지)
## 4.1. 시작하기
- 브라우저 자동화, 테스팅 API 제공
- 크롬 드라이버 사용 (기타 selenium 호환 드라이버 사용가능)
- dusk명령어 사용시 PHPUnit 테스트에 전달할 수 있는 인자들을 받을 수 있음

## 4.2. 설치하기
- 설치순서
    1. 컴포저의존성 추가
    2. 아티즌 install실행
    3. .env파일 - APP_URL 환경변수 설정
    4. dusk아티즌 명령어로 테스트 실행

- 크롬 드라이버 설치 관리  
    - (dusk에 포함된 것과 다른 버전의 크롬 드라이버 설치시)
- 크롬외 다른 브라우저 사용하기

## 4.3. 시작하기
### 4.3.1. 테스트 클래스 생성하기
```bash
$ php artisan dusk:make LoginTest # tests/Browser 디렉토리에 저장됨
```
### 4.3.2. 테스트 실행하기
```bash
$ php artisan dusk
$ php artisan dusk:fails
```
### 4.3.3. 구동환경 처리
.env.dusk.{environment} 파일을 루트 디렉토리에 생성 (기존 .env파일 복사)

### 4.3.4. 브라우저 생성하기
- $this->browse(function($browser1,$browser2...)) 메소드 사용
    - $browser1, $browser2은 생성된 브라우저 인스턴스들

### 4.3.5. 브라우저 매크로
- 커스텀 브라우저 메소드 정의시 사용(여러 테스트에서 재사용 가능)
    ```php
    public function boot() // DuskServiceProvider 클래스
    {
        Browser::macro('scrollToElement', function ($element = null) {
            $this->script("$('html, body').animate({ scrollTop: $('$element').offset().top }, 0);");

            return $this;
        });
    }
    ```
### 4.3.6. 인증
- loginAs(사용자ID 혹은 모델인스턴스) 메소드로 인증생략
    - 파일 내의 모든 테스트에서 사용자 세션 유지


### 4.3.7. 데이터베이스 마이그레이션
DatabaseMigrations 트레이트사용

## 4.4. Element 조작하기
### 4.4.1. Dusk 선택자
- CSS선택자(html구조 변경시 테스트 중단) 대신 사용
- html요소에 dusk속성추가, 속성값으로 엘리먼트 조작

### 4.4.2. 링크 클릭
$browser->clickLink($linkText) // jQuery사용됨

### 4.4.3. Text(), Values(), & Attributes()

### 4.4.4. Form 사용하기
- 값 입력, 선택 메소드들
    - type(), select(), check(), uncheck(), radio()

### 4.4.5. 파일 첨부
- attach()

### 4.4.6. 키보드 사용하기
- keys()

### 4.4.7. 마우스 사용하기
- click(), mouseover(), drag() 등

### 4.4.8. 자바스크립트 대화상자
```php
// Wait for a dialog to appear:
$browser->waitForDialog($seconds = null);

// Assert that a dialog has been displayed and that its message matches the given value:
$browser->assertDialogOpened('value');

// Type the given value in an open JavaScript prompt dialog:
$browser->typeInDialog('Hello World');
```

### 4.4.9. Scoping Selectors
```php
//  주어진 selector안에서 특정 범위를 지정하여 동작을 수행
$browser->with('.table', function ($table) {
    $table->assertSee('Hello World')
          ->clickLink('Delete');
});
```

### 4.4.10. Elements 렌더링 기다리기
- pause(), waitFOr() 등...
### 4.4.11. Vue Assertions 만들기
- Vue컴포넌트 데이터에 대한 값을 가정하는 메소드 assertVue() 사용



## 4.5. 사용 가능한 Assertions



## 4.6. 페이지-Pages
- 순서대로 수행되는 복잡한 작업을 단일메소드로 정의
- 페이지 탐색, 페이지 내 공통선택자에 대한 단축키 사용

### 4.6.1. 페이지 생성하기
### 4.6.2. 페이지 설정하기
### 4.6.3. 페이지 탐색
### 4.6.4. 단축 셀렉터
### 4.6.5. 페이지 메소드


## 4.7. 컴포넌트
- 페이지와 유사. but 특정URL과 바인딩되지 않음
컴포넌트 생성하기
컴포넌트 사용하기
## 4.8. CI - 지속적 통합
CircleCI
Codeship
Heroku CI
Travis CI
GitHub Actions



# 5. 데이터베이스
## 5.1. 시작하기
- assertDatabaseHas(), assertDatabaseMissing() 등의 헬퍼함수 사용하여 DB내용 테스트 

## 5.2. 팩토리 생성하기

## 5.3. 각각의 테스트 수행 후에 데이터베이스 재설정하기
- 다음테스트를 위해 테스트종료시 DB재설정 필요
- RefreshDatabase 트레이트, DatabaseTransactions 트레이트 사용하여 DB를 자동재설정

## 5.4. 팩토리 작성하기
- 모델 팩토리를 사용하여 각각의 Eloquent 모델을 위한 기본 속성의 세트를 정의하도록 해줌
- Faker PHP 라이브러리 인스턴스를 통해 랜덤데이터 생성

### 5.4.1. 팩토리 상태(States)
- $factory->state() : 모델 팩토리의 속성을 변경가능

### 5.4.2. 팩토리 콜백
모델 생성시/ 생성후 작업 수행

## 5.5. 팩토리 사용하기
팩토리의 다양한 메소드를 사용해 다음을 수행
### 5.5.1. 모델 생성하기
### 5.5.2. 모델 저장하기
### 5.5.3. 모델 간 관계 설정

## 5.6. Seed 사용
seed() 메소드 사용

## 5.7. 사용 가능한 Assertions
- db에 데이터 존재/삭제 여부 등 확인하는 메소드들






# 6. MOCKING 모의객체
- 애플리케이션의 특정 부분을 mock하여 테스트진행시 실행되지 않도록 할 수 있음 
- e.g. 이벤트 발생 컨트롤러 테스트시, 이벤트리스너가 실행되지 않도록 하여 컨트롤러의 응답만 테스트 가능

## 6.2. Mocking 객체로 사용할 수 있게 하는 메소드들
- mocking(), partialMock(), Mockery::spy()


- mock대신 사용가능한 것들..
    - Bus::Fake(), Event::Fake() 등...
    - 테스트 대상 코드가 실행된 뒤에 검증이(assertions) 수행
## 6.3. Bus::Fake()
- job처리 방지
## 6.4. Event::Fake()
- 이벤트리스너 동작방지
### 6.4.1. Scoped Event Fakes
## 6.5. Mail::Fake()
- 메일발송 방지
## 6.6. Notification::Fake()
- 알림발송 방지
## 6.7. Queue::Fake()
- job처리 방지
## 6.8. Storage::Fake()
- UploadedFile 파일생성,가짜디스크생성
## 6.9. 파사드
- 전통적인 스태틱 메소드의 호출과 다르게, 파사드는 mock이 가능