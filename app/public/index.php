<?php

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../..');

require 'add/badhat/build.php';
require 'add/badhat/error.php';
require 'add/badhat/core.php';
require 'add/badhat/db.php';
require 'add/badhat/auth.php';

try {
    auth(AUTH_SETUP, 'username', qp(db(), "SELECT password_hash FROM operator WHERE username = ? AND status = 1", null));

    $io = __DIR__ . '/../io';
    $in_path    = $io . '/route';
    $out_path   = $io . '/render';

    $re_quest   = http_in();
    $request_admin = strpos($re_quest, '/admin') === 0;
    $request_admin && auth(AUTH_GUARD, '/login');

    // business: find the route and invoke it
    [$route_path, $args]   = io_map($in_path, $re_quest, 'php', IO_FLEX) ?: io_map($in_path, 'index');
    $in_quest = $route_path ? io_run($route_path, []) : [];
    [$render_path, $args]   = io_map($out_path, $re_quest, 'php', IO_DEEP | IO_FLEX) ?: io_map($out_path, 'index');
    $out_quest  = io_run($render_path,  $in_quest[IO_RETURN] ?? [], IO_BUFFER);

    if (is_string($out_quest[IO_BUFFER]) || is_string($out_quest[IO_ABSORB])) {
        http_out(200, $out_quest[IO_BUFFER], ['Content-Type' => 'text/html; charset=utf-8']);
        exit;
    }

    error_log('404 Not Found for ' . $re_quest);
    http_out(404, 'Not Found at all');
} catch (LogicException | RuntimeException $t) {
    vd(-1, $t);
    header('HTTP/1.1 500 Forbidden');
} catch (Throwable $t) {
    vd(-1, $t);
    // out quest that fetch an error page within the layout, firsttests if error page has 200 
    die;
}

function rangeOfWeeksFrom(DateTime $date, int $weeksAhead, int $lowSeasonPrice, int $highSeasonPrice): array
{
    $weeks = [];
    $after = $date->format('Y-m-d');

    // init a full year starting from the given date
    for ($i = 0, $looper = clone $date; $i < $weeksAhead; ++$i, $looper->modify('+1 week'))
        $weeks[$looper->format('Y-m-d')] = ['is_high_season' => 0, 'confirmed' => 0, 'price' => $lowSeasonPrice];

    // mark high-season weeks
    $highSeasonWeeks = array_merge(highSeasonWeeks((int)$date->format('Y')), highSeasonWeeks((int)$date->modify('+1 year')->format('Y')));
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
    $sql  = "SELECT week_start, price, confirmed FROM week WHERE week_start >= ? ORDER BY week_start ASC LIMIT $weeksAhead";
    $rows = qp(db(), $sql, [$after])->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $weeks[$r['week_start']] = [
            'price'     => $r['price'] ? (float)$r['price'] : null,
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

function highSeasonWeeks(int $year): array
{
    // Western Easter for PHP
    $easter   = new DateTime("$year-03-21 +" . easter_days($year) . " days");
    $carnival = (clone $easter)->modify('-7 weeks');

    $ranges = [];

    // Summer: mid June - mid September
    $ranges[] = [alignToMonday(new DateTime("$year-06-16"))->format('Y-m-d'), alignToMonday(new DateTime("$year-09-15"))->format('Y-m-d')];

    // Easter: 3 weeks before/after
    $easterStart = alignToMonday((clone $easter)->modify('-3 weeks'));
    $easterEnd   = (clone $easter)->modify('+3 weeks');
    $ranges[] = [$easterStart->format('Y-m-d'), $easterEnd->format('Y-m-d')];

    // Carnival: 3 weeks before/after
    $carnivalStart = alignToMonday((clone $carnival)->modify('-3 weeks'));
    $carnivalEnd   = (clone $carnival)->modify('+3 weeks');
    $ranges[] = [$carnivalStart->format('Y-m-d'), $carnivalEnd->format('Y-m-d')];

    // Winter: 3 weeks before Christmas - 3 weeks after New Year
    $winterStart = alignToMonday(new DateTime("$year-12-04"));
    $winterEnd   = new DateTime(($year + 1) . "-01-22");
    $ranges[] = [$winterStart->format('Y-m-d'), $winterEnd->format('Y-m-d')];

    return $ranges;
}
