<?php

declare(strict_types=1);

session_name('job_placement_session');
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';