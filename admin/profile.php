<?php
/**
 * Admin profile — update name, email, password
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = db();
$aid = admin_id();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $err = 'Invalid security token.';
    } else {
        $full_name = clean_string($_POST['full_name'] ?? '', 100);
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $newPass = $_POST['new_password'] ?? '';
        $newPass2 = $_POST['new_password_confirm'] ?? '';

        if ($full_name === '' || !$email) {
            $err = 'Name and valid email are required.';
        } elseif ($newPass !== '' && $newPass !== $newPass2) {
            $err = 'New passwords do not match.';
        } elseif ($newPass !== '' && strlen($newPass) < 8) {
            $err = 'Password must be at least 8 characters.';
        } else {
            if ($newPass !== '') {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $st = $pdo->prepare('UPDATE admins SET full_name=?, email=?, password_hash=? WHERE id=?');
                $st->execute([$full_name, $email, $hash, $aid]);
            } else {
                $st = $pdo->prepare('UPDATE admins SET full_name=?, email=? WHERE id=?');
                $st->execute([$full_name, $email, $aid]);
            }
            $_SESSION['admin_name'] = $full_name;
            $msg = 'Profile updated.';
        }
    }
}

$st = $pdo->prepare('SELECT username, email, full_name FROM admins WHERE id=?');
$st->execute([$aid]);
$admin = $st->fetch() ?: [];

$pageTitle = 'My Profile';
$currentPage = 'profile';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert--success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?= h($err) ?></div><?php endif; ?>

<form method="post" class="glass" style="max-width:480px;padding:24px;">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <div class="form-group">
        <label>Username (read-only)</label>
        <input class="input" type="text" value="<?= h($admin['username'] ?? '') ?>" disabled>
    </div>
    <div class="form-group">
        <label for="full_name">Full name</label>
        <input class="input" name="full_name" id="full_name" required value="<?= h($admin['full_name'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input class="input" type="email" name="email" id="email" required value="<?= h($admin['email'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="new_password">New password (leave blank to keep)</label>
        <input class="input" type="password" name="new_password" id="new_password" autocomplete="new-password" minlength="8">
    </div>
    <div class="form-group">
        <label for="new_password_confirm">Confirm new password</label>
        <input class="input" type="password" name="new_password_confirm" id="new_password_confirm" autocomplete="new-password" minlength="8">
    </div>
    <button type="submit" class="btn btn--primary">Save changes</button>
</form>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
