<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$search = trim((string) ($_GET['search'] ?? ''));
$category = (int) ($_GET['category'] ?? 0);
$location = trim((string) ($_GET['location'] ?? ''));
$type = trim((string) ($_GET['type'] ?? ''));
$workplace = trim((string) ($_GET['workplace'] ?? ''));
$where = ["j.status = 'active'", 'j.deadline >= CURDATE()'];
$params = [];
if ($search !== '') {
    $where[] = '(j.title LIKE ? OR j.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category) {
    $where[] = 'j.category_id = ?';
    $params[] = $category;
} if ($location !== '') {
    $where[] = 'j.location LIKE ?';
    $params[] = "%$location%";
}
if (in_array($type, ['full_time','part_time','contract','internship','temporary'], true)) {
    $where[] = 'j.employment_type = ?';
    $params[] = $type;
}
if (in_array($workplace, ['on_site','hybrid','remote'], true)) {
    $where[] = 'j.workplace_type = ?';
    $params[] = $workplace;
}
$sql = 'SELECT j.*, c.name category_name, e.company_name FROM jobs j JOIN job_categories c ON c.id=j.category_id JOIN employers e ON e.user_id=j.employer_id WHERE ' . implode(' AND ', $where) . ' ORDER BY j.created_at DESC';
$statement = database()->prepare($sql);
$statement->execute($params);
$jobs = $statement->fetchAll();
$categories = database()->query('SELECT id, name FROM job_categories WHERE is_active=1 ORDER BY name')->fetchAll();
page_header('Browse jobs'); ?>
<h1>Open positions</h1><form method="get" class="form-grid"><div><label>Search</label><input name="search" value="<?= e($search) ?>" placeholder="Title or keyword"></div><div><label>Category</label><select name="category"><option value="">All categories</option><?php foreach ($categories as $item): ?><option value="<?= $item['id'] ?>" <?= $category === (int)$item['id'] ? 'selected' : '' ?>><?= e($item['name']) ?></option><?php endforeach; ?></select></div><div><label>Location</label><input name="location" value="<?= e($location) ?>"></div><div><label>Employment type</label><select name="type"><option value="">Any</option><option value="full_time">Full time</option><option value="internship">Internship</option><option value="contract">Contract</option></select></div><div class="full"><button>Search jobs</button></div></form><p class="meta"><?= count($jobs) ?> job(s) found</p><section class="grid"><?php foreach ($jobs as $job): ?><article class="card"><span class="badge"><?= e($job['category_name']) ?></span><h3><?= e($job['title']) ?></h3><p><?= e($job['company_name']) ?> · <?= e($job['location']) ?></p><p class="meta"><?= status_label($job['employment_type']) ?> · <?= status_label($job['workplace_type']) ?> · Apply by <?= e($job['deadline']) ?></p><a class="button small" href="job-details.php?id=<?= $job['id'] ?>">View role</a></article><?php endforeach; ?></section><?php if (!$jobs): ?><p class="notice">No active jobs match these filters.</p><?php endif; ?>
<?php page_footer(); ?>