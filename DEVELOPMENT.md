# Petstagram 개발 기록

이 문서는 Petstagram 프로젝트의 구조와 변경 이력을 정리합니다.
**앞으로 기능 추가/수정/버그 픽스가 있을 때마다 이 문서의 "변경 이력"에 새 항목을 추가합니다.**

> 이 프로젝트는 Git으로 관리되고 있지 않아서, 이 문서를 작성한 시점(2026-09-13) 이전의 정확한 날짜별 이력은 남아있지 않습니다.
> 그래서 그 이전 기능들은 "베이스라인(최초 구현)"으로 한 번에 정리하고, 이후부터는 변경할 때마다 이력을 쌓아갑니다.

## 개요
- 반려동물 전용 인스타그램 클론 (강아지, 고양이 등 반려동물을 키우거나 좋아하는 사람들이 사진을 공유하고 구경하는 SNS)
- Vanilla HTML/CSS/JS + PHP(PDO) + MySQL
- 모바일 화면(최대 480px) 기준 디자인
- 운영 서버: http://ryu.io.kr/dogstagram/

## 기술 스택
- 서버: PHP (세션 기반 인증, PDO + MySQL)
- DB: MySQL/MariaDB (`dogstagram` 데이터베이스, 프로젝트명 변경 이전 이름을 그대로 사용 중)
- 프론트: 순수 HTML/CSS/JS (`js/app.js`, AJAX 통신)

## 폴더 구조
```
dogstagram/
├── config/database.php      # DB 연결 설정
├── includes/                # 공통 함수(functions.php), 상단바(topbar.php), 하단탭바(bottomnav.php)
├── sql/schema.sql           # DB 스키마
├── api/like.php             # 좋아요 AJAX API
├── api/comment.php          # 댓글 AJAX API
├── api/follow.php           # 팔로우/언팔로우 AJAX API
├── css/style.css            # 전체 스타일 (모바일 우선)
├── js/app.js                # 프론트엔드 스크립트
├── uploads/posts            # 업로드된 게시물 이미지
├── uploads/avatars          # 업로드된 프로필 이미지
├── index.php                # 전체 공개 피드 (홈)
├── profile.php              # 개인 피드 / 프로필 (내 프로필, 타인 프로필 profile.php?id=)
├── edit_profile.php         # 프로필 편집
├── upload.php                # 게시물 업로드
├── post.php                  # 게시물 상세
├── search.php                # 유저/별명 검색
├── activity.php              # 활동(알림) 탭
├── follows.php               # 팔로워/팔로잉 목록
├── login.php / register.php / logout.php
```

## DB 스키마 (`sql/schema.sql`)
- `users` — id, username, email, password_hash, nickname, interests, bio, profile_image, created_at
- `posts` — id, user_id, image_path, caption, created_at (users FK, ON DELETE CASCADE)
- `likes` — post_id + user_id 유니크, 게시물당 1인 1좋아요
- `comments` — post_id, user_id, comment
- `follows` — follower_id(팔로우 하는 사람), following_id(팔로우 받는 사람) 유니크 쌍

## 베이스라인 기능 (최초 구현)
- 회원가입 / 로그인 / 로그아웃 (세션 기반, 비밀번호 해시 저장)
- 게시물 업로드(이미지 + 문구) / 삭제
- 전체 피드(`index.php`): 모든 사용자의 게시물을 최신순으로 표시
- 개인 피드/프로필(`profile.php`): 내 게시물 그리드, `profile.php?id=번호`로 타인 프로필 조회
- 팔로우/팔로잉: 팔로우 기능, 팔로워·팔로잉 목록(`follows.php`), 프로필 카운트 클릭 시 목록 이동
- 좋아요: 버튼 클릭 + 이미지 더블탭(AJAX, `api/like.php`)
- 댓글 작성/조회 (AJAX, `api/comment.php`)
- 프로필 편집: 별명, 관심동물, 소개글, 프로필 사진
- 유저/별명 검색 (`search.php`)
- 활동(알림) 탭: 내 게시물에 달린 좋아요/댓글 모음 (`activity.php`)

