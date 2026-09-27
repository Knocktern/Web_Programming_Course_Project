<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$search = trim((string) ($_GET['search'] ?? ''));
$category = (int) ($_GET['category'] ?? 0);
$location = trim((string) ($_GET['location'] ?? ''));
$type = trim((string) ($_GET['type'] ?? ''));
$workplace = trim((string) ($_GET['workplace'] ?? ''));
$skill = trim((string) ($_GET['skill'] ?? ''));
$where = ["j.status = 'active'", 'j.deadline >= CURDATE()'];
$params = [];
if ($search !== '') {
    $where[] = '(j.title LIKE ? OR j.description LIKE ? OR EXISTS (SELECT 1 FROM job_required_skills search_skill WHERE search_skill.job_id=j.id AND search_skill.skill_name LIKE ?))';
    $params[] = "%$search%";
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
if ($skill !== '') {
    $where[] = 'EXISTS (SELECT 1 FROM job_required_skills selected_skill WHERE selected_skill.job_id=j.id AND selected_skill.skill_name=?)';
    $params[] = $skill;
}
$sql = 'SELECT j.*, c.name category_name, e.company_name, (SELECT GROUP_CONCAT(skill_name ORDER BY skill_name SEPARATOR "|") FROM job_required_skills WHERE job_id=j.id) skills FROM jobs j JOIN job_categories c ON c.id=j.category_id JOIN employers e ON e.user_id=j.employer_id WHERE ' . implode(' AND ', $where) . ' ORDER BY j.created_at DESC';
$statement = database()->prepare($sql);
$statement->execute($params);
$jobs = $statement->fetchAll();
$categories = database()->query('SELECT id, name FROM job_categories WHERE is_active=1 ORDER BY name')->fetchAll();
$availableSkills = database()->query("SELECT s.skill_name, COUNT(*) total FROM job_required_skills s JOIN jobs j ON j.id=s.job_id WHERE j.status='active' AND j.deadline>=CURDATE() GROUP BY s.skill_name ORDER BY s.skill_name")->fetchAll();
// Common badges stay available even when there are no matching open jobs yet.
$skillBadges = [];
foreach (['C++', 'Python', 'JavaScript', 'Java', 'PHP', 'SQL'] as $name) {
    $skillBadges[strtolower($name)] = ['name' => $name, 'total' => 0];
}
foreach ($availableSkills as $item) {
    $skillBadges[strtolower($item['skill_name'])] = ['name' => $item['skill_name'], 'total' => (int) $item['total']];
}
if ($skill !== '' && !isset($skillBadges[strtolower($skill)])) {
    $skillBadges[strtolower($skill)] = ['name' => $skill, 'total' => 0];
}
$filters = ['search' => $search, 'category' => $category, 'location' => $location, 'type' => $type, 'workplace' => $workplace];
$extraFilterCount = count(array_filter($filters, static fn($value) => $value !== '' && $value !== 0));
page_header('Browse jobs'); ?>
<h1>Open positions</h1>
<p class="meta">Find a role that matches your skills and preferred workplace.</p>
<section class="card skill-search" aria-labelledby="skill-search-title">
    <h2 id="skill-search-title">Search by skill</h2>
    <p class="meta">Choose a skill. Scroll the badges for more. Counts show all open jobs.</p>
    <nav class="skill-filters" aria-label="Filter jobs by skill">
        <a class="badge skill-badge <?= $skill === '' ? 'active' : '' ?>" <?= $skill === '' ? 'aria-current="true"' : '' ?> href="jobs.php?<?= e(http_build_query($filters)) ?>">All skills</a>
        <?php foreach ($skillBadges as $item): $selected = strcasecmp($skill, $item['name']) === 0; ?>
            <a class="badge skill-badge <?= $selected ? 'active' : '' ?>" <?= $selected ? 'aria-current="true"' : '' ?> href="jobs.php?<?= e(http_build_query(array_merge($filters, ['skill' => $selected ? '' : $item['name']]))) ?>" aria-label="<?= e(($selected ? 'Remove ' : 'Filter by ') . $item['name'] . ' skill filter, ' . $item['total'] . ' open jobs') ?>">
                <?php if ($selected): ?><span aria-hidden="true">✓</span><?php endif; ?><?= e($item['name']) ?> <span class="skill-count"><?= $item['total'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <details class="job-filters">
        <summary>More filters<?= $extraFilterCount ? ' (' . $extraFilterCount . ' applied)' : '' ?></summary>
<form method="get" action="jobs.php" class="form-grid">
    <input type="hidden" name="skill" value="<?= e($skill) ?>">
    <label>Search<input name="search" value="<?= e($search) ?>" placeholder="Title, keyword, or skill"></label>
    <label>Category<select name="category"><option value="">All categories</option>
        <?php foreach ($categories as $item): ?><option value="<?= $item['id'] ?>" <?= $category === (int) $item['id'] ? 'selected' : '' ?>><?= e($item['name']) ?></option><?php endforeach; ?>
    </select></label>
    <label>Location<input name="location" value="<?= e($location) ?>" placeholder="City or area"></label>
    <label>Employment type<select name="type"><option value="">Any type</option>
        <?php foreach (['full_time', 'part_time', 'contract', 'internship', 'temporary'] as $option): ?><option value="<?= $option ?>" <?= $type === $option ? 'selected' : '' ?>><?= status_label($option) ?></option><?php endforeach; ?>
    </select></label>
    <label>Workplace<select name="workplace"><option value="">Any workplace</option>
        <?php foreach (['on_site', 'hybrid', 'remote'] as $option): ?><option value="<?= $option ?>" <?= $workplace === $option ? 'selected' : '' ?>><?= status_label($option) ?></option><?php endforeach; ?>
    </select></label>
    <div class="full"><button>Apply filters</button> <a class="button secondary" href="jobs.php">Clear filters</a></div>
</form>
    </details>
</section>
<?php if ($extraFilterCount): ?><p class="meta"><?= $extraFilterCount ?> additional <?= $extraFilterCount === 1 ? 'filter is' : 'filters are' ?> applied. <a class="text-link" href="jobs.php?<?= e(http_build_query(['skill' => $skill])) ?>">Clear additional filters</a></p><?php endif; ?>
<div class="page-heading"><p class="meta" role="status"><?= count($jobs) ?> <?= count($jobs) === 1 ? 'job' : 'jobs' ?> found<?= $skill !== '' ? ' requiring ' . e($skill) : '' ?></p><?php if ($skill !== ''): ?><a class="text-link" href="jobs.php?<?= e(http_build_query($filters)) ?>">Remove skill filter</a><?php endif; ?></div>
<section class="grid">
    <?php foreach ($jobs as $job): ?>
        <article class="card"><span class="badge"><?= e($job['category_name']) ?></span><h3><?= e($job['title']) ?></h3>
            <p><?= e($job['company_name']) ?> · <?= e($job['location']) ?></p>
            <?php if ($job['skills']): ?><p><?php foreach (explode('|', $job['skills']) as $jobSkill): ?><a class="badge skill-badge <?= strcasecmp($skill, $jobSkill) === 0 ? 'active' : '' ?>" href="jobs.php?<?= e(http_build_query(array_merge($filters, ['skill' => $jobSkill]))) ?>" aria-label="<?= e('Find jobs requiring ' . $jobSkill) ?>"><?= e($jobSkill) ?></a> <?php endforeach; ?></p><?php endif; ?>
            <p class="meta"><?= status_label($job['employment_type']) ?> · <?= status_label($job['workplace_type']) ?> · Apply by <?= e(date('M j, Y', strtotime($job['deadline']))) ?></p>
            <a class="button small" href="job-details.php?id=<?= $job['id'] ?>">View role</a>
        </article>
    <?php endforeach; ?>
</section>
<?php if (!$jobs): ?><div class="card empty-state"><h2>No matching jobs</h2><p>Try a different keyword or remove a filter to see more roles.</p><a class="button secondary" href="jobs.php">Clear filters</a></div><?php endif; ?>
<?php page_footer(); ?>
