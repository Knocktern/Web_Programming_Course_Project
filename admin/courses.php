<?php require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login('admin');
$p = database();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $p->prepare('INSERT INTO courses(title,description,objectives,skills_covered,difficulty,duration_hours,created_by) VALUES(?,?,?,?,?,?,?)')->execute([posted('title'),posted('description'),posted('objectives') ?: null,posted('skills') ?: null,posted('difficulty'),posted('duration'),$u['id']]);
    $id = $p->lastInsertId();
    foreach ($_POST['categories'] ?? [] as $category) {
        $p->prepare('INSERT INTO course_job_categories(course_id,category_id) VALUES(?,?)')->execute([$id,(int)$category]);
    }redirect('courses.php');
}$categories = $p->query('SELECT id,name FROM job_categories WHERE is_active=1')->fetchAll();
$courses = $p->query('SELECT c.*,GROUP_CONCAT(j.name SEPARATOR ", ") categories FROM courses c LEFT JOIN course_job_categories x ON x.course_id=c.id LEFT JOIN job_categories j ON j.id=x.category_id GROUP BY c.id ORDER BY c.created_at DESC')->fetchAll();
page_header('Courses');?><h1>Course catalog</h1><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><div><label>Title</label><input name="title" required></div><div><label>Difficulty</label><select name="difficulty"><option>beginner</option><option>intermediate</option><option>advanced</option></select></div><div><label>Duration (hours)</label><input type="number" min="1" name="duration" required></div><div><label>Categories</label><?php foreach ($categories as $c):?><label><input type="checkbox" name="categories[]" value="<?=$c['id']?>"> <?=e($c['name'])?></label><?php endforeach;?></div><div class="full"><label>Description</label><textarea name="description" required></textarea></div><div><label>Objectives</label><textarea name="objectives"></textarea></div><div><label>Skills covered</label><textarea name="skills"></textarea></div><div class="full"><button>Add course</button></div></form><table><tr><th>Course</th><th>Categories</th><th>Actions</th></tr><?php foreach ($courses as $c):?><tr><td><?=e($c['title'])?></td><td><?=e($c['categories'])?></td><td><a href="edit-course.php?id=<?=$c['id']?>">Edit</a> · <a href="videos.php?course_id=<?=$c['id']?>">Manage videos</a></td></tr><?php endforeach;?></table><?php page_footer(); ?>