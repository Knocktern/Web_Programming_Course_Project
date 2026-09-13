<?php require_once __DIR__ . '/../includes/bootstrap.php';
require_login('admin');
$p = database();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $p->prepare('UPDATE users SET is_active=? WHERE id=? AND role<>"admin"')->execute([(int) $_POST['active'], (int) $_POST['id']]);
    redirect('users.php');
}
$users = $p->query('SELECT id,email,role,is_active,created_at FROM users ORDER BY created_at DESC')->fetchAll();
page_header('Manage users'); ?>
<h1>Users</h1>
<table>
    <tr>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th></th>
    </tr><?php foreach ($users as $item): ?>
        <tr>
            <td><?= e($item['email']) ?></td>
            <td><?= e($item['role']) ?></td>
            <td><?= $item['is_active'] ? 'Active' : 'Inactive' ?></td>
            <td><?php if ($item['role'] !== 'admin'): ?>
                    <form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden"
                            name="id" value="<?= $item['id'] ?>"><input type="hidden" name="active"
                            value="<?= $item['is_active'] ? 0 : 1 ?>"><button
                            class="small"><?= $item['is_active'] ? 'Deactivate' : 'Activate' ?></button></form><?php endif; ?>
            </td>
        </tr><?php endforeach; ?>
</table><?php page_footer(); ?>