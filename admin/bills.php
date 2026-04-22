<?php
/**
 * Create bills, mark paid, link to invoice
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = db();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_bill'])) {
    if (!csrf_verify()) {
        $err = 'Invalid security token.';
    } else {
        $patient_id = (int) ($_POST['patient_id'] ?? 0);
        $amount = (float) ($_POST['amount'] ?? 0);
        $description = clean_string($_POST['description'] ?? '', 2000);

        if ($patient_id <= 0 || $amount <= 0) {
            $err = 'Select patient and enter a positive amount.';
        } else {
            $inv = generate_invoice_number($pdo);
            $st = $pdo->prepare(
                'INSERT INTO bills (patient_id, invoice_number, amount, description, status) VALUES (?,?,?,?,\'pending\')'
            );
            $st->execute([$patient_id, $inv, $amount, $description]);
            $msg = 'Bill created. Invoice: ' . $inv;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {
    if (!csrf_verify()) {
        $err = 'Invalid token.';
    } else {
        $bid = (int) ($_POST['bill_id'] ?? 0);
        $method = clean_string($_POST['payment_method'] ?? 'cash', 50);
        if ($bid > 0) {
            $st = $pdo->prepare(
                "UPDATE bills SET status='paid', payment_method=?, paid_at=NOW() WHERE id=? AND status='pending'"
            );
            $st->execute([$method, $bid]);
            $msg = 'Payment recorded.';
        }
    }
}

$patients = $pdo->query('SELECT id, full_name, username FROM patients ORDER BY full_name')->fetchAll();
$bills = $pdo->query(
    'SELECT b.*, p.full_name AS patient_name, p.username
     FROM bills b JOIN patients p ON p.id = b.patient_id
     ORDER BY b.id DESC'
)->fetchAll();

$pageTitle = 'Bills & Payments';
$currentPage = 'bills';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert--success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?= h($err) ?></div><?php endif; ?>

<div class="glass" style="padding:24px;margin-bottom:24px;">
    <h2 class="section-title mt-0" style="font-size:1.1rem;">New bill</h2>
    <form method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;align-items:end;">
        <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="create_bill" value="1">
        <div class="form-group" style="margin:0;">
            <label for="patient_id">Patient</label>
            <select class="input" name="patient_id" id="patient_id" required>
                <option value="">— Select —</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"><?= h($p['full_name'] . ' (' . $p['username'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label for="amount">Amount (USD)</label>
            <input class="input" type="number" step="0.01" min="0.01" name="amount" id="amount" required>
        </div>
        <div class="form-group" style="margin:0;grid-column:1/-1;">
            <label for="description">Description</label>
            <input class="input" name="description" id="description" placeholder="e.g. Consultation, lab bundle">
        </div>
        <button type="submit" class="btn btn--primary">Generate bill</button>
    </form>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Patient</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bills as $b): ?>
                <tr>
                    <td><?= h($b['invoice_number']) ?></td>
                    <td><?= h($b['patient_name']) ?></td>
                    <td>$<?= h(number_format((float) $b['amount'], 2)) ?></td>
                    <td>
                        <?php if ($b['status'] === 'paid'): ?>
                            <span class="badge badge--paid">paid</span>
                        <?php else: ?>
                            <span class="badge badge--pending">pending</span>
                        <?php endif; ?>
                    </td>
                    <td><?= h($b['created_at']) ?></td>
                    <td>
                        <a class="btn btn--ghost btn--sm" href="invoice.php?id=<?= (int) $b['id'] ?>">Invoice</a>
                        <?php if ($b['status'] === 'pending'): ?>
                            <form method="post" style="display:inline-flex;gap:6px;margin-top:4px;flex-wrap:wrap;">
                                <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="mark_paid" value="1">
                                <input type="hidden" name="bill_id" value="<?= (int) $b['id'] ?>">
                                <select class="input" name="payment_method" style="width:auto;padding:6px 10px;">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="insurance">Insurance</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                                <button type="submit" class="btn btn--primary btn--sm">Mark paid</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
