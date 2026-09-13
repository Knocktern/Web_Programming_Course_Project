<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (current_user())
    redirect(dashboard_path(current_user()['role']));
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $statement = database()->prepare('SELECT id, password_hash, role, is_active FROM users WHERE email = ?');
    $statement->execute([strtolower(posted('email'))]);
    $user = $statement->fetch();
    if ($user && $user['is_active'] && password_verify(posted('password'), $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        database()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
        redirect(dashboard_path($user['role']));
    }
    $error = 'Invalid email, password, or inactive account.';
}
page_header('Login'); ?>
<h1>Welcome back</h1><?php if ($error): ?>
    <div class="notice error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <p><label for="email">Email</label><input id="email" type="email" name="email" required></p>
    <p><label for="password">Password</label><input id="password" type="password" name="password" required></p>
    <button>Login</button>
</form>
<?php page_footer(); ?>