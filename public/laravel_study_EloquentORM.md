# 1. Eloquent ORM 시작하기
## 1.1. 시작하기
- ORM : 객체와 관계형 데이터베이스의 데이터를 자동으로 매핑(연결)
- Eloquent : 라라벨의 ORM 
- onfig/database.php에 db connection이 설정되어 있어야 함

## 1.2. 모델 정의하기
- 모델의 위치
    - 일반적으로 app 디렉토리에 위치
    - composer.json에 의해 오토로드되는 위치면 어디든 상관없음
- 모든 Eloquent모델은 Illuminate\Database\Eloquent\Model을 상속
- make:model 아티즌 커맨드로 모델 클래스 생성
    ```bash
    $ php artisan make:model Flight

    # 마이그레이션도 함께 생성하는 경우 옵션 사용   
    $ php artisan make:model Flight --migration
        $ php artisan make:model Flight -m
    ```


### 1.2.1. Eloquent 모델 컨벤션
```php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    /* 테이블명 */
    // 스네이크 케이스, 복수형 이름
    // 미지정시 Flight모델의 테이블을 flights로 추정
    protected $table = 'my_flights';

    /* primary keys */
    // 미지정시 컬럼이름을 id로 추정
    protected $primaryKey = 'flight_id';

    // Eloquent는 기본적으로 pk를 incrementing int로 추정(자동 캐스팅)

    /* primary keys - incrementing 속성*/
    // non-incrementing pk사용시 false로 지정
    public $incrementing = false;

    /* primary keys - keyType 속성*/
    // int아닌 pk 사용시 string으로 지정
    protected $keyType = 'string';


    /* timestamps */
    // Eloquent는 기본적으로 테이블에 created_at, updated_at 컬럼이 존재한다고 추정
    // 이 컬럼값을 채우지 않으려면 false로 지정
    public $timestamps = false;

    /* timestamp 포맷 변경시 */
    // 날짜 속성이 db에 저장될 때 포맷, 
    // 모델이 배열/JSON으로 직렬화될 때 포맷을 지정
    protected $dateFormat = 'U';
    
    /* timestamp 저장하는 컬럼명 수정하는 경우 상수설정 */
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'last_update';


    /* DB connection */
    // 미지지정시 기본으로 지정된 커넥션 사용
    protected $connection = 'connection-name';


    /* 모델 속성에 대한 기본값 정의 */
    protected $attributes = [
        'delayed' => false,
    ];
}
```

### 1.2.2. 기본 속성 값
1.2.1. Eloquent 모델 컨벤션 참조

## 1.3. 모델 조회하기
- 각 Eloquent모델을 쿼리빌더처럼 사용가능
    ```php

    $flights = App\Flight::where('active', 1)
            ->orderBy('name', 'desc')
            ->take(10)
            ->get();
    // $flights = App\Flight::all();

    foreach ($flights as $flight) {
        echo $flight->name;
    }
    ```
- 모델 리프레쉬
    ```php
    // fresh() : 모델을 다시 검색 (기존 모델 인스턴스에 영향 X)
    $flight = App\Flight::where('number', 'FR 900')->first();
    $freshFlight = $flight->fresh();

    // refresh() : 기존 모델 갱신 (기존 모델 인스턴스에 영향 O)
    $flight = App\Flight::where('number', 'FR 900')->first();
    $flight->number = 'FR 456';
    $flight->refresh();
    $flight->number; // "FR 900"
    ```

### 1.3.1. 컬렉션
- 복수의 레코드를 조회하는 Eloquent메소드는 Illuminate\Database\Eloquent\Collection인스턴스를 반환
- 컬렉션 메소드 적용가능
    ```php
    $flights = $flights->reject(function ($flight) {
        return $flight->cancelled;
    });
    foreach ($flights as $flight) {
        echo $flight->name;
    }
    ```

### 1.3.2. 결과 분할하기 (메모리 절약)
- chunk() : 결과를 n개로 나누어 처리
    ```php
    // 200개의 레코드를 받아 각각 클로저로 처리
    Flight::chunk(200, function ($flights) {
        foreach ($flights as $flight) {
            //
        }
    });
    ```
- cursor() : 단 하나의 쿼리를 실행하여 반복. 대량 데이터처리.
    ```php
    foreach (Flight::where('foo', 'bar')->cursor() as $flight) {
        //
    }
    ```

