<?php
/**
 * Add new administrator
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = db();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $err = 'Invalid security token.';
    } else {
        $username = clean_string($_POST['username'] ?? '', 50);
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $full_name = clean_string($_POST['full_name'] ?? '', 100);
        $password = $_POST['password'] ?? '';

        if ($username === '' || !$email || $full_name === '' || strlen($password) < 8) {
            $err = 'All fields required; password min 8 characters.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $st = $pdo->prepare(
                    'INSERT INTO admins (username, email, password_hash, full_name) VALUES (?,?,?,?)'
                );
                $st->execute([$username, $email, $hash, $full_name]);
                $msg = 'Administrator created.';
            } catch (Throwable $e) {
                $err = 'Could not create admin (username may be taken).';
            }
        }
    }
}

$pageTitle = 'Add Admin';
$currentPage = 'admins';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert--success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?= h($err) ?></div><?php endif; ?>

<form method="post" class="glass" style="max-width:480px;padding:24px;">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <div class="form-group">
        <label for="username">Username</label>
        <input class="input" name="username" id="username" required maxlength="50">
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input class="input" type="email" name="email" id="email" required>
    </div>
    <div class="form-group">
        <label for="full_name">Full name</label>
        <input class="input" name="full_name" id="full_name" required maxlength="100">
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input class="input" type="password" name="password" id="password" required minlength="8" autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn--primary">Create admin</button>
</form>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
