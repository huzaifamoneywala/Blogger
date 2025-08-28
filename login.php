<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        echo "<script>alert('Please fill all fields.'); history.back();</script>"; exit;
    }

    $stmt = $mysqli->prepare("SELECT id, name, password_hash FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        echo "<script>location.href='index.php';</script>"; exit;
    } else {
        echo "<script>alert('Invalid email or password.'); history.back();</script>"; exit;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — MiniBlogger</title>
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
        <h2>Log In</h2>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="note">Don't have an account? <a href="signup.php">Sign up</a></div>
    </div>
</div>
</body>
</html>
