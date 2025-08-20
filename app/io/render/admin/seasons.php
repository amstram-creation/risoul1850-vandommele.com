<?php
// app/io/render/admin/seasons.php
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gestion des Saisons</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/rules.css">
</head>

<body>
    <header>
        <h1>Gestion des Saisons</h1>
        <a href="/admin">← Admin</a>
    </header>

    <main>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif ?>

        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif ?>

        <div class="rules-container">
            <!-- New Rule Card -->
            <div class="rule-card new-rule-card" id="new-rule-card" onclick="toggleNewRule()">
                <div id="new-rule-prompt">
                    <h3 style="margin: 0; color: #6c757d;">+ Ajouter une nouvelle règle</h3>
                    <p style="margin: 0.5rem 0 0; color: #6c757d;">Cliquez pour créer une règle de saison</p>
                </div>

                <form method="post" id="new-rule-form" class="rule-form active" style="display: none;">
                    <input type="hidden" name="action" value="save_rule">

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nom de la règle</label>
                            <input type="text" name="name" required>
                        </div>

                        <div class="type-tabs">
                            <button type="button" class="type-tab active" data-type="fixed_range">Plage Fixe</button>
                            <button type="button" class="type-tab" data-type="easter_family">Pâques</button>
                            <button type="button" class="type-tab" data-type="fixed_anchor">Ancre Fixe</button>
                        </div>

                        <input type="hidden" name="rule_type" value="fixed_range">

                        <div id="new-fixed_range" class="config-section active">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Date début (MM-DD)</label>
                                    <input type="text" name="fixed_start_mmdd" pattern="\d{2}-\d{2}" placeholder="06-15">
                                </div>
                                <div class="form-group">
                                    <label>Date fin (MM-DD)</label>
                                    <input type="text" name="fixed_end_mmdd" pattern="\d{2}-\d{2}" placeholder="09-15">
                                </div>
                            </div>
                        </div>

                        <div id="new-easter_family" class="config-section">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Événement</label>
                                    <select name="anchor">
                                        <option value="easter">Pâques</option>
                                        <option value="carnival">Carnaval</option>
                                        <option value="ascension">Ascension</option>
                                        <option value="pentecost">Pentecôte</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Semaines avant</label>
                                    <input type="number" name="start_offset_weeks" min="0" value="1">
                                </div>
                                <div class="form-group">
                                    <label>Semaines après</label>
                                    <input type="number" name="end_offset_weeks" min="0" value="1">
                                </div>
                            </div>
                        </div>

                        <div id="new-fixed_anchor" class="config-section">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Date ancre (MM-DD)</label>
                                    <input type="text" name="anchor" pattern="\d{2}-\d{2}" placeholder="12-25">
                                </div>
                                <div class="form-group">
                                    <label>Semaines avant</label>
                                    <input type="number" name="start_offset_weeks" min="0" value="2">
                                </div>
                                <div class="form-group">
                                    <label>Semaines après</label>
                                    <input type="number" name="end_offset_weeks" min="0" value="2">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="active" checked> Règle active
                            </label>
                        </div>

                        <div class="preview-box">
                            <strong>Aperçu <?= $current_year ?>:</strong>
                            <div class="preview-content">Configurez la règle pour voir l'aperçu...</div>
                        </div>

                        <div class="rule-actions">
                            <button type="submit" class="btn-add">Créer la Règle</button>
                            <button type="button" onclick="cancelNewRule()" class="btn-secondary">Annuler</button>
                        </div>
                    </div>

                    <?= csrf_field() ?>
                </form>
            </div>

            <!-- Existing Rules -->
            <?php foreach ($rules as $rule): ?>
                <div class="rule-card <?= $rule['active'] ? '' : 'inactive' ?>" id="rule-<?= $rule['id'] ?>">
                    <div class="rule-display">
                        <div class="rule-header">
                            <h3 class="rule-title"><?= htmlspecialchars($rule['name']) ?></h3>
                            <span class="rule-type-badge <?= getRuleType($rule) ?>">
                                <?= match (getRuleType($rule)) {
                                    'fixed_range' => 'Plage fixe',
                                    'easter_family' => 'Pâques',
                                    'fixed_anchor' => 'Ancre fixe'
                                } ?>
                            </span>
                        </div>

                        <div class="rule-config">
                            <?php if (!empty($rule['fixed_start_mmdd'])): ?>
                                Du <?= $rule['fixed_start_mmdd'] ?> au <?= $rule['fixed_end_mmdd'] ?>
                            <?php else: ?>
                                Ancre: <?= $rule['anchor'] ?> |
                                <?= $rule['start_offset_weeks'] ?> sem. avant → +<?= $rule['end_offset_weeks'] ?> sem. après
                            <?php endif ?>
                        </div>

                        <div class="rule-preview">
                            <?php if (isset($rule_previews[$rule['id']]['error'])): ?>
                                <div class="preview-error"><?= htmlspecialchars($rule_previews[$rule['id']]['error']) ?></div>
                            <?php else: ?>
                                <?php foreach ($rule_previews[$rule['id']]['ranges'] ?? [] as $range): ?>
                                    <span class="date-range"><?= date('d/m', strtotime($range[0])) ?> - <?= date('d/m', strtotime($range[1])) ?></span>
                                <?php endforeach ?>
                                <?php if ($rule_previews[$rule['id']]['total_weeks'] ?? 0): ?>
                                    <div class="preview-meta"><?= $rule_previews[$rule['id']]['total_weeks'] ?> semaines au total</div>
                                <?php endif ?>
                            <?php endif ?>
                        </div>

                        <div class="rule-actions">
                            <button onclick="toggleEdit(<?= $rule['id'] ?>)" class="btn-primary edit-btn">Modifier</button>

                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="id" value="<?= $rule['id'] ?>">
                                <button class="<?= $rule['active'] ? 'btn-secondary' : 'btn-success' ?>">
                                    <?= $rule['active'] ? 'Désactiver' : 'Activer' ?>
                                </button>
                                <?= csrf_field() ?>
                            </form>
                        </div>
                    </div>

                    <form method="post" class="rule-form" id="form-<?= $rule['id'] ?>">
                        <input type="hidden" name="action" value="save_rule">
                        <input type="hidden" name="id" value="<?= $rule['id'] ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Nom de la règle</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($rule['name']) ?>" required>
                            </div>

                            <div class="type-tabs">
                                <button type="button" class="type-tab <?= getRuleType($rule) === 'fixed_range' ? 'active' : '' ?>" data-type="fixed_range">Plage Fixe</button>
                                <button type="button" class="type-tab <?= getRuleType($rule) === 'easter_family' ? 'active' : '' ?>" data-type="easter_family">Pâques</button>
                                <button type="button" class="type-tab <?= getRuleType($rule) === 'fixed_anchor' ? 'active' : '' ?>" data-type="fixed_anchor">Ancre Fixe</button>
                            </div>

                            <input type="hidden" name="rule_type" value="<?= getRuleType($rule) ?>">

                            <div id="edit-<?= $rule['id'] ?>-fixed_range" class="config-section <?= getRuleType($rule) === 'fixed_range' ? 'active' : '' ?>">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Date début (MM-DD)</label>
                                        <input type="text" name="fixed_start_mmdd" pattern="\d{2}-\d{2}" value="<?= $rule['fixed_start_mmdd'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Date fin (MM-DD)</label>
                                        <input type="text" name="fixed_end_mmdd" pattern="\d{2}-\d{2}" value="<?= $rule['fixed_end_mmdd'] ?>">
                                    </div>
                                </div>
                            </div>

                            <div id="edit-<?= $rule['id'] ?>-easter_family" class="config-section <?= getRuleType($rule) === 'easter_family' ? 'active' : '' ?>">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Événement</label>
                                        <select name="anchor">
                                            <option value="easter" <?= $rule['anchor'] === 'easter' ? 'selected' : '' ?>>Pâques</option>
                                            <option value="carnival" <?= $rule['anchor'] === 'carnival' ? 'selected' : '' ?>>Carnaval</option>
                                            <option value="ascension" <?= $rule['anchor'] === 'ascension' ? 'selected' : '' ?>>Ascension</option>
                                            <option value="pentecost" <?= $rule['anchor'] === 'pentecost' ? 'selected' : '' ?>>Pentecôte</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Semaines avant</label>
                                        <input type="number" name="start_offset_weeks" min="0" value="<?= $rule['start_offset_weeks'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Semaines après</label>
                                        <input type="number" name="end_offset_weeks" min="0" value="<?= $rule['end_offset_weeks'] ?>">
                                    </div>
                                </div>
                            </div>

                            <div id="edit-<?= $rule['id'] ?>-fixed_anchor" class="config-section <?= getRuleType($rule) === 'fixed_anchor' ? 'active' : '' ?>">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Date ancre (MM-DD)</label>
                                        <input type="text" name="anchor" pattern="\d{2}-\d{2}" value="<?= $rule['anchor'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Semaines avant</label>
                                        <input type="number" name="start_offset_weeks" min="0" value="<?= $rule['start_offset_weeks'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Semaines après</label>
                                        <input type="number" name="end_offset_weeks" min="0" value="<?= $rule['end_offset_weeks'] ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="active" <?= $rule['active'] ? 'checked' : '' ?>> Règle active
                                </label>
                            </div>

                            <div class="preview-box">
                                <strong>Aperçu <?= $current_year ?>:</strong>
                                <div class="preview-content">...</div>
                            </div>

                            <div class="rule-actions">
                                <button type="submit" class="btn-primary">Enregistrer</button>
                                <button type="button" onclick="cancelEdit(<?= $rule['id'] ?>)" class="btn-secondary">Annuler</button>
                            </div>
                        </div>

                        <?= csrf_field() ?>
                    </form>
                </div>
            <?php endforeach ?>
        </div>
    </main>

    <script>
        function toggleNewRule() {
            const card = document.getElementById('new-rule-card');
            const prompt = document.getElementById('new-rule-prompt');
            const form = document.getElementById('new-rule-form');

            card.classList.add('active');
            prompt.style.display = 'none';
            form.style.display = 'block';
            updatePreview(form);
        }

        function cancelNewRule() {
            const card = document.getElementById('new-rule-card');
            const prompt = document.getElementById('new-rule-prompt');
            const form = document.getElementById('new-rule-form');

            card.classList.remove('active');
            prompt.style.display = 'block';
            form.style.display = 'none';
            form.reset();
        }

        function toggleEdit(ruleId) {
            const card = document.getElementById(`rule-${ruleId}`);
            const form = document.getElementById(`form-${ruleId}`);

            card.classList.add('editing');
            form.classList.add('active');
            updatePreview(form);
        }

        function cancelEdit(ruleId) {
            const card = document.getElementById(`rule-${ruleId}`);
            const form = document.getElementById(`form-${ruleId}`);

            card.classList.remove('editing');
            form.classList.remove('active');
        }

        // Tab switching for all forms
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('type-tab')) {
                const form = e.target.closest('form');
                const type = e.target.dataset.type;
                const formId = form.id.replace('form-', '').replace('new-rule-form', 'new');

                // Update tabs
                form.querySelectorAll('.type-tab').forEach(tab => tab.classList.remove('active'));
                e.target.classList.add('active');

                // Update sections
                form.querySelectorAll('.config-section').forEach(section => section.classList.remove('active'));
                document.getElementById(`${formId}-${type}`).classList.add('active');

                // Update hidden field
                form.querySelector('input[name="rule_type"]').value = type;

                updatePreview(form);
            }
        });

        // Live preview for all forms
        document.addEventListener('input', (e) => {
            if (e.target.closest('form')) {
                updatePreview(e.target.closest('form'));
            }
        });

        async function updatePreview(form) {
            const formData = new FormData(form);
            const params = new URLSearchParams();

            params.append('preview', '1');
            params.append('year', <?= $current_year ?>);
            params.append('name', formData.get('name') || 'Preview');

            const ruleType = formData.get('rule_type');
            if (ruleType === 'fixed_range') {
                params.append('fixed_start_mmdd', formData.get('fixed_start_mmdd') || '');
                params.append('fixed_end_mmdd', formData.get('fixed_end_mmdd') || '');
            } else {
                params.append('anchor', formData.get('anchor') || '');
                params.append('start_offset_weeks', formData.get('start_offset_weeks') || '0');
                params.append('end_offset_weeks', formData.get('end_offset_weeks') || '0');
            }

            try {
                const response = await fetch(`/admin/seasons?${params}`);
                const data = await response.json();

                const content = form.querySelector('.preview-content');
                if (data.error) {
                    content.innerHTML = `<div class="preview-error">${data.error}</div>`;
                } else if (data.ranges?.length) {
                    content.innerHTML = data.ranges.map(range =>
                        `<span class="date-range">${new Date(range[0]).toLocaleDateString('fr-FR')} - ${new Date(range[1]).toLocaleDateString('fr-FR')}</span>`
                    ).join('') + (data.total_weeks ? `<div class="preview-meta">${data.total_weeks} semaines au total</div>` : '');
                } else {
                    content.innerHTML = 'Aucune date générée';
                }
            } catch (e) {
                form.querySelector('.preview-content').innerHTML = `<div class="preview-error">Erreur: ${e.message}</div>`;
            }
        }
    </script>
</body>

</html>