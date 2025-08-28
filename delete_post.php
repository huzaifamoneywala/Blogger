<?php
require_once 'db.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$password = $_GET['password'] ?? '';
if (!$id || !$password) {
    echo "<script>alert('Missing id or password'); history.back();</script>"; exit;
}
$stmt = $mysqli->prepare("SELECT post_password FROM posts WHERE id = ?");
$stmt->bind_param('i',$id); $stmt->execute(); $res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row) { echo "<script>alert('Post not found'); location.href='index.php';</script>"; exit; }
if (!password_verify($password, $row['post_password'])) {
    echo "<script>alert('Wrong password'); history.back();</script>"; exit;
}
// delete (comments cascade)
$del = $mysqli->prepare("DELETE FROM posts WHERE id = ?");
$del->bind_param('i',$id);
if ($del->execute()) {
    echo "<script>alert('Post deleted'); location.href='index.php';</script>"; exit;
} else {
    echo "<script>alert('Could not delete'); history.back();</script>"; exit;
}
?>
