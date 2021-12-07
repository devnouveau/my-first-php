# 1. 시작하기
## 1.1. 시작하기
라라벨에서는 raw SQL, 쿼리빌더, Eloquent ORM을 사용해 DB처리를 쉽게 할 수 있게 해줌

### 1.1.1. 설정하기
- config/database.php에서 DB커넥션 및 기본DB커넥션 지정
- SQLite
    - DB 생성 후
    - DB의 절대경로 사용해 환경설정
        ```php
        DB_CONNECTION=sqlite
        DB_DATABASE=/absolute/path/to/database.sqlite  // 절대경로
        ```
        ```php
        // 외래키 제약조건 사용시 
        DB_FOREIGN_KEYS=true
        ```
- URL사용하여 구성
    - 일반적으로 DB연결 설정시 host, database, username, password 등 설정값 구성
    - 각 설정값은 환경변수를 가짐
    - 각 설정값으로 구성하는 대신 URL(혹은 DATABASE_URL 환경변수)설정 옵션으로 DB연결/인증 가능
    - DB url의 예
        ```php
        mysql://root:password@127.0.0.1/forge?charset=UTF-8
        ```
    - 표준 스키마 규칙
        ```php
        driver://username:password@host:port/database?options
        ```


### 1.1.2. 읽기 & 쓰기 커넥션 설정
- DB읽기, 쓰기에 따라 서로다른 DB커넥션 사용하도록 설정 가능
    ```php
    'mysql' => [
        'read' => [
            'host' => [
                '192.168.1.1',
                '196.168.1.2',
            ],
        ],
        'write' => [
            'host' => [
                '196.168.1.3',
            ],
        ],
        'sticky'    => true, // write한 데이터를 동일한 request 내에서 read할 수 있게 하는 옵션

        'driver'    => 'mysql',
        'database'  => 'database',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
    ],
    ```


### 1.1.3. 다수의 DB 커넥션 사용
- DB 설정 후
- 방법1. DB파사드의 connection(DB커넥션명)메소드로 DB사용
    ```php
    $users = DB::connection('foo')->select(...);
    ```
- 방법2. 커넥션 인스턴스의 getPdo()메소드 사용
    ```php
    $pdo = DB::connection()->getPdo();
    ```


## 1.2. Raw SQL쿼리 실행
- DB커넥션 후 DB파사드로 raw SQL 실행가능
- select()
    ```php
    // 컨트롤러
    public function index()
    {
        $users = DB::select('select * from users where active = ?', [1]); // sql인젝션 방지 위해 파라미터 바인딩
        return view('user.index', ['users' => $users]);

        /* 반환되는 배열 내 값들은 php의 stdClass객체형태
        foreach ($users as $user) {
            echo $user->name;
        }
        */
    }
    ```
- insert()
- update() : 변경된 레코드 갯수 반환
- delete() : 변경된 레코드 갯수 반환
- statement()
    ```php
    DB::statement('drop table users');
    ```




## 1.3. 쿼리 이벤트 리스닝
- 쿼리실행시 각 쿼리 확인하고 로깅, 디버깅 등 수행시 사용
- 서비스프로바이더에 쿼리리스너 등록
    ```php
    public function boot()
    {
        DB::listen(function ($query) {
            // $query->sql
            // $query->bindings
            // $query->time
        });
    }
    ```

## 1.4. 데이터베이스 트랜잭션
- DB::transaction(클로저, [데드락발생시 재시도횟수]) : 자동으로 트랜잭션
    ```php
    DB::transaction(function () {
        // Closure 내 Exception 발생시 자동으로 롤백
        DB::table('users')->update(['votes' => 1]);
        DB::table('posts')->delete();
    }, 5); // 데드락발생시 재시도횟수는 생략가능 // 모든 시도 실패시 exception 발생
    ```
    -  쿼리빌더 와 Eloquent ORM 모두에서 사용가능
- 수동 트랜잭션 사용
    ```php
    DB::beginTransaction();
    DB::rollBack();
    DB::commit();
    ```


# 2. 쿼리 빌더
## 2.1. 시작하기
- 쿼리 생성 및 운영에 편의기능 제공
- 라라벨 쿼리빌더는 PDO파라미터 바인딩을 사용
    - sql injection공격 방지
    - POD는 컬럼명 바인딩 미지원

