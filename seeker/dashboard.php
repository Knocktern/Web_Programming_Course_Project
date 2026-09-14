<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$user = require_login('seeker');
$pdo = database();
$counts = [];
foreach (['applications' => 'SELECT COUNT(*) FROM applications WHERE seeker_id=?','attempts' => 'SELECT COUNT(*) FROM quiz_attempts WHERE seeker_id=?','courses' => 'SELECT COUNT(*) FROM course_completions WHERE seeker_id=?'] as $key => $sql) {
    $s = $pdo->prepare($sql);
    $s->execute([$user['id']]);
    $counts[$key] = $s->fetchColumn();
}
$recent = $pdo->prepare('SELECT a.status,a.applied_at,j.title FROM applications a JOIN jobs j ON j.id=a.job_id WHERE a.seeker_id=? ORDER BY a.applied_at DESC LIMIT 5');
$recent->execute([$user['id']]);
$quizzes = $pdo->prepare('SELECT qa.percentage,qa.passed,qa.attempted_at,j.title,j.id job_id FROM quiz_attempts qa JOIN jobs j ON j.id=qa.job_id WHERE qa.seeker_id=? ORDER BY qa.attempted_at DESC LIMIT 5');
$quizzes->execute([$user['id']]);
page_header('Seeker dashboard'); ?>
<h1>Your career dashboard</h1>
<section class="stats">
    <div class="stat"><strong><?= $counts['applications'] ?></strong>Applications</div>
    <div class="stat"><strong><?= $counts['attempts'] ?></strong>Quiz attempts</div>
    <div class="stat"><strong><?= $counts['courses'] ?></strong>Courses completed</div>
</section>
<p>
    <a class="button" href="profile.php">Build profile</a>
    <a class="button secondary" href="cv-preview.php">View CV</a>
    <a class="button secondary" href="applications.php">My applications</a>
    <a class="button secondary" href="../public/jobs.php">Browse jobs</a>
</p>
<h2>Recent applications</h2>
<table>
    <tr><th>Job</th><th>Status</th><th>Date</th></tr>
    <?php foreach ($recent as $item): ?>
    <tr>
        <td><?= e($item['title']) ?></td>
        <td><?= status_badge($item['status']) ?></td>
        <td><?= e($item['applied_at']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php if (!$recent->rowCount() && empty($counts['applications'])): ?>
    <p class="meta">No applications yet. Browse jobs to get started.</p>
<?php endif; ?>

<h2>Recent quiz attempts</h2>
<table>
    <tr><th>Job</th><th>Score</th><th>Result</th><th>Date</th></tr>
    <?php foreach ($quizzes as $q): ?>
    <tr>
        <td><?= e($q['title']) ?></td>
        <td><?= e($q['percentage']) ?>%</td>
        <td><?= passed_badge((bool)$q['passed']) ?></td>
        <td><?= e($q['attempted_at']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php page_footer(); ?>