### 1.3.3. 고급 서브쿼리
- 서브쿼리 선택
    ```php
    use App\Destination;
    use App\Flight;
    // addSelect(), select() 사용
    return Destination::addSelect(
        [
        'last_flight' => Flight::select('name')
        ->whereColumn('destination_id', 'destinations.id')
        ->orderBy('arrived_at', 'desc')
        ->limit(1)
        ]
    )->get();    
    ```
- 서브쿼리 정렬
    ```php
    // 서브쿼리 내 orderBy()
    return Destination::orderByDesc(
        Flight::select('arrived_at')
            ->whereColumn('destination_id', 'destinations.id')
            ->orderBy('arrived_at', 'desc')
            ->limit(1)
    )->get();
    ```

## 1.4. 하나의 모델 조회 / 집계조회
```php
// Retrieve a model by its primary key...
$flight = App\Flight::find(1);
$flights = App\Flight::find([1, 2, 3]);
// Retrieve the first model matching the query constraints...
$flight = App\Flight::where('active', 1)->first();
```
- Not Found Exceptions
    ```php
    // findOrFail(), firstOrFail() 
    // : 쿼리의 첫번째 결과반환
    // : 결과 없으면 Illuminate\Database\Eloquent\ModelNotFoundException
    $model = App\Flight::findOrFail(1);
    $model = App\Flight::where('legs', '>', 100)->firstOrFail();
    ```
    ```php
    Route::get('/api/flights/{id}', function ($id) {
        return App\Flight::findOrFail($id); // 404응답으로 반환됨
    });
    ```
### 1.4.1. 합계 조회
- 쿼리빌더의  count, sum, max 등 집계 메소드 사용


## 1.5. 모델 추가/수정하기
### 1.5.1. Insert
```php
class FlightController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request...


        // 1. 모델 인스턴스 생성
        $flight = new Flight;
        // 2. 모델 속성 지정
        $flight->name = $request->name;
        // 3. save() 메소드 호출
        $flight->save(); 
        // save() 호출시 
        // : DB에 레코드 추가됨
        // : created_at와 updated_at 타임스탬프 자동 설정
    }
}
```
### 1.5.2. Update
```php
// 모델 조회하여 인스턴스 가져오기
$flight = App\Flight::find(1);

$flight->name = 'New Flight Name';

$flight->save();
```
- update(컬럼=>값 쌍의 배열) : 복수의 모델 update
    ```php
    App\Flight::where('active', 1)
            ->where('destination', 'San Diego')
            ->update(['delayed' => 1]); 
    ```

### 1.5.3. 대량 할당 - Mass Assignment
- Mass Assignment 취약점
    - 파라미터 변조 하여 관리자 권한 탈취 가능
- $fillable, $guarded 중 하나의 속성을 사용하여 대량할당 허용/제한 가능
1. $fillable 속성 지정 (화이트리스트) : 
    - 취약점 보완을 위해 특정 모델의 특정 속성만 대량 할당이 가능하도록 설정해야 함
        ```php
        class Flight extends Model
        {
            protected $fillable = ['name']; // 대량할당 가능한 속성
        }
        ```
    - fillable속성 지정 후 
        ```php
        // create()메소드로 db에 레코드 추가 및 모델 인스턴스 반환
        $flight = App\Flight::create(['name' => 'Flight 10']);

        // 모델 인스턴스가 이미 있는 경우
        $flight->fill(['name' => 'Flight 22']);
        ```
2. $guarded 속성 지정 (블랙리스트) : 
    - 특정속성의 대량할당을 방지
    ```php
    class Flight extends Model
    {
        protected $guarded = ['price'];
       
        protected $guarded = []; // 모든 속성들의 대량할당을 허용시
    }
    ```


### 1.5.4. 기타 생성을 위한 메소드들
- firstOrCreate() / firstOrNew() 
    - 속성 대량할당하여 모델생성
        ```php
        // firstOrCreate() : 
        // 주어진 컬럼-값 쌍으로 모델 검색 후 모델을 찾을 수 없으면 새로운 레코드 입력
        $flight = App\Flight::firstOrCreate(['name' => 'Flight 10']);
        $flight = App\Flight::firstOrCreate(
            ['name' => 'Flight 10'],
            ['delayed' => 1, 'arrival_time' => '11:30']
        );

        // firstOrNew() : 
        // 모델을 찾을 수 없으면 새 모델 인스턴스 반환
        // (레코드를 저장하려면 save()를 호출해야 함)
        $flight = App\Flight::firstOrNew(['name' => 'Flight 10']);
        $flight = App\Flight::firstOrNew(
            ['name' => 'Flight 10'],
            ['delayed' => 1, 'arrival_time' => '11:30']
        );
        ```
