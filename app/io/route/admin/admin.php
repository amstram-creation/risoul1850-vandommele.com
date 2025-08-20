<?php

$error = null;
$success = null;
$form_data = [];
$weeks = [];
$price_low = 0;
$price_high = 0;
$stats = ['total' => 0, 'confirmed' => 0, 'pending' => 0, 'confirmed_revenue' => 0];

if ($_POST) {
    $action = $_POST['action'] ?? '';
    $form_data = $_POST;

    try {
        switch ($action) {
            case 'update_global_prices':
                qp(db(), "UPDATE price SET amount = ? WHERE is_high = 0", [(float)$_POST['low_price']]);
                qp(db(), "UPDATE price SET amount = ? WHERE is_high = 1", [(float)$_POST['high_price']]);
                $success = "Tarifs mis à jour";
                break;

            case 'update_week':
                $week_start = $_POST['week_start'];
                $price = (float)$_POST['price'];

                qp(db(), "INSERT INTO week (week_start, price) VALUES (?, ?) 
                          ON DUPLICATE KEY UPDATE price = VALUES(price)", [$week_start, $price]);
                $success = "Semaine mise à jour";
                break;

            case 'confirm_booking':
                qp(db(), "UPDATE week SET confirmed = 1 WHERE week_start = ?", [$_POST['week_start']]);
                $success = "Réservation confirmée";
                break;

            case 'cancel_booking':
                qp(db(), "UPDATE week SET confirmed = NULL, guest_name = NULL, guest_email = NULL, 
                          guest_phone = NULL, booked_at = NULL WHERE week_start = ?", [$_POST['week_start']]);
                $success = "Réservation annulée";
                break;
        }

        header('Location: /admin');
        exit;
    } catch (PDOException $e) {
        $error = 'Erreur: ' . $e->getMessage();
    }
}

try {
    // Get prices
    $prices = db()->query("SELECT amount FROM price ORDER BY is_high")->fetchAll(PDO::FETCH_COLUMN);
    if (count($prices) >= 2) {
        [$price_low, $price_high] = $prices;
    }

    // Generate weeks starting from today
    $date = new DateTime();
    if ($date->format('N') != 1) $date->modify('next monday');

    // $generated_weeks = rangeOfWeeksFrom($date, 52, $price_low, $price_high);

    // Get actual bookings from database
    $weeks = [];
    $stmt = db()->query("SELECT week_start, price, confirmed, guest_name, guest_email, guest_phone FROM week");
    if ($stmt) {
        while ($row = $stmt->fetch()) {
            $weeks[$row['week_start']] = $row;
        }
    }
    // Get stats
    $stats_row = db()->query("SELECT * FROM week_summary")->fetch();
    if ($stats_row) {
        $stats = $stats_row;
    }
} catch (Exception $e) {
    $error = 'Erreur de chargement: ' . $e->getMessage();
    // Ensure defaults are set
    $weeks = [];
    $price_low = $price_high = 0;
}

$weeks ??= [];

return compact('weeks', 'price_low', 'price_high', 'stats', 'error', 'success', 'form_data');
