<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$employer = require_login('employer');
$pdo = database();
require_once __DIR__ . '/../includes/interview-details.php';
$applicationId = (int) ($_GET['application_id'] ?? $_POST['application_id'] ?? 0);

$statement = $pdo->prepare('SELECT a.*, j.title job_title, js.*, u.email, qa.percentage FROM applications a JOIN jobs j ON j.id=a.job_id JOIN job_seekers js ON js.user_id=a.seeker_id JOIN users u ON u.id=js.user_id JOIN quiz_attempts qa ON qa.id=a.qualifying_attempt_id WHERE a.id=? AND j.employer_id=? AND qa.passed=1');
$statement->execute([$applicationId, $employer['id']]);
$application = $statement->fetch();
if (!$application) {
    http_response_code(404);
    exit('Applicant not found.');
}

$errors = [];
$form = $application;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $mode = posted('interview_mode');
    $interviewAt = posted('interview_at');
    $meetingUrl = posted('meeting_url');
    $location = posted('interview_location');
    foreach (['interview_mode', 'interview_at', 'meeting_url', 'interview_location', 'interview_notes'] as $field) {
        $form[$field] = posted($field);
    }
    if (!in_array($mode, ['online', 'office'], true))
        $errors[] = 'Choose an interview type.';
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $interviewAt);
    if (!$date || $date->format('Y-m-d\TH:i') !== $interviewAt || $date->getTimestamp() <= time())
        $errors[] = 'Choose a future interview date and time.';
    if ($mode === 'online' && (!is_http_url($meetingUrl) || strlen($meetingUrl) > 500))
        $errors[] = 'Enter an http:// or https:// meeting link of no more than 500 characters.';
    if ($mode === 'office' && $location === '')
        $errors[] = 'Enter the office interview location.';
    if ($mode === 'office' && preg_match_all('/./us', $location) > 255)
        $errors[] = 'Keep the office location within 255 characters.';
    if (!$errors) {
        $pdo->prepare("UPDATE applications a JOIN jobs j ON j.id=a.job_id SET a.interview_mode=?,a.interview_at=?,a.meeting_url=?,a.interview_location=?,a.interview_notes=?,a.status='interview' WHERE a.id=? AND j.employer_id=?")->execute([$mode, date('Y-m-d H:i:s', strtotime($interviewAt)), $mode === 'online' ? $meetingUrl : null, $mode === 'office' ? $location : null, posted('interview_notes') ?: null, $applicationId, $employer['id']]);
        flash('success', 'Interview saved. The candidate can now see the date, joining details, and instructions in My applications.');
        redirect('applicant-profile.php?application_id=' . $applicationId);
    }
}

$sections = [
    'Education' => 'SELECT institution, degree, field_of_study, result_gpa FROM education WHERE seeker_id=? ORDER BY start_date DESC',
    'Experience' => 'SELECT company, position_title, description FROM experience WHERE seeker_id=? ORDER BY start_date DESC',
    'Projects' => 'SELECT title, description, technologies, project_url FROM projects WHERE seeker_id=?',
    'Skills' => 'SELECT skill_name, skill_level FROM job_seeker_skills WHERE seeker_id=? ORDER BY skill_name',
    'Certifications' => 'SELECT certification_name, issuing_organization, issue_date, credential_url FROM certifications WHERE seeker_id=?',
    'Languages' => 'SELECT language_name, proficiency FROM languages WHERE seeker_id=? ORDER BY language_name',
];
page_header('Applicant profile');
?>
<p><a href="applicants.php?job_id=<?= $application['job_id'] ?>">Back to applicants</a></p>
<section class="card applicant-summary">
    <span class="badge">Quiz: <?= e($application['percentage']) ?>%</span>
    <h1><?= e($application['full_name']) ?></h1>
    <p class="lead"><?= e($application['email']) ?> · <?= e($application['phone']) ?></p>
    <p><?= nl2br(e($application['profile_summary'])) ?></p>
    <p class="meta">Applied for <?= e($application['job_title']) ?></p>
</section>

<div class="profile-sections">
    <?php foreach ($sections as $title => $query):
        $items = $pdo->prepare($query);
        $items->execute([$application['seeker_id']]);
        $rows = $items->fetchAll();
        if ($rows): ?>
            <section class="card">
                <h2><?= e($title) ?></h2><?php foreach ($rows as $row): ?>
                    <div class="cv-line">
                        <?php foreach ($row as $value):
                            if ($value): ?><span><?= e((string) $value) ?></span><?php endif; endforeach; ?>
                    </div><?php endforeach; ?>
            </section>
        <?php endif; endforeach; ?>
</div>

<h2 id="schedule-interview"><?= !empty($application['interview_at']) ? 'Update interview' : 'Schedule interview' ?></h2>
<p class="meta">The candidate will see these details on their dashboard and in My applications. Times use
    <?= e(date_default_timezone_get()) ?>.</p>
<?php foreach ($errors as $error): ?>
    <div class="notice error"><?= e($error) ?></div><?php endforeach; ?>
<?php if (!empty($application['interview_at'])): ?>
    <section class="card interview-card">
        <h3>Current interview details</h3><?php interview_details($application); ?>
    </section>
<?php endif; ?>
<form method="post" class="form-grid" id="interview-form">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="application_id" value="<?= $applicationId ?>">
    <label>Interview type<select name="interview_mode" id="interview-mode">
            <option value="online" <?= ($form['interview_mode'] ?? '') === 'online' ? 'selected' : '' ?>>Online meeting
            </option>
            <option value="office" <?= ($form['interview_mode'] ?? '') === 'office' ? 'selected' : '' ?>>Office interview
            </option>
        </select></label>
    <label>Date and time<input type="datetime-local" name="interview_at"
            value="<?= e($_SERVER['REQUEST_METHOD'] === 'POST' ? $form['interview_at'] : (!empty($form['interview_at']) ? date('Y-m-d\TH:i', strtotime($form['interview_at'])) : '')) ?>"
            required></label>
    <label id="meeting-url-field">Meeting URL<input type="url" name="meeting_url"
            value="<?= e($form['meeting_url'] ?? '') ?>" maxlength="500"
            placeholder="https://meet.google.com/..."></label>
    <label id="office-location-field">Office location<input name="interview_location"
            value="<?= e($form['interview_location'] ?? '') ?>" maxlength="255"
            placeholder="Building, street, city, floor and room"></label>
    <label class="full">Notes<textarea name="interview_notes"
            placeholder="What to bring, arrival instructions, or meeting passcode"><?= e($form['interview_notes'] ?? '') ?></textarea></label>
    <div class="full">
        <button><?= !empty($application['interview_at']) ? 'Save interview changes' : 'Schedule interview' ?></button>
    </div>
</form>
<script>
    const mode = document.getElementById('interview-mode');
    const meetingField = document.getElementById('meeting-url-field');
    const officeField = document.getElementById('office-location-field');
    const updateInterviewFields = () => {
        const online = mode.value === 'online';
        meetingField.hidden = !online;
        officeField.hidden = online;
        meetingField.querySelector('input').required = online;
        meetingField.querySelector('input').disabled = !online;
        officeField.querySelector('input').required = !online;
        officeField.querySelector('input').disabled = online;
    };
    mode.addEventListener('change', updateInterviewFields);
    updateInterviewFields();
</script>
<?php page_footer(); ?>