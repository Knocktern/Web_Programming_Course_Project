<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$user = require_login('seeker');
$pdo = database();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = posted('action');
    if ($action === 'profile') {
        $pdo->prepare('UPDATE job_seekers SET full_name=?,phone=?,address=?,date_of_birth=?,profile_summary=? WHERE user_id=?')->execute([posted('full_name'), posted('phone') ?: null, posted('address') ?: null, posted('date_of_birth') ?: null, posted('profile_summary') ?: null, $user['id']]);
        flash('success', 'Profile updated.');
    }
    if ($action === 'skill' && posted('skill_name') !== '') {
        $pdo->prepare('INSERT INTO job_seeker_skills(seeker_id,skill_name,skill_level) VALUES(?,?,?) ON DUPLICATE KEY UPDATE skill_level=VALUES(skill_level)')->execute([$user['id'], posted('skill_name'), posted('skill_level')]);
        flash('success', 'Skill saved.');
    }
    if ($action === 'education' && posted('institution') !== '' && posted('degree') !== '') {
        $pdo->prepare('INSERT INTO education(seeker_id,institution,degree,field_of_study,start_date,end_date,result_gpa) VALUES(?,?,?,?,?,?,?)')->execute([$user['id'], posted('institution'), posted('degree'), posted('field') ?: null, posted('start_date') ?: null, posted('end_date') ?: null, posted('result') ?: null]);
        flash('success', 'Education added.');
    }
    if ($action === 'experience' && posted('company') !== '' && posted('position') !== '') {
        $pdo->prepare('INSERT INTO experience(seeker_id,company,position_title,description,start_date,end_date) VALUES(?,?,?,?,?,?)')->execute([$user['id'], posted('company'), posted('position'), posted('description') ?: null, posted('start_date'), posted('end_date') ?: null]);
        flash('success', 'Experience added.');
    }
    if ($action === 'project' && posted('title') !== '') {
        $pdo->prepare('INSERT INTO projects(seeker_id,title,description,technologies,project_url) VALUES(?,?,?,?,?)')->execute([$user['id'], posted('title'), posted('description') ?: null, posted('technologies') ?: null, posted('url') ?: null]);
        flash('success', 'Project added.');
    }
    if ($action === 'certification' && posted('certification_name') !== '') {
        $pdo->prepare('INSERT INTO certifications(seeker_id,certification_name,issuing_organization,issue_date,credential_url) VALUES(?,?,?,?,?)')->execute([$user['id'], posted('certification_name'), posted('issuer'), posted('issue_date') ?: null, posted('url') ?: null]);
        flash('success', 'Certification added.');
    }
    if ($action === 'language' && posted('language_name') !== '') {
        $pdo->prepare('INSERT INTO languages(seeker_id,language_name,proficiency) VALUES(?,?,?) ON DUPLICATE KEY UPDATE proficiency=VALUES(proficiency)')->execute([$user['id'], posted('language_name'), posted('proficiency')]);
        flash('success', 'Language saved.');
    }
    if ($action === 'delete_entry') {
        $allowedTables = ['job_seeker_skills', 'education', 'experience', 'projects', 'certifications', 'languages'];
        $table = posted('entry_table');
        if (in_array($table, $allowedTables, true)) {
            $pdo->prepare("DELETE FROM {$table} WHERE id = ? AND seeker_id = ?")->execute([(int) $_POST['entry_id'], $user['id']]);
            flash('success', 'CV entry removed.');
        }
    }
    redirect('profile.php');
}
$s = $pdo->prepare('SELECT js.*,u.email FROM job_seekers js JOIN users u ON u.id=js.user_id WHERE js.user_id=?');
$s->execute([$user['id']]);
$profile = $s->fetch();
$entryQueries = [
    'job_seeker_skills' => ['Skills', 'SELECT id, skill_name label, skill_level detail FROM job_seeker_skills WHERE seeker_id=?'],
    'education' => ['Education', "SELECT id, institution label, CONCAT(degree, COALESCE(CONCAT(' - ', field_of_study), '')) detail FROM education WHERE seeker_id=?"],
    'experience' => ['Experience', "SELECT id, company label, position_title detail FROM experience WHERE seeker_id=?"],
    'projects' => ['Projects', "SELECT id, title label, technologies detail FROM projects WHERE seeker_id=?"],
    'certifications' => ['Certifications', "SELECT id, certification_name label, issuing_organization detail FROM certifications WHERE seeker_id=?"],
    'languages' => ['Languages', 'SELECT id, language_name label, proficiency detail FROM languages WHERE seeker_id=?'],
];
page_header('My profile'); ?>
<h1>Profile and CV builder</h1>
<p>
    <a class="button" href="cv-preview.php">Preview / print CV</a>
    <a class="button secondary" href="dashboard.php">Back to dashboard</a>
