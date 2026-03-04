<?php
// ── Auth + logic BEFORE any HTML output ───────────────────────────────────────
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$user    = getCurrentUser();
$stats   = getUserStats($user['id']);
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $db     = getDB();

    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email']     ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } else {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                $error = 'Email already in use';
            } else {
                $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$fullName, $email, $user['id']]);
                $success = 'Profile updated successfully';
                $user    = getCurrentUser();
            }
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $user['password'])) {
            $error = 'Current password is incorrect';
        } elseif (strlen($newPass) < 6) {
            $error = 'New password must be at least 6 characters';
        } elseif ($newPass !== $confirm) {
            $error = 'New passwords do not match';
        } else {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$hash, $user['id']]);
            $success = 'Password changed successfully';
        }
    }
}

// ── Now safe to output HTML ────────────────────────────────────────────────────
$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-5 py-10">

    <div class="mb-8 a-up">
        <p class="section-tag mb-1">Account</p>
        <h1 class="font-display font-extrabold text-3xl sm:text-4xl text-neutral-900">My Profile</h1>
        <p class="text-neutral-500 mt-1">Manage your account details and password</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-err mb-6 a-up">
        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
        <span class="font-medium"><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert alert-ok mb-6 a-up">
        <i data-lucide="check-circle-2" class="w-5 h-5 flex-shrink-0"></i>
        <span class="font-medium"><?= htmlspecialchars($success) ?></span>
    </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-3 gap-6">

        <!-- Sidebar -->
        <div class="space-y-5">
            <div class="card p-6 text-center a-up d1" style="border-radius:20px">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 text-white text-2xl font-extrabold"
                     style="background:linear-gradient(135deg,var(--orange),#FF9E5E);font-family:'DM Sans',sans-serif">
                    <?= strtoupper(substr($user['username'], 0, 2)) ?>
                </div>
                <h3 class="font-display font-bold text-lg text-neutral-900"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></h3>
                <p class="text-sm text-neutral-500 mt-1">@<?= htmlspecialchars($user['username']) ?></p>
                <p class="text-xs text-neutral-400 mt-2 font-medium">Member since <?= date('M Y', strtotime($user['created_at'])) ?></p>
            </div>

            <div class="card p-5 space-y-4 a-up d2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-neutral-500 font-medium">Total QRs</span>
                    <span class="font-display font-extrabold text-xl" style="color:var(--orange)"><?= number_format($stats['total_codes']) ?></span>
                </div>
                <div class="divider"></div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-neutral-500 font-medium">Total Scans</span>
                    <span class="font-display font-extrabold text-xl text-blue-500"><?= number_format($stats['total_scans']) ?></span>
                </div>
                <div class="divider"></div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-neutral-500 font-medium">Plan</span>
                    <span class="badge badge-gr uppercase"><?= $user['plan'] ?></span>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="md:col-span-2 space-y-5">

            <!-- Profile info -->
            <div class="card p-6 a-up d2" style="border-radius:20px">
                <h2 class="font-display font-bold text-lg text-neutral-900 mb-5 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5" style="color:var(--orange)"></i> Profile Information
                </h2>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Full Name</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="field">
                        </div>
                        <div>
                            <label class="label">Username</label>
                            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled class="field" style="opacity:.5;cursor:not-allowed;background:var(--bg2)">
                        </div>
                    </div>
                    <div>
                        <label class="label">Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="field">
                    </div>
                    <button type="submit" class="btn btn-p">Save Changes</button>
                </form>
            </div>

            <!-- Change password -->
            <div class="card p-6 a-up d3" style="border-radius:20px">
                <h2 class="font-display font-bold text-lg text-neutral-900 mb-5 flex items-center gap-2">
                    <i data-lucide="lock" class="w-5 h-5 text-blue-500"></i> Change Password
                </h2>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="change_password">
                    <div>
                        <label class="label">Current Password</label>
                        <input type="password" name="current_password" required class="field">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">New Password</label>
                            <input type="password" name="new_password" required class="field" placeholder="Min 6 characters">
                        </div>
                        <div>
                            <label class="label">Confirm New Password</label>
                            <input type="password" name="confirm_password" required class="field">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-s">Update Password</button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
