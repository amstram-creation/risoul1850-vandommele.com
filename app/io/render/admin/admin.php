<?php
$weeks ??= [];
?>
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
        <nav>
            <a href="/admin">Accueil</a>
            <a href="/admin/seasons">Saisons</a>
        </nav>
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
                    <label>Basse saison <input type="number" name="low_price" value="<?= $price_low ?>" required>€</label>
                    <label>Haute saison <input type="number" name="high_price" value="<?= $price_high ?>" required>€</label>
                    <button type="submit" class="btn-primary">Mettre à jour</button>
                </div>
                <?= csrf_field() ?>
            </form>
        </section>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card"><strong><?= count($weeks) ?></strong><br>Total semaines</div>
            <div class="stat-card"><strong><?= $stats['confirmed'] ?></strong><br>Confirmées</div>
            <div class="stat-card"><strong><?= $stats['pending'] ?></strong><br>En attente</div>
            <div class="stat-card"><strong><?= number_format($stats['confirmed_revenue'] ?? 0) ?>€</strong><br>CA confirmé</div>
        </div>

        <!-- Weeks Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Semaine</th>
                        <th>Prix</th>
                        <th>Saison</th>
                        <th>Statut</th>
                        <th>Client</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($weeks, 0, 50) as $week): ?>
                        <tr class="week-<?= $week['status'] ?>">
                            <td><?= date('d M Y', strtotime($week['week_start'])) ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="update_week">
                                    <input type="hidden" name="week_start" value="<?= $week['week_start'] ?>">
                                    <input type="number" name="price" class="price-input" value="<?= $week['price'] ?>">
                                    <button class="btn-save">✓</button>
                                    <?= csrf_field() ?>
                                </form>
                            </td>
                            <td>
                                <span class="badge <?= $week['is_high_season'] ? 'high' : 'low' ?>">
                                    <?= $week['is_high_season'] ? 'HAUTE' : 'BASSE' ?>
                                </span>
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
                                    <em class="text-muted">Disponible</em>
                                <?php endif ?>
                            </td>
                            <td>
                                <?php if ($week['guest_name'] && $week['confirmed'] != 1): ?>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="action" value="confirm_booking">
                                        <input type="hidden" name="week_start" value="<?= $week['week_start'] ?>">
                                        <button class="btn-confirm">Confirmer</button>
                                        <?= csrf_field() ?>
                                    </form>
                                <?php endif ?>

                                <?php if ($week['confirmed'] == 1): ?>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="action" value="cancel_booking">
                                        <input type="hidden" name="week_start" value="<?= $week['week_start'] ?>">
                                        <button class="btn-cancel" onclick="return confirm('Annuler?')">Annuler</button>
                                        <?= csrf_field() ?>
                                    </form>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
            <p class="text-muted">Affichage des 50 premières semaines. <?= count($weeks) ?> semaines générées au total.</p>
        </div>
    </main>
</body>

</html>