<?php
// Shared by the seeker dashboard, application history, and employer preview.
// The calling page must load an application belonging to the signed-in user.
function interview_details(array $application): void
{
    $scheduled = !empty($application['interview_at']);
    ?>
    <div class="interview-details">
        <?php if (!$scheduled): ?>
            <p class="meta"><?= $application['status'] === 'interview'
                ? 'The employer has marked this application for interview. The date and joining details have not been shared yet.'
                : 'No interview scheduled yet. Details will appear here when the employer schedules one.' ?></p>
        <?php else: ?>
            <dl class="detail-list">
                <div><dt>Date &amp; time</dt><dd><time datetime="<?= e(date('Y-m-d\TH:i:s', strtotime($application['interview_at']))) ?>"><?= e(date('D, M j, Y · g:i A', strtotime($application['interview_at']))) ?></time><span class="timezone-label"><?= e(date_default_timezone_get()) ?></span></dd></div>
                <div><dt>Interview type</dt><dd><?= ($application['interview_mode'] ?? '') === 'office' ? 'Office interview' : (($application['interview_mode'] ?? '') === 'online' ? 'Online meeting' : 'Not specified') ?></dd></div>
                <?php if (!empty($application['interview_location'])): ?>
                    <div><dt>Office location</dt><dd><?= nl2br(e($application['interview_location'])) ?></dd></div>
                <?php endif; ?>
                <?php if (!empty($application['meeting_url']) && is_http_url($application['meeting_url'])): ?>
                    <div><dt>Meeting link</dt><dd><a class="text-link" href="<?= e($application['meeting_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($application['meeting_url']) ?></a></dd></div>
                <?php elseif (($application['interview_mode'] ?? '') === 'online'): ?>
                    <div><dt>Meeting link</dt><dd>The employer has not provided a valid meeting link yet.</dd></div>
                <?php endif; ?>
                <?php if (($application['interview_mode'] ?? '') === 'office' && empty($application['interview_location'])): ?>
                    <div><dt>Office location</dt><dd>The employer has not provided an office address yet.</dd></div>
                <?php endif; ?>
            </dl>
        <?php endif; ?>
        <?php if (!empty($application['interview_notes'])): ?>
            <div class="interview-notes"><strong>Instructions from the employer</strong><p><?= nl2br(e($application['interview_notes'])) ?></p></div>
        <?php endif; ?>
        <?php if ($scheduled && !empty($application['meeting_url']) && is_http_url($application['meeting_url'])): ?>
            <a class="button small" href="<?= e($application['meeting_url']) ?>" target="_blank" rel="noopener noreferrer">Open meeting (new tab)</a>
        <?php endif; ?>
    </div>
    <?php
}