## 2.2. 결과 조회하기
- 한 테이블의 모든 행 조회 : DB파사드의 table()메소드 사용
    ```php
    namespace App\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\DB;

    class UserController extends Controller
    {
        public function index()
        {
            $users = DB::table('users')->get(); 
            // DB::table('users')은 user테이블의 대한 쿼리빌더 인스턴스를 반환
            // get()을 통해 결과를 php stdClass객체로 구성된 Illuminate\Support\Collection으로 반환
            return view('user.index', ['users' => $users]);
        }
    }
    ```
- 하나의 결과/컬럼 조회
    ```php
    /* 하나의 행 조회 */
    // first()는 하나의 stdClass 객체 반환 
    $user = DB::table('users')->where('name', 'John')->first(); 
    echo $user->name;

    /* 하나의 컬럼에서 하나의 행 조회 */
    // value()
    $email = DB::table('users')->where('name', 'John')->value('email');

    /* id 컬럼에서 하나의 행 조회 */
    // find()
    $user = DB::table('users')->find(3);
    ```

- 한 컬럼 값 목록 조회 
    ```php
    /* title컬럼 값의 컬렉션 조회 */
    // pluck()
    $titles = DB::table('roles')->pluck('title');
    foreach ($titles as $title) {
        echo $title;
    }

    /* title컬럼 값의 컬렉션 조회 (컬럼 키를 name으로 지정)*/
    $roles = DB::table('roles')->pluck('title', 'name');
    foreach ($roles as $name => $title) {
        echo $title;
    }
    ```


### 2.2.1. 결과 분할하기
- chunk() 메소드를 통해 많은 결과행을 분할 하여 처리
    ```php
    DB::table('users')->orderBy('id')->chunk(100, function ($users) {
        foreach ($users as $user) 
        }
        // 클로저에서 false 반환시 더이상chunk처리 하지않도록 중단
    });

    // 청크처리 중 db업데이트시 청크결과 변경됨. 
    // chunkById() 메소드를 사용해 기본키를 기반으로 청킹할 수 있도록 해야 함.
    DB::table('users')->where('active', false)
        ->chunkById(100, function ($users) {
            foreach ($users as $user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['active' => true]);
            }
        });
    ```

### 2.2.2. Aggregates
- count(), max(), min(), avg(), sum() 등
- exists(), doesntExist()

## 2.3. Selects
```php
// 특정컬럼 조회
$users = DB::table('users')->select('name', 'email as user_email')->get();

// 중복값 제외
$users = DB::table('users')->distinct()->get();

// 쿼리빌더 인스턴스에서 조회할 컬럼 추가
$query = DB::table('users')->select('name');
$users = $query->addSelect('age')->get();
```

## 2.4. Raw 표현식
- sql injection에 주의
- DB::raw() 메소드로 raw 표현식 사용
    ```php
    // 컬럼지정
    $users = DB::table('users')
                        ->select(DB::raw('count(*) as user_count, status'))
                        ->where('status', '<>', 1)
                        ->groupBy('status')
                        ->get();
    ```
- 기타 표현식
    ```php
    // 컬럼 값 지정
    ->selectRaw('price * ? as price_with_tax', [1.0825])

    // where절 값 지정
    ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
    ->orWhereRaw()

    // having절 값 지정
    ->havingRaw('SUM(price) > ?', [2500])
    ->orHavingRaw()

    // orderBy절 값 지정
    ->orderByRaw('updated_at - created_at DESC')
    ```
    

## 2.5. Joins
- inner join
    ```php
    // join(테이블명, 제약조건컬럼인자들...)
    // 다중테이블 조인
    $users = DB::table('users')
            ->join('contacts', 'users.id', '=', 'contacts.user_id')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->select('users.*', 'contacts.phone', 'orders.price')
            ->get();
    ```
- left/right join
    ```php
    $users = DB::table('users')
                ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
                ->get();

    $users = DB::table('users')
                ->rightJoin('posts', 'users.id', '=', 'posts.user_id')
                ->get();
    ```
- Cross join
    ```php
    ->crossJoin('colours') // cartesian product 생성
    ```
