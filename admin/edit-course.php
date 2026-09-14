<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login('admin');
$pdo = database();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$statement = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
$statement->execute([$id]);
$course = $statement->fetch();
if (!$course) {
    exit('Course not found.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE courses SET title=?,description=?,objectives=?,skills_covered=?,difficulty=?,duration_hours=?,is_active=? WHERE id=?')->execute([
        posted('title'), posted('description'), posted('objectives') ?: null, posted('skills') ?: null,
        posted('difficulty'), posted('duration'), (int) $_POST['is_active'], $id,
    ]);
    $pdo->prepare('DELETE FROM course_job_categories WHERE course_id = ?')->execute([$id]);
    $insertCategory = $pdo->prepare('INSERT INTO course_job_categories (course_id, category_id) VALUES (?, ?)');
    foreach ($_POST['categories'] ?? [] as $categoryId) {
        $insertCategory->execute([$id, (int) $categoryId]);
    }
    $pdo->commit();
    flash('success', 'Course updated.');
    redirect('courses.php');
}

$categories = $pdo->query('SELECT id, name FROM job_categories WHERE is_active = 1 ORDER BY name')->fetchAll();
$selectedStatement = $pdo->prepare('SELECT category_id FROM course_job_categories WHERE course_id = ?');
$selectedStatement->execute([$id]);
$selectedCategories = array_map('intval', $selectedStatement->fetchAll(PDO::FETCH_COLUMN));
page_header('Edit course');
?>
<h1>Edit course</h1>
<form method="post" class="form-grid">
	<input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
	<label>Title<input name="title" value="<?= e($course['title']) ?>" required></label>
	<label>Difficulty<select name="difficulty"><?php foreach (['beginner','intermediate','advanced'] as $level): ?><option <?= $course['difficulty'] === $level ? 'selected' : '' ?>><?= e($level) ?></option><?php endforeach; ?></select></label>
	<label>Duration (hours)<input type="number" min="1" name="duration" value="<?= e($course['duration_hours']) ?>" required></label>
	<label>Active<select name="is_active"><option value="1" <?= $course['is_active'] ? 'selected' : '' ?>>Yes</option><option value="0" <?= $course['is_active'] ? '' : 'selected' ?>>No</option></select></label>
	<div class="full"><label>Related job categories</label><?php foreach ($categories as $category): ?><label><input type="checkbox" name="categories[]" value="<?= $category['id'] ?>" <?= in_array((int) $category['id'], $selectedCategories, true) ? 'checked' : '' ?>> <?= e($category['name']) ?></label><?php endforeach; ?></div>
	<label class="full">Description<textarea name="description" required><?= e($course['description']) ?></textarea></label>
	<label>Objectives<textarea name="objectives"><?= e($course['objectives']) ?></textarea></label>
	<label>Skills covered<textarea name="skills"><?= e($course['skills_covered']) ?></textarea></label>
	<button class="full">Save course</button>
</form>
<?php page_footer(); ?>