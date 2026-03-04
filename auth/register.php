<?php
// ── Logic BEFORE any HTML output ──────────────────────────────────────────────
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) { header('Location: ' . url('/dashboard')); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']         ?? '');
    $email    = trim($_POST['email']            ?? '');
    $password =      $_POST['password']         ?? '';
    $confirm  =      $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name']        ?? '');

    if (strlen($username) < 3)
        $error = 'Username must be at least 3 characters';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $error = 'Please enter a valid email';
    elseif (strlen($password) < 6)
        $error = 'Password must be at least 6 characters';
    elseif ($password !== $confirm)
        $error = 'Passwords do not match';
    else {
        $result = registerUser($username, $email, $password, $fullName);
        if ($result['success']) {
            setFlash('success', 'Account created! Welcome to QRPro.');
            header('Location: ' . url('/dashboard'));
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

// ── Now safe to output HTML ────────────────────────────────────────────────────
$pageTitle = 'Create Account';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4" style="background:var(--bg2)">
    <div class="w-full max-w-md">

        <div class="text-center mb-8 a-up">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-5" style="background:linear-gradient(135deg,var(--orange),#FF9E5E);box-shadow:0 6px 24px rgba(255,92,53,.3)">
                <i data-lucide="user-plus" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="font-display font-extrabold text-3xl text-neutral-900 mb-2">Create account</h1>
            <p class="text-neutral-500">Generate QR codes in seconds. It's free.</p>
        </div>

        <div class="card p-8 shadow-xl a-up d2" style="border-radius:20px">
            <?php if ($error): ?>
            <div class="alert alert-err mb-6">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                <span class="font-medium"><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="label">Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" class="field" placeholder="John Doe">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Username <span class="text-red-400">*</span></label>
                        <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" class="field" placeholder="johndoe">
                    </div>
                    <div>
                        <label class="label">Email <span class="text-red-400">*</span></label>
                        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="field" placeholder="john@example.com">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Password <span class="text-red-400">*</span></label>
                        <input type="password" name="password" required class="field" placeholder="Min 6 chars">
                    </div>
                    <div>
                        <label class="label">Confirm <span class="text-red-400">*</span></label>
                        <input type="password" name="confirm_password" required class="field" placeholder="Repeat">
                    </div>
                </div>
                <button type="submit" class="btn btn-p btn-lg w-full justify-center" style="font-size:15px">
                    Create Account <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-neutral-100 text-center">
                <p class="text-sm text-neutral-500">Already have an account? <a href="<?= url('/login') ?>" class="font-bold hover:underline" style="color:var(--orange)">Log in →</a></p>
            </div>
        </div>

        <div class="flex justify-center gap-6 mt-6">
            <span class="text-xs text-neutral-400 flex items-center gap-1"><i data-lucide="check" class="w-3 h-3 text-green-500"></i> No credit card</span>
            <span class="text-xs text-neutral-400 flex items-center gap-1"><i data-lucide="check" class="w-3 h-3 text-green-500"></i> Unlimited QRs</span>
            <span class="text-xs text-neutral-400 flex items-center gap-1"><i data-lucide="check" class="w-3 h-3 text-green-500"></i> Free forever</span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