- updateOrCreate() 
    ```php
    // updateOrCreate() 
    // : 모델 조회하여 있으면 update, 없으면 insert. 
    // : 자동으로 save()됨
    $flight = App\Flight::updateOrCreate(
        ['departure' => 'Oakland', 'destination' => 'San Diego'],
        ['price' => 99, 'discounted' => 1]
    );
    ```
## 1.6. 모델 삭제하기
```php
/* 모델객체 조회하여 삭제 */
$flight = App\Flight::find(1);
$flight->delete();

/* 모델의 기본키를 통해서 삭제 */
App\Flight::destroy(1);
App\Flight::destroy(1, 2, 3);
App\Flight::destroy([1, 2, 3]);
App\Flight::destroy(collect([1, 2, 3]));

/* 쿼리빌더로 삭제 */
$deletedRows = App\Flight::where('active', 0)->delete();
```



### 1.6.1. 소프트 삭제하기
- 모델에 deleted_at 속성을 지정해 DB에 입력되도록함 (실제로 삭제하지 않으면서 삭제된 것처럼 간주함)
1. Illuminate\Database\Eloquent\SoftDeletes 속성을 사용하여 deleted_at 값을 가질 수 있도록 함
    ```php
    namespace App;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class Flight extends Model
    {
        use SoftDeletes;
    }
    ```  
2. deleted_at 컬럼을 DB 테이블에 추가
    ```php
    Schema::table('flights', function (Blueprint $table) {
        $table->softDeletes();
    });
    ```
3. delete() 호출
    - deleted_at컬럼값에 현재 타임스탬프 저장됨
    - 쿼리시 소프트삭제된 모델은 자동으로 제외됨
4. 모델 인스턴스의 소프트삭제 여부 확인
    ```php
    if ($flight->trashed()) {
        //
    }
    ```

### 1.6.2. 소프트 삭제된 모델 쿼리하기
- 쿼리시 소프트삭제된 모델은 자동으로 제외되나, withTrashed()메소드로 조회가능
    ```php
    $flights = App\Flight::withTrashed()
                    ->where('account_id', 1)
                    ->get();

    $flight->history()->withTrashed()->get();

    /* 소프트 삭제된 모델만 가져오기 */ 
    $flights = App\Flight::onlyTrashed()
                ->where('airline_id', 1)
                ->get();

    /* 소프트 삭제된 모델 복구 */ 
    $flight->restore();
    
    App\Flight::withTrashed() 
            ->where('airline_id', 1)
            ->restore();
    
    $flight->history()->restore();
    ```

- 모델 영구 삭제
    ```php
    // Force deleting a single model instance...
    $flight->forceDelete();

    // Force deleting all related models...
    $flight->history()->forceDelete();
    ```

## 1.7. 쿼리 스코프
### 1.7.1. 글로벌 스코프
- 글로벌 스코프 작성
    - Illuminate\Database\Eloquent\Scope인터페이스 구현
    - 글로벌 스코프 적용하여 쿼리시 select()대신 addSelect() 사용하여 기존 select절이 교체되지 않도록 해야 함
        ```php
        namespace App\Scopes;

        use Illuminate\Database\Eloquent\Builder;
        use Illuminate\Database\Eloquent\Model;
        use Illuminate\Database\Eloquent\Scope;

        class AgeScope implements Scope
        {
            public function apply(Builder $builder, Model $model)
            {
                $builder->where('age', '>', 200);
            }
        }
        ```
- 글로벌 스코프 적용
    - 모델 클래스의 boot() 메소드 오버라이딩 하여 addGlobalScope() 호출
    ```php
    namespace App;

    use App\Scopes\AgeScope;
    use Illuminate\Database\Eloquent\Model;

    class User extends Model
    {
        protected static function boot()
        {
            parent::boot();
            static::addGlobalScope(new AgeScope);

            // 글로벌스코프 클래스 생성하지 않고 클로저를 사용하여 정의할 수도 있음
            /*
            static::addGlobalScope('age', function (Builder $builder) {
                    $builder->where('age', '>', 200);
                });
            }
            */
    }
    ```
