<?php
session_start();
if (!isset($_SESSION['nickname'])) {
    echo "<script>alert('로그인 후 이용 가능합니다.');location.href='index.php';</script>";
    exit;
}

// 글 저장 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = $_SESSION['nickname'];

    if ($title && $content) {
        $postsFile = __DIR__ . '/posts.json';
        $posts = [];
        if (file_exists($postsFile)) {
            $posts = json_decode(file_get_contents($postsFile), true) ?: [];
        }
        $posts[] = [
            'title' => $title,
            'content' => $content,
            'author' => $author,
            'like_count' => 0
        ];
        file_put_contents($postsFile, json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        echo "<script>alert('글이 등록되었습니다.');location.href='index.php';</script>";
        exit;
    } else {
        echo "<script>alert('제목과 내용을 모두 입력하세요.');history.back();</script>";
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>글쓰기 - dlxogk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">dlxogk</a>
    </header>
    <main>
        <h1>글쓰기</h1>
        <form method="post">
            <input type="text" name="title" placeholder="제목" required><br>
            <textarea name="content" placeholder="내용" required></textarea><br>
            <button type="submit">등록</button>
        </form>
    </main>
</body>
</html>