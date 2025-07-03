<?php
session_start();
$loggedIn = isset($_SESSION['username']);
$nickname = $_SESSION['nickname'] ?? '';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>기본 웹사이트</title>
    <link rel="stylesheet" href="style.css">
    <style>
    /* ...existing code... */
    .like-btn.liked {
        background: #ffb3b3;
        color: #fff;
        border: 1px solid #ffb3b3;
        cursor: not-allowed;
    }
    .like-btn {
        background: #eee;
        color: #333;
        border: 1px solid #ccc;
        padding: 4px 10px;
        border-radius: 4px;
        cursor: pointer;
    }
    </style>
</head>
<body>
    <header>
        <h1>환영합니다!</h1>
        <div class="top-buttons">
            <?php if ($loggedIn): ?>
                <span style="margin-right:10px;"><?php echo htmlspecialchars($nickname); ?>님 환영합니다!</span>
                <button onclick="location.href='logout.php'">로그아웃</button>
            <?php else: ?>
                <button id="loginBtn">로그인</button>
            <?php endif; ?>
            <button id="writeBtn">글쓰기</button>
        </div>
    </header>
    <main>
        <h2>글 목록</h2>
        <ul class="post-list">
        <?php
            $postsFile = __DIR__ . '/posts.json';
            if (file_exists($postsFile)) {
                $posts = json_decode(file_get_contents($postsFile), true);
                if ($posts && is_array($posts)) {
                    foreach ($posts as $idx => $post) {
                        $title = htmlspecialchars($post['title']);
                        $author = htmlspecialchars($post['author']);
                        $content = htmlspecialchars($post['content'] ?? '');
                        $likeCount = isset($post['like_count']) ? (int)$post['like_count'] : 0;
                        $likedUsers = isset($post['liked_users']) && is_array($post['liked_users']) ? $post['liked_users'] : [];
                        $userLiked = $loggedIn && in_array($_SESSION['username'], $likedUsers);
                        echo "<li>";
                        // 제목에 data-content 속성 추가
                        echo "<a href='#' class='view-post' data-title=\"{$title}\" data-content=\"{$content}\" data-author=\"{$author}\">{$title}</a> - {$author}";
                        // 좋아요 버튼
                        echo " <form method='post' action='like.php' style='display:inline;'>";
                        echo "<input type='hidden' name='post_idx' value='{$idx}'>";
                        $likeBtnClass = $userLiked ? "like-btn liked" : "like-btn";
                        $likeBtnText = "좋아요";
                        echo "<button type='submit' class='{$likeBtnClass}'" . ($userLiked ? " disabled" : "") . ">{$likeBtnText}</button>";
                        echo "</form>";
                        // 본인 글이면 수정 버튼
                        if ($loggedIn && $post['author'] === $nickname) {
                            echo " <button onclick=\"location.href='edit.php?idx={$idx}'\">수정</button>";
                        }
                        // 좋아요 수 표시
                        echo " <span style='color:#888;'>♥ {$likeCount}</span>";
                        echo "</li>";
                    }
                } else {
                    echo "<li>등록된 글이 없습니다.</li>";
                }
            } else {
                echo "<li>등록된 글이 없습니다.</li>";
            }
        ?>
        </ul>
    </main>
    <!-- 로그인 모달 -->
    <div id="loginModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" id="closeLogin">&times;</span>
            <h2>로그인</h2>
            <form method="post" action="login.php">
                <input type="text" name="username" placeholder="아이디" required><br>
                <input type="password" name="password" placeholder="비밀번호" required><br>
                <button type="submit">로그인</button>
            </form>
            <p>아직 회원이 아니신가요? <button id="showRegister">회원가입</button></p>
        </div>
    </div>
    <!-- 회원가입 모달 -->
    <div id="registerModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" id="closeRegister">&times;</span>
            <h2>회원가입</h2>
            <form method="post" action="register.php">
                <input type="text" name="username" placeholder="아이디" required><br>
                <input type="password" name="password" placeholder="비밀번호" required><br>
                <input type="text" name="nickname" placeholder="닉네임" required><br>
                <button type="submit">회원가입</button>
            </form>
        </div>
    </div>
    <!-- 글쓰기 모달 -->
    <div id="writeModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" id="closeWrite">&times;</span>
            <h2>글쓰기</h2>
            <form method="post" action="write.php">
                <input type="text" name="title" placeholder="제목" required><br>
                <textarea name="content" placeholder="글 내용" required style="width:90%;height:100px;"></textarea><br>
                <button type="submit">업로드</button>
            </form>
        </div>
    </div>
    <!-- 글 내용 보기 모달 -->
    <div id="viewPostModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" id="closeViewPost">&times;</span>
            <h2 id="viewPostTitle"></h2>
            <div style="color:#888; margin-bottom:10px;" id="viewPostAuthor"></div>
            <div id="viewPostContent" style="white-space:pre-wrap; text-align:left;"></div>
        </div>
    </div>
    <script src="script.js"></script>
    <script>
    // 모달 제어 스크립트
    var loginBtn = document.getElementById('loginBtn');
    if (loginBtn) {
        loginBtn.onclick = function() {
            document.getElementById('loginModal').style.display = 'block';
        };
    }
    var closeLogin = document.getElementById('closeLogin');
    if (closeLogin) {
        closeLogin.onclick = function() {
            document.getElementById('loginModal').style.display = 'none';
        };
    }
    var showRegister = document.getElementById('showRegister');
    if (showRegister) {
        showRegister.onclick = function() {
            document.getElementById('loginModal').style.display = 'none';
            document.getElementById('registerModal').style.display = 'block';
        };
    }
    var closeRegister = document.getElementById('closeRegister');
    if (closeRegister) {
        closeRegister.onclick = function() {
            document.getElementById('registerModal').style.display = 'none';
        };
    }
    // 글쓰기 모달 제어
    var writeBtn = document.getElementById('writeBtn');
    if (writeBtn) {
        writeBtn.onclick = function() {
            document.getElementById('writeModal').style.display = 'block';
        };
    }
    var closeWrite = document.getElementById('closeWrite');
    if (closeWrite) {
        closeWrite.onclick = function() {
            document.getElementById('writeModal').style.display = 'none';
        };
    }
    // 글 내용 보기 모달 제어
    document.querySelectorAll('.view-post').forEach(function(el) {
        el.onclick = function(e) {
            e.preventDefault();
            document.getElementById('viewPostTitle').textContent = this.dataset.title;
            document.getElementById('viewPostAuthor').textContent = '작성자: ' + this.dataset.author;
            document.getElementById('viewPostContent').textContent = this.dataset.content;
            document.getElementById('viewPostModal').style.display = 'block';
        };
    });
    var closeViewPost = document.getElementById('closeViewPost');
    if (closeViewPost) {
        closeViewPost.onclick = function() {
            document.getElementById('viewPostModal').style.display = 'none';
        };
    }
    // 모달 바깥 클릭 시 닫기
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            var modals = ['loginModal', 'registerModal', 'writeModal', 'viewPostModal'];
            modals.forEach(function(id) {
                var modal = document.getElementById(id);
                if (modal) modal.style.display = 'none';
            });
        }
    };
    </script>
</body>
</html>