- 복잡한 join
    ```php
    // join() 두번째인자로 Closure전달
    DB::table('users')
        ->join('contacts', function ($join) { 
            // Closure는 joinClause객체를 전달받아 on(), orOn(), where(), orWhere() 등으로 제약사항 지정
            $join->on('users.id', '=', 'contacts.user_id')
            ->orOn(...)
            ->where('contacts.user_id', '>', 5);
        })
        ->get();
    ```
- 서브쿼리 join
    - joinSub(서브쿼리, 테이블 alias, 연관컬럼), leftJoinSub(), rightJoinSub()메소드 사용
        ```php
        $latestPosts = DB::table('posts')
                    ->select('user_id', DB::raw('MAX(created_at) as last_post_created_at'))
                    ->where('is_published', true)
                    ->groupBy('user_id');

        $users = DB::table('users')
                ->joinSub($latestPosts, 'latest_posts', function ($join) { 
                    $join->on('users.id', '=', 'latest_posts.user_id');
                })->get();
        ```

## 2.6. Unions
- union(), unionAll() 메소드 사용
    ```php
    $first = DB::table('users')
                ->whereNull('first_name');

    $users = DB::table('users')
                ->whereNull('last_name')
                ->union($first)
                ->get();
    ```
## 2.7. Where 구문
- 간단한 where(컬럼명, 연산자, 비교값) 
    ```php
    $users = DB::table('users')->where('votes', '=', 100)->get();
    // 조건 배열 전달 (and로 연결됨)
    $users = DB::table('users')->where([
        ['status', '=', '1'],
        ['subscribed', '<>', '1'],
    ])->get();
    ```
- orWhere()
    ```php
    $users = DB::table('users')
                    ->where('votes', '>', 100)
                    ->orWhere('name', 'John') // 
                    ->get();
    ```
- 기타 where구문
    ```php
    // whereBetween / orWhereBetween
    // whereNotBetween / orWhereNotBetween
    ->whereBetween('votes', [1, 100])
    
    // whereIn / whereNotIn / orWhereIn / orWhereNotIn
    ->whereIn('id', [1, 2, 3])


    // whereNull / whereNotNull / orWhereNull / orWhereNotNull
    ->whereNotNull('updated_at')

    // whereDate / whereMonth / whereDay / whereYear / whereTime
    ->whereDate('created_at', '2016-12-31')
    ->whereTime('created_at', '=', '11:20:45')


    // whereColumn / orWhereColumn
    ->whereColumn('first_name', 'last_name')
    ->whereColumn('updated_at', '>', 'created_at')
    ->whereColumn([
                    ['first_name', '=', 'last_name'],
                    ['updated_at', '>', 'created_at'],
                ])
    ```

### 2.7.1. 파라미터 그룹
```php
$users = DB::table('users')
           ->where('name', '=', 'John')
           ->where(function ($query) {
               $query->where('votes', '>', 100)
                     ->orWhere('title', '=', 'Admin');
           })
           ->get();
```
```sql
select * 
from users 
where name = 'John' 
    and (votes > 100 or title = 'Admin');
```


### 2.7.2. 존재여부를 판단하는(Exists) Where 절
```php
$users = DB::table('users')
           ->whereExists(function ($query) {
               $query->select(DB::raw(1))
                     ->from('orders')
                     ->whereRaw('orders.user_id = users.id');
           })
           ->get();
```
```sql
select * 
from users
where exists (
    select 1 from orders where orders.user_id = users.id
)
```


### 2.7.3. JSON Where 절
- JSON타입 컬럼에 대한 쿼리 지원
- -> 연산자 사용
    ```php
    // where()에서 -> 연산자 사용
    $users = DB::table('users')
                ->where('preferences->dining->meal', 'salad')
                ->get();

    // JSON배열을 쿼리하는 경우
    ->whereJsonContains('options->languages', 'en')
    // JSON배열에 대한 다중값 쿼리 (MySQL과 PostgreSQL 지원)
    ->whereJsonContains('options->languages', ['en', 'de'])
    

    // JSON배열 길이에 따른 쿼리
    ->whereJsonLength('options->languages', 0)
    ->whereJsonLength('options->languages', '>', 1)
    ```


## 2.8. Ordering, Grouping, Limit & Offset
- orderBy 정렬
    ```php
    ->orderBy('name', 'desc')
    ```
- latest / oldest 정렬
    ```php
    $user = DB::table('users')
                ->latest() // 인자 없으면 기본으로 created_at컬럼기준으로 정렬
                ->first();
    ```
