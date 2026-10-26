# 🐾 Petstagram

반려동물 전용 인스타그램 클론. 강아지, 고양이 등 반려동물을 키우거나 좋아하는 사람들이 사진을 공유하고 구경하는 SNS입니다. 순수 HTML/CSS/JS + PHP + MySQL로 제작. 모바일 화면(최대 480px) 기준으로 디자인되어 있습니다.

## 주요 기능
- 회원가입 / 로그인 / 로그아웃 (세션 기반, 비밀번호 해시 저장)
- 게시물 업로드 (이미지 + 문구), 삭제
- **전체 피드**(`index.php`): 모든 사용자의 게시물을 최신순으로 확인
- **개인 피드/프로필**(`profile.php`): 내 게시물 그리드, 다른 사용자 프로필(`profile.php?id=번호`)도 조회 가능
- **팔로우 / 팔로잉**: 다른 사용자 팔로우, 팔로워·팔로잉 목록 확인(`follows.php`), 프로필에서 카운트 클릭 시 목록 이동
- 좋아요 (버튼 클릭 + 이미지 더블탭, AJAX)
- 댓글 작성/조회 (AJAX)
- 프로필 편집 (별명, 관심동물, 소개글, 프로필 사진)
- 유저/별명 검색
- 활동(알림) 탭 - 내 게시물에 달린 좋아요/댓글 모음

## v2 업데이트 (기존 서버 반영 방법)
이미 v1으로 설치해서 쓰고 계셨다면, DB에 아래 마이그레이션만 추가로 적용하면 됩니다.
```bash
mysql -u [계정] -p dogstagram < sql/migration_v2.sql
```
이 스크립트는 다음을 수행합니다:
- `users.dog_name` 컬럼을 `nickname`으로 이름 변경
- `users.interests` 컬럼 추가 (선택한 관심동물을 쉼표로 구분해 저장)
- `follows` 테이블 추가 (팔로워/팔로잉 관계)

이후 전체 PHP 파일을 다시 업로드하시면 됩니다.

## 설치 방법

### 1. 요구 사항
- PHP 7.4 이상 (PDO, fileinfo, gd 확장 활성화)
- MySQL 5.7 이상 또는 MariaDB
- Apache/Nginx (또는 PHP 내장 서버)

### 2. 데이터베이스 생성
```bash
mysql -u root -p < sql/schema.sql
```

### 3. DB 접속 정보 설정
`config/database.php` 파일을 열어 본인 환경에 맞게 수정하세요.
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dogstagram');
define('DB_USER', 'root');
define('DB_PASS', '비밀번호입력');
```

### 4. 업로드 폴더 권한
```bash
chmod -R 755 uploads
```

### 5. 서버 실행
PHP 내장 서버로 간단히 테스트하려면 프로젝트 루트에서:
```bash
php -S localhost:8000
```
브라우저(모바일 화면 크기로 확인 권장)에서 `http://localhost:8000/register.php` 접속 후 회원가입하여 시작하세요.

실제 서버(Apache)에 배포할 경우 문서 루트를 이 폴더로 지정하면 됩니다.

## 폴더 구조
```
dogstagram/
├── config/database.php      # DB 연결 설정
├── includes/                # 공통 함수, 상단바, 하단탭바
├── sql/schema.sql           # DB 스키마
├── api/like.php             # 좋아요 AJAX API
├── api/comment.php          # 댓글 AJAX API
├── css/style.css            # 전체 스타일 (모바일 우선)
├── js/app.js                # 프론트엔드 스크립트
├── uploads/posts            # 업로드된 게시물 이미지
├── uploads/avatars          # 업로드된 프로필 이미지
├── index.php                # 전체 공개 피드 (홈)
├── profile.php              # 개인 피드 / 프로필
├── upload.php                # 게시물 업로드
├── post.php                  # 게시물 상세
├── search.php                # 검색
├── activity.php              # 알림
├── edit_profile.php          # 프로필 편집
├── login.php / register.php / logout.php
```

## 참고 / 다음 확장 아이디어
- 팔로우 기능을 추가하면 "팔로잉 피드"와 "전체 피드"를 분리할 수 있습니다.
- 이미지 리사이징(GD/Imagick)을 추가하면 업로드 용량을 최적화할 수 있습니다.
- 현재는 데모 목적상 CSRF 토큰이 생략되어 있습니다. 실서비스 배포 전 폼에 CSRF 토큰 검증을 추가하는 것을 권장합니다.
