<?php

declare(strict_types=1);

function page_header(string $title, string $base = '../'): void
{
    $user = current_user();
    $home = $base . 'public/index.php';
    $jobs = $base . 'public/jobs.php';
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . e($title) . ' | JobPath</title><link rel="stylesheet" href="' . $base . 'assets/css/style.css"></head><body>';
    echo '<header class="site-header"><a class="brand" href="' . $home . '">JobPath</a><nav><a href="' . $jobs . '">Jobs</a>';
    if ($user) {
        echo '<a href="' . $base . substr(dashboard_path($user['role']), 3) . '">Dashboard</a><a href="' . $base . 'public/logout.php">Logout</a>';
    } else {
        echo '<a href="' . $base . 'public/login.php">Login</a><a class="button small" href="' . $base . 'public/register.php">Get started</a>';
    }
    echo '</nav></header><main class="container">';
    foreach (pull_flashes() as $flash) {
        echo '<div class="notice ' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
    }
}

function page_footer(): void
{
    echo '</main><footer class="site-footer">JobPath - Job Grooming &amp; Placement Platform</footer><script src="../assets/js/main.js"></script></body></html>';
}

function status_label(string $status): string
{
    return e(ucwords(str_replace('_', ' ', $status)));
}