- inRandomOrder() 랜덤 정렬
- groupBy / having
    ```php    
    ->groupBy('account_id')
    ->having('account_id', '>', 100)

    // 멀티컬럼 groupby
    ->groupBy('first_name', 'status')
    ```
- skip / take
    - take() : 결과갯수제한, skip() : 결과 건너뛰기
        ```php
        $users = DB::table('users')->skip(10)->take(5)->get();
        ```
    - limit / offset으로 대체 가능
        ```php
        $users = DB::table('users')
                    ->offset(10)
                    ->limit(5)
                    ->get();
        ```


## 2.9. Conditional where
- when(boolean, true일떄 Closure, false일때 Closure) 메소드
    ```php
    $users = DB::table('users')
                ->when($sortBy, function ($query, $sortBy) {
                    return $query->orderBy($sortBy);
                }, function ($query) {
                    return $query->orderBy('name');
                })
                ->get();
    ```

## 2.10. Inserts
- insert([컬럼명=>컬럼값...], [...], ...) / insertOrIgnore()
    ```php
    DB::table('users')->insert( 
        ['email' => 'john@example.com', 'votes' => 0],
        ['email' => 'dayle@example.com', 'votes' => 0]
    );
    DB::table('users')->insertOrIgnore( // 중복레코드 오류 무시
    ```
- Auto-Incrementing IDs
    - 테이블에 Auto-Incrementing ID컬럼이 있는 경우
    - insertGetId() : 레코드 추가 후 추가된 ID반환 가능
        ```php
        $id = DB::table('users')->insertGetId(
            ['email' => 'john@example.com', 'votes' => 0]
        ); 
        ```


## 2.11. Updates
- update()
- updateOrInsert([배열1], [배열2]) : 배열1로 조회가능하면 배열 2로 업데이트. 배열1 미존재시 배열1,2속성으로 insert
    ```php
    DB::table('users')
        ->updateOrInsert( 
            ['email' => 'john@example.com', 'name' => 'John'],
            ['votes' => '2']
        );
    ```
### 2.11.1. JSON 컬럼 업데이트
```php
->update(['options->enabled' => true]); // ->로 요소에 접근
```
### 2.11.2. Increment & Decrement
- 컬럼 값 증감시켜 update
    ```php
    DB::table('users')->increment('votes');
    DB::table('users')->increment('votes', 5);
    DB::table('users')->decrement('votes');
    DB::table('users')->decrement('votes', 5);

    // 업데이트할 컬럼 추가 가능
    DB::table('users')->increment('votes', 1, ['name' => 'John']);
    ```
## 2.12. Deletes
- delete(), where()->delete(), truncate()

### 2.12.1. Pessimistic Locking 배타적 로킹
```php
// sharedLock() : 선택된 row가 커밋되기 전까지 수정 방지
DB::table('users')->where('votes', '>', 100)->sharedLock()->get();

// lockForUpdate() : 선택된 row가 커밋되기 전까지 수정방지
//    + 다른 공유 lock에 의해 선택되는 것 방지
DB::table('users')->where('votes', '>', 100)->lockForUpdate()->get();
```
## 2.13. 디버깅
- dd() : 디버그 정보 출력 및 요청실행 중단
- dump() : 디버그 정보 출력 및 요청실행 지속






# 3. 페이지네이션
## 3.1. 시작하기
- 라라벨 페이지네이터 
    - 쿼리빌더와 Eloquent ORM에 통합되어 있음
    - HTML을 생성해주며, 이는 부트스트랩 CSS프레임워크와 호환됨


## 3.2. 기본적인 사용법
### 3.2.1. 쿼리 빌더 결과를 페이징 하기
- pagenate(한 페이지 당 항목 수) 메소드
    - 쿼리빌더, Eloquent쿼리에서 사용
    - request의 page쿼리스트링값
        - 자동으로 확인하여 limit offset값을 결정
        - paginator에 의해 링크에 해당 값이 다시 추가됨
    ```php
    // controller
    public function index()
    {
        $users = DB::table('users')->paginate(15);
        return view('user.index', ['users' => $users]);
    }
    ```
    - groupBy사용시 수동으로 paginator 생성하여 쿼리하는 것을 권장
- simplePaginate() : 다음/이전 링크만 보여주는 경우

