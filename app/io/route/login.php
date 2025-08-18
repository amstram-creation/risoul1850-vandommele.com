<?php


if ($_POST) {
    if (auth(AUTH_VERIFY, 'username', 'password')) {
        header('Location: /admin');
    }
    header('Location: /login?error=Invalid credentials');
    exit;
}

return [];