- 글로벌 스코프 적용 예시
    ```php
    User::all();
    // select * from `users` where `age` > 200 
    ```
- 글로벌 스코프 삭제하기
    ```php
    // withoutGlobalScope(스코프클래스)
    User::withoutGlobalScope(AgeScope::class)->get();

    // withoutGlobalScope(글로벌스코프명?) : 클로저로 글로벌스코프정의한 경우
    User::withoutGlobalScope('age')->get();

    
    // Remove all of the global scopes...
    User::withoutGlobalScopes()->get();

    // Remove some of the global scopes...
    User::withoutGlobalScopes([
        FirstScope::class, SecondScope::class
    ])->get();
    ```

### 1.7.2. 로컬 스코프
- Eloquent메소드 이름에 scope접두어를 붙여 스코프 정의
- scope는 항상 쿼리빌더 인스턴스 반환
    ```php
    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class User extends Model
    {
        public function scopePopular($query) //scope...()
        {
            return $query->where('votes', '>', 100);
        }
        public function scopeActive($query)
        {
            return $query->where('active', 1);
        }
    }
    ```
- 적용예시
    ```php
    // 스코프 메소드 호출시에는 scope접두사를 제외해야 함
    $users = App\User::popular()->active()->orderBy('created_at')->get();

    // or쿼리 연산자로 모델범위 결합시 1.
    // 클로저 내에서 스코프 메소드 호출
    $users = App\User::popular()->orWhere(function (Builder $query) {
        $query->active();
    })->get();

    // or쿼리 연산자로 모델범위 결합시 2.
    // 클로저 사용할 필요없이 바로 스코프 메소드 호출
    $users = App\User::popular()->orWhere->active()->get();
    ```
- 다이나믹 스코프
    - 파라미터를 받는 스코프 정의시 
        ```php
        class User extends Model
        {
            // $query인자 다음으로 파라미터 전달
            public function scopeOfType($query, $type) 
            {
                return $query->where('type', $type);
            }
        }
        ```
    - 스코프 메소드 호출시 파라미터 전달
        ```php
        $users = App\User::ofType('admin')->get();
        ```



## 1.8. 모델 비교
```php
// 두개의 모델이 동일한 primary key, 테이블, 데이터베이스 커넥션을 가지는지 비교
if ($post->is($anotherPost)) {

}
```


## 1.9. 이벤트
- Eloquent 모델은 이벤트를 발생시켜 모델 라이프사이클의 다양한 지점에서 후킹가능
- 각 이벤트는 생성자를 통해 모델 인스턴스를 받음
- 대량 업데이트/삭제시 saved, updated, deleting 및 deleted 모델 이벤트는 발생 X (실제로 모델이 검색되진 않기 때문)
- 이벤트 종류
    - retrieved : DB에서 모델 존재하여 조회시
    - creating, created : 새로운 모델이 처음 저장
    - updating, updated : 모델이 이미 DB에 존재할 때 save()호출시
    - saving, saved : save() 호출시
    - deleting, deleted
    - restoring, restored
- 모델 클래스에 이벤트 정의/매핑
    ```php
    class User extends Authenticatable
    {
        use Notifiable;

        protected $dispatchesEvents = [
            'saved' => UserSaved::class,
            'deleted' => UserDeleted::class,
        ];
    }
    ```
    - 이벤트 리스너를 이용해 해당 이벤트 처리






## 1.10. 옵저버
### 1.10.1. 옵저버 객체 정의
- 주어진 모델이 다수의 이벤트 수신하고자 하는 경우
- 모든 리스너를 하나의 옵저버클래스로 구성
- 옵저버클래스는 수신하는 Eloquent이벤트와 대응하는 메소드 이름을 가짐
1. make:observer 아티즌 명령어로 옵저버 클래스 생성
    ```bash
    $ php artisan make:observer UserObserver --model=User
    ```
    - App/Observers디렉토리에 옵저버클래스 생성됨
        ```php
        namespace App\Observers;
        use App\User;
        class UserObserver
        {
            public function created(User $user){}
            public function updated(User $user){}
            public function deleted(User $user){}
        }
        ```
2. 옵저버 등록
    - AppServiceProvider의 boot()내에서 observe()메소드로 옵저버 등록
        ```php
        namespace App\Providers;

        use App\Observers\UserObserver;
        use App\User;
        use Illuminate\Support\ServiceProvider;

        class AppServiceProvider extends ServiceProvider
        {
            //... 
            public function boot()
            {
                User::observe(UserObserver::class);
            }
        }
        ```









