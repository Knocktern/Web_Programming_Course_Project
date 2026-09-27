<?php require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login('employer');
$p = database();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $categoryId = $_POST['category_id'] ?? null;
    if (!$categoryId) {
        flash('error', 'Please select a job category. If none are available, please contact the administrator.');
        redirect('create-job.php');
    }
    $salaryMin = trim($_POST['salary_min'] ?? '') !== '' ? (int)$_POST['salary_min'] : null;
    $salaryMax = trim($_POST['salary_max'] ?? '') !== '' ? (int)$_POST['salary_max'] : null;
    $p->beginTransaction();
    $p->prepare('INSERT INTO jobs(employer_id,category_id,title,description,responsibilities,requirements_text,employment_type,workplace_type,location,salary_min,salary_max,vacancies,deadline,minimum_passing_score,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$u['id'],(int)$categoryId,posted('title'),posted('description'),posted('responsibilities') ?: null,posted('requirements') ?: null,posted('employment_type'),posted('workplace_type'),posted('location'),$salaryMin,$salaryMax,(int)$_POST['vacancies'],posted('deadline'),posted('minimum_passing_score'),'active']);
    $id = $p->lastInsertId();
    $insertSkill = $p->prepare('INSERT INTO job_required_skills (job_id, skill_name) VALUES (?, ?)');
    foreach (array_unique(array_filter(array_map('trim', explode(',', posted('skills'))))) as $skill) $insertSkill->execute([$id, $skill]);
    $p->commit();
    flash('success', 'Job created. Add its quiz questions next.');
    redirect('quiz-builder.php?job_id='.$id);
}$categories = $p->query('SELECT id,name FROM job_categories WHERE is_active=1 ORDER BY name')->fetchAll();
page_header('Create job');?><h1>Create job</h1><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><div><label>Title</label><input name="title" required></div><div><label>Category</label><select name="category_id" required><?php if (!$categories): ?><option value="">No categories available</option><?php endif; ?><?php foreach ($categories as $c):?><option value="<?=$c['id']?>"><?=e($c['name'])?></option><?php endforeach;?></select></div><div><label>Employment type</label><select name="employment_type"><option value="full_time">Full time</option><option value="part_time">Part time</option><option value="contract">Contract</option><option value="internship">Internship</option></select></div><div><label>Workplace</label><select name="workplace_type"><option value="on_site">On site</option><option value="hybrid">Hybrid</option><option value="remote">Remote</option></select></div><div><label>Location</label><input name="location" required></div><div><label>Vacancies</label><input type="number" min="1" name="vacancies" value="1" required></div><div><label>Minimum pass score (%)</label><input type="number" min="0" max="100" name="minimum_passing_score" value="60" required></div><div><label>Deadline</label><input type="date" name="deadline" required></div><div><label>Minimum salary</label><input type="number" name="salary_min"></div><div><label>Maximum salary</label><input type="number" name="salary_max"></div><div class="full"><label>Required skills</label><input name="skills" placeholder="C++, Python, JavaScript"><span class="meta">Separate skills with commas.</span></div><div class="full"><label>Description</label><textarea name="description" required></textarea></div><div><label>Responsibilities</label><textarea name="responsibilities"></textarea></div><div><label>Requirements</label><textarea name="requirements"></textarea></div><div class="full"><button>Create job</button></div></form><?php page_footer(); ?>