## 알려진 제약 / 향후 아이디어 (README 기준)
- 현재 CSRF 토큰 검증 생략 (데모 목적) — 실서비스 전 추가 권장
- 이미지 리사이징(GD/Imagick) 미적용

---

## 변경 이력

### 2026-09-13 — 프로젝트명 Dogstagram → Petstagram 변경, 관심동물 다중선택 개선
- **배경**: 강아지 전용이 아니라 반려동물(강아지, 고양이 등)을 키우거나 좋아하는 사람들이 함께 사진을 공유하는 SNS로 방향을 잡으면서 프로젝트명을 Petstagram으로 변경.
- **변경**: 화면에 노출되는 타이틀/로고/문구(`Dogstagram` → `Petstagram`, "반려견" → "반려동물" 등)를 전체적으로 수정. 실제 DB 이름(`dogstagram`)과 운영 서버 경로(`/dogstagram/`)는 하위 호환을 위해 그대로 유지.
- **관심동물 옵션**: 회원가입/프로필 편집의 관심동물 선택지를 강아지·고양이·새·물고기·곤충·기타로 정리하고 최대 3개까지 선택 가능하도록 변경. "기타" 선택 시 직접 입력한 항목도 최대 개수 내에서 저장 가능(`includes/functions.php`의 `interestOptions()`, `buildInterestsString()`, `js/interest-picker.js`).
- **검증**: 코드 리뷰로 확인(로컬에 PHP CLI가 없어 실행 검증은 미완료 — 배포 후 회원가입/프로필 편집 화면에서 육안 확인 필요).

### 2026-09-13 — 데이터 초기화 후 세션이 남아 프로필 진입이 막히던 문제 수정
- **증상**: DB 데이터를 초기화한 뒤에도 브라우저는 로그인 상태로 남아 있었고, 프로필 아이콘을 눌러도 화면이 넘어가지 않음(`profile.php` → `index.php`로 즉시 리다이렉트).
- **원인**: `includes/functions.php`의 `requireLogin()`은 세션에 `user_id`가 있는지만 확인하고, 그 유저가 실제로 DB에 존재하는지는 검증하지 않았음. DB가 초기화되면 세션은 남아있지만 `currentUser()`가 `false`를 반환했고, `profile.php`가 `$me['id']`에 접근하면서 조회 대상 유저를 찾지 못해 `index.php`로 되돌아가는 문제였음.
- **수정**: `includes/functions.php`의 `requireLogin()`에서 세션은 있지만 `currentUser()`가 유저를 찾지 못하면 세션을 정리(`session_unset` + `session_destroy`)하고 `login.php`로 리다이렉트하도록 변경. `requireLogin()`을 사용하는 모든 페이지(`index.php`, `profile.php`, `upload.php`, `edit_profile.php`, `activity.php`, `follows.php`, `post.php`, `search.php`)에 공통 적용됨.
- **검증**: `http://ryu.io.kr/dogstagram/`에서 실제 브라우저로 재현 후 반영 확인 완료.

### 2026-09-14 — 피드/돋보기 분리, 두 번째 계정 업로드 실패 원인 수정
- **배경**: 홈 피드가 전체 공개였는데, 실제 인스타그램처럼 "홈 피드 = 팔로우한 사람 글만" / "돋보기 = 전체 공개 탐색"으로 역할을 나눠달라는 요청.
- **변경 (피드/돋보기 분리)**:
  - `index.php`: 쿼리를 `내 게시물 + 내가 팔로우한 사용자(맞팔 포함)의 게시물`만 가져오도록 수정. 팔로우한 계정의 글이 없으면 돋보기로 안내하는 빈 상태 문구로 변경.
  - `search.php`: 검색어가 없을 때(기본 화면) 맞팔/팔로우 여부와 무관하게 가입한 모든 사용자의 최신 게시물을 그리드로 보여주도록 추가(`profile.php`의 `.profile-grid`/`.grid-item` 스타일 재사용). 검색어를 입력하면 기존처럼 유저 검색 결과를 보여줌.
