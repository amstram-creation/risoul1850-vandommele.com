<?php

$error = null;
$success = null;
$form_data = [];

if ($_POST) {
    $action = $_POST['action'] ?? '';
    $form_data = $_POST;

    try {
        switch ($action) {
            case 'update_global_prices':
                qp(db(), "UPDATE price SET amount = ? WHERE is_high = 0", [(float)$_POST['low_price']]);
                qp(db(), "UPDATE price SET amount = ? WHERE is_high = 1", [(float)$_POST['high_price']]);
                $success = "Tarifs globaux mis à jour";
                break;

            case 'update_week_price':
                qp(db(), "UPDATE week SET price = ? WHERE id = ?", [(float)$_POST['price'], (int)$_POST['id']]);
                $success = "Prix de la semaine mis à jour";
                break;

            case 'confirm_booking':
                qp(db(), "UPDATE week SET confirmed = 1 WHERE id = ?", [(int)$_POST['id']]);
                $success = "Réservation confirmée";
                break;

            case 'cancel_booking':
                qp(db(), "UPDATE week SET confirmed = NULL, guest_name = NULL, guest_email = NULL, guest_phone = NULL, booked_at = NULL WHERE id = ?", [(int)$_POST['id']]);
                $success = "Réservation annulée";
                break;

            case 'add_week':
                qp(
                    db(),
                    "INSERT INTO week (week_start, price, is_high_season) VALUES (?, ?, ?)",
                    [$_POST['week_start'], (float)$_POST['price'], isset($_POST['is_high_season']) ? 1 : 0]
                );
                $success = "Semaine ajoutée";
                break;
        }

        header('Location: /admin');
        exit;
    } catch (PDOException $e) {
        $error = match ($e->getCode()) {
            '23000' => 'Conflit de données (semaine existante?)',
            default => 'Erreur base de données: ' . $e->getMessage()
        };
    }
}

try {
    // Load global prices
    $prices = db()->query("SELECT is_high, amount FROM price ORDER BY is_high")->fetchAll(PDO::FETCH_KEY_PAIR);

    // Load weeks with guest info
    $weeks = db()->query("SELECT * FROM week_with_status")->fetchAll();

    // Stats
    $stats = db()->query("SELECT * FROM week_summary")->fetch();
} catch (PDOException $e) {
    $error = 'Impossible de charger les données: ' . $e->getMessage();
    $weeks = [];
    $prices = db()->query("SELECT amount FROM `price` ORDER BY is_high ASC")->fetchAll(PDO::FETCH_COLUMN); // [0 => $price_low, 1 => $price_high];
    $stats = ['total' => 0, 'confirmed' => 0, 'pending' => 0, 'confirmed_revenue' => 0, 'total_revenue' => 0];
}

return compact('weeks', 'prices', 'stats', 'error', 'success', 'form_data');
