<?php
/**
 * Approve / deny appointments
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = db();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appt_action'])) {
    if (!csrf_verify()) {
        $err = 'Invalid security token.';
    } else {
        $id = (int) ($_POST['appointment_id'] ?? 0);
        $action = $_POST['appt_action'] ?? '';
        if ($id > 0 && ($action === 'approved' || $action === 'rejected')) {
            $st = $pdo->prepare("UPDATE appointments SET status=? WHERE id=?");
            $st->execute([$action, $id]);
            $msg = 'Appointment updated.';
        }
    }
}

$rows = $pdo->query(
    'SELECT a.*, p.full_name AS patient_name, p.username
     FROM appointments a
     JOIN patients p ON p.id = a.patient_id
     ORDER BY a.appointment_date DESC, a.appointment_time DESC'
)->fetchAll();

$pageTitle = 'Appointments';
$currentPage = 'appointments';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert--success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?= h($err) ?></div><?php endif; ?>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Department</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int) $r['id'] ?></td>
                    <td><?= h($r['patient_name']) ?></td>
                    <td><?= h($r['doctor_name']) ?></td>
                    <td><?= h($r['department']) ?></td>
                    <td><?= h($r['appointment_date']) ?></td>
                    <td><?= h(substr($r['appointment_time'], 0, 5)) ?></td>
                    <td>
                        <?php
                        $s = $r['status'];
                        $cls = $s === 'approved' ? 'badge--approved' : ($s === 'rejected' ? 'badge--rejected' : 'badge--pending');
                        ?>
                        <span class="badge <?= $cls ?>"><?= h($s) ?></span>
                    </td>
                    <td>
                        <?php if ($r['status'] === 'pending'): ?>
                            <form method="post" style="display:inline-flex;gap:6px;flex-wrap:wrap;">
                                <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="appointment_id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" name="appt_action" value="approved" class="btn btn--primary btn--sm">Approve</button>
                                <button type="submit" name="appt_action" value="rejected" class="btn btn--ghost btn--sm">Reject</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
