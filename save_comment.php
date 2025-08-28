<?php
require_once 'db.php';
$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$name = trim($_POST['name'] ?? '');
$content = trim($_POST['content'] ?? '');
if (!$post_id || !$name || !$content) {
    echo "<script>alert('Please provide name and comment.'); history.back();</script>"; exit;
}
$stmt = $mysqli->prepare("INSERT INTO comments (post_id, name, content, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param('iss', $post_id, $name, $content);
if ($stmt->execute()) {
    // redirect
    echo "<script>location.href='post.php?id={$post_id}';</script>";
    exit;
} else {
    echo "<script>alert('Failed to save comment.'); history.back();</script>"; exit;
}
?>
