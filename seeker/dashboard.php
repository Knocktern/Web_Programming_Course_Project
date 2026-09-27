<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$user = require_login('seeker');
$pdo = database();

// Profile info
$s = $pdo->prepare('SELECT js.*, u.email FROM job_seekers js JOIN users u ON u.id=js.user_id WHERE js.user_id=?');
$s->execute([$user['id']]);
$profile = $s->fetch();

// Stats
$counts = [];
foreach (['applications' => 'SELECT COUNT(*) FROM applications WHERE seeker_id=?','attempts' => 'SELECT COUNT(*) FROM quiz_attempts WHERE seeker_id=?','courses' => 'SELECT COUNT(*) FROM course_completions WHERE seeker_id=?'] as $key => $sql) {
    $st = $pdo->prepare($sql);
    $st->execute([$user['id']]);
    $counts[$key] = $st->fetchColumn();
}

// Skills
$skills = $pdo->prepare('SELECT skill_name, skill_level FROM job_seeker_skills WHERE seeker_id=?');
$skills->execute([$user['id']]);
$skillList = $skills->fetchAll();

// Recent applications
$recent = $pdo->prepare('SELECT a.status,a.applied_at,j.title FROM applications a JOIN jobs j ON j.id=a.job_id WHERE a.seeker_id=? ORDER BY a.applied_at DESC LIMIT 5');
$recent->execute([$user['id']]);
$recentApps = $recent->fetchAll();

// Recent quizzes
$quizzes = $pdo->prepare('SELECT qa.percentage,qa.passed,qa.attempted_at,j.title,j.id job_id FROM quiz_attempts qa JOIN jobs j ON j.id=qa.job_id WHERE qa.seeker_id=? ORDER BY qa.attempted_at DESC LIMIT 5');
$quizzes->execute([$user['id']]);
$quizList = $quizzes->fetchAll();

page_header('Seeker dashboard'); ?>
<h1>Welcome, <?= e($profile['full_name']) ?></h1>

<article class="card">
    <h2>Your profile</h2>
    <p><strong>Email:</strong> <?= e($profile['email']) ?></p>
    <?php if ($profile['phone']): ?><p><strong>Phone:</strong> <?= e($profile['phone']) ?></p><?php endif; ?>
    <?php if ($profile['address']): ?><p><strong>Address:</strong> <?= e($profile['address']) ?></p><?php endif; ?>
    <?php if ($profile['profile_summary']): ?><p><?= nl2br(e($profile['profile_summary'])) ?></p><?php endif; ?>
    <?php if ($skillList): ?>
        <p><strong>Skills:</strong>
        <?php foreach ($skillList as $sk): ?>
            <span class="badge"><?= e($sk['skill_name']) ?> (<?= e($sk['skill_level']) ?>)</span>
        <?php endforeach; ?></p>
    <?php endif; ?>
    <p><a href="profile.php">Edit profile</a></p>
</article>

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
<?php if ($recentApps): ?>
<table>
    <tr><th>Job</th><th>Status</th><th>Date</th></tr>
    <?php foreach ($recentApps as $item): ?>
    <tr>
        <td><?= e($item['title']) ?></td>
        <td><?= status_badge($item['status']) ?></td>
        <td><?= e($item['applied_at']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
    <p class="meta">No applications yet. Browse jobs to get started.</p>
<?php endif; ?>

<h2>Recent quiz attempts</h2>
<?php if ($quizList): ?>
<table>
    <tr><th>Job</th><th>Score</th><th>Result</th><th>Date</th></tr>
    <?php foreach ($quizList as $q): ?>
    <tr>
        <td><?= e($q['title']) ?></td>
        <td><?= e($q['percentage']) ?>%</td>
        <td><?= passed_badge((bool)$q['passed']) ?></td>
        <td><?= e($q['attempted_at']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
    <p class="meta">No quiz attempts yet. Take a quiz from a job listing.</p>
<?php endif; ?>
<?php page_footer(); ?>