<?php
// app/io/render/login.php
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login</title>
</head>

<body style="margin:0;font:16px/1.5 system-ui;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center">
    <form method="post" style="background:white;padding:2rem;border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,.1);width:100%;max-width:400px;margin:1rem">
        <h2 style="margin:0 0 1.5rem;text-align:center;color:#333">Login</h2>
        <?php if (isset($_GET['error'])): ?><p style="color:#e74c3c;margin:0 0 1rem;padding:.5rem;background:#ffeaea;border-radius:4px;font-size:14px"><?= htmlspecialchars($_GET['error']) ?></p><?php endif ?>
        <input type="text" name="username" placeholder="Username" required style="width:100%;padding:.75rem;margin:0 0 1rem;border:1px solid #ddd;border-radius:6px;font-size:16px;box-sizing:border-box">
        <input type="password" name="password" placeholder="Password" required style="width:100%;padding:.75rem;margin:0 0 1.5rem;border:1px solid #ddd;border-radius:6px;font-size:16px;box-sizing:border-box">
        <?= csrf_field() ?>
        <button type="submit" style="width:100%;padding:.75rem;background:#667eea;color:white;border:none;border-radius:6px;font-size:16px;cursor:pointer">Login</button>
    </form>
</body>

</html>