<?php require_once __DIR__ . '/../includes/bootstrap.php';
$user = require_login('seeker');
$pdo = database();
require_once __DIR__ . '/../includes/interview-details.php';
$jobId = (int) ($_GET['job_id'] ?? $_POST['job_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $s = $pdo->prepare('SELECT id FROM quiz_attempts WHERE job_id=? AND seeker_id=? AND passed=1 ORDER BY attempted_at DESC LIMIT 1');
    $s->execute([$jobId, $user['id']]);
    $attempt = $s->fetchColumn();
    if (!$attempt) {
        flash('error', 'A passing preliminary quiz result is required before applying.');
        redirect('../public/job-details.php?id=' . $jobId);
    }
    try {
        $pdo->prepare('INSERT INTO applications(job_id,seeker_id,qualifying_attempt_id,cover_letter) VALUES(?,?,?,?)')->execute([$jobId, $user['id'], $attempt, posted('cover_letter') ?: null]);
        flash('success', 'Application submitted.');
    } catch (PDOException $e) {
        flash('error', 'You have already applied for this job.');
    }
    redirect('applications.php');
}
if ($jobId) {
    $s = $pdo->prepare("SELECT title FROM jobs WHERE id=? AND status='active'");
    $s->execute([$jobId]);
    $job = $s->fetch();
    if (!$job) {
        exit('Job not found.');
    }
    page_header('Apply'); ?>
    <h1>Apply for <?= e($job['title']) ?></h1>
    <form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="job_id"
            value="<?= $jobId ?>"><label>Cover letter</label><textarea name="cover_letter"></textarea>
        <p><button>Submit application</button></p>
    </form>
    <?php page_footer();
    exit;
}
$s = $pdo->prepare('SELECT a.*,j.title,e.company_name FROM applications a JOIN jobs j ON j.id=a.job_id JOIN employers e ON e.user_id=j.employer_id WHERE a.seeker_id=? ORDER BY a.applied_at DESC');
$s->execute([$user['id']]);
$applications = $s->fetchAll();
page_header('My applications'); ?>
<div class="page-heading"><div><h1>My applications</h1><p class="meta">Track your progress and find your interview details in one place.</p></div><a class="button secondary small" href="../public/jobs.php">Browse jobs</a></div>
<?php if (!$applications): ?>
    <section class="card empty-state"><h2>Your next opportunity starts here</h2><p>Apply for a role after passing its preliminary quiz. Your application updates will appear here.</p><a class="button" href="../public/jobs.php">Explore jobs</a></section>
<?php else: ?>
    <div class="application-list">
        <?php foreach ($applications as $a): ?>
            <article class="card application-card" id="application-<?= (int) $a['id'] ?>">
                <div class="page-heading"><div><h2><?= e($a['title']) ?></h2><p><?= e($a['company_name']) ?></p></div><?= status_badge($a['status']) ?></div>
                <p class="meta">Applied <?= e(date('M j, Y', strtotime($a['applied_at']))) ?></p>
                <h3><?= !empty($a['interview_at']) ? 'Interview details' : 'Interview update' ?></h3>
                <?php interview_details($a); ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php page_footer(); ?>
