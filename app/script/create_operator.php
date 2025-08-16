<?php
// scripts/create_operator.php
require 'add/badhat/core.php';
require 'add/badhat/db.php';

$label = $argv[1] ?? 'Administrator';
$username = $argv[2] ?? 'admin';
$password = $argv[3] ?? 'admin123';

$hash = password_hash($password, PASSWORD_DEFAULT);

qp(db(), "
    INSERT INTO operator (label, username, password_hash, status, enabled_at) 
    VALUES (?, ?, ?, 1, NOW())
", [$label, $username, $hash]);

echo "Operator '$username' created with password '$password'\n";
