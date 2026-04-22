<?php
/**
 * Contact form inbox (from landing page)
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = db();

$rows = $pdo->query(
    'SELECT * FROM contact_messages ORDER BY id DESC LIMIT 200'
)->fetchAll();

$pageTitle = 'Contact Inbox';
$currentPage = 'contact';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>From</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Received</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int) $r['id'] ?></td>
                    <td><?= h($r['name']) ?></td>
                    <td><?= h($r['email']) ?></td>
                    <td>
                        <strong><?= h($r['subject']) ?></strong>
                        <p class="text-muted" style="margin:6px 0 0;font-size:0.85rem;"><?= nl2br(h($r['message'])) ?></p>
                    </td>
                    <td><?= h($r['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (count($rows) === 0): ?>
    <p class="text-muted">No messages yet.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
