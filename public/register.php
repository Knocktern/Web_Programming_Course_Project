<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (current_user()) {
    redirect(dashboard_path(current_user()['role']));
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(posted('email'));
    $password = posted('password');
    $role = posted('role');
    $name = posted('name');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must contain at least 8 characters.';
    }
    if (!in_array($role, ['seeker', 'employer'], true)) {
        $errors[] = 'Choose an account type.';
    }
    if ($name === '') {
        $errors[] = 'Enter your name or company name.';
    }
    if (!$errors) {
        try {
            $pdo = database();
            $pdo->beginTransaction();
            $statement = $pdo->prepare('INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)');
            $statement->execute([$email, password_hash($password, PASSWORD_DEFAULT), $role]);
            $userId = (int) $pdo->lastInsertId();
            if ($role === 'seeker') {
                database()->prepare('INSERT INTO job_seekers (user_id, full_name) VALUES (?, ?)')->execute([$userId, $name]);
            } else {
                database()->prepare('INSERT INTO employers (user_id, company_name, contact_name) VALUES (?, ?, ?)')->execute([$userId, $name, posted('contact_name') ?: null]);
            }
            $pdo->commit();
            flash('success', 'Account created. Please sign in.');
            redirect('login.php');
        } catch (PDOException $exception) {
            if (database()->inTransaction()) {
                database()->rollBack();
            }
            $errors[] = 'That email address is already registered.';
        }
    }
}
page_header('Create account'); ?>
<h1>Create your account</h1><?php foreach ($errors as $error): ?>
    <div class="notice error"><?= e($error) ?></div><?php endforeach; ?>
<form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div><label for="role">I am a</label><select id="role" name="role" required>
            <option value="seeker" <?= posted('role') === 'seeker' ? 'selected' : '' ?>>Job seeker</option>
            <option value="employer" <?= posted('role') === 'employer' ? 'selected' : '' ?>>Employer / company</option>
        </select></div>
    <div><label for="name">Full name / company name</label><input id="name" name="name"
            value="<?= e($_POST['name'] ?? '') ?>" required></div>
    <div id="employer-contact-field" hidden><label for="contact_name">Employer contact name</label><input
            id="contact_name" name="contact_name" value="<?= e(posted('contact_name')) ?>"></div>
    <div><label for="email">Email</label><input id="email" type="email" name="email"
            value="<?= e($_POST['email'] ?? '') ?>" required></div>
    <div><label for="password">Password</label><input id="password" type="password" name="password" minlength="8" autocomplete="new-password"
            aria-describedby="password-hint" required><small class="meta" id="password-hint">Use at least 8 characters.</small></div>
    <div class="full"><button>Create account</button> Already registered? <a href="login.php">Login</a></div>
</form>
<script>
    const roleSelect = document.getElementById('role');
    const employerContactField = document.getElementById('employer-contact-field');
    const updateEmployerFields = () => { employerContactField.hidden = roleSelect.value !== 'employer'; };
    roleSelect.addEventListener('change', updateEmployerFields);
    updateEmployerFields();
</script>
<?php page_footer(); ?>
