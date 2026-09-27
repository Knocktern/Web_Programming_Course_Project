<?php require_once __DIR__ . '/../includes/bootstrap.php';
page_header('Find your next opportunity'); ?>

<style>
/* Override default hero background to ensure uniform page background */
.hero {
    text-align: center;
    margin: 0 auto;
    padding: 3.5rem 1rem 3rem;
    max-width: 900px;
    background: transparent !important;
}
.hero h1 {
    font-size: clamp(2.25rem, 5vw, 3.5rem);
    margin-top: 1.5rem;
}
.hero .lead {
    margin: 0 auto 2.5rem;
    font-size: 1.05rem;
    color: var(--slate);
    max-width: 600px;
}
.hero-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}
.features-section {
    padding: 2rem 0;
    margin-top: 2rem;
}
.section-title {
    text-align: center;
    margin-bottom: 2rem;
}
.feature-card {
    padding: 1.5rem;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 24px;
    box-shadow: var(--shadow-sm);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
}
.feature-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}
.feature-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    background: var(--peach);
    color: var(--amber);
    border-radius: 16px;
    margin-bottom: 2rem;
}
.feature-icon .material-symbols-outlined {
    font-size: 32px;
}
.feature-card h3 {
    margin: 0 0 1rem 0;
    font-size: 1.2rem;
}
.feature-card p {
    color: var(--slate);
    margin: 0;
    line-height: 1.6;
}
.cta-section {
    background: var(--ink);
    color: #fff;
    border-radius: 32px;
    padding: 3rem 1.5rem;
    text-align: center;
    margin: 3rem 0 2rem;
}
.cta-section h2 {
    color: #fff;
    margin-top: 0;
    font-size: clamp(1.5rem, 3vw, 2rem);
}
.cta-section .lead {
    color: rgba(255, 255, 255, 0.7);
    max-width: 500px;
}
.cta-section .button {
    background: #fff;
    color: var(--ink);
}
.cta-section .button.secondary {
    background: transparent;
    color: #fff;
    border-color: rgba(255,255,255,0.2);
}
.cta-section .button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
</style>

<section class="hero">
    <span class="badge" style="background: var(--peach); color: var(--amber);">Career readiness, made practical</span>
    <h1>Prepare. Prove it. Get hired.</h1>
    <p class="lead">Explore roles, build a professional CV, complete job-specific screening, and strengthen your skills through focused courses.</p>
    <div class="hero-buttons">
        <a class="button" href="jobs.php" style="font-size: 1.1rem; padding: 1rem 2rem;">Explore Jobs</a>
        <?php if (!current_user()): ?>
            <a class="button secondary" href="register.php" style="font-size: 1.1rem; padding: 1rem 2rem;">Create Account</a>
        <?php else: ?>
            <a class="button secondary" href="<?= dashboard_path(current_user()['role']) ?>" style="font-size: 1.1rem; padding: 1rem 2rem;">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</section>

<section class="features-section">
    <div class="section-title">
        <h2>How SkillGate works</h2>
        <p class="lead" style="margin:0 auto; text-align:center;">An end-to-end platform connecting talent with opportunity.</p>
    </div>
    <div class="grid">
        <article class="feature-card">
            <div class="feature-icon">
                <span class="material-symbols-outlined">person_search</span>
            </div>
            <h3>For job seekers</h3>
            <p>Build your comprehensive profile, take preliminary skills assessments, and apply to relevant roles with confidence.</p>
        </article>
        
        <article class="feature-card">
            <div class="feature-icon">
                <span class="material-symbols-outlined">business_center</span>
            </div>
            <h3>For employers</h3>
            <p>Publish targeted roles, build practical screening quizzes, and manage qualified candidates efficiently in one place.</p>
        </article>
        
        <article class="feature-card">
            <div class="feature-icon">
                <span class="material-symbols-outlined">school</span>
            </div>
            <h3>For learning</h3>
            <p>Use category-linked course recommendations to prepare for specific roles and upskill for your next career move.</p>
        </article>
    </div>
</section>

<section class="cta-section">
    <h2>Ready to take the next step?</h2>
    <p class="lead" style="margin: 0 auto 2rem; max-width: 600px;">Build your profile, practise your skills, and take the next step toward your career.</p>
    <div class="hero-buttons">
        <?php if (!current_user()): ?>
            <a class="button" href="register.php">Get Started for Free</a>
            <a class="button secondary" href="login.php">Sign In</a>
        <?php else: ?>
             <a class="button" href="jobs.php">Find your dream job</a>
        <?php endif; ?>
    </div>
</section>

<?php page_footer(); ?>
