<?php
/**
 * Upload / delete medical reports (PDF)
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = db();
$aid = admin_id();

ensure_upload_dir();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_report'])) {
    if (!csrf_verify()) {
        $err = 'Invalid security token.';
    } else {
        $patient_id = (int) ($_POST['patient_id'] ?? 0);
        $title = clean_string($_POST['title'] ?? '', 200);
        if ($patient_id <= 0 || $title === '') {
            $err = 'Patient and title required.';
        } elseif (empty($_FILES['report_file']['name']) || $_FILES['report_file']['error'] !== UPLOAD_ERR_OK) {
            $err = 'Please choose a valid file.';
        } else {
            $f = $_FILES['report_file'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $err = 'Only PDF uploads are allowed.';
            } elseif ($f['size'] > MAX_UPLOAD_BYTES) {
                $err = 'File too large (max 5 MB).';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($f['tmp_name']);
                if ($mime !== 'application/pdf') {
                    $err = 'File must be a PDF document.';
                } else {
                    $safe = bin2hex(random_bytes(16)) . '.pdf';
                    $dest = UPLOAD_PATH . DIRECTORY_SEPARATOR . $safe;
                    if (move_uploaded_file($f['tmp_name'], $dest)) {
                        $rel = 'uploads/reports/' . $safe;
                        $st = $pdo->prepare(
                            'INSERT INTO reports (patient_id, title, file_path, original_filename, uploaded_by) VALUES (?,?,?,?,?)'
                        );
                        $st->execute([$patient_id, $title, $rel, basename($f['name']), $aid]);
                        $msg = 'Report uploaded.';
                    } else {
                        $err = 'Could not store file.';
                    }
                }
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_report'])) {
    if (!csrf_verify()) {
        $err = 'Invalid token.';
    } else {
        $rid = (int) ($_POST['report_id'] ?? 0);
        if ($rid > 0) {
            $st = $pdo->prepare('SELECT file_path FROM reports WHERE id=?');
            $st->execute([$rid]);
            $row = $st->fetch();
            if ($row) {
                $full = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $row['file_path']);
                if (is_file($full)) {
                    @unlink($full);
                }
                $pdo->prepare('DELETE FROM reports WHERE id=?')->execute([$rid]);
                $msg = 'Report deleted.';
            }
        }
    }
}

$patients = $pdo->query('SELECT id, full_name, username FROM patients ORDER BY full_name')->fetchAll();
$reports = $pdo->query(
    'SELECT r.*, p.full_name AS patient_name
     FROM reports r JOIN patients p ON p.id = r.patient_id
     ORDER BY r.id DESC'
)->fetchAll();

$pageTitle = 'Medical Reports';
$currentPage = 'reports';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert--success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?= h($err) ?></div><?php endif; ?>

<div class="glass" style="padding:24px;margin-bottom:24px;">
    <h2 class="section-title mt-0" style="font-size:1.1rem;">Upload report (PDF)</h2>
    <form method="post" enctype="multipart/form-data" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;align-items:end;">
        <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="upload_report" value="1">
        <div class="form-group" style="margin:0;">
            <label for="patient_id">Patient</label>
            <select class="input" name="patient_id" id="patient_id" required>
                <option value="">— Select —</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"><?= h($p['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label for="title">Title</label>
            <input class="input" name="title" id="title" required maxlength="200" placeholder="e.g. Blood work — Jan 2026">
        </div>
        <div class="form-group" style="margin:0;grid-column:1/-1;">
            <label for="report_file">PDF file (max 5 MB)</label>
            <input class="input" type="file" name="report_file" id="report_file" accept=".pdf,application/pdf" required>
        </div>
        <button type="submit" class="btn btn--primary">Upload</button>
    </form>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Title</th>
                <th>File</th>
                <th>Uploaded</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $r): ?>
                <tr>
                    <td><?= (int) $r['id'] ?></td>
                    <td><?= h($r['patient_name']) ?></td>
                    <td><?= h($r['title']) ?></td>
                    <td><?= h($r['original_filename']) ?></td>
                    <td><?= h($r['created_at']) ?></td>
                    <td>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this report?');">
                            <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="delete_report" value="1">
                            <input type="hidden" name="report_id" value="<?= (int) $r['id'] ?>">
                            <button type="submit" class="btn btn--danger btn--sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
