<?php require_once __DIR__ . '/../includes/bootstrap.php';
require_login('admin');
$rows = database()->query('SELECT a.status,a.applied_at,j.title,js.full_name,e.company_name FROM applications a JOIN jobs j ON j.id=a.job_id JOIN job_seekers js ON js.user_id=a.seeker_id JOIN employers e ON e.user_id=j.employer_id ORDER BY a.applied_at DESC')->fetchAll();
page_header('Applications'); ?>
<h1>All applications</h1>
<table>
    <tr>
        <th>Candidate</th>
        <th>Job</th>
        <th>Company</th>
        <th>Status</th>
        <th>Date</th>
    </tr><?php foreach ($rows as $r): ?>
        <tr>
            <td><?= e($r['full_name']) ?></td>
            <td><?= e($r['title']) ?></td>
            <td><?= e($r['company_name']) ?></td>
            <td><?= status_badge($r['status']) ?></td>
            <td><?= e($r['applied_at']) ?></td>
        </tr><?php endforeach; ?>
</table><?php page_footer(); ?>