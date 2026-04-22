<?php
/**
 * Admin dashboard — overview stats + basic charts
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = db();

$patients = (int) $pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn();
$appointments = (int) $pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn();
$pendingAppt = (int) $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();

$revRow = $pdo->query("SELECT COALESCE(SUM(amount),0) AS t FROM bills WHERE status = 'paid'")->fetch();
$revenue = (float) ($revRow['t'] ?? 0);

// Last 6 months revenue for chart (paid bills)
$chart = [];
$maxVal = 1.0;
for ($i = 5; $i >= 0; $i--) {
    $d = new DateTimeImmutable('first day of this month');
    $d = $d->modify("-$i months");
    $start = $d->format('Y-m-01');
    $end = $d->format('Y-m-t');
    $st = $pdo->prepare(
        "SELECT COALESCE(SUM(amount),0) AS s FROM bills WHERE status = 'paid'
         AND paid_at IS NOT NULL AND DATE(paid_at) BETWEEN ? AND ?"
    );
    $st->execute([$start, $end]);
    $s = (float) $st->fetchColumn();
    $chart[] = ['label' => $d->format('M Y'), 'value' => $s];
    if ($s > $maxVal) {
        $maxVal = $s;
    }
}

$pageTitle = 'Dashboard';
$currentPage = 'dash';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="stats-grid">
    <div class="stat-card glass">
        <div class="label">Patients</div>
        <div class="value"><?= h((string) $patients) ?></div>
    </div>
    <div class="stat-card glass">
        <div class="label">Appointments</div>
        <div class="value"><?= h((string) $appointments) ?></div>
    </div>
    <div class="stat-card glass">
        <div class="label">Pending approvals</div>
        <div class="value"><?= h((string) $pendingAppt) ?></div>
    </div>
    <div class="stat-card glass">
        <div class="label">Total revenue (paid)</div>
        <div class="value">$<?= h(number_format($revenue, 2)) ?></div>
    </div>
</div>

<div class="chart-block glass">
    <h2 class="section-title mt-0" style="font-size:1.1rem;">Revenue by month (paid bills)</h2>
    <p class="text-muted mb-2" style="font-size:0.9rem;">Based on payment date</p>
    <?php foreach ($chart as $row): ?>
        <?php
        $pct = $maxVal > 0 ? round(($row['value'] / $maxVal) * 100) : 0;
        ?>
        <div class="chart-row">
            <span class="chart-label"><?= h($row['label']) ?></span>
            <div class="chart-bar-wrap">
                <div class="chart-bar" style="width: <?= (int) $pct ?>%;"></div>
            </div>
            <span style="font-family:var(--font-mono);font-size:0.85rem;min-width:72px;text-align:right;">
                $<?= h(number_format($row['value'], 0)) ?>
            </span>
        </div>
    <?php endforeach; ?>
</div>

<div class="flex-between mb-2">
    <h2 class="section-title mt-0" style="font-size:1.1rem;">Quick actions</h2>
</div>
<div class="card-grid">
    <a class="feature-card glass" href="appointments.php" style="text-decoration:none;color:inherit;">
        <h3>Review appointments</h3>
        <p class="text-muted"><?= (int) $pendingAppt ?> pending</p>
    </a>
    <a class="feature-card glass" href="bills.php" style="text-decoration:none;color:inherit;">
        <h3>Billing</h3>
        <p class="text-muted">Issue invoices &amp; record payments</p>
    </a>
    <a class="feature-card glass" href="complaints.php" style="text-decoration:none;color:inherit;">
        <h3>Complaints</h3>
        <p class="text-muted">Resolve patient feedback</p>
    </a>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
