<?php

[$price_low, $price_high] = db()->query("SELECT amount FROM `price` ORDER BY is_high ASC")->fetchAll(PDO::FETCH_COLUMN);

if (isset($_GET['after'])) {
    $date = new DateTime($_GET['after']);

    if ($date->format('N') != 1)
        $date->modify('next monday');

    $weeks = rangeOfWeeksFrom($date, 52, $price_low, $price_high);

    http_out(
        200,
        json_encode($weeks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ['Content-Type' => 'application/json; charset=utf-8']
    );
    exit;
}


$response = [
    'price_low' => $price_low,
    'price_high' => $price_high
];

if ($_POST) {
    $week_date   = trim($_POST['week_date'] ?? '');
    $guest_name  = trim($_POST['guest_name'] ?? '');
    $guest_email = trim($_POST['guest_email'] ?? '');
    $guest_phone = trim($_POST['guest_phone'] ?? '');

    $weeks = rangeOfWeeksFrom(new DateTime($week_date), 52, $price_low, $price_high);

    if ($week_date && $guest_name && $guest_email && isset($weeks[$week_date])) {
        $pdo = db();

        try {
            $price = $weeks[$week_date]['price'] ?: throw new Exception('Semaine non trouvée', 400);
            // 1) try to insert a fresh row
            $insert = qp(
                $pdo,
                "INSERT INTO week (week_start, guest_name, guest_email, guest_phone, price)
                 VALUES (?, ?, ?, ?, ?)",
                [$week_date, $guest_name, $guest_email, $guest_phone ?: null, $price]
            );
            if ($insert->rowCount() !== 1)
                $response['message'] = '';
        } catch (PDOException $e) {
            // 2) on duplicate key → update only if not confirmed
            if ($e->getCode() === '23000') { // integrity constraint violation
                $update = qp(
                    $pdo,
                    "UPDATE week
                     SET guest_name = ?, guest_email = ?, guest_phone = ?, confirmed = 1, booked_at = NOW()
                     WHERE week_start = ? AND confirmed <> 1 AND guest_name IS NULL AND guest_email IS NULL",
                    [$guest_name, $guest_email, $guest_phone ?: null, $week_date]
                );
                if ($update->rowCount() !== 1)
                    $response['message'] = 'Semaine déjà réservée';
            } else {
                $response['message'] = 'Erreur lors de la réservation : ' . $e->getMessage();
                $response['status'] = 500;
            }
        }
    } else {
        $response['message'] = 'Paramètres invalides';
        $response['status'] = 400;
    }
}

return $response;
