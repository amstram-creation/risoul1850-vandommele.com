<?php
$week_id     = (int)($_POST['week_id'] ?? 0);
$guest_name  = trim($_POST['guest_name'] ?? '');
$guest_email = trim($_POST['guest_email'] ?? '');
$guest_phone = trim($_POST['guest_phone'] ?? '');

if(!empty($week_id) && !empty($guest_name) && !empty($guest_email)) {
    $res = qp(
        db(),
        "UPDATE week SET guest_name = ?, guest_email = ?, guest_phone = ?, confirmed = 1, booked_at = NOW() WHERE id = ? AND confirmed <> 1",
        [$guest_name, $guest_email, $guest_phone ?: null, $week_id]
    );

    if ($res->rowCount() === 1) {
        http_out(200, '', ['Location' => '/']);

    }
}

http_out(400, "This week is already booked or does not exist.", ['Content-Type' => 'text/plain; charset=utf-8']);
exit;
