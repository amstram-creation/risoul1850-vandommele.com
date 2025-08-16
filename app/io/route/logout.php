<?php
// app/io/route/logout.php
auth(AUTH_REVOKE);
header('Location: /login');
exit;
