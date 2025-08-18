<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Administration</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>

<body>

    <header>
        <h1>Administration Réservations</h1>
        <a href="/logout">Déconnexion</a>
    </header>

    <main>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif ?>

        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif ?>

        <!-- Global Pricing -->
        <section class="pricing-section">
            <h2>Tarifs Globaux</h2>
            <form method="post" class="pricing-form">
                <input type="hidden" name="action" value="update_global_prices">
                <div class="price-inputs">
                    <label>
                        Basse saison
                        <input type="number" name="low_price" value="<?= $prices[0] ?>" required>€
                    </label>
                    <label>
                        Haute saison
                        <input type="number" name="high_price" value="<?= $prices[1] ?>" required>€
                    </label>
                    <button type="submit" class="btn-primary">Mettre à jour</button>
                </div>
                <?= csrf_field() ?>
            </form>
        </section>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <strong><?= $stats['total'] ?></strong><br>Total semaines
            </div>
            <div class="stat-card">
                <strong><?= $stats['confirmed'] ?></strong><br>Confirmées
            </div>
            <div class="stat-card">
                <strong><?= $stats['pending'] ?></strong><br>En attente
            </div>
            <div class="stat-card">
                <strong><?= number_format($stats['confirmed_revenue']) ?>€</strong><br>CA confirmé
            </div>
        </div>

        <!-- Weeks Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Semaine</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th>Client</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weeks as $week): ?>
                        <tr class="week-<?= $week['status'] ?>">
                            <td><?= date('d M Y', strtotime($week['week_start'])) ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="update_week_price">
                                    <input type="hidden" name="id" value="<?= $week['id'] ?>">
                                    <input type="number" name="price" class="price-input" value="<?= $week['price'] ?>">
                                    <button class="btn-save">✓</button>
                                    <?= csrf_field() ?>
                                </form>
                            </td>
                            <td>
                                <span class="badge <?= $week['status'] ?>">
                                    <?= match ($week['status']) {
                                        'confirmed' => 'CONFIRMÉ',
                                        'pending' => 'EN ATTENTE',
                                        default => 'DISPONIBLE'
                                    } ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($week['guest_name']): ?>
                                    <strong><?= htmlspecialchars($week['guest_name']) ?></strong><br>
                                    <small><?= htmlspecialchars($week['guest_email']) ?></small>
                                    <?php if ($week['guest_phone']): ?>
                                        <br><small><?= htmlspecialchars($week['guest_phone']) ?></small>
                                    <?php endif ?>
                                <?php else: ?>
                                    <em class="text-muted">Aucune réservation</em>
                                <?php endif ?>
                            </td>
                            <td>
                                <?php if ($week['guest_name'] && $week['confirmed'] != 1): ?>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="action" value="confirm_booking">
                                        <input type="hidden" name="id" value="<?= $week['id'] ?>">
                                        <button class="btn-confirm" onclick="return confirm('Confirmer la réservation?')">Confirmer</button>
                                        <?= csrf_field() ?>
                                    </form>
                                <?php endif ?>

                                <?php if ($week['confirmed'] == 1): ?>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="action" value="cancel_booking">
                                        <input type="hidden" name="id" value="<?= $week['id'] ?>">
                                        <button class="btn-cancel" onclick="return confirm('Annuler la réservation?')">Annuler</button>
                                        <?= csrf_field() ?>
                                    </form>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Add Week -->
        <details <?= ($form_data['action'] ?? '') === 'add_week' ? 'open' : '' ?>>
            <summary>Ajouter une semaine</summary>
            <form method="post" class="add-week">
                <div>
                    <label>Date<br>
                        <input type="date" name="week_start" value="<?= htmlspecialchars($form_data['week_start'] ?? '') ?>" required>
                    </label>
                </div>
                <div>
                    <label>Prix<br>
                        <input type="number" name="price" value="<?= htmlspecialchars($form_data['price'] ?? '') ?>" required>
                    </label>
                </div>
                <div>
                    <label><input type="checkbox" name="is_high_season" <?= isset($form_data['is_high_season']) ? 'checked' : '' ?>> Haute saison</label>
                </div>
                <input type="hidden" name="action" value="add_week">
                <button class="btn-add">Ajouter</button>
                <?= csrf_field() ?>
            </form>
        </details>

    </main>
</body>

</html>