# 2. RELATIONSHIPS-관계
## 2.1. 시작하기

## 2.2. 관계 정의하기
- Eloquent모델 클래스에서 메소드로 정의
- 관계 정의시 쿼리빌더 기능으로도 작동함


### 2.2.1. 1:1(일대일) 관계 - hasOne(), belongsTo()
- 관계 정의
    ```php
    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class User extends Model
    {
        public function phone()
        {
            // User 모델이 phone을 하나 가짐 (1:1 관계)
            return $this->hasOne('App\Phone');

        
            // Eloquent는 모델명에 근거해 관계의 외래키를 결정
            // (phone모델은 user_id를 외래키로 가질 것으로 추정함)
            /* 외래키 재정의시 */
            // return $this->hasOne('App\Phone', 'foreign_key');


            /* 외래키가 참조하는 기본키 재정의시 */
            // 기본적으로 id컬럼 값을 기본키로 추정
            // return $this->hasOne('App\Phone', 'foreign_key', 'local_key');

        }
    }
    ```
- 관계의 역 정의 
    ```php
    class Phone extends Model
    {
        // Phone이 User에 하나 소유됨 (1:1)
        public function user()
        {
            return $this->belongsTo('App\User');
            // return $this->belongsTo('App\User', 'foreign_key');
            // return $this->belongsTo('App\User', 'foreign_key', 'other_key');
        }
    }
    ```
- 동적속성에 접근하듯 relationship 메소드에 접근 가능
    ```php
    // 1:1 관계
    $phone = App\Post::find(1)->phone;
    // 1:1 역관계
    $phone = App\Phone::find(1);
    echo $phone->user->name;
    ```


### 2.2.2. 1:*(일대다) 관계 - hasMany(), belongsTo()
- 관계 정의
    ```php
    class Post extends Model
    {
        public function comments()
        {
            return $this->hasMany('App\Comment');

            // Comment모델의 외래키를 자동으로 결정
            // 소유하는 모델의 snake case이름에 _id를 붙여 외래키로 추정
            // (comment모델의 외래키는 post_id)


            // return $this->hasMany('App\Comment', 'foreign_key');
            // return $this->hasMany('App\Comment', 'foreign_key', 'local_key');
        }
    }
    ```
- 관계의 역 정의
    ```php
    class Comment extends Model
    {
        public function post()
        {
            return $this->belongsTo('App\Post');
            // return $this->belongsTo('App\User', 'foreign_key');
            // return $this->belongsTo('App\User', 'foreign_key', 'other_key');
        }
    }
    ```
- 동적속성에 접근하듯 relationship 메소드에 접근 가능
    ```php
    // 1:N 관계
    $comments = App\Post::find(1)->comments;
    foreach ($comments as $comment) {
    }
    // 1:N 역관계
    $comment = App\Comment::find(1);
    echo $comment->post->title;
    ```
- 모든 relationship 메소드 쿼리빌더 역할을 함 (조건 메소드 체이닝 가능)
    ```php
    $comment = App\Post::find(1)->comments()->where('title', 'foo')->first();
    ```
    
### 2.2.4. *:*(다대다) 관계 - belongsToMany()
```
users
    id - integer
    name - string

roles
    id - integer
    name - string

role_user (중간테이블 = 피벗모델)
    user_id - integer
    role_id - integer
```

- 관계 정의
    ```php
    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class User extends Model
    {
        /**
        * The roles that belong to the user.
        */
        public function roles()
        {
            return $this->belongsToMany('App\Role');


            /*  join 테이블명 지정  */
            // (미지정시 자동으로 두 모델이름을 알파벳순으로 결합)
            // return $this->belongsToMany('App\Role', 'role_user');

            /* 키 이름 커스텀 */
            // return $this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id');
            // 세번째 인자는 관계정의하는 모델의 외래키이름
            // 네번째 인자는 join되는 모델의 외래키이름
            



            /* 중간 테이블이 추가속성을 포함하는 경우 */
            // return $this->belongsToMany('App\Role')->withPivot('column1', 'column2');

            /* 중간 테이블이 created_at와 updated_at 타임스탬프를 포함하는 경우 */
            // return $this->belongsToMany('App\Role')->withTimestamps();

            /* pivot속성명(중간테이블에 접근하는 속성) 커스텀 */
            // return $this->belongsToMany('App\Role')
            //    ->as('role_user') // 중간테이블 접근시 pivot대신 role_user으로 접근 가능
            //    ->withTimestamps();

            /* belongsToMany가 반환하는 결과를 중간테이블값으로 필터링 */
            // return $this->belongsToMany('App\Role')->wherePivot('approved', 1);

            // return $this->belongsToMany('App\Role')->wherePivotIn('priority', [1, 2]);

        }
    }
    ```
