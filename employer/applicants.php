<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login('employer');
$p = database();

$id = (int)($_GET['job_id'] ?? $_POST['job_id'] ?? 0);
$own = $p->prepare('SELECT title FROM jobs WHERE id=? AND employer_id=?');
$own->execute([$id, $u['id']]);
$job = $own->fetch();

if (!$job) {
    exit('Job not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $status = posted('status');
    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $candidate = $p->prepare('SELECT a.* FROM applications a JOIN jobs j ON j.id=a.job_id WHERE a.id=? AND a.job_id=? AND j.employer_id=?');
    $candidate->execute([$applicationId, $id, $u['id']]);
    $application = $candidate->fetch();
    if (!$application) {
        http_response_code(404);
        exit('Application not found.');
    }
    if ($status === 'interview') {
        redirect('applicant-profile.php?application_id=' . $applicationId . '#schedule-interview');
    }
    if (in_array($status, ['submitted', 'under_review', 'shortlisted', 'interview', 'selected', 'rejected', 'withdrawn'], true)) {
        $p->prepare('UPDATE applications a JOIN jobs j ON j.id=a.job_id SET a.status=? WHERE a.id=? AND a.job_id=? AND j.employer_id=?')
          ->execute([$status, $applicationId, $id, $u['id']]);
        flash('success', 'Application status updated.');
    }
    redirect('applicants.php?job_id='.$id);
}

$s = $p->prepare('SELECT a.*, js.full_name, u.email, qa.percentage 
                  FROM applications a 
                  JOIN job_seekers js ON js.user_id=a.seeker_id 
                  JOIN users u ON u.id=js.user_id 
                  JOIN quiz_attempts qa ON qa.id=a.qualifying_attempt_id 
                  WHERE a.job_id=? AND qa.passed=1');
$s->execute([$id]);
$applicants = $s->fetchAll();

page_header('Applicants');
?>

<h1>Applicants: <?= e($job['title']) ?></h1>
<p><a href="quiz-builder.php?job_id=<?= $id ?>">Edit this job's quiz</a></p>

<table>
    <tr>
        <th>Candidate</th>
        <th>Quiz</th>
        <th>Status</th>
        <th>Update</th>
    </tr>
    <?php if (!$applicants): ?><tr><td colspan="4">No applicants yet. Candidates appear here after passing the quiz and applying.</td></tr><?php endif; ?>
    <?php foreach ($applicants as $a): ?>
    <tr>
        <td>
            <a href="applicant-profile.php?application_id=<?= $a['id'] ?>"><strong><?= e($a['full_name']) ?></strong></a><br>
            <span class="meta"><?= e($a['email']) ?></span>
            <br><a href="applicant-profile.php?application_id=<?= $a['id'] ?>">View profile, CV &amp; interview</a>
        </td>
        <td><?= e($a['percentage']) ?>%</td>
        <td><?= status_badge($a['status']) ?></td>
        <td>
            <form method="post" class="inline-form status-form">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="job_id" value="<?= $id ?>">
                <input type="hidden" name="application_id" value="<?= $a['id'] ?>">
                <label class="sr-only" for="status-<?= $a['id'] ?>">Status for <?= e($a['full_name']) ?></label>
                <select name="status" id="status-<?= $a['id'] ?>">
                    <?php foreach (['submitted', 'under_review', 'shortlisted', 'interview', 'selected', 'rejected', 'withdrawn'] as $status): ?>
                        <option value="<?= $status ?>" <?= $a['status'] === $status ? 'selected' : '' ?>><?= $status === 'interview' ? 'Schedule / edit interview' : status_label($status) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="small">Save</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php page_footer(); ?>
