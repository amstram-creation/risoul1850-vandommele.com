<?php

function rangeOfWeeksFrom(DateTime $date, int $weeksAhead, int $lowSeasonPrice, int $highSeasonPrice): array
{
    $weeks = [];
    $after = $date->format('Y-m-d');

    // init a full year starting from the given date
    for ($i = 0, $looper = clone $date; $i < $weeksAhead; ++$i, $looper->modify('+1 week'))
        $weeks[$looper->format('Y-m-d')] = ['is_high_season' => 0, 'confirmed' => 0, 'price' => $lowSeasonPrice];

    // mark high-season weeks
    $highSeasonWeeks = array_merge(highSeasonWeeks(db(),(int)$date->format('Y')), highSeasonWeeks(db(),(int)$date->modify('+1 year')->format('Y')));
    foreach ($highSeasonWeeks as $range) {
        for ($d = new DateTime($range[0]); $d <= new DateTime($range[1]); $d->modify('+1 week')) {
            $key = $d->format('Y-m-d');
            if (isset($weeks[$key])) {
                $weeks[$key]['is_high_season'] = 1;
                $weeks[$key]['price'] = $highSeasonPrice;
            }
        }
    }

    // Fetch next $weeksAhead weeks strictly after cursor
    $sql = "SELECT week_start, price, confirmed FROM week WHERE week_start >= ? ORDER BY week_start ASC LIMIT $weeksAhead";
    $rows = qp(db(), $sql, [$after])->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $weeks[$r['week_start']] = [
            'price' => $r['price'] ? (float)$r['price'] : null,
            'confirmed' => $r['confirmed'] ?? null, // NULL | 0 | 1
        ];
    }

    return $weeks;
}

function alignToMonday(DateTime $date): DateTime
{
    if ($date->format('N') != 1)
        $date->modify('next monday');
    return $date;
}






function anchorDateFromRule(array $r, int $year): ?DateTime
{
    $key = isset($r['anchor']) ? strtolower(trim($r['anchor'])) : '';

    // B) Easter-family (anchor === 'easter'), "which day?" decided by the row name
    if ($key === 'easter') {
        $name = strtolower(trim($r['name']));
        $easter = (new DateTime("$year-03-21 00:00:00"))->modify('+' . easter_days($year) . ' days');

        // Offsets in DAYS relative to Easter Sunday:
        $dayOffsets = [
            'easter' => 0,
            'carnival' => -49, // 7 weeks before
            'ascension' => 39, // 39 days after
            'pentecost' => 49, // 49 days after
        ];

        if (!array_key_exists($name, $dayOffsets)) return null;
        return (clone $easter)->modify(($dayOffsets[$name] >= 0 ? '+' : '') . $dayOffsets[$name] . ' days');
    }

    // A) Fixed-date anchor 'MM-DD'
    if ($key && preg_match('/^\d{2}-\d{2}$/', $key)) {
        [$mm, $dd] = array_map('intval', explode('-', $key));
        return (new DateTime())->setDate($year, $mm, $dd)->setTime(0, 0, 0);
    }

    // C) No single-day anchor (maybe it's a fixed range only)
    return null;
}

function highSeasonWeeks(PDO $pdo, int $year): array
{

    $rules = $pdo->query("SELECT * FROM season_rules WHERE active = 1 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    $ranges = [];

    foreach ($rules as $r) {
        // 1) Pure fixed range?
        if (!empty($r['fixed_start_mmdd']) && !empty($r['fixed_end_mmdd'])) {
            [$sm, $sd] = array_map('intval', explode('-', $r['fixed_start_mmdd']));
            [$em, $ed] = array_map('intval', explode('-', $r['fixed_end_mmdd']));

            $start = (new DateTime())->setDate($year, $sm, $sd)->setTime(0, 0, 0);
            $endYear = ($em * 100 + $ed) < ($sm * 100 + $sd) ? $year + 1 : $year;
            $end = (new DateTime())->setDate($endYear, $em, $ed)->setTime(0, 0, 0);

            $ranges[] = [alignToMonday($start)->format('Y-m-d'), $end->format('Y-m-d')];
            continue;
        }

        // 2) Single-day anchor + week offsets
        $anchor = anchorDateFromRule($r, $year);
        if (!$anchor) continue;

        $start = (clone $anchor)->modify(sprintf('-%d weeks', (int)$r['start_offset_weeks']));
        $end = (clone $anchor)->modify(sprintf('+%d weeks', (int)$r['end_offset_weeks']));

        $ranges[] = [alignToMonday($start)->format('Y-m-d'), $end->format('Y-m-d')];
    }

    return $ranges;
}