- 관계의 역 정의
    ```php
    namespace App;
    use Illuminate\Database\Eloquent\Model;
    class Role extends Model
    {
        public function users()
        {
            return $this->belongsToMany('App\User');
        }
    }
    ```
- roles동적 속성으로 접근가능
- relationships메소드를 쿼리빌더처럼 체이닝하여 사용가능
- 중간 테이블 컬럼 조회하기
    - 다대다 관계는 중간 테이블 필요
    - 관계에 접근 후 pivot속성으로 중간테이블에 접근 가능
        ```php
        $user = App\User::find(1);
        foreach ($user->roles as $role) { // 관계에 접근
            echo $role->pivot->created_at; // pivot속성으로 접근
        }
        ```
    - 관계에 접근 후 커스텀된 이름으로 중간테이블에 접근
        ```php
        $users = User::with('roles')->get();
        foreach ($users->roles as $role) {
            echo $podcast->role_user->created_at;
        }
        ```

### 2.2.5. 커스텀 중간 테이블 모델(pivot모델) 정의하기
- using() 메소드로 관계정의
    ```php
    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Role extends Model
    {
        /**
         * The users that belong to the role.
         */
        public function users()
        {
            return $this->belongsToMany('App\User')
                        ->using('App\RoleUser'); // 피벗모델지정
            
            // pivot 모델은
            // Illuminate\Database\Eloquent\Relations\Pivot 클래스를 상속해야 함
            
            // pivot 모델이 커스텀 다형성 다대다 피벗모델일 경우
            // Illuminate\Database\Eloquent\Relations\MorphPivot클래스를 상속해야 함

            /* pivot 테이블에서 컬럼 검색시 */
            // using(), withPivot() 결합
            // return $this->belongsToMany('App\User')
            //             ->using('App\RoleUser')
            //             ->withPivot([
            //                   'created_by',
            //                   'updated_by',
            //                ]);
        }
    }
    ```
- 커스텀 pivot 모델 정의
    ```php
    namespace App;

    use Illuminate\Database\Eloquent\Relations\Pivot;

    class RoleUser extends Pivot // pivot클래스 상속
    {
        // pivot모델에 auto_incrementing 기본키 존재시
        public $incrementing = true;
    }
    ```


### 2.2.6. 연결을 통한 단일 관계 - hasOneThrough()
- 하나의 중간 테이블을 통해 연결
    ```
    users
    (중간테이블)
        id - integer
        supplier_id - integer

    suppliers
        id - integer

    history 
    (접근대상테이블)
    (supplier_id 컬럼이 없지만 suppliers가 users를 통해접근가능)
        id - integer
        user_id - integer
    ```
- Supplier 모델 정의
    ```php
    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Supplier extends Model
    {
        public function userHistory()
        {
            // hasOneThrough(접근대상모델, 중간모델)
            return $this->hasOneThrough('App\History', 'App\User'); 

            return $this->hasOneThrough(
                'App\History',
                'App\User',
                'supplier_id', // Foreign key on users table...
                'user_id', // Foreign key on history table...
                'id', // Local key on suppliers table...
                'id' // Local key on users table...
            );
        }
    }
    ```

### 2.2.7. 연결을 통한 다수를 가지는 관계
```php
countries
    id - integer
    name - string

users
    id - integer
    country_id - integer
    name - string

posts
    id - integer
    user_id - integer
    title - string
```

## 2.3. 다형성 관계
### 2.3.1. 1:1(일대일) 관계
### 2.3.2. 1:*(일대다) 관계
### 2.3.3. *:*(다대다) 관계
### 2.3.4. 사용자 정의 다형성 타입


## 2.4. 관계 쿼리 질의하기
### 2.4.1. 관계 메소드 Vs. 동적 속성
### 2.4.2. 존재하는 관계에 대한 쿼리 질의하기
### 2.4.3. 관계된 모델이 존재하지 않는 것을 확인하며 질의하기
### 2.4.4. 다형성 관계 쿼리
### 2.4.5. 연관된 모델 수량 확인하기-카운트


