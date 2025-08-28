<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        echo "<script>alert('All fields are required.'); history.back();</script>"; exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email address.'); history.back();</script>"; exit;
    }
    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match.'); history.back();</script>"; exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $name, $email, $hash);
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $mysqli->insert_id;
        $_SESSION['user_name'] = $name;
        echo "<script>location.href='index.php';</script>"; exit;
    } else {
        echo "<script>alert('Email already in use or error.'); history.back();</script>"; exit;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Signup — MiniBlogger</title>
<style>
body{margin:0;background:linear-gradient(180deg,#071027 0%, #051022 100%);font-family:Inter,system-ui,Arial;color:#eaf6ff}
.wrap{max-width:400px;margin:40px auto;padding:20px}
.card{background:rgba(255,255,255,0.03);padding:20px;border-radius:12px}
h2{text-align:center;margin-bottom:20px}
input{width:100%;padding:10px;margin-bottom:12px;border-radius:8px;border:1px solid rgba(255,255,255,0.05);background:transparent;color:inherit}
.btn{width:100%;padding:10px;background:linear-gradient(135deg,#60a5fa,#7dd3fc);color:#04233a;border:0;border-radius:8px;font-weight:600;cursor:pointer}
.note{text-align:center;margin-top:10px;color:#9fb0c6}
a{color:#7dd3fc;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h2>Create Account</h2>
        <form method="post">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit" class="btn">Sign Up</button>
        </form>
        <div class="note">Already have an account? <a href="login.php">Log in</a></div>
    </div>
</div>
</body>
</html>
