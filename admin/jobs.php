<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login('admin');
$pdo = database();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $status = posted('status');
    if (in_array($status, ['active', 'closed', 'deactivated'], true)) {
        $pdo->prepare('UPDATE jobs SET status = ? WHERE id = ?')->execute([$status, (int) $_POST['job_id']]);
        flash('success', 'Job status updated.');
    }
    redirect('jobs.php');
}
$jobs = $pdo->query('SELECT j.*, e.company_name, c.name category_name FROM jobs j JOIN employers e ON e.user_id=j.employer_id JOIN job_categories c ON c.id=j.category_id ORDER BY j.created_at DESC')->fetchAll();
page_header('Moderate jobs');
?>
<h1>Job moderation</h1>
<table><tr><th>Role</th><th>Company</th><th>Category</th><th>Status</th><th>Moderate</th></tr>
<?php foreach ($jobs as $job): ?><tr><td><?= e($job['title']) ?></td><td><?= e($job['company_name']) ?></td><td><?= e($job['category_name']) ?></td><td><?= status_label($job['status']) ?></td><td><form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="job_id" value="<?= $job['id'] ?>"><select name="status"><option value="active">Active</option><option value="closed">Closed</option><option value="deactivated">Deactivated</option></select><button class="small">Save</button></form></td></tr><?php endforeach; ?>
</table>
<?php page_footer(); ?>