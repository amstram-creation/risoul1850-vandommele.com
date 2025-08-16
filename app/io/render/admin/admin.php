<?php
// app/io/render/admin/index.php
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
        <a href="/logout">Déconnexion</a>
    </header>

    <main>

        <?php if ($error): ?>
            <div class="alert">
                <strong>Erreur :</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif ?>

        <div class="stats">
            <div class="stat-card"><strong><?= $stats['total'] ?></strong><br>Semaines totales</div>
            <div class="stat-card"><strong><?= $stats['booked'] ?></strong><br>Réservées</div>
            <div class="stat-card"><strong><?= number_format($stats['booked_revenue']) ?>€</strong><br>Chiffre d'affaires</div>
            <div class="stat-card"><strong><?= number_format($stats['total_revenue']) ?>€</strong><br>Potentiel</div>
        </div>

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
                        <tr>
                            <td><?= strftime('%e %b %Y', strtotime($week['week_start'])) ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="update_price">
                                    <input type="hidden" name="id" value="<?= $week['id'] ?>">
                                    <input type="number" name="price" class="price-input" value="<?=
                                                                                                    ($form_data['action'] ?? '') === 'update_price' && ($form_data['id'] ?? '') == $week['id']
                                                                                                        ? htmlspecialchars($form_data['price'] ?? $week['price'])
                                                                                                        : $week['price']
                                                                                                    ?>">
                                    <button class="btn-save">✓</button>
                                    <?= csrf_field() ?>
                                </form>
                            </td>
                            <td>
                                <?php if ($week['confirmed']): ?>
                                    <span class="badge reserved">RÉSERVÉ</span>
                                <?php else: ?>
                                    <span class="badge available">DISPONIBLE</span>
                                <?php endif ?>
                            </td>
                            <td>
                                <?php if ($week['guest_name']): ?>
                                    <strong><?= htmlspecialchars($week['guest_name']) ?></strong><br>
                                    <small><?= htmlspecialchars($week['guest_email']) ?></small>
                                <?php else: ?>
                                    <em class="text-muted">Aucune réservation</em>
                                <?php endif ?>
                            </td>
                            <td>
                                <?php if ($week['confirmed']): ?>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="action" value="cancel_booking">
                                        <input type="hidden" name="id" value="<?= $week['id'] ?>">
                                        <button onclick="return confirm('Annuler la réservation ?')" class="btn-cancel">Annuler</button>
                                        <?= csrf_field() ?>
                                    </form>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <details <?= ($form_data['action'] ?? '') === 'add_week' ? 'open' : '' ?>>
            <summary>Ajouter une nouvelle semaine</summary>
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