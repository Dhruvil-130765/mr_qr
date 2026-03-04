<?php
// ── Auth BEFORE any HTML output ───────────────────────────────────────────────
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$user        = getCurrentUser();
$stats       = getUserStats($user['id']);
$recentCodes = getUserQRCodes($user['id'], 8);
$types       = getQRTypes();

// ── Now safe to output HTML ────────────────────────────────────────────────────
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-5 py-10">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10 a-up">
        <div>
            <p class="section-tag mb-1">Welcome back</p>
            <h1 class="font-display font-extrabold text-3xl sm:text-4xl text-neutral-900 leading-tight">
                <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?> <span class="text-grad">👋</span>
            </h1>
            <p class="text-neutral-500 mt-1">Here's your QR code activity at a glance</p>
        </div>
        <a href="<?= url('/generate') ?>" class="btn btn-p btn-lg self-start sm:self-auto">
            <i data-lucide="plus" class="w-5 h-5"></i> New QR Code
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <div class="card p-6 a-up d1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:var(--orange-light)">
                    <i data-lucide="qr-code" class="w-5 h-5" style="color:var(--orange)"></i>
                </div>
                <span class="badge badge-o">TOTAL</span>
            </div>
            <p class="font-display font-extrabold text-3xl sm:text-4xl text-neutral-900"><?= number_format($stats['total_codes']) ?></p>
            <p class="text-sm text-neutral-500 mt-1 font-medium">QR Codes Created</p>
        </div>
        <div class="card p-6 a-up d2">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-50">
                    <i data-lucide="scan-line" class="w-5 h-5 text-blue-500"></i>
                </div>
                <span class="badge badge-g">SCANS</span>
            </div>
            <p class="font-display font-extrabold text-3xl sm:text-4xl text-neutral-900"><?= number_format($stats['total_scans']) ?></p>
            <p class="text-sm text-neutral-500 mt-1 font-medium">Total Scans</p>
        </div>
        <div class="card p-6 a-up d3">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-purple-50">
                    <i data-lucide="folder-open" class="w-5 h-5 text-purple-500"></i>
                </div>
                <span class="badge badge-g">TYPES</span>
            </div>
            <p class="font-display font-extrabold text-3xl sm:text-4xl text-neutral-900"><?= count($stats['by_type']) ?></p>
            <p class="text-sm text-neutral-500 mt-1 font-medium">QR Types Used</p>
        </div>
        <div class="card p-6 a-up d4">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-green-50">
                    <i data-lucide="crown" class="w-5 h-5 text-green-500"></i>
                </div>
                <span class="badge badge-gr">PLAN</span>
            </div>
            <p class="font-display font-extrabold text-3xl sm:text-4xl text-neutral-900 capitalize"><?= $user['plan'] ?></p>
            <p class="text-sm text-neutral-500 mt-1 font-medium">Current Plan</p>
        </div>
    </div>

    <!-- Quick Generate -->
    <div class="mb-10 a-up d3">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-display font-bold text-xl text-neutral-900">Quick Generate</h2>
            <a href="<?= url('/generate') ?>" class="text-sm font-semibold flex items-center gap-1 hover:underline" style="color:var(--orange)">See all types <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
        </div>
        <div class="grid grid-cols-4 sm:grid-cols-8 gap-3">
            <?php foreach (['url','wifi','vcard','email','phone','whatsapp','upi','text'] as $key):
                $t = $types[$key] ?? ['name'=>$key,'icon'=>'qr-code','color'=>'#999']; ?>
            <a href="<?= url('/generate?type='.$key) ?>" class="card card-hover p-3.5 text-center group" style="border-radius:14px">
                <div class="w-10 h-10 rounded-xl mx-auto mb-2 flex items-center justify-center transition-transform duration-300 group-hover:scale-110" style="background:<?= $t['color'] ?>15">
                    <i data-lucide="<?= $t['icon'] ?>" class="w-5 h-5" style="color:<?= $t['color'] ?>"></i>
                </div>
                <span class="text-xs font-bold text-neutral-600 leading-snug"><?= $t['name'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent QR codes -->
    <div class="a-up d4">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-display font-bold text-xl text-neutral-900">Recent QR Codes</h2>
            <?php if ($stats['total_codes'] > 0): ?>
            <a href="<?= url('/history') ?>" class="text-sm font-semibold flex items-center gap-1 hover:underline" style="color:var(--orange)">View all <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            <?php endif; ?>
        </div>

        <?php if (empty($recentCodes)): ?>
        <div class="card p-16 text-center" style="border-radius:20px;border-style:dashed">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:var(--bg2)">
                <i data-lucide="qr-code" class="w-8 h-8 text-neutral-300"></i>
            </div>
            <h3 class="font-display font-bold text-lg mb-2 text-neutral-700">No QR codes yet</h3>
            <p class="text-neutral-400 text-sm mb-6">Create your first QR code to get started</p>
            <a href="<?= url('/generate') ?>" class="btn btn-p"><i data-lucide="plus" class="w-4 h-4"></i> Create QR Code</a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($recentCodes as $code):
                $typeInfo = $types[$code['type']] ?? ['name'=>$code['type'],'icon'=>'qr-code','color'=>'#999']; ?>
            <div class="card card-hover p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:<?= $typeInfo['color'] ?>15">
                        <i data-lucide="<?= $typeInfo['icon'] ?>" class="w-4 h-4" style="color:<?= $typeInfo['color'] ?>"></i>
                    </div>
                    <span class="text-xs text-neutral-400 font-medium"><?= date('M j', strtotime($code['created_at'])) ?></span>
                </div>
                <h4 class="font-bold text-sm text-neutral-800 truncate mb-1"><?= htmlspecialchars($code['title'] ?: $typeInfo['name']) ?></h4>
                <p class="text-xs text-neutral-400 truncate font-medium mb-3"><?= htmlspecialchars(substr($code['content'], 0, 50)) ?></p>
                <div class="flex items-center justify-between">
                    <span class="badge" style="background:<?= $typeInfo['color'] ?>15;color:<?= $typeInfo['color'] ?>"><?= $code['type'] ?></span>
                    <a href="<?= url('/generate?type='.$code['type']) ?>" class="text-xs font-semibold hover:underline" style="color:var(--orange)">Recreate →</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
