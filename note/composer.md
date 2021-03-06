# composer
php의 의존성 관리도구. 


### Packagist.org
- 컴포저의 메인저장소

### 의존성 정의
- composer.json 파일에 정의
```json
{
    "require": {
        "dflydev/markdown": "1.0.3"
    }
} 
```
### composer 디렉토리 초기화
- 프로젝트 내에서 컴포저 디렉토리로 관리 하기를 원하는 경로에서 다음의 명령어 입력
    ```bash
    $ composer init 
    ```
- composer.json 파일이 생성됨
    ```json
    ...
    "require" : {
        // 의존성 내용...
        // Packagist에서 패키지를 찾을 수 있음 
        // (의존성 추가 명령어를 실행하여 파일내용이 변경되는 것을 확인할 수도 있음)
    }
    ...
    ```

### composer install
- composer.json 내용을 읽어서 정의된 라이브러리를 설치함

### composer.lock
- 컴포저 인스톨시 스냅샷으로 생성
- 현재 설치된 라이브러리를 사용하기 위한 선행 라이브러리 항목 및 버전 기술
- 컴포저 인스톨 실행시 이 파일에 기술된 라이브러리와 다른 버전의 라이브러리를 설치하게 됨

### composer update
- 라이브러리를 최신버전으로 갱신

### 라이브러리 사용
- 다운로드된 패키지는 vendor디렉토리에 저장됨
- 사용코드
```php 
require 'vendor/autoload.php'; // 다운로드된 패키지 가져오기
use dflydev\markdown\MarkdownParser;
$markdownParser = new MarkdownParser();
echo $markdownParser->transformMarkdown("#Hello World");
```