<?php
// app/route/book.php

// 1) Parse POST JSON or form data
$week_id     = (int)($_POST['week_id'] ?? 0);
$guest_name  = trim($_POST['guest_name'] ?? '');
$guest_email = trim($_POST['guest_email'] ?? '');
$guest_phone = trim($_POST['guest_phone'] ?? '');

// 2) Basic validation
if ($week_id < 1 || $guest_name === '' || $guest_email === '') {
    http_response_code(400);
    echo "Missing required fields.";
    return;
}

// 3) Ensure the week exists & is not already booked
$row = qp(
    db(),
    "SELECT id, confirmed FROM week WHERE id = ? FOR UPDATE",
    [$week_id]
);
if (!$row) {
    http_response_code(404);
    echo "Week not found.";
    return;
}
$row = $row->fetch(PDO::FETCH_ASSOC);
if ((int)$row['confirmed'] === 1) {
    http_response_code(409);
    echo "This week is already booked.";
    return;
}

// 4) Update booking info
$res = qp(
    db(),
    "UPDATE week SET guest_name = ?, guest_email = ?, guest_phone = ?, confirmed = 1, booked_at = NOW() WHERE id = ?",
    [$guest_name, $guest_email, $guest_phone ?: null, $week_id]
);