## 2.5. Eager 로딩
### 2.5.1. Eager 로딩 조건 제한하기
### 2.5.2. 지연 Eager 로딩

## 2.6. 연관된 모델 삽입하기
### 2.6.1. save 메소드



# 3. COLLECTIONS
### 3.1. 시작하기
- Eloquent로 부터 반환되는 모든 멀티 레코드 결과는 Illuminate\Database\Eloquent\Collection(라라벨의 base collection 상속받음) 객체의 인스턴스
- 모든 컬렉션은 Iterators이므로 반복문에서도 사용가능
- map() / reduce() 메소드를 통해 메소드 체이닝가능


### 3.2. 사용 가능한 메소드들
    ```php
    // 주어진 모델 인스턴스가 컬렉션에 포함되어 있는지 확인
    $users->contains(1);
    // $users컬렉션에 User::find(1)결과모델인스턴스가 있는지
    $users->contains(User::find(1));

    // 주어진 컬렉션에 존재하지 않는 모든 모델들 반환
    use App\User;
    $users = $users->diff(User::whereIn('id', [1, 2, 3])->get());

    // 주어진 기본키를 가지고 있지 않은 모든 모델 반환
    $users = $users->except([1, 2, 3]);


    // 주어진 기본키를 가진 모델 반환
    $users = User::all();
    $user = $users->find(1);

    // 컬렉션 내 모든 모델에 대한 관계를 로드
    $users->load('comments', 'posts');
    $users->load('comments.author');

    // 관계 로드 전 컬렉션 내 모든 모델에 대한 관계를 로드
    $users->loadMissing('comments', 'posts');
    $users->loadMissing('comments.author');

    // 주어진 컬렉션의 각 모델의 새 인스턴스 가져옴
    $users = $users->fresh();
    $users = $users->fresh('comments');

    // 주어진 컬렉션에 존재하는 모든 모델 반환
    use App\User;
    $users = $users->intersect(User::whereIn('id', [1, 2, 3])->get());


    // 컬렉션 내 모든 모델의 기본키 배열 반환
    $users->modelKeys();
    // [1, 2, 3, 4, 5]

    // 컬렉션의 각 모델의 속성을 가시화/숨김
    $users = $users->makeVisible(['address', 'phone_number']);
    $users = $users->makeHidden(['address', 'phone_number']);

    // 주어진 기본키를 갖는 모든 모델 반환
    $users = $users->only([1, 2, 3]);

    // 컬렉션 내 모든 유일한 모델 반환
    $users = $users->unique();
    ```

### 3.3. 커스텀 Collections 클래스
- 모델 클래스에서 newCollection()을 오버라이드
    ```php
    namespace App;

    use App\CustomCollection;
    use Illuminate\Database\Eloquent\Model;

    class User extends Model
    {
        /**
         * Create a new Eloquent Collection instance.
         *
         * @param  array  $models
         * @return \Illuminate\Database\Eloquent\Collection
         */
        public function newCollection(array $models = [])
        {
            return new CustomCollection($models);
        }
    }
    ```



# 4. MUTATORS
## 4.1. 시작하기

## 4.2. Accessors & Mutators
### 4.2.1. Accessor 정의하기
### 4.2.2. Mutator 정의하기

## 4.3. 날짜 Mutators

## 4.4. 속성(Attribute) 캐스팅
### 4.4.1. 배열 & JSON 캐스팅
### 4.4.2. 날짜 캐스팅






# 5. API RESOURCES
## 5.1. 시작하기

## 5.2. 리소스 클래스 생성하기

## 5.3. 컨셉 살펴보기
### 5.3.1. 리소스 컬렉션

## 5.4. 리소스 클래스 작성하기
### 5.4.1. 데이터 Wrapping(랩핑)
### 5.4.2. 페이지네이션
### 5.4.3. 조건에 따른 속성값 표현
### 5.4.4. 조건에 따른 관계 표현
### 5.4.5. 메타 데이터 추가하기

## 5.5. 리소스 응답






# 6. SERIALIZATION

## 6.1. 시작하기

## 6.2. 모델 & 컬렉션 Serializing
### 6.2.1. 배열로 Serializing
### 6.2.2. JSON 으로 Serializing

## 6.3. JSON 변환시 속성값 숨기기

## 6.4. JSON 변환시 특정 값 추가하기

## 6.5. 날짜 Serialization




