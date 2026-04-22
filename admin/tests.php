<?php
/**
 * Manage medical tests catalog
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = db();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_test'])) {
    if (!csrf_verify()) {
        $err = 'Invalid security token.';
    } else {
        $name = clean_string($_POST['name'] ?? '', 150);
        $description = clean_string($_POST['description'] ?? '', 2000);
        $price = (float) ($_POST['price'] ?? 0);
        $duration = (int) ($_POST['duration_minutes'] ?? 30);
        $active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            $err = 'Name is required.';
        } else {
            $st = $pdo->prepare(
                'INSERT INTO medical_tests (name, description, price, duration_minutes, is_active) VALUES (?,?,?,?,?)'
            );
            $st->execute([$name, $description ?: null, $price, max(1, $duration), $active]);
            $msg = 'Test added.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_test'])) {
    if (!csrf_verify()) {
        $err = 'Invalid token.';
    } else {
        $tid = (int) ($_POST['test_id'] ?? 0);
        $name = clean_string($_POST['name'] ?? '', 150);
        $description = clean_string($_POST['description'] ?? '', 2000);
        $price = (float) ($_POST['price'] ?? 0);
        $duration = (int) ($_POST['duration_minutes'] ?? 30);
        $active = isset($_POST['is_active']) ? 1 : 0;
        if ($tid > 0 && $name !== '') {
            $st = $pdo->prepare(
                'UPDATE medical_tests SET name=?, description=?, price=?, duration_minutes=?, is_active=? WHERE id=?'
            );
            $st->execute([$name, $description ?: null, $price, max(1, $duration), $active, $tid]);
            $msg = 'Test updated.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_test'])) {
    if (!csrf_verify()) {
        $err = 'Invalid token.';
    } else {
        $tid = (int) ($_POST['test_id'] ?? 0);
        if ($tid > 0) {
            $pdo->prepare('DELETE FROM medical_tests WHERE id=?')->execute([$tid]);
            $msg = 'Test removed.';
        }
    }
}

$rows = $pdo->query('SELECT * FROM medical_tests ORDER BY name')->fetchAll();

$pageTitle = 'Medical Tests';
$currentPage = 'tests';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert--success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?= h($err) ?></div><?php endif; ?>

<div class="glass" style="padding:24px;margin-bottom:24px;">
    <h2 class="section-title mt-0" style="font-size:1.1rem;">Add test</h2>
    <form method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;align-items:end;">
        <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="add_test" value="1">
        <div class="form-group" style="margin:0;">
            <label for="name">Name</label>
            <input class="input" name="name" id="name" required maxlength="150">
        </div>
        <div class="form-group" style="margin:0;">
            <label for="price">Price</label>
            <input class="input" type="number" step="0.01" min="0" name="price" id="price" value="0">
        </div>
        <div class="form-group" style="margin:0;">
            <label for="duration_minutes">Duration (min)</label>
            <input class="input" type="number" min="1" name="duration_minutes" id="duration_minutes" value="30">
        </div>
        <div class="form-group" style="margin:0;align-self:center;">
            <label><input type="checkbox" name="is_active" checked> Active</label>
        </div>
        <div class="form-group" style="margin:0;grid-column:1/-1;">
            <label for="description">Description</label>
            <textarea class="input" name="description" id="description" rows="2"></textarea>
        </div>
        <button type="submit" class="btn btn--primary">Add</button>
    </form>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Duration</th>
                <th>Active</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $t): ?>
                <tr>
                    <td><?= h($t['name']) ?></td>
                    <td>$<?= h(number_format((float) $t['price'], 2)) ?></td>
                    <td><?= (int) $t['duration_minutes'] ?> min</td>
                    <td><?= $t['is_active'] ? 'Yes' : 'No' ?></td>
                    <td>
                        <details>
                            <summary class="text-muted" style="cursor:pointer;">Edit</summary>
                            <form method="post" style="margin-top:12px;padding:12px;background:rgba(0,0,0,0.2);border-radius:8px;">
                                <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="update_test" value="1">
                                <input type="hidden" name="test_id" value="<?= (int) $t['id'] ?>">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input class="input" name="name" required value="<?= h($t['name']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Price</label>
                                    <input class="input" type="number" step="0.01" name="price" value="<?= h((string) $t['price']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Duration</label>
                                    <input class="input" type="number" name="duration_minutes" value="<?= (int) $t['duration_minutes'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea class="input" name="description"><?= h($t['description'] ?? '') ?></textarea>
                                </div>
                                <label><input type="checkbox" name="is_active" <?= $t['is_active'] ? 'checked' : '' ?>> Active</label>
                                <button type="submit" class="btn btn--primary btn--sm" style="margin-top:8px;">Save</button>
                            </form>
                            <form method="post" style="margin-top:8px;" onsubmit="return confirm('Delete this test?');">
                                <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="delete_test" value="1">
                                <input type="hidden" name="test_id" value="<?= (int) $t['id'] ?>">
                                <button type="submit" class="btn btn--danger btn--sm">Delete</button>
                            </form>
                        </details>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
