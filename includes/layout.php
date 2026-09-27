<?php

declare(strict_types=1);

function page_header(string $title, string $base = '../'): void
{
    $user = current_user();
    $home = $base . 'public/index.php';
    $jobs = $base . 'public/jobs.php';
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . e($title) . ' | SkillGate</title><link rel="stylesheet" href="' . $base . 'assets/css/style.css"><link href="https://api.fontshare.com/v2/css?f[]=cabinet-grotesk@800,700,500,400&f[]=satoshi@900,700,500,400&display=swap" rel="stylesheet"><link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet"><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"></head><body>';
    echo '<a class="skip-link" href="#main-content">Skip to content</a><header class="site-header"><a class="brand" href="' . $home . '">SkillGate</a><nav aria-label="Main navigation"><a href="' . $jobs . '">Jobs</a>';
    if ($user) {
        echo '<a href="' . $base . substr(dashboard_path($user['role']), 3) . '">Dashboard</a>';
        if ($user['role'] === 'seeker') {
            echo '<a href="' . $base . 'seeker/applications.php">My applications</a><a href="' . $base . 'seeker/profile.php">My profile</a>';
        } elseif ($user['role'] === 'employer') {
            echo '<a href="' . $base . 'employer/jobs.php">My jobs</a>';
        }
        echo '<a href="' . $base . 'public/logout.php">Logout</a>';
    } else {
        echo '<a href="' . $base . 'public/login.php">Login</a><a class="button small" href="' . $base . 'public/register.php">Get started</a>';
    }
    echo '</nav></header><main class="container" id="main-content">';
    foreach (pull_flashes() as $flash) {
        echo '<div class="notice ' . e($flash['type']) . '" role="status">' . e($flash['message']) . '</div>';
    }
}

function page_footer(): void
{
    echo '</main><footer class="site-footer">SkillGate - Job Grooming &amp; Placement Platform</footer><script src="../assets/js/main.js"></script></body></html>';
}

function status_label(string $status): string
{
    return e(ucwords(str_replace('_', ' ', $status)));
}

function status_badge(string $status): string
{
    $formatted = e(ucwords(str_replace('_', ' ', $status)));
    return '<span class="badge badge-' . e(str_replace('_', '-', strtolower($status))) . '">' . $formatted . '</span>';
}

function passed_badge(bool $passed): string
{
    if ($passed) {
        return '<span class="badge badge-passed"><span class="material-symbols-outlined">check_circle</span> Passed</span>';
    }
    return '<span class="badge badge-not-passed"><span class="material-symbols-outlined">cancel</span> Not Passed</span>';
}
