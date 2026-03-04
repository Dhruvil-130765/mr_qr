<?php
// ── Logic BEFORE any HTML output ──────────────────────────────────────────────
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) { header('Location: ' . url('/dashboard')); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login']    ?? '');
    $password =      $_POST['password'] ?? '';
    if (!$login || !$password) {
        $error = 'Please fill in all fields';
    } else {
        $result = loginUser($login, $password);
        if ($result['success']) {
            setFlash('success', 'Welcome back!');
            header('Location: ' . url('/dashboard'));
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

// ── Now safe to output HTML ────────────────────────────────────────────────────
$pageTitle = 'Log In';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4" style="background:var(--bg2)">
    <div class="w-full max-w-md">

        <div class="text-center mb-8 a-up">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-5" style="background:var(--orange);box-shadow:0 6px 24px rgba(255,92,53,.3)">
                <i data-lucide="log-in" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="font-display font-extrabold text-3xl text-neutral-900 mb-2">Welcome back</h1>
            <p class="text-neutral-500">Log in to your QRPro account</p>
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
                    <label class="label">Username or Email</label>
                    <input type="text" name="login" required value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" class="field" placeholder="johndoe or john@example.com" autofocus>
                </div>
                <div>
                    <label class="label">Password</label>
                    <input type="password" name="password" required class="field" placeholder="Enter your password">
                </div>
                <button type="submit" class="btn btn-p btn-lg w-full justify-center" style="font-size:15px">
                    Log In <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-neutral-100 text-center">
                <p class="text-sm text-neutral-500">Don't have an account? <a href="<?= url('/register') ?>" class="font-bold hover:underline" style="color:var(--orange)">Sign up free →</a></p>
            </div>
        </div>

        <p class="text-center text-xs text-neutral-400 mt-6">No credit card required · Free forever</p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
