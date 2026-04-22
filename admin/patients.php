<?php
/**
 * Register patients (admin) + view / delete
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = db();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_patient'])) {
    if (!csrf_verify()) {
        $err = 'Invalid security token.';
    } else {
        $username = clean_string($_POST['username'] ?? '', 50);
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $full_name = clean_string($_POST['full_name'] ?? '', 100);
        $phone = clean_string($_POST['phone'] ?? '', 20);
        $password = $_POST['password'] ?? '';

        if ($username === '' || !$email || $full_name === '' || strlen($password) < 8) {
            $err = 'Username, email, name, and password (8+ chars) required.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $st = $pdo->prepare(
                    'INSERT INTO patients (username, email, password_hash, full_name, phone) VALUES (?,?,?,?,?)'
                );
                $st->execute([$username, $email, $hash, $full_name, $phone ?: null]);
                $msg = 'Patient registered.';
            } catch (Throwable $e) {
                $err = 'Could not register (duplicate username/email?).';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_patient'])) {
    if (!csrf_verify()) {
        $err = 'Invalid token.';
    } else {
        $pid = (int) ($_POST['patient_id'] ?? 0);
        if ($pid > 0) {
            $st = $pdo->prepare('DELETE FROM patients WHERE id=?');
            $st->execute([$pid]);
            $msg = 'Patient removed.';
        }
    }
}

$rows = $pdo->query(
    'SELECT id, username, email, full_name, phone, created_at FROM patients ORDER BY id DESC'
)->fetchAll();

$pageTitle = 'Patients';
$currentPage = 'patients';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert--success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?= h($err) ?></div><?php endif; ?>

<div class="glass" style="padding:24px;margin-bottom:24px;">
    <h2 class="section-title mt-0" style="font-size:1.1rem;">Register new patient</h2>
    <form method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;align-items:end;">
        <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="register_patient" value="1">
        <div class="form-group" style="margin:0;">
            <label for="username">Username</label>
            <input class="input" name="username" id="username" required maxlength="50">
        </div>
        <div class="form-group" style="margin:0;">
            <label for="email">Email</label>
            <input class="input" type="email" name="email" id="email" required>
        </div>
        <div class="form-group" style="margin:0;">
            <label for="full_name">Full name</label>
            <input class="input" name="full_name" id="full_name" required>
        </div>
        <div class="form-group" style="margin:0;">
            <label for="phone">Phone</label>
            <input class="input" name="phone" id="phone" maxlength="20">
        </div>
        <div class="form-group" style="margin:0;">
            <label for="password">Password</label>
            <input class="input" type="password" name="password" id="password" required minlength="8">
        </div>
        <button type="submit" class="btn btn--primary">Register</button>
    </form>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Joined</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int) $r['id'] ?></td>
                    <td><?= h($r['full_name']) ?></td>
                    <td><?= h($r['username']) ?></td>
                    <td><?= h($r['email']) ?></td>
                    <td><?= h($r['phone'] ?? '') ?></td>
                    <td><?= h($r['created_at']) ?></td>
                    <td>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this patient and related data?');">
                            <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="delete_patient" value="1">
                            <input type="hidden" name="patient_id" value="<?= (int) $r['id'] ?>">
                            <button type="submit" class="btn btn--danger btn--sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
