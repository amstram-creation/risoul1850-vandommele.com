<?php
// app/io/route/admin/index.php

$error = null;
$form_data = [];

if ($_POST) {
    $action = $_POST['action'] ?? '';
    $form_data = $_POST; // Save for repopulating forms

    try {
        switch ($action) {
            case 'update_price':
                qp(
                    db(),
                    "UPDATE week SET price = ? WHERE id = ?",
                    [(float)$_POST['price'], (int)$_POST['id']]
                );
                break;

            case 'cancel_booking':
                qp(
                    db(),
                    "UPDATE week SET confirmed = NULL, guest_name = NULL, guest_email = NULL, guest_phone = NULL, booked_at = NULL WHERE id = ?",
                    [(int)$_POST['id']]
                );
                break;

            case 'add_week':
                $res = qp(
                    db(),
                    "INSERT INTO week (week_start, price, is_high_season) VALUES (?, ?, ?)",
                    [$_POST['week_start'], (float)$_POST['price'], isset($_POST['is_high_season']) ? 1 : 0]
                );
                break;
        }

        // Success - redirect to clear POST
        header('Location: /admin');
        exit;
    } catch (PDOException $e) {
        // Database error
        $error = match ($e->getCode()) {
            '23000' => 'Cette semaine existe déjà dans la base de données.',
            '22001' => 'Une des valeurs saisies est trop longue.',
            '22003' => 'Le prix saisi est invalide.',
            '01000' => 'Avertissement de la base de données : ' . $e->getMessage(),
            default => 'Erreur de base de données : ' . $e->getMessage()
        };
    }
}

try {
    $weeks = db()->query("SELECT * FROM week ORDER BY week_start ASC")->fetchAll();
    $stats = db()->query("SELECT 
        COUNT(*) as total,
        COUNT(confirmed) as booked,
        SUM(price) as total_revenue,
        SUM(CASE WHEN confirmed = 1 THEN price ELSE 0 END) as booked_revenue
        FROM week")->fetch();
} catch (PDOException $e) {
    $error = 'Impossible de charger les données : ' . $e->getMessage();
    $weeks = [];
    $stats = ['total' => 0, 'booked' => 0, 'total_revenue' => 0, 'booked_revenue' => 0];
}

return compact('weeks', 'stats', 'error', 'form_data');
