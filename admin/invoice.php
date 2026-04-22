<?php
/**
 * Printable HTML invoice (PDF-like layout)
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = db();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$st = $pdo->prepare(
    'SELECT b.*, p.full_name AS patient_name, p.email, p.phone, p.address
     FROM bills b JOIN patients p ON p.id = b.patient_id
     WHERE b.id = ?'
);
$st->execute([$id]);
$b = $st->fetch();
if (!$b) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$pageTitle = 'Invoice ' . $b['invoice_number'];
$currentPage = 'bills';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="invoice-page no-print">
    <div class="invoice-actions flex-between">
        <a href="bills.php" class="btn btn--ghost">â† Back to bills</a>
        <button type="button" class="btn btn--primary" onclick="window.print()">Print / Save as PDF</button>
    </div>
</div>

<div class="glass invoice-print" id="invoice">
    <div class="flex-between" style="margin-bottom:24px;">
        <div>
            <h2 style="margin:0;font-size:1.5rem;">Dhami Hospital</h2>
            <p class="text-muted" style="margin:4px 0 0;font-size:0.9rem;">Neo City Medical District</p>
        </div>
        <div style="text-align:right;">
            <div style="font-family:var(--font-mono);font-size:1.25rem;"><?= h($b['invoice_number']) ?></div>
            <p class="text-muted" style="margin:4px 0 0;font-size:0.85rem;">Issued: <?= h($b['created_at']) ?></p>
        </div>
    </div>
    <hr style="border:none;border-top:1px solid var(--glass-border);margin:16px 0;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
        <div>
            <h3 style="margin:0 0 8px;font-size:0.85rem;color:var(--text-muted);text-transform:uppercase;">Bill to</h3>
            <p style="margin:0;font-weight:600;"><?= h($b['patient_name']) ?></p>
            <p class="text-muted" style="margin:4px 0 0;font-size:0.9rem;"><?= h($b['email'] ?? '') ?></p>
            <p class="text-muted" style="margin:4px 0 0;font-size:0.9rem;"><?= h($b['phone'] ?? '') ?></p>
            <p class="text-muted" style="margin:4px 0 0;font-size:0.9rem;"><?= h($b['address'] ?? '') ?></p>
        </div>
        <div style="text-align:right;">
            <h3 style="margin:0 0 8px;font-size:0.85rem;color:var(--text-muted);text-transform:uppercase;">Status</h3>
            <p style="margin:0;">
                <?php if ($b['status'] === 'paid'): ?>
                    <span class="badge badge--paid">Paid</span>
                <?php else: ?>
                    <span class="badge badge--pending">Pending</span>
                <?php endif; ?>
            </p>
            <?php if ($b['status'] === 'paid' && $b['paid_at']): ?>
                <p class="text-muted" style="margin:8px 0 0;font-size:0.9rem;">Paid: <?= h($b['paid_at']) ?></p>
                <p class="text-muted" style="margin:4px 0 0;font-size:0.9rem;">Method: <?= h($b['payment_method'] ?? '') ?></p>
            <?php endif; ?>
        </div>
    </div>
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
    <div style="text-align:right;margin-top:16px;font-size:1.25rem;font-weight:700;">
        Total: $<?= h(number_format((float) $b['amount'], 2)) ?>
    </div>
    <p class="text-muted" style="margin-top:32px;font-size:0.8rem;">
        Thank you for choosing Dhami Hospital. This is a computer-generated document.
    </p>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
