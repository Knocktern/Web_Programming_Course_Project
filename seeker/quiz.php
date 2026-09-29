<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$user = require_login('seeker');
$jobId = (int) ($_GET['job_id'] ?? $_POST['job_id'] ?? 0);
$pdo = database();
$job = $pdo->prepare("SELECT id,title,minimum_passing_score FROM jobs WHERE id=? AND status='active'");
$job->execute([$jobId]);
$job = $job->fetch();
if (!$job) {
    exit('Quiz is unavailable.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $q = $pdo->prepare('SELECT id,correct_option,marks FROM quiz_questions WHERE job_id=?');
    $q->execute([$jobId]);
    $questions = $q->fetchAll();
    if (!$questions) {
        flash('error', 'This job has no questions yet.');
        redirect('../public/job-details.php?id=' . $jobId);
    }
    $total = 0;
    $score = 0;
    foreach ($questions as $item) {
        $total += (float) $item['marks'];
        if (($_POST['answer'][$item['id']] ?? '') === $item['correct_option']) {
            $score += (float) $item['marks'];
        }
    }
    $percentage = round($score / $total * 100, 2);
    $passed = $percentage >= (float) $job['minimum_passing_score'];
    $pdo->beginTransaction();
    $s = $pdo->prepare('INSERT INTO quiz_attempts(job_id,seeker_id,score,total_marks,percentage,passed) VALUES(?,?,?,?,?,?)');
    $s->execute([$jobId, $user['id'], $score, $total, $percentage, (int) $passed]);
    $attempt = (int) $pdo->lastInsertId();
    $a = $pdo->prepare('INSERT INTO quiz_answers(attempt_id,job_id,question_id,selected_option,is_correct,marks_awarded) VALUES(?,?,?,?,?,?)');
    foreach ($questions as $item) {
        $answer = $_POST['answer'][$item['id']] ?? null;
        $correct = $answer === $item['correct_option'];
        $a->execute([$attempt, $jobId, $item['id'], in_array($answer, ['A', 'B', 'C', 'D'], true) ? $answer : null, (int) $correct, $correct ? $item['marks'] : 0]);
    }
    $pdo->commit();
    if (posted('submission_reason') === 'tab_hidden') {
        flash('error', 'Your quiz was automatically submitted because the quiz tab became hidden. Unanswered questions received zero marks.');
    }
    redirect('quiz-result.php?id=' . $attempt);
}
$s = $pdo->prepare('SELECT id,question_text,option_a,option_b,option_c,option_d,marks FROM quiz_questions WHERE job_id=? ORDER BY display_order');
$s->execute([$jobId]);
$questions = $s->fetchAll();
page_header('Preliminary quiz'); ?>
<h1><?= e($job['title']) ?> quiz</h1>
<p class="lead">Pass score: <?= e($job['minimum_passing_score']) ?>%. Your score is calculated securely after
    submission.
</p>
<?php if (!$questions): ?>
    <p class="notice">This job has no quiz questions yet. Please check again later.</p>
<?php else: ?>
<section class="card" id="quiz-caution" aria-labelledby="quiz-caution-title">
    <h2 id="quiz-caution-title">Before you start</h2>
    <p>Once you start, stay on this quiz tab. Switching to another tab, minimizing the browser, or switching apps when it hides this page will automatically submit your current answers.</p>
    <p>Unanswered questions will receive zero marks. Only start when you are ready to finish without leaving this tab.</p>
    <button type="button" id="start-quiz" disabled>I understand — start quiz</button>
    <noscript><p class="notice error">Enable JavaScript to start this quiz.</p></noscript>
</section>
<p class="notice" id="quiz-active-notice" hidden>Quiz in progress. Keep this tab visible to avoid automatic submission.</p>
<form method="post" id="quiz-form" hidden><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden"
        name="submission_reason" id="submission-reason" value="manual"><input type="hidden"
        name="job_id" value="<?= $jobId ?>"><?php foreach ($questions as $number => $q): ?>
        <fieldset>
            <legend><?= ($number + 1) . '. ' . e($q['question_text']) ?> (<?= e($q['marks']) ?> mark)</legend>
            <?php foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $field): ?><label><input
                        type="radio" name="answer[<?= $q['id'] ?>]" value="<?= $letter ?>" required> <?= $letter ?>.
                    <?= e($q[$field]) ?></label><?php endforeach; ?>
        </fieldset><br><?php endforeach; ?><button>Submit quiz</button>
</form>
<script>
    const quizForm = document.getElementById('quiz-form');
    const startButton = document.getElementById('start-quiz');
    let quizStarted = false;
    let quizSubmitted = false;

    startButton.disabled = false;
    startButton.addEventListener('click', () => {
        if (document.hidden || quizStarted) return;
        quizStarted = true;
        document.getElementById('quiz-caution').hidden = true;
        document.getElementById('quiz-active-notice').hidden = false;
        quizForm.hidden = false;
        quizForm.querySelector('input[type="radio"]').focus();
    });

    quizForm.addEventListener('submit', (event) => {
        if (quizSubmitted) {
            event.preventDefault();
            return;
        }
        quizSubmitted = true;
    });

    document.addEventListener('visibilitychange', () => {
        if (!quizStarted || quizSubmitted || !document.hidden) return;
        quizSubmitted = true;
        document.getElementById('submission-reason').value = 'tab_hidden';
        // Bypass required radio validation so incomplete answers are submitted too.
        quizForm.submit();
    });
</script>
<?php endif; ?>
<?php page_footer(); ?>