</p>

<article class="card">

    <!-- Personal information -->
    <h2>Personal information</h2>
    <form method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="profile">
        <div><label>Full name</label><input name="full_name" value="<?= e($profile['full_name']) ?>" required></div>
        <div><label>Email</label><input value="<?= e($profile['email']) ?>" disabled></div>
        <div><label>Phone</label><input name="phone" value="<?= e($profile['phone']) ?>"></div>
        <div><label>Date of birth</label><input type="date" name="date_of_birth" value="<?= e($profile['date_of_birth']) ?>"></div>
        <div class="full"><label>Address</label><input name="address" value="<?= e($profile['address']) ?>"></div>
        <div class="full"><label>Professional summary</label><textarea name="profile_summary"><?= e($profile['profile_summary']) ?></textarea></div>
        <div class="full"><button>Save profile</button></div>
    </form>

    <hr>

    <!-- Skills -->
    <h2>Skills</h2>
    <?php $entries = $pdo->prepare('SELECT id, skill_name label, skill_level detail FROM job_seeker_skills WHERE seeker_id=?');
    $entries->execute([$user['id']]); $skillEntries = $entries->fetchAll(); ?>
    <?php if ($skillEntries): ?>
        <?php foreach ($skillEntries as $entry): ?>
        <div class="entry-row">
            <span><strong><?= e($entry['label']) ?></strong> <small><?= e($entry['detail']) ?></small></span>
            <form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="delete_entry"><input type="hidden" name="entry_table" value="job_seeker_skills"><input type="hidden" name="entry_id" value="<?= $entry['id'] ?>"><button class="button danger small">Remove</button></form>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <form method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="skill">
        <div><label>Skill name</label><input name="skill_name" required></div>
        <div><label>Level</label><select name="skill_level"><option>beginner</option><option>intermediate</option><option>advanced</option><option>expert</option></select></div>
        <div class="full"><button>Add skill</button></div>
    </form>

    <hr>

    <!-- Education -->
    <h2>Education</h2>
    <?php $entries = $pdo->prepare("SELECT id, institution label, CONCAT(degree, COALESCE(CONCAT(' - ', field_of_study), '')) detail FROM education WHERE seeker_id=?");
    $entries->execute([$user['id']]); $eduEntries = $entries->fetchAll(); ?>
    <?php if ($eduEntries): ?>
        <?php foreach ($eduEntries as $entry): ?>
        <div class="entry-row">
            <span><strong><?= e($entry['label']) ?></strong> <small><?= e($entry['detail']) ?></small></span>
            <form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="delete_entry"><input type="hidden" name="entry_table" value="education"><input type="hidden" name="entry_id" value="<?= $entry['id'] ?>"><button class="button danger small">Remove</button></form>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <form method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="education">
        <div><label>Institution</label><input name="institution" required></div>
        <div><label>Degree</label><input name="degree" required></div>
        <div><label>Field of study</label><input name="field"></div>
        <div><label>Result / GPA</label><input name="result"></div>
        <div><label>Start date</label><input type="date" name="start_date"></div>
        <div><label>End date</label><input type="date" name="end_date"></div>
        <div class="full"><button>Add education</button></div>
    </form>

    <hr>

    <!-- Experience -->
    <h2>Experience</h2>
    <?php $entries = $pdo->prepare("SELECT id, company label, position_title detail FROM experience WHERE seeker_id=?");
    $entries->execute([$user['id']]); $expEntries = $entries->fetchAll(); ?>
    <?php if ($expEntries): ?>
        <?php foreach ($expEntries as $entry): ?>
        <div class="entry-row">
            <span><strong><?= e($entry['label']) ?></strong> <small><?= e($entry['detail']) ?></small></span>
            <form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="delete_entry"><input type="hidden" name="entry_table" value="experience"><input type="hidden" name="entry_id" value="<?= $entry['id'] ?>"><button class="button danger small">Remove</button></form>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <form method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="experience">
        <div><label>Company</label><input name="company" required></div>
        <div><label>Position</label><input name="position" required></div>
        <div class="full"><label>Description</label><textarea name="description"></textarea></div>
        <div><label>Start date</label><input type="date" name="start_date" required></div>
        <div><label>End date</label><input type="date" name="end_date"></div>
        <div class="full"><button>Add experience</button></div>
    </form>

    <hr>

    <!-- Projects -->
    <h2>Projects</h2>
    <?php $entries = $pdo->prepare("SELECT id, title label, technologies detail FROM projects WHERE seeker_id=?");
    $entries->execute([$user['id']]); $projEntries = $entries->fetchAll(); ?>
    <?php if ($projEntries): ?>
        <?php foreach ($projEntries as $entry): ?>
        <div class="entry-row">
            <span><strong><?= e($entry['label']) ?></strong> <small><?= e($entry['detail']) ?></small></span>
            <form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="delete_entry"><input type="hidden" name="entry_table" value="projects"><input type="hidden" name="entry_id" value="<?= $entry['id'] ?>"><button class="button danger small">Remove</button></form>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <form method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="project">
        <div><label>Title</label><input name="title" required></div>
        <div><label>Technologies</label><input name="technologies"></div>
        <div class="full"><label>Description</label><textarea name="description"></textarea></div>
        <div class="full"><label>URL</label><input name="url" type="url"></div>
        <div class="full"><button>Add project</button></div>
    </form>

    <hr>

    <!-- Certifications -->
    <h2>Certifications</h2>
    <?php $entries = $pdo->prepare("SELECT id, certification_name label, issuing_organization detail FROM certifications WHERE seeker_id=?");
    $entries->execute([$user['id']]); $certEntries = $entries->fetchAll(); ?>
    <?php if ($certEntries): ?>
        <?php foreach ($certEntries as $entry): ?>
        <div class="entry-row">
            <span><strong><?= e($entry['label']) ?></strong> <small><?= e($entry['detail']) ?></small></span>
            <form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="delete_entry"><input type="hidden" name="entry_table" value="certifications"><input type="hidden" name="entry_id" value="<?= $entry['id'] ?>"><button class="button danger small">Remove</button></form>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <form method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="certification">
        <div><label>Name</label><input name="certification_name" required></div>
        <div><label>Issuer</label><input name="issuer" required></div>
        <div><label>Issue date</label><input type="date" name="issue_date"></div>
        <div><label>Credential URL</label><input name="url" type="url"></div>
        <div class="full"><button>Add certification</button></div>
    </form>

    <hr>

    <!-- Languages -->
    <h2>Languages</h2>
    <?php $entries = $pdo->prepare('SELECT id, language_name label, proficiency detail FROM languages WHERE seeker_id=?');
    $entries->execute([$user['id']]); $langEntries = $entries->fetchAll(); ?>
    <?php if ($langEntries): ?>
        <?php foreach ($langEntries as $entry): ?>
        <div class="entry-row">
            <span><strong><?= e($entry['label']) ?></strong> <small><?= e($entry['detail']) ?></small></span>
            <form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="delete_entry"><input type="hidden" name="entry_table" value="languages"><input type="hidden" name="entry_id" value="<?= $entry['id'] ?>"><button class="button danger small">Remove</button></form>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <form method="post" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="language">
        <div><label>Language</label><input name="language_name" required></div>
        <div><label>Proficiency</label><select name="proficiency"><option>basic</option><option>conversational</option><option>professional</option><option>native</option></select></div>
        <div class="full"><button>Add language</button></div>
    </form>

</article>
<?php page_footer(); ?>