### 3.2.2. Eloquent 결과를 페이징 하기
```php
$users = App\User::paginate(15);

// Eloquent모델에 쿼리 지정 후 페이징
$users = User::where('votes', '>', 100)->paginate(15);
$users = User::where('votes', '>', 100)->simplePaginate(15);
```
### 3.2.3. 수동으로 Paginator 생성하기
- 항목 배열을 잘라서 전달하면서 Paginator인스턴스생성
- paginator인스턴스
    - Illuminate\Pagination\Paginator
        - 결과 셋에 설정되어있는 전체 항목의 개수 필요 없음
        - simplePaginate()에 대응
    - Illuminate\Pagination\LengthAwarePaginator
        - 결과 셋에 설정되어있는 전체 항목의 개수 필요
        - paginate()에 대응 

## 3.3. 페이지네이션 결과 출력하기
- paginate() 호출시 Illuminate\Pagination\LengthAwarePaginator 인스턴스반환
- simplePaginate() 호출시 Illuminate\Pagination\Paginator  인스턴스반환
- 헬퍼메소드 사용가능
- iterators로 동작
- 반복문에서 배열처럼 사용가능
    ```php
    <div class="container">
        @foreach ($users as $user)
            {{ $user->name }}
        @endforeach
    </div>
    {{ $users->links() }} // 결과셋에서 페이지 링크를 렌더링
    ```
- 커스텀 Paginator URI
    ```php
    Route::get('users', function () {
        $users = App\User::paginate(15);
        $users->withPath('custom/url'); 
        // paginator가 withPath()메소드로 url생성
        // http://example.com/custom/url?page=N 생성
    });
    ```
- 페이지 링크에 추가
    ```php
    /* 쿼리스트링 추가 */
    {{ $users->appends(['sort' => 'votes'])->links() }}
    // // appends() 메소드 호출

    /* hash fragment 추가 */
    {{ $users->fragment('foo')->links() }}

    /* 링크 창 조정 */
    {{ $users->onEachSide(5)->links() }}
    ```

### 3.3.1. 페이지네이션 결과를 JSON으로 변환하기
- paginator 결과 클래스는 Illuminate\Contracts\Support\Jsonable인터페이스 contract를 구현하며, toJson()을 제공
- 라우트에서 paginator인스턴스를 JSON으로 변환하여 반환
    ```php
    Route::get('users', function () {
        return App\User::paginate();
    });
    ```
- JSON으로 변환된 paginator인스턴스
    ```php
    {
    "total": 50,
    "per_page": 15,
    "current_page": 1,
    "last_page": 4,
    "first_page_url": "http://laravel.app?page=1",
    "last_page_url": "http://laravel.app?page=4",
    "next_page_url": "http://laravel.app?page=2",
    "prev_page_url": null,
    "path": "http://laravel.app",
    "from": 1,
    "to": 15,
    "data":[
            {
                // Result Object
            },
            {
                // Result Object
            }
    ]
    }
    ```
## 3.4. 페이지네이션 뷰 파일 수정하기
- 방법1. links() 호출시 뷰이름과 데이터 전달
    ```php
    {{ $paginator->links('view.name') }}

    // Passing data to the view...
    {{ $paginator->links('view.name', ['foo' => 'bar']) }}
    ```
- 방법2. 뷰파일 퍼블리싱
    1.  vendor:publish 명령어실행
        ```bash
        $ php artisan vendor:publish --tag=laravel-pagination
        ```
        - 뷰파일들을 resources/views/vendor/pagination로 옮김
    2. 기본 페이지네이션 뷰 : bootstrap-4.blade.php
    (페이지네이션 HTML 수정가능)
        - 기본 페이지네이션 뷰 변경시
            - AppServiceProvider에서 관련 메소드 호출
                ```php
                use Illuminate\Pagination\Paginator;

                public function boot()
                {
                    Paginator::defaultView('view-name');

                    Paginator::defaultSimpleView('view-name');
                }
                ```

## 3.5. 페이지네이터 인스턴스 메소드
- docs 참조







# 4. 마이그레이션
## 4.1. 시작하기
- DB의 수정, 스키마공유, 버전컨트롤을 가능하게 해줌
- 스키마 빌더를 함께 사용하여, DB 스키마를 쉽게 생성

