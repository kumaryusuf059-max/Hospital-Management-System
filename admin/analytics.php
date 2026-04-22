<?php
/**
 * Analytics dashboard — aggregates & charts
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = db();

$totalPatients = (int) $pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn();
$totalAppt = (int) $pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn();
$apptByStatus = $pdo->query(
    "SELECT status, COUNT(*) AS c FROM appointments GROUP BY status"
)->fetchAll(PDO::FETCH_KEY_PAIR);

$openComplaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE status='open'")->fetchColumn();
$totalReports = (int) $pdo->query('SELECT COUNT(*) FROM reports')->fetchColumn();

$revenuePending = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM bills WHERE status='pending'")->fetchColumn();
$revenuePaid = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM bills WHERE status='paid'")->fetchColumn();

// Appointments per department (top)
$deptRows = $pdo->query(
    'SELECT department, COUNT(*) AS c FROM appointments GROUP BY department ORDER BY c DESC LIMIT 8'
)->fetchAll();
$maxDept = 1;
foreach ($deptRows as $d) {
    if ((int) $d['c'] > $maxDept) {
        $maxDept = (int) $d['c'];
    }
}

$pageTitle = 'Analytics';
$currentPage = 'analytics';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="stats-grid">
    <div class="stat-card glass">
        <div class="label">Patients</div>
        <div class="value"><?= $totalPatients ?></div>
    </div>
    <div class="stat-card glass">
        <div class="label">Appointments</div>
        <div class="value"><?= $totalAppt ?></div>
    </div>
    <div class="stat-card glass">
        <div class="label">Open complaints</div>
        <div class="value"><?= $openComplaints ?></div>
    </div>
    <div class="stat-card glass">
        <div class="label">Reports on file</div>
        <div class="value"><?= $totalReports ?></div>
    </div>
</div>

<div class="chart-block glass">
    <h2 class="section-title mt-0" style="font-size:1.1rem;">Revenue snapshot</h2>
    <p>Paid: <strong class="text-success">$<?= h(number_format($revenuePaid, 2)) ?></strong>
        &nbsp;·&nbsp; Pending: <strong>$<?= h(number_format($revenuePending, 2)) ?></strong></p>
</div>

<div class="chart-block glass">
    <h2 class="section-title mt-0" style="font-size:1.1rem;">Appointment status</h2>
    <?php foreach (['pending', 'approved', 'rejected'] as $st): ?>
        <?php $n = (int) ($apptByStatus[$st] ?? 0); ?>
        <div class="chart-row">
            <span class="chart-label"><?= h(ucfirst($st)) ?></span>
            <div class="chart-bar-wrap">
                <?php $pct = $totalAppt > 0 ? round(($n / $totalAppt) * 100) : 0; ?>
                <div class="chart-bar" style="width: <?= (int) $pct ?>%;"></div>
            </div>
            <span style="font-family:var(--font-mono);font-size:0.85rem;min-width:36px;"><?= $n ?></span>
        </div>
    <?php endforeach; ?>
</div>

<div class="chart-block glass">
    <h2 class="section-title mt-0" style="font-size:1.1rem;">Appointments by department</h2>
    <?php foreach ($deptRows as $d): ?>
        <?php $n = (int) $d['c']; $pct = $maxDept > 0 ? round(($n / $maxDept) * 100) : 0; ?>
        <div class="chart-row">
            <span class="chart-label" style="width:120px;"><?= h($d['department']) ?></span>
            <div class="chart-bar-wrap">
                <div class="chart-bar" style="width: <?= (int) $pct ?>%;"></div>
            </div>
            <span style="font-family:var(--font-mono);font-size:0.85rem;min-width:36px;"><?= $n ?></span>
        </div>
    <?php endforeach; ?>
    <?php if (count($deptRows) === 0): ?>
        <p class="text-muted">No data yet.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
