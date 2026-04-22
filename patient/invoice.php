<?php
/**
 * Patient invoice view (read-only, own bills only)
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_patient();
$pdo = db();
$pid = patient_id();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$st = $pdo->prepare(
    'SELECT b.*, p.full_name AS patient_name, p.email, p.phone, p.address
     FROM bills b JOIN patients p ON p.id = b.patient_id
     WHERE b.id = ? AND b.patient_id = ?'
);
$st->execute([$id, $pid]);
$b = $st->fetch();
if (!$b) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$pageTitle = 'Invoice';
$currentPage = 'bills';
require_once __DIR__ . '/../includes/patient_header.php';
?>

<div class="invoice-page no-print flex-between" style="margin-bottom:16px;">
    <a href="bills.php" class="btn btn--ghost">â† Back</a>
    <button type="button" class="btn btn--primary" onclick="window.print()">Print / Save PDF</button>
</div>

<div class="glass invoice-print" id="invoice">
    <div class="flex-between" style="margin-bottom:24px;">
        <div>
            <h2 style="margin:0;font-size:1.5rem;">Dhami Hospital</h2>
            <p class="text-muted" style="margin:4px 0 0;font-size:0.9rem;">Invoice for patient</p>
        </div>
        <div style="text-align:right;">
            <div style="font-family:var(--font-mono);font-size:1.25rem;"><?= h($b['invoice_number']) ?></div>
            <p class="text-muted" style="margin:4px 0 0;font-size:0.85rem;">Issued: <?= h($b['created_at']) ?></p>
        </div>
    </div>
    <hr style="border:none;border-top:1px solid var(--glass-border);margin:16px 0;">
    <p style="margin:0 0 8px;"><strong><?= h($b['patient_name']) ?></strong></p>
    <p class="text-muted" style="margin:0;font-size:0.9rem;"><?= h($b['email'] ?? '') ?></p>
    <table class="data-table" style="margin-top:24px;">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= h($b['description'] ?: 'Medical services') ?></td>
                <td style="text-align:right;font-family:var(--font-mono);">$<?= h(number_format((float) $b['amount'], 2)) ?></td>
            </tr>
        </tbody>
    </table>
    <div style="text-align:right;margin-top:16px;font-size:1.15rem;font-weight:700;">
        Total: $<?= h(number_format((float) $b['amount'], 2)) ?>
    </div>
    <p style="margin-top:16px;">
        Status:
        <?php if ($b['status'] === 'paid'): ?>
            <span class="badge badge--paid">Paid</span>
            <?php if ($b['paid_at']): ?> Â· <?= h($b['paid_at']) ?><?php endif; ?>
        <?php else: ?>
            <span class="badge badge--pending">Pending payment</span>
        <?php endif; ?>
    </p>
</div>

<?php require_once __DIR__ . '/../includes/patient_footer.php'; ?>
