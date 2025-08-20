<?php
// app/io/route/admin/seasons.php

$error = null;
$success = null;

if ($_POST) {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'save_rule':
                $id = $_POST['id'] ?? null;
                $type = $_POST['rule_type'];

                $fields = [
                    'name' => trim($_POST['name']),
                    'active' => isset($_POST['active']) ? 1 : 0,
                    'anchor' => null,
                    'start_offset_weeks' => 0,
                    'end_offset_weeks' => 0,
                    'fixed_start_mmdd' => null,
                    'fixed_end_mmdd' => null
                ];

                switch ($type) {
                    case 'fixed_range':
                        $fields['fixed_start_mmdd'] = $_POST['fixed_start_mmdd'];
                        $fields['fixed_end_mmdd'] = $_POST['fixed_end_mmdd'];
                        break;
                    case 'easter_family':
                        $fields['anchor'] = $_POST['anchor'];
                        $fields['start_offset_weeks'] = (int)$_POST['start_offset_weeks'];
                        $fields['end_offset_weeks'] = (int)$_POST['end_offset_weeks'];
                        break;
                    case 'fixed_anchor':
                        $fields['anchor'] = $_POST['anchor'];
                        $fields['start_offset_weeks'] = (int)$_POST['start_offset_weeks'];
                        $fields['end_offset_weeks'] = (int)$_POST['end_offset_weeks'];
                        break;
                }

                if ($id) {
                    $sets = array_map(fn($k) => "$k = ?", array_keys($fields));
                    qp(db(), "UPDATE season_rules SET " . implode(', ', $sets) . " WHERE id = ?", [...array_values($fields), $id]);
                    $success = "Règle modifiée";
                } else {
                    $placeholders = str_repeat('?,', count($fields) - 1) . '?';
                    qp(db(), "INSERT INTO season_rules (" . implode(',', array_keys($fields)) . ") VALUES ($placeholders)", array_values($fields));
                    $success = "Règle créée";
                }
                break;

            case 'toggle_active':
                $id = (int)$_POST['id'];
                qp(db(), "UPDATE season_rules SET active = NOT active WHERE id = ?", [$id]);
                $success = "Statut modifié";
                break;
        }

        header('Location: /admin/seasons');
        exit;
    } catch (PDOException $e) {
        $error = 'Erreur: ' . $e->getMessage();
    }
}

// Preview endpoint
if (isset($_GET['preview'])) {
    $year = (int)($_GET['year'] ?? date('Y'));
    $rule = [
        'name' => $_GET['name'] ?? 'Preview',
        'anchor' => $_GET['anchor'] ?? null,
        'start_offset_weeks' => (int)($_GET['start_offset_weeks'] ?? 0),
        'end_offset_weeks' => (int)($_GET['end_offset_weeks'] ?? 0),
        'fixed_start_mmdd' => $_GET['fixed_start_mmdd'] ?? null,
        'fixed_end_mmdd' => $_GET['fixed_end_mmdd'] ?? null,
    ];

    http_out(200, json_encode(previewRule($rule, $year)), ['Content-Type' => 'application/json']);
    exit;
}

$rules = db()->query("SELECT * FROM season_rules ORDER BY active DESC, name")->fetchAll();
$current_year = (int)date('Y');

// Generate previews
$rule_previews = [];
foreach ($rules as $rule) {
    $rule_previews[$rule['id']] = previewRule($rule, $current_year);
}

return compact('rules', 'rule_previews', 'current_year', 'error', 'success');

function previewRule(array $rule, int $year): array
{
    try {
        $ranges = [];

        if (!empty($rule['fixed_start_mmdd']) && !empty($rule['fixed_end_mmdd'])) {
            [$sm, $sd] = array_map('intval', explode('-', $rule['fixed_start_mmdd']));
            [$em, $ed] = array_map('intval', explode('-', $rule['fixed_end_mmdd']));

            $start = (new DateTime())->setDate($year, $sm, $sd);
            $endYear = ($em * 100 + $ed) < ($sm * 100 + $sd) ? $year + 1 : $year;
            $end = (new DateTime())->setDate($endYear, $em, $ed);

            $ranges[] = [$start->format('Y-m-d'), $end->format('Y-m-d')];
        } else if (!empty($rule['anchor'])) {
            $anchor = anchorDateFromRule($rule, $year);
            if ($anchor) {
                $start = (clone $anchor)->modify(sprintf('-%d weeks', (int)$rule['start_offset_weeks']));
                $end = (clone $anchor)->modify(sprintf('+%d weeks', (int)$rule['end_offset_weeks']));

                $ranges[] = [alignToMonday($start)->format('Y-m-d'), $end->format('Y-m-d')];
            }
        }

        return [
            'ranges' => $ranges,
            'anchor_date' => isset($anchor) ? $anchor->format('Y-m-d') : null,
            'total_weeks' => array_sum(array_map(function ($range) {
                $start = new DateTime($range[0]);
                $end = new DateTime($range[1]);
                return (int)($start->diff($end)->days / 7);
            }, $ranges))
        ];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function getRuleType(array $rule): string
{
    if (!empty($rule['fixed_start_mmdd'])) return 'fixed_range';
    if (in_array($rule['anchor'], ['easter', 'carnival', 'ascension', 'pentecost'])) return 'easter_family';
    return 'fixed_anchor';
}