## 4.2. 마이그레이션 파일 생성하기
-  make:migration 명령어로 마이그레이션 파일 생성
    ```bash
    $ php artisan make:migration create_users_table

    # --create 옵션 : 테이블명, 마이그레이션이 테이블을 생성할지를 명시(생성된 마이그레이션 stub 파일을 특정한 테이블로 미리 채움)
    $ php artisan make:migration create_users_table --create=users 
    $ php artisan make:migration add_votes_to_users_table --table=users

    # --path 옵션 : 마이그레이션 생성 경로 지정
    ```
    - database/migrations에 생성됨
    - 마이그레이션 파일은 타임스탬프를 포함(마이그레이션 순서 판별 가능)
    
## 4.3. 마이그레이션 클래스의 구조
- up(), down() 메소드로 구성
    ```php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateFlightsTable extends Migration
    {
        public function up() // 테이블, 컬럼, 인덱스를 추가
        {
            // Schema 빌더를 사용
            Schema::create('flights', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('airline');
                $table->timestamps();
            });
        }
        public function down() // up()동작 취소
        {
            Schema::drop('flights');
        }
    }
    ```

## 4.4. 마이그레이션 실행하기
```bash
$ php artisan migrate
$ php artisan migrate --force # 프로덕션 서버에서 확인 메시지 없이 강제 마이그레이션 실행
```
### 4.4.1. 마이그레이션 되돌리기-롤백
```bash
$ php artisan migrate:rollback

# 최근 5개 마이그레이션만 롤백
$ php artisan migrate:rollback --step=5 

# 모든 마이그레이션 롤백
$ php artisan migrate:reset 

# 하나의 명령어로 롤백과 마이그레이트 함께 실행 하기
$ php artisan migrate:refresh
# Refresh the database and run all database seeds...
$ php artisan migrate:refresh --seed

# 모든 테이블과 마이그레이션 Drop
$ php artisan migrate:fresh
$ php artisan migrate:fresh --seed
```


## 4.5. 테이블
### 4.5.1. 테이블 생성하기
- Schema::create(테이블명, Blueprint객체를 받는 클로저)
    ```php
    Schema::create('users', function (Blueprint $table) {
        $table->bigIncrements('id');  // 컬럼 타입지정
    });

    /* 테이블/컬럼 존재여부 확인 */
    // if (Schema::hasTable('users')) {
    // if (Schema::hasColumn('users', 'email')) {

    /* DB기본커넥션 아닌 다른 커넥션지정하여 테이블 생성 */
    Schema::connection('foo')->create('users', function (Blueprint $table) {
        $table->bigIncrements('id');
    });
    ```

### 4.5.2. 테이블의 이름변경 / 제거
```php
Schema::rename($from, $to);
Schema::drop('users');
Schema::dropIfExists('users');
```
## 4.6. 컬럼
### 4.6.1. 컬럼 생성하기
- Schema::table(테이블명, Blueprint객체를 받는 클로저)에서 생성
    ```php
    Schema::table('users', function (Blueprint $table) {
        // 컬럼 추가
        $table->json('movies')->default(new Expression('(JSON_ARRAY())'));
        $table->timestamps();
    });
    ```
### 4.6.2. 컬럼 modifier
```php
    Schema::table('users', function (Blueprint $table) {
        // 컬럼 modifier로 컬럼 수정
        // nullable() modifier
        $table->string('email')->nullable(); 
    });
```

### 4.6.3~4. 컬럼 수정, 삭제하기
- 사전 준비
    - Doctrine DBAL 라이브러리 : 컬럼의 현재 상태를 확인, 필요한 쿼리를 생성, 컬럼 변경을 수행
        - composer.json에 doctrine/dbal의존성 추가
            ```bash
            $ composer require doctrine/dbal
            ```

- 컬럼 속성변경
    ```php
    Schema::table('users', function (Blueprint $table) {
        /* 컬럼 속성변경 */
        // 변경불가한 속성도 있으므로 주의
        $table->string('name', 50)->nullable()->change();

        /* 컬럼명 변경 */
        $table->renameColumn('from', 'to');

        /* 컬럼 삭제 */
        $table->dropColumn('votes');
        $table->dropColumn(['votes', 'avatar', 'location']);
    });
    ```