- **증상 (업로드 오류)**: 첫 번째 계정은 업로드가 정상 동작하지만, 두 번째 계정에서는 사진 선택 후 미리보기까지는 되는데 "공유하기"를 누르면 미리보기가 사라지고 "이미지를 선택해주세요" 오류가 뜸.
- **원인**: 운영 서버의 실제 PHP 업로드 제한(`upload_max_filesize`/`post_max_size`, 기본값 2MB 부근)이 앱 코드가 안내하는 8MB 제한보다 낮았음. 서버 자체 제한을 초과한 요청은 PHP가 파일을 아예 `$_FILES`에 채우지 않거나 에러 코드만 채우는데, `upload.php`가 이 경우를 전부 "이미지를 선택해주세요"로 뭉뚱그려 표시해서 실제 원인(용량 초과)을 알 수 없었음. 두 계정 자체의 차이가 아니라 업로드하려던 사진의 용량 차이(2~8MB 사이) 때문에 발생한 문제였음 — `ryu.io.kr` 운영 서버에서 5MB/3MB 더미 파일로 재현, 1MB는 통과하는 것으로 임계값 확인.
- **수정**:
  - `upload.php`: 게시물 업로드 용량 제한을 8MB → **20MB**로 상향(`MAX_UPLOAD_BYTES` 상수화). 오류 분기도 세분화해서 `UPLOAD_ERR_INI_SIZE`/`UPLOAD_ERR_FORM_SIZE`(용량 초과) 및 `post_max_size` 초과로 `$_FILES`가 통째로 비는 경우를 감지해 "이미지 용량이 너무 커서 업로드할 수 없습니다" 메시지를 보여주도록 수정. 실제 파일이 없는 경우에만 "이미지를 선택해주세요"가 뜨도록 분리.
  - 프로젝트 루트에 `.htaccess`를 추가해 `upload_max_filesize`/`post_max_size`를 20MB 제한보다 여유 있게 상향(25M/28M).
- **검증**: `http://ryu.io.kr/dogstagram/`에서 실제 계정으로 재현. 로컬에 PHP CLI가 없어 코드 자체 실행 검증은 미완료 — 배포 후 큰 용량(수~20MB) 사진으로 실제 업로드 확인 필요. `.htaccess`의 `php_value` 오버라이드는 호스팅 환경이 `AllowOverride`로 이를 허용해야 적용되므로, 배포 후에도 여전히 실패하면 호스팅사에 `upload_max_filesize`/`post_max_size` 상향을 직접 요청해야 함.
- **후속 확인 (2026-09-14)**: 배포 후에도 여전히 실패 — 진단용 스크립트(`ini_get`)로 확인해보니 `ryu.io.kr`은 mod_php가 아닌 PHP-FPM/CGI 방식이라 `.htaccess`의 `php_value`가 전혀 적용되지 않고 있었음(`upload_max_filesize`가 PHP 기본값 2M 그대로). 프로젝트 루트에 `.user.ini`를 추가(PHP가 SAPI 종류와 무관하게 직접 읽는 파일)해서 실제로 반영 확인 완료. **이 환경에서는 업로드 용량 관련 설정은 `.htaccess`가 아니라 `.user.ini`로 해야 함** — 앞으로 유사 설정을 건드릴 때 참고.

