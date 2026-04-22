<?php
/**
 * Public landing page â€” hero, about, services, contact, book appointment CTA
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

session_boot();

$contactSuccess = false;
$contactError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    if (!csrf_verify()) {
        $contactError = 'Invalid security token. Please try again.';
    } else {
        $name = clean_string($_POST['name'] ?? '', 100);
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $subject = clean_string($_POST['subject'] ?? '', 200);
        $message = clean_string($_POST['message'] ?? '', 5000);

        if ($name === '' || !$email || $subject === '' || $message === '') {
            $contactError = 'Please fill in all fields with a valid email.';
        } else {
            try {
                $pdo = db();
                $st = $pdo->prepare(
                    'INSERT INTO contact_messages (name, email, subject, message) VALUES (?,?,?,?)'
                );
                $st->execute([$name, $email, $subject, $message]);
                $contactSuccess = true;
            } catch (Throwable $e) {
                $contactError = 'Could not send message. Please try again later.';
            }
        }
    }
}

$pageTitle = 'Welcome';
$GLOBALS['__asset_prefix'] = '';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero container">
    <div class="hero-badge">Next-gen hospital OS</div>
    <h1>Care orchestrated by intelligence</h1>
    <p class="hero-lead">
        Dhami Hospital unifies appointments, diagnostics, and billing in one futuristic command center â€”
        glass-clear workflows with neon precision.
    </p>
    <div class="hero-cta">
        <a class="btn btn--primary" href="register.php">Book as Patient</a>
        <a class="btn btn--ghost" href="#contact">Contact Us</a>
    </div>
</section>

<section class="section container" id="about">
    <h2 class="section-title">About Dhami Hospital</h2>
    <p class="section-sub">
        We are a digitally native hospital network focused on speed, transparency, and patient sovereignty.
        Our command dashboards give clinicians clarity; our patient portal gives you control.
    </p>
    <div class="card-grid">
        <article class="feature-card glass">
            <div class="feature-icon">âš¡</div>
            <h3>Real-time coordination</h3>
            <p>Appointments flow through intelligent queues with instant status updates.</p>
        </article>
        <article class="feature-card glass">
            <div class="feature-icon">ðŸ”’</div>
            <h3>Secure records</h3>
            <p>Medical reports are isolated per patient with audited admin uploads.</p>
        </article>
        <article class="feature-card glass">
            <div class="feature-icon">ðŸ“Š</div>
            <h3>Operational clarity</h3>
            <p>Analytics and billing snapshots keep leadership ahead of demand.</p>
        </article>
    </div>
</section>

<section class="section container" id="services">
    <h2 class="section-title">Services</h2>
    <p class="section-sub">Comprehensive care rails â€” from triage to discharge.</p>
    <div class="card-grid">
        <article class="feature-card glass">
            <div class="feature-icon">ðŸ©º</div>
            <h3>Outpatient &amp; ER</h3>
            <p>Rapid intake, vitals capture, and specialist routing.</p>
        </article>
        <article class="feature-card glass">
            <div class="feature-icon">ðŸ§ª</div>
            <h3>Diagnostics</h3>
            <p>Labs, imaging, and structured medical test catalog with pricing.</p>
        </article>
        <article class="feature-card glass">
            <div class="feature-icon">ðŸ’³</div>
            <h3>Billing</h3>
            <p>Transparent invoices, payment capture, and downloadable statements.</p>
        </article>
    </div>
</section>

<section class="section container" id="contact">
    <h2 class="section-title">Contact</h2>
    <p class="section-sub">Reach our liaison team â€” encrypted intake, human response.</p>

    <?php if ($contactSuccess): ?>
        <div class="alert alert--success">Thank you â€” your message was received.</div>
    <?php endif; ?>
    <?php if ($contactError !== ''): ?>
        <div class="alert alert--error"><?= h($contactError) ?></div>
    <?php endif; ?>

    <div class="contact-split">
        <form method="post" action="index.php#contact" class="glass" style="padding:24px;">
            <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
            <div class="form-group">
                <label for="name">Name</label>
                <input class="input" type="text" id="name" name="name" required maxlength="100"
                       value="<?= h($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input class="input" type="email" id="email" name="email" required
                       value="<?= h($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <input class="input" type="text" id="subject" name="subject" required maxlength="200"
                       value="<?= h($_POST['subject'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea class="input" id="message" name="message" required><?= h($_POST['message'] ?? '') ?></textarea>
            </div>
            <button type="submit" name="contact_submit" value="1" class="btn btn--primary">Send Message</button>
        </form>
        <div class="glass" style="padding:24px;">
            <h3 class="mt-0">Visit</h3>
            <p class="text-muted">Dhami Tower, Sector 7<br>Neo City â€” Medical District</p>
            <h3>Direct line</h3>
            <p class="text-muted" style="font-family:var(--font-mono);">+1 (555) 010-2048</p>
            <a class="btn btn--primary" href="register.php" style="margin-top:16px;">Book Appointment</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