## 4.7. 인덱스
### 4.7.1. 인덱스 생성하기
```php
$table->string('email')->unique(); // 컬럼정의시 인덱스지정
$table->unique('email'); // 컬럼정의 후 인덱스지정
$table->index(['account_id', 'created_at']); // 복합키인덱스
$table->unique('email', 'unique_email'); // 인덱스명 지정

// primary(), unique(), index(), spatialIndex()
```
### 4.7.2. 인덱스 이름 변경하기
```php
$table->renameIndex('from', 'to')
```
### 4.7.3. 인덱스 삭제
```php
Schema::table('geo', function (Blueprint $table) {
    $table->dropIndex(['state']); // Drops index 'geo_state_index'

    // dropPrimary(), dropUnique(), dropIndex(), dropSpatialIndex()
});
```

### 4.7.4. 외래키 제약조건
```php
/* 외래키 제약조건 지정 */
$table->foreign('user_id')
      ->references('id')->on('users')
      ->onDelete('cascade')->onUpdate(...);

/* 외래키 제약조건 삭제 */
$table->dropForeign('posts_user_id_foreign'); // 제약조건명으로 삭제
$table->dropForeign(['user_id']); // 컬럼명으로 삭제(배열로 전달)
```
- 마이그레이션에서 외래키제약조건 활/비활성화
    ```php
    Schema::enableForeignKeyConstraints();
    Schema::disableForeignKeyConstraints();
    ```





# 5. SEEDING
## 5.1. 시작하기
- seeding : seed 클래스를 사용해 테스트데이터를 DB에 설정

## 5.2. Seeders 작성하기
- make:seeder 명령어 실행하여 seeder클래스 생성
    ```bash
    $ php artisan make:seeder UsersTableSeeder
    ```
    - database/seeds에 위치하게 됨
    - 클래스이름은 DatabaseSeeder가 기본값
- seeder 클래스
    - 기본적으로 run() 메소드만 가짐 
        - 메소드 내 쿼리빌더로 데이터 입력 or Eloquent모델 팩토리 사용
        - db:seed 아티즌 명령어 실행시 호출
        - call() 메소드 호출을 통해 다른 seed클래스를 실행해 seeding순서 조정가능
    ```php
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Str;

    class DatabaseSeeder extends Seeder
    {
        public function run()
        {
            DB::table('users')->insert([
                'name' => Str::random(10),
                'email' => Str::random(10).'@gmail.com',
                'password' => bcrypt('password'),
            ]);
        }
    }
    ```
### 5.2.1. 모델 팩토리 사용하기
- 각 모델 seed 속성을 수동으로 지정하는 대신, 모델팩토리를 사용해 대량의 DB레코드 생성가능
- 모델 팩토리 정의
    - 데이터베이스 테스팅 - 팩토리 작성하기 참조
- factory() 헬퍼함수 사용하여 DB에 레코드 추가
    ```php
    public function run()
    {   
        // factory() 
        factory(App\User::class, 50)->create()->each(function ($user) {
            $user->posts()->save(factory(App\Post::class)->make());
        });
    }
    ```
### 5.2.2. 추가적인 Seeder 호출하기
```php
// DatabaseSeeder 클래스에서 call()메소드로 seeder클래스를 실행
public function run()
{
    $this->call([
        UsersTableSeeder::class,
        PostsTableSeeder::class,
        CommentsTableSeeder::class,
    ]);
}
```
## 5.3. Seeders 실행하기
```bash
# seeder클래스 작성 후, 컴포저의 오토로더 재생성 필요
$ composer dump-autoload

# seeder 클래스 실행
# (DatabaseSeeder클래스 실행되어 다른 seeder클래스 호출)
$ php artisan db:seed

# 특정 seeder클래스 실행
$ php artisan db:seed --class=UsersTableSeeder 

# 모든 테이블 삭제하고 마이그레이션 재실행
$ php artisan migrate:fresh --seed

# 프로덕션 환경 - 확인메시지없이 강제로 시딩하는 경우
$ php artisan db:seed --force
```


# 6. REDIS
## 6.1. 시작하기
- Redis 
    - key - value기반 오픈소스 저장소 
    - key는 strings, hashes, lists, sets, sorted sets를 포함
    - 자료구조 서버로 자주 사용
    - 사전 준비
        - PECL를 통하여 PhpRedis PHP extension 설치
        - predis 패키지는 원작자가 더이상 지원하지 않음

