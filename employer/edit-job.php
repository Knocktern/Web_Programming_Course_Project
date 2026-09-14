<?php require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login('employer');
$p = database();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$s = $p->prepare('SELECT * FROM jobs WHERE id=? AND employer_id=?');
$s->execute([$id,$u['id']]);
$j = $s->fetch();
if (!$j) {
    exit('Job not found.');
}if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $p->prepare('UPDATE jobs SET title=?,description=?,location=?,deadline=?,minimum_passing_score=?,status=? WHERE id=? AND employer_id=?')->execute([posted('title'),posted('description'),posted('location'),posted('deadline'),posted('minimum_passing_score'),posted('status'),$id,$u['id']]);
    flash('success', 'Job updated.');
    redirect('jobs.php');
}page_header('Edit job');?><h1>Edit job</h1><form method="post"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><label>Title</label><input name="title" value="<?=e($j['title'])?>"><label>Description</label><textarea name="description"><?=e($j['description'])?></textarea><label>Location</label><input name="location" value="<?=e($j['location'])?>"><label>Deadline</label><input type="date" name="deadline" value="<?=e($j['deadline'])?>"><label>Pass score</label><input type="number" name="minimum_passing_score" value="<?=e($j['minimum_passing_score'])?>"><label>Status</label><select name="status"><?php foreach (['draft','active','closed','deactivated'] as $status):?><option <?=$j['status'] === $status ? 'selected' : ''?>><?=$status?></option><?php endforeach;?></select><p><button>Save changes</button></p></form><?php page_footer(); ?>