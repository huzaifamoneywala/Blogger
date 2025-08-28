<?php
require_once 'db.php';

// get values
$id = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : 0;
$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$excerpt = trim($_POST['excerpt'] ?? '');
$category_id = intval($_POST['category_id'] ?? 0);
$content = $_POST['content_html'] ?? '';
$post_password = $_POST['post_password'] ?? '';

// basic validation
if (!$title || !$author || !$content || !$category_id) {
    echo "<script>alert('Please fill required fields.'); history.back();</script>"; exit;
}

if ($id === 0) {
    // create new post
    if (!$post_password) {
        echo "<script>alert('Please set a post password to protect edits/deletion.'); history.back();</script>"; exit;
    }
    $hash = password_hash($post_password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO posts (title, excerpt, content, author, category_id, post_password, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param('ssssis', $title, $excerpt, $content, $author, $category_id, $hash);
    if($stmt->execute()){
        $newId = $mysqli->insert_id;
        // redirect using JS for smooth client-side redirect as requested
        echo "<script>location.href='post.php?id={$newId}';</script>";
        exit;
    } else {
        echo "<script>alert('Failed to save post.'); history.back();</script>"; exit;
    }
} else {
    // edit existing post: require current password (or allow blank to keep)
    $stmt = $mysqli->prepare("SELECT post_password FROM posts WHERE id = ?");
    $stmt->bind_param('i',$id); $stmt->execute(); $res = $stmt->get_result(); $row = $res->fetch_assoc();
    if (!$row) { echo "<script>alert('Post not found.'); location.href='index.php';</script>"; exit; }
    $currentHash = $row['post_password'];

    if ($post_password) {
        // verify: if provided and matches, allow; else if provided but wrong -> reject
        if (!password_verify($post_password, $currentHash)) {
            echo "<script>alert('Wrong post password. Cannot edit.'); history.back();</script>"; exit;
        }
        // same password provided => keep hash (no change), or re-hash? we'll re-hash to be safe
        $newHash = password_hash($post_password, PASSWORD_DEFAULT);
    } else {
        // password not provided; keep existing hash
        $newHash = $currentHash;
    }

    $upd = $mysqli->prepare("UPDATE posts SET title = ?, excerpt = ?, content = ?, author = ?, category_id = ?, post_password = ?, updated_at = NOW() WHERE id = ?");
    $upd->bind_param('sssisii', $title, $excerpt, $content, $author, $category_id, $newHash, $id);
    if ($upd->execute()) {
        echo "<script>location.href='post.php?id={$id}';</script>"; exit;
    } else {
        echo "<script>alert('Failed to update.'); history.back();</script>"; exit;
    }
}
?>
