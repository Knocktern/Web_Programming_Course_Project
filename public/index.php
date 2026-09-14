<?php require_once __DIR__ . '/../includes/bootstrap.php';
page_header('Find your next opportunity'); ?>

<style>
.hero {
    text-align: center;
    margin: 0 auto;
    padding: 8rem 1rem 6rem;
    max-width: 900px;
}
.hero h1 {
    font-size: clamp(3rem, 8vw, 5rem);
    margin-top: 1.5rem;
}
.hero .lead {
    margin: 0 auto 2.5rem;
    font-size: 1.5rem;
    color: var(--slate);
}
.hero-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}
.features-section {
    padding: 6rem 0;
    border-top: 1px solid var(--line);
    margin-top: 4rem;
}
.section-title {
    text-align: center;
    margin-bottom: 4rem;
}
.feature-card {
    text-align: center;
    padding: 3rem 2rem;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 24px;
    box-shadow: var(--shadow-sm);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}
.feature-icon {
    font-size: 3rem;
    margin-bottom: 1.5rem;
    display: inline-block;
}
.cta-section {
    background: var(--ink);
    color: #fff;
    border-radius: 32px;
    padding: 5rem 2rem;
    text-align: center;
    margin: 6rem 0 2rem;
}
.cta-section h2 {
    color: #fff;
    margin-top: 0;
}
.cta-section .lead {
    color: rgba(255, 255, 255, 0.8);
}
.cta-section .button {
    background: #fff;
    color: var(--ink);
}
.cta-section .button.secondary {
    background: transparent;
    color: #fff;
    border-color: rgba(255,255,255,0.3);
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
        <?php if (!is_logged_in()): ?>
            <a class="button secondary" href="register.php" style="font-size: 1.1rem; padding: 1rem 2rem;">Create Account</a>
        <?php else: ?>
            <a class="button secondary" href="<?= dashboard_path(get_user_role()) ?>" style="font-size: 1.1rem; padding: 1rem 2rem;">Go to Dashboard</a>
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
            <div class="feature-icon">🎯</div>
            <h3>For job seekers</h3>
            <p>Build your comprehensive profile, take preliminary skills assessments, and apply with confidence.</p>
        </article>
        
        <article class="feature-card">
            <div class="feature-icon">🏢</div>
            <h3>For employers</h3>
            <p>Publish targeted roles, build practical screening quizzes, and manage qualified candidates efficiently.</p>
        </article>
        
        <article class="feature-card">
            <div class="feature-icon">📚</div>
            <h3>For learning</h3>
            <p>Use category-linked course recommendations to prepare for roles and upskill for your next attempt.</p>
        </article>
    </div>
</section>

<section class="cta-section">
    <h2>Ready to take the next step?</h2>
    <p class="lead" style="margin: 0 auto 2rem; max-width: 600px;">Join thousands of job seekers and top companies using SkillGate to find the perfect match.</p>
    <div class="hero-buttons">
        <?php if (!is_logged_in()): ?>
            <a class="button" href="register.php">Get Started for Free</a>
            <a class="button secondary" href="login.php">Sign In</a>
        <?php else: ?>
             <a class="button" href="jobs.php">Find your dream job</a>
        <?php endif; ?>
    </div>
</section>

<?php page_footer(); ?>