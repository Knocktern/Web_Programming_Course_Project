<?php require_once __DIR__ . '/../includes/bootstrap.php';
require_login('admin');
$p = database();
$id = (int)($_GET['course_id'] ?? $_POST['course_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $youtube = posted('youtube_video_id');
    if (is_valid_youtube_id($youtube)) {
        $order = (int)$p->query('SELECT COALESCE(MAX(display_order),0)+1 FROM course_videos WHERE course_id='.$id)->fetchColumn();
        $p->prepare('INSERT INTO course_videos(course_id,title,youtube_video_id,display_order) VALUES(?,?,?,?)')->execute([$id,posted('title'),$youtube,$order]);
    } else {
        flash('error', 'Enter an 11-character YouTube video ID.');
    }redirect('videos.php?course_id='.$id);
}$s = $p->prepare('SELECT * FROM courses WHERE id=?');
$s->execute([$id]);
$course = $s->fetch();
if (!$course) {
    exit('Course not found.');
}$s = $p->prepare('SELECT * FROM course_videos WHERE course_id=?');
$s->execute([$id]);
page_header('Course videos');?><h1>Videos: <?=e($course['title'])?></h1><form method="post"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="course_id" value="<?=$id?>"><label>Video title</label><input name="title" required><label>YouTube video ID</label><input name="youtube_video_id" maxlength="11" required><p><button>Add video</button></p></form><table><tr><th>Title</th><th>Video ID</th></tr><?php foreach ($s as $v):?><tr><td><?=e($v['title'])?></td><td><?=e($v['youtube_video_id'])?></td></tr><?php endforeach;?></table><?php page_footer(); ?>