### 2026-09-14 — 게시물 여러 장(최대 3장) 업로드 지원
- **배경**: 게시물 업로드 시 최대 3장까지 사진을 올릴 수 있게 해달라는 요청.
- **변경**:
  - `sql/schema.sql`, `sql/migration_v3.sql`(기존 운영 DB 반영용): `post_images` 테이블 추가(`post_id`, `image_path`, `sort_order`). `posts.image_path`는 대표(첫 번째) 이미지로 계속 사용해 프로필 그리드·돋보기·알림 탭 등 기존 코드와 호환.
  - `includes/functions.php`: `getPostImages($postId, $fallbackImage)` 추가 — `post_images`에 기록이 없는 기존 단일 이미지 게시물은 `posts.image_path` 하나짜리 배열로 대체. `maxPostImages()`(현재 3)로 상한을 한 곳에서 관리.
  - `upload.php`: `input[name="images[]"]`로 다중 선택(최대 3장) 지원. 장당 20MB 제한은 유지하되 전체 파일 목록을 모두 검증(형식/용량/개수)한 뒤에만 저장하고, 검증 실패 시 이미 옮겨진 파일은 롤백(`unlink`). 미리보기도 선택한 장수만큼 그리드로 표시(JS, 3장 초과 선택 시 알림 후 재선택 요구).
  - `includes/post_carousel.php`(신규, index.php/post.php 공용): 이미지가 여러 장이면 가로 스와이프 캐러셀(CSS `scroll-snap`) + 하단 점 표시로 렌더링. 더블탭 좋아요는 각 이미지에서 동일하게 동작.
  - `js/app.js`: 캐러셀 스크롤 위치에 맞춰 점 표시를 동기화(`scroll` 이벤트는 버블링되지 않아 document에 capture 단계로 위임).
  - `profile.php`, `search.php`: 그리드 썸네일에 여러 장인 게시물 표시용 작은 스택 아이콘 배지 추가.
  - `post.php`: 게시물 삭제 시 대표 이미지 한 장만 지우던 것을 `getPostImages()`로 모든 이미지 파일을 지우도록 수정(DB 행은 `post_images`가 `ON DELETE CASCADE`라 자동 정리됨).
  - `.user.ini`: 한 번에 최대 3장(장당 20MB)까지 전송될 수 있어 `post_max_size`를 28M → 70M로 상향(`upload_max_filesize`는 장당 제한 20MB보다 큰 25M 유지). `.htaccess`도 동일하게 맞춤(이 환경에선 미적용이지만 다른 환경 대비 유지).
- **배포 필요**: `sql/migration_v3.sql`을 운영 DB에 먼저 적용해야 함(`mysql -u [계정] -p dogstagram < sql/migration_v3.sql`). 이후 PHP/CSS/JS 파일과 갱신된 `.user.ini`를 서버에 올릴 것.
- **배포 중 이슈**: DB 마이그레이션 없이 PHP 파일만 먼저 올렸다가 사이트 전체가 404로 보이는 문제 발생. `post_images` 테이블이 없는 상태에서 `getPostImages()`가 쿼리하며 PDO 예외(치명적 오류)가 발생했는데, 이 운영 서버(nginx + PHP-FPM)가 PHP 치명적 오류를 500이 아니라 404로 보여주는 설정이라 "파일이 없다"처럼 보였음. `login.php`/`register.php`/`activity.php`(post_images 미사용)는 정상 동작했던 것이 결정적 단서. `migration_v3.sql` 실행 후 정상화 확인.
- **검증**: `http://ryu.io.kr/dogstagram/`에서 실제 계정으로 1장/3장 업로드, 피드 캐러셀 스와이프(점 표시 동기화), 프로필 그리드 다중 이미지 배지, 게시물 삭제 시 파일 정리까지 실제 브라우저로 확인 완료.

### 2026-09-15 — 캐러셀 마우스 드래그 지원, 업로드 미리보기 캐러셀화, 게시물 헤더에 프로필 링크 추가
- **배경**: (1) 여러 장 게시물에서 이미지를 옆으로 "넘기기"가 안 된다는 신고 — PC 마우스로 테스트할 경우 브라우저가 overflow 영역의 클릭 드래그 스크롤을 기본 지원하지 않아서 발생(터치는 원래도 브라우저 기본 스와이프로 동작). (2) 업로드 화면의 여러 장 미리보기가 3열 그리드로만 보여서 실제 게시물처럼 한 장씩 넘겨보기가 안 됨. (3) 피드/돋보기에서 다른 사람 게시물의 프로필 사진·아이디를 눌러도 반응이 없었음(그 사람 프로필로 이동 불가).
- **변경**:
  - `js/app.js`: `enableCarouselDrag()` 추가 — 마우스 포인터일 때만 pointerdown/move/up으로 캐러셀을 드래그해 넘길 수 있게 함(터치는 브라우저 기본 스와이프를 그대로 사용해 성능 저하 없음). 점 표시 동기화 로직을 `.post-carousel`과 업로드 미리보기용 `.upload-preview-carousel` 공용으로 일반화.
  - `upload.php`: 미리보기를 3열 그리드(`upload-preview-grid`) → 한 장씩 넘겨보는 캐러셀(`upload-preview-carousel`) + 하단 점 표시로 교체. 드롭 영역을 `<label>`에서 `<div>`로 바꾸고 파일 input은 "선택 전" 플레이스홀더 안에만 두어, 미리보기 캐러셀을 드래그/스와이프할 때 매번 파일 선택창이 다시 열리는 충돌을 막음. 선택 후에는 우상단 "사진 변경" 버튼으로 재선택.
  - `css/style.css`: `.post-carousel`/`.upload-preview-carousel`에 `cursor: grab`/`.dragging` 상태(`cursor: grabbing`, 드래그 중 `scroll-snap-type: none`) 추가. 이미지 네이티브 드래그(고스트 이미지)가 커스텀 드래그와 충돌하지 않도록 `-webkit-user-drag: none`, `user-select: none` 추가. `.upload-change-btn` 등 추가 스타일.
  - `index.php`, `post.php`: 게시물 헤더(아바타+아이디)를 `<div>`에서 `<a href="profile.php?id=...">`로 변경해 클릭 시 작성자 프로필로 이동.