### 6.1.1. 설정하기
- config/database.php의 redis배열
    ```php
    'redis' => [
        // redis클라이언트(phpredis / predis) 설정
        'client' => env('REDIS_CLIENT', 'phpredis'), 

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],

        /*  Redis 서버 클러스터 사용시 */
        // 네이티브 Redis클러스터링 사용시에 options설정
        'options' => [ 
            'cluster' => env('REDIS_CLUSTER', 'redis'),
        ],
        'clusters' => [
            'default' => [
                [
                    'host' => env('REDIS_HOST', 'localhost'),
                    'password' => env('REDIS_PASSWORD', null),
                    'port' => env('REDIS_PORT', 6379),
                    'database' => 0,
                ],
            ],
        ],
    ],
    ```
- 클러스터
    - 원래의미
        - 하나의DB를 복수의 서버에 구축하는 것
        - Fail Over 시스템을 구축하기 위해 사용
        - 동기방식으로 각 서버의 노드 간 데이터 동기화
    - 라라벨에서는 
        - 클라이언트사이드 샤딩(분산 저장) 수행
        - Fail Over 처리는 하지 않음. 
        - 주 데이터 저장소로부터 데이터를 캐싱하는데 적합
        - 노드 풀링(리소스 공유), ram 고가용성

### 6.1.2. Predis

### 6.1.3. PhpRedis
- config/database.php에서 클라이언트로 사용 설정
    ```php
    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        // Rest of Redis configuration...
    ],
    ```
- phpRedis사용시, Redis 파사드 클래스와 충돌하지 않도록 파사드클래스에 alias 재지정 필요
    - app.php의 aliases섹션
        ```php
        'RedisManager' => Illuminate\Support\Facades\Redis::class,
        // alias => 파사드 클래스
        ```

## 6.2. Redis 와 상호작용
- Redis파사드 메소드 호출시 Redis 명령어가 Redis에 전달됨
    ```php
    class UserController extends Controller
    {
        public function showProfile($id)
        {
            // redis명령어와 동명의 메소드 호출
            $user = Redis::get('user:profile:'.$id);

            Redis::set('name', 'Taylor');
            $values = Redis::lrange('names', 5, 10);

            // command(명령어명, 전달하는 값 배열) 메소드 사용
            $values = Redis::command('lrange', ['name', 5, 10]);


            return view('user.profile', ['user' => $user]);
        }
    }
    ```
- 여러개의 redis connection 사용
    ```php
    // redis인스턴스 가져오기
    $redis = Redis::connection();
    $redis = Redis::connection('my-connection'); // (서버or클러스터명)
    ```

### 6.2.1.파이프라이닝 명령어
- 파이프라이닝 : 다수의 명령어를 한 번에 서버로 전달시 사용
- pipline() 메소드 : 클로저(redis인스턴스를 전달받음)를 인자로 받아 redis인스턴스에 한 번에 명령을 내려 실행시킴
    ```php
    Redis::pipeline(function ($pipe) {
        for ($i = 0; $i < 1000; $i++) {
            $pipe->set("key:$i", $i);
        }
    });
    ```


## 6.3. Pub / Sub (publish / subscribe)
- 라라벨은 redis의 publish / subscribe 명령에 대한 인터페이스 제공
- 주어진 채널에서 메시지를 수신할 수 있게 해줌
1. subscribe()로 채널에 리스너 설정
    - 아티즌 명령어로 호출하기 위해(subscribe()는 장시간 실행되는 프로세스로 동작하기 때문) 명령어 클래스 작성
        ```php
        namespace App\Console\Commands;

        use Illuminate\Console\Command;
        use Illuminate\Support\Facades\Redis;

        class RedisSubscribe extends Command
        {
            protected $signature = 'redis:subscribe';

            protected $description = 'Subscribe to a Redis channel';
            
            public function handle()
            {
                // 명령어 실행시
                // redis에 subscribe 명령이 전달되게 함
                Redis::subscribe(['test-channel'], function ($message) {
                    echo $message;
                });
            }
        }
        ```
2. publish() 메소드로 채널에 메세지 표시
    ```php
    Route::get('publish', function () {
        // Route logic...
        Redis::publish('test-channel', json_encode(['foo' => 'bar']));
    });
    ```
d