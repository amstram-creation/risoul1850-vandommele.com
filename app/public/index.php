<?php

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../..');


require 'add/badhat/build.php';
require 'add/badhat/error.php';
require 'add/badhat/core.php';
require 'add/badhat/db.php';
// require 'add/badhat/auth.php';
// require 'add/badhat/trust.php';
// require 'app/morph/html.php';

// vd(qp(db(),"INSERT INTO operator (label, username, password_hash, status) VALUES (?, ?, ?, 1)",['jp', 'jp', password_hash('jp', PASSWORD_DEFAULT)]));die;
try {
    // auth(AUTH_SETUP, 'operator.username', qp(db(), "SELECT `password_hash` FROM `operator` WHERE `username` = ?"));
    // l('fra'); // load french language

    $io = __DIR__ . '/../io';
    $in_path    = $io . '/route';
    $out_path   = $io . '/render';

    $re_quest   = http_in();
    $request_admin = strpos($re_quest, '/admin') === 0;
    $request_admin && auth(AUTH_GUARD, '/login');

    // business: find the route and invoke it
    [$route_path, $args]   = io_map($in_path, $re_quest, 'php', IO_DEEP | IO_FLEX) ?: io_map($in_path, 'index');
    $in_quest      = io_run($route_path, $args ?? [], IO_INVOKE);
    // i18n: load french language

    // render: match route file and absorb it when possible
    [$render_path, $args]   = io_map($out_path, $re_quest, 'php', IO_DEEP | IO_FLEX) ?: io_map($out_path, 'index');

    // $render_path = str_replace($in_path, $out_path, $route_path) ?? '';
    $out_quest  = io_run($render_path, $in_quest[IO_INVOKE] ?? [], IO_ABSORB);

    // absorption is optional, http_body() settles the output
    if (is_string($out_quest[IO_BUFFER]) || is_string($out_quest[IO_ABSORB])) {
        http_out(200, http_body($out_quest, $request_admin), ['Content-Type' => 'text/html; charset=utf-8']);
        exit;
    }

    error_log('404 Not Found for ' . $re_quest);
    http_out(404, 'Not Found at all');
} catch (LogicException | RuntimeException $t) {
    vd(-1, $t);
    handle_badhat_exception($t);
    header('HTTP/1.1 500 Forbidden');
} catch (Throwable $t) {
    vd(-1, $t);
    // out quest that fetch an error page within the layout, firsttests if error page has 200 
    die;
}

function http_body(array $out_quest, bool $request_admin): string
{
    if (isset($out_quest[IO_ABSORB]))
        return $out_quest[IO_ABSORB];

    $template = $request_admin ? 'app/io/render/admin/layout.php' : 'app/io/render/layout.php';
    return ob_ret_get($template, ['main' => $out_quest[IO_BUFFER]])[1];
}

function handle_badhat_exception(Throwable $t): void
{
    if ((int)$t->getCode() === 403) {
        $u = parse_url($_SERVER['HTTP_REFERER'] ?? '');
        parse_str($u['query'] ?? '', $q);

        if (isset($q['error'])) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $q['error'] = $t->getMessage();
        header('Location: ' . ($u['path'] ?: '/') . '?' . http_build_query($q) . (isset($u['fragment']) ? "#{$u['fragment']}" : ''));
        exit;
    }

    if ((int)$t->getCode() === 401) {
        header('Location: /login');
        exit;
    }
}