- **검증**: 로컬에 PHP CLI가 없어 실행 검증은 미완료 — 배포 후 PC(마우스 드래그)/모바일(터치 스와이프) 양쪽에서 캐러셀 넘기기, 업로드 미리보기 캐러셀, 게시물 헤더 클릭 시 프로필 이동을 확인 필요.

### 2026-09-15 — 게시물 상세(돋보기 경유)에 댓글 아이콘 추가, 댓글 아이콘 클릭 시 바텀시트로 상세 보기
- **배경**: 피드(`index.php`)의 게시물에는 댓글 아이콘이 있는데, 돋보기에서 게시물을 눌러 들어가는 상세 페이지(`post.php`)에는 좋아요 버튼만 있고 댓글 아이콘이 없었음. 또한 댓글 아이콘을 눌렀을 때 화면 하단의 인라인 입력창으로 포커스만 이동할 뿐, 댓글을 자세히 모아보는 화면이 없었음.
- **변경**:
  - `post.php`: `post-actions`에 좋아요 버튼과 동일한 스타일의 댓글 아이콘 버튼(`onclick="focusComment(...)"`) 추가 — `index.php`와 동일하게 맞춤.
  - `includes/comment_sheet.php`(신규, index.php/post.php 공용): 화면 하단에서 위로 슬라이드되어 올라오는 댓글 바텀시트 마크업(제목/닫기 버튼/댓글 목록/입력 폼) 1개를 페이지당 한 번만 포함.
  - `js/app.js`: `focusComment(postId)`가 인라인 입력창 포커스 대신 `openCommentSheet(postId)`를 호출하도록 변경. 바텀시트는 해당 게시물의 댓글을 API로 새로 불러와 표시하고, 새 댓글 작성도 바텀시트 안에서 바로 처리(작성 후 피드에 보이는 댓글 수 배지도 함께 갱신, 인라인 "모두 보기" 목록은 다음에 열 때 새로 불러오도록 캐시 무효화). 배경 클릭이나 닫기 버튼, 다른 게시물의 아이콘 클릭 시 정상적으로 닫히거나 전환됨. 기존 "댓글 N개 모두 보기" 인라인 펼치기 동작은 그대로 유지(변경 없음).
  - `css/style.css`: `.comment-sheet`/`.comment-sheet-backdrop` 등 슬라이드업 바텀시트 스타일 추가(트랜지션으로 아래→위 애니메이션, 최대 높이 75vh, 다크모드는 기존 `--bg-elevated`/`--border` 등 변수 재사용).
- **검증**: 로컬에 PHP CLI가 없어 실행 검증은 미완료 — 배포 후 돋보기 → 게시물 상세에서 댓글 아이콘이 보이는지, 피드/상세 양쪽에서 댓글 아이콘 클릭 시 바텀시트가 올라오고 댓글 목록/작성이 정상 동작하는지, 배경 클릭으로 닫히는지 확인 필요.
