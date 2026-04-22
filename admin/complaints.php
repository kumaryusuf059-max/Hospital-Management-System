<?php
/**
 * View and resolve complaints
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = db();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_complaint'])) {
    if (!csrf_verify()) {
        $err = 'Invalid security token.';
    } else {
        $cid = (int) ($_POST['complaint_id'] ?? 0);
        $response = clean_string($_POST['admin_response'] ?? '', 5000);
        if ($cid > 0 && $response !== '') {
            $st = $pdo->prepare(
                "UPDATE complaints SET status='resolved', admin_response=?, resolved_at=NOW() WHERE id=? AND status='open'"
            );
            $st->execute([$response, $cid]);
            $msg = 'Complaint marked resolved.';
        } else {
            $err = 'Response text required.';
        }
    }
}

$rows = $pdo->query(
    'SELECT c.*, p.full_name AS patient_name, p.username
     FROM complaints c JOIN patients p ON p.id = c.patient_id
     ORDER BY c.id DESC'
)->fetchAll();

$pageTitle = 'Complaints';
$currentPage = 'complaints';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert--success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?= h($err) ?></div><?php endif; ?>

<?php foreach ($rows as $c): ?>
    <div class="glass" style="padding:20px;margin-bottom:16px;">
        <div class="flex-between">
            <strong>#<?= (int) $c['id'] ?> — <?= h($c['subject']) ?></strong>
            <?php if ($c['status'] === 'open'): ?>
                <span class="badge badge--open">open</span>
            <?php else: ?>
                <span class="badge badge--resolved">resolved</span>
            <?php endif; ?>
        </div>
        <p class="text-muted" style="font-size:0.9rem;margin:8px 0;">
            <?= h($c['patient_name']) ?> (@<?= h($c['username']) ?>) · <?= h($c['created_at']) ?>
        </p>
        <p style="margin:0 0 12px;"><?= nl2br(h($c['message'])) ?></p>
        <?php if ($c['status'] === 'resolved'): ?>
            <div style="border-left:3px solid var(--accent-2);padding-left:12px;margin-top:12px;">
                <strong>Response:</strong>
                <p style="margin:8px 0 0;"><?= nl2br(h((string) $c['admin_response'])) ?></p>
                <p class="text-muted" style="font-size:0.85rem;"><?= h($c['resolved_at'] ?? '') ?></p>
            </div>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="resolve_complaint" value="1">
                <input type="hidden" name="complaint_id" value="<?= (int) $c['id'] ?>">
                <div class="form-group">
                    <label for="resp<?= (int) $c['id'] ?>">Admin response</label>
                    <textarea class="input" name="admin_response" id="resp<?= (int) $c['id'] ?>" required></textarea>
                </div>
                <button type="submit" class="btn btn--primary">Resolve</button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if (count($rows) === 0): ?>
    <p class="text-muted">No complaints yet.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
