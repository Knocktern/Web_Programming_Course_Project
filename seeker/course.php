<?php require_once __DIR__ . '/../includes/bootstrap.php';
$user = require_login('seeker');
$pdo = database();
$id = (int) ($_GET['id'] ?? $_POST['course_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $pdo->prepare('INSERT INTO course_completions(course_id,seeker_id) VALUES(?,?) ON DUPLICATE KEY UPDATE completed_at=NOW()')->execute([$id, $user['id']]);
    flash('success', 'Course marked as completed.');
    redirect('course.php?id=' . $id);
}
$s = $pdo->prepare('SELECT * FROM courses WHERE id=? AND is_active=1');
$s->execute([$id]);
$course = $s->fetch();
if (!$course)
    exit('Course not found.');
$s = $pdo->prepare('SELECT * FROM course_videos WHERE course_id=? ORDER BY display_order');
$s->execute([$id]);
$videos = $s->fetchAll();
$video = $videos[0] ?? null;
if (isset($_GET['video']))
    foreach ($videos as $v)
        if ((int) $v['id'] === (int) $_GET['video'])
            $video = $v;
page_header($course['title']); ?>
<h1><?= e($course['title']) ?></h1>
<p class="lead"><?= e($course['description']) ?></p>
<p><span class="badge"><?= e($course['difficulty']) ?></span> <?= e($course['duration_hours']) ?> hours</p>
<h2>Objectives</h2>
<p><?= nl2br(e($course['objectives'])) ?></p>
<h2>Skills covered</h2>
<p><?= e($course['skills_covered']) ?></p><?php if ($video && is_valid_youtube_id($video['youtube_video_id'])): ?><iframe
        class="video" src="https://www.youtube.com/embed/<?= e($video['youtube_video_id']) ?>" title="<?= e($video['title']) ?>"
        allowfullscreen></iframe>
    <p><?php foreach ($videos as $v): ?><a class="badge"
                href="course.php?id=<?= $id ?>&video=<?= $v['id'] ?>"><?= e($v['title']) ?></a> <?php endforeach; ?></p><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden"
        name="course_id" value="<?= $id ?>"><button>Mark course completed</button></form><?php page_footer(); ?>