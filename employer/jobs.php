<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login('employer');
$p = database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) $_POST['job_id'];
    $newStatus = posted('new_status') === 'active' ? 'active' : 'deactivated';

    $p->prepare("UPDATE jobs SET status=? WHERE id=? AND employer_id=?")->execute([$newStatus, $id, $u['id']]);

    flash('success', $newStatus === 'active' ? 'Job reactivated.' : 'Job deactivated.');
    redirect('jobs.php');
}

$s = $p->prepare('SELECT j.*,c.name category FROM jobs j JOIN job_categories c ON c.id=j.category_id WHERE j.employer_id=? ORDER BY j.created_at DESC');
$s->execute([$u['id']]);

page_header('My jobs');
?>
<h1>Job postings</h1>
<p><a class="button" href="create-job.php">Create job</a></p>
<table>
    <tr>
        <th>Title</th>
        <th>Category</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($s as $job): ?>
        <tr>
            <td><?= e($job['title']) ?></td>
            <td><?= e($job['category']) ?></td>
            <td><?= status_badge($job['status']) ?></td>
            <td>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="edit-job.php?id=<?= $job['id'] ?>">Edit</a> &middot;
                    <a href="quiz-builder.php?job_id=<?= $job['id'] ?>">Quiz</a> &middot;
                    <a href="applicants.php?job_id=<?= $job['id'] ?>">Applicants</a>

                    <?php if ($job['status'] === 'active'): ?>
                        <form method="post" class="inline-form" style="margin-left: 0.5rem;">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                            <input type="hidden" name="new_status" value="deactivated">
                            <button class="button danger small" data-confirm="Deactivate this job?"
                                style="margin-bottom: 0;">Deactivate</button>
                        </form>
                    <?php elseif ($job['status'] === 'deactivated'): ?>
                        <form method="post" class="inline-form" style="margin-left: 0.5rem;">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                            <input type="hidden" name="new_status" value="active">
                            <button class="button small" data-confirm="Reactivate this job?"
                                style="margin-bottom: 0;">Reactivate</button>
                        </form>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php page_footer(); ?>