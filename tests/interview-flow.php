<?php
// Run with: php tests/interview-flow.php
// Uses one existing application; every database change is rolled back.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$cases = ['online-save', 'office-save', 'invalid-link', 'invalid-date', 'missing-location', 'dashboard', 'office-history', 'pending-history', 'status-schedule', 'unsafe-link', 'employer-ownership', 'seeker-ownership'];
if (!isset($argv[1])) {
    $failed = false;
    foreach ($cases as $case) {
        $process = proc_open([PHP_BINARY, __FILE__, $case], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        echo stream_get_contents($pipes[1]), stream_get_contents($pipes[2]);
        $failed = proc_close($process) !== 0 || $failed;
    }
    exit($failed ? 1 : 0);
}
set_error_handler(static function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
session_id('interview-check-' . bin2hex(random_bytes(8)));
require __DIR__ . '/../includes/bootstrap.php';
$pdo = database();
$fixture = $pdo->query('SELECT a.*,j.employer_id FROM applications a JOIN jobs j ON j.id=a.job_id ORDER BY a.id LIMIT 1')->fetch();
if (!$fixture) { throw new RuntimeException('An existing application is needed for this rollback-only check.'); }
$case = $argv[1];
$pdo->beginTransaction();
$future = date('Y-m-d\TH:i', time() + 7 * 86400);
$url = 'https://example.com/meeting?code=demo&candidate=test';
$location = 'Demo office, 12 Example Road, floor 4, room 402';
$notes = "Bring your CV.\nAsk for the hiring team. <script>unsafe</script>";
$_SESSION['user_id'] = $fixture['employer_id'];
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET = ['application_id' => $fixture['id'], 'job_id' => $fixture['job_id']];
$_POST = ['csrf_token' => csrf_token(), 'application_id' => $fixture['id'], 'job_id' => $fixture['job_id'], 'interview_mode' => 'online', 'interview_at' => $future, 'meeting_url' => $url, 'interview_location' => $location, 'interview_notes' => $notes];
$page = 'employer/applicant-profile.php';
if ($case === 'office-save') { $_POST['interview_mode'] = 'office'; }
if ($case === 'invalid-link') { $_POST['meeting_url'] = 'javascript:alert(1)'; }
if ($case === 'invalid-date') { $_POST['interview_at'] = '2030-02-30T12:00'; }
if ($case === 'missing-location') { $_POST['interview_mode'] = 'office'; $_POST['interview_location'] = ''; }
if (in_array($case, ['dashboard', 'office-history', 'pending-history', 'unsafe-link', 'seeker-ownership'], true)) {
    $_SESSION['user_id'] = $fixture['seeker_id'];
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_POST = []; $_GET = [];
    $pdo->prepare("UPDATE applications SET status='interview',interview_mode=?,interview_at=?,meeting_url=?,interview_location=?,interview_notes=? WHERE id=?")
        ->execute([$case === 'office-history' ? 'office' : 'online', $case === 'pending-history' ? null : str_replace('T', ' ', $future), $case === 'unsafe-link' ? 'javascript:alert(1)' : ($case === 'office-history' ? null : $url), $case === 'office-history' ? $location : null, $notes, $fixture['id']]);
    $page = $case === 'dashboard' ? 'seeker/dashboard.php' : 'seeker/applications.php';
}
if ($case === 'status-schedule') { $_POST['status'] = 'interview'; $page = 'employer/applicants.php'; }
if ($case === 'employer-ownership' || $case === 'seeker-ownership') {
    $role = $case === 'employer-ownership' ? 'employer' : 'seeker';
    $ownerId = $role === 'employer' ? $fixture['employer_id'] : $fixture['seeker_id'];
    $other = $pdo->prepare('SELECT id FROM users WHERE role=? AND id<>? AND is_active=1 LIMIT 1');
    $other->execute([$role, $ownerId]);
    $otherId = $other->fetchColumn();
    if (!$otherId) { $pdo->rollBack(); session_destroy(); echo 'SKIP ' . $case . ' (needs a second ' . $role . ')' . PHP_EOL; exit; }
    $_SESSION['user_id'] = $otherId;
}
ob_start();
register_shutdown_function(static function () use ($pdo, $case, $fixture, $url, $location, $notes, $future) {
    $html = ob_get_clean();
    $s = $pdo->prepare('SELECT * FROM applications WHERE id=?'); $s->execute([$fixture['id']]); $saved = $s->fetch();
    $ok = !error_get_last();
    if ($case === 'online-save' || $case === 'office-save') {
        $ok = $ok && $saved['status'] === 'interview' && $saved['interview_notes'] === $notes
            && $saved['interview_at'] === str_replace('T', ' ', $future) . ':00';
        $ok = $ok && ($case === 'online-save' ? $saved['meeting_url'] === $url && $saved['interview_location'] === null : $saved['interview_location'] === $location && $saved['meeting_url'] === null);
    } elseif ($case === 'invalid-link') {
        $ok = $ok && $saved === array_intersect_key($fixture, $saved) && str_contains($html, 'Enter an http:// or https://') && str_contains($html, 'javascript:alert(1)');
    } elseif ($case === 'invalid-date') {
        $ok = $ok && $saved === array_intersect_key($fixture, $saved) && str_contains($html, 'Choose a future interview date') && str_contains($html, '2030-02-30T12:00');
    } elseif ($case === 'missing-location') {
        $ok = $ok && $saved === array_intersect_key($fixture, $saved) && str_contains($html, 'Enter the office interview location');
    } elseif ($case === 'employer-ownership') {
        $ok = $ok && http_response_code() === 404 && $saved === array_intersect_key($fixture, $saved) && str_contains($html, 'Applicant not found.');
    } elseif ($case === 'seeker-ownership') {
        $ok = $ok && !str_contains($html, e($notes)) && !str_contains($html, 'Ask for the hiring team.') && !str_contains($html, 'id="application-' . $fixture['id'] . '"');
    } elseif ($case === 'status-schedule') {
        $ok = $ok && $saved === array_intersect_key($fixture, $saved) && $html === '';
    } else {
        $ok = $ok && str_contains($html, 'Instructions from the employer') && str_contains($html, '&lt;script&gt;unsafe&lt;/script&gt;') && !str_contains($html, '<script>unsafe</script>');
        if ($case === 'dashboard') $ok = $ok && str_contains($html, e($url)) && str_contains($html, 'Your interviews');
        if ($case === 'office-history') $ok = $ok && str_contains($html, $location) && str_contains($html, 'Office interview');
        if ($case === 'pending-history') $ok = $ok && str_contains($html, 'have not been shared yet');
        if ($case === 'unsafe-link') $ok = $ok && !str_contains($html, 'href="javascript:');
    }
    $pdo->rollBack(); session_destroy();
    echo ($ok ? 'PASS ' : 'FAIL ') . $case . PHP_EOL;
    if (!$ok) exit(1);
});
require __DIR__ . '/../' . $page;
