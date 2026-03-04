<?php
// ── Auth + POST handler BEFORE any HTML output ────────────────────────────────
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$types      = getQRTypes();
$filterType = $_GET['type'] ?? '';
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 12;
$offset     = ($page - 1) * $perPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleted = deleteQRCode((int)$_POST['delete_id'], $currentUser['id']);
    if ($deleted) setFlash('success', 'QR code deleted');
    header('Location: ' . url('/history') . ($filterType ? "?type={$filterType}" : ''));
    exit;
}

$codes      = getUserQRCodes($currentUser['id'], $perPage, $offset, $filterType);
$db         = getDB();
$countSql   = "SELECT COUNT(*) as total FROM qr_codes WHERE user_id = ?";
$countParams = [$currentUser['id']];
if ($filterType) { $countSql .= " AND type = ?"; $countParams[] = $filterType; }
$stmt = $db->prepare($countSql);
$stmt->execute($countParams);
$totalCodes = $stmt->fetch()['total'];
$totalPages = ceil($totalCodes / $perPage);

// ── Now safe to output HTML ────────────────────────────────────────────────────
$pageTitle = 'My QR Codes';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-5 py-10">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 a-up">
        <div>
            <p class="section-tag mb-1">Collection</p>
            <h1 class="font-display font-extrabold text-3xl text-neutral-900">My QR Codes</h1>
            <p class="text-neutral-500 mt-1"><?= number_format($totalCodes) ?> QR codes total</p>
        </div>
        <a href="<?= url('/generate') ?>" class="btn btn-p self-start sm:self-auto">
            <i data-lucide="plus" class="w-4 h-4"></i> New QR Code
        </a>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-2 mb-8 a-up d1">
        <a href="<?= url('/history') ?>" class="btn btn-sm <?= !$filterType ? 'btn-p' : 'btn-s' ?>">All</a>
        <?php foreach ($types as $key => $type): if ($key === 'bulk') continue; ?>
        <a href="<?= url('/history?type=' . $key) ?>" class="btn btn-sm <?= $filterType === $key ? 'btn-p' : 'btn-s' ?>">
            <?= $type['name'] ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($codes)): ?>
    <div class="card p-16 text-center a-up d2" style="border-radius:20px;border-style:dashed">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:var(--bg2)">
            <i data-lucide="search" class="w-8 h-8 text-neutral-300"></i>
        </div>
        <h3 class="font-display font-bold text-lg mb-2 text-neutral-700">No QR codes found</h3>
        <p class="text-neutral-400 text-sm mb-6"><?= $filterType ? 'Try a different filter or create' : 'Create' ?> your first QR code.</p>
        <a href="<?= url('/generate') ?>" class="btn btn-p"><i data-lucide="plus" class="w-4 h-4"></i> Create QR Code</a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8 a-up d2">
        <?php foreach ($codes as $code):
            $typeInfo = $types[$code['type']] ?? ['name'=>$code['type'],'icon'=>'qr-code','color'=>'#999'];
            $settings = json_decode($code['settings'], true) ?: []; ?>
        <div class="card group overflow-hidden" style="border-radius:16px">
            <!-- QR preview -->
            <div class="p-4 flex items-center justify-center" style="background:var(--bg2);min-height:120px">
                <div class="qr-mini"
                     data-content="<?= htmlspecialchars($code['content']) ?>"
                     data-fg="<?= $settings['fg_color'] ?? '#000000' ?>"
                     data-bg="<?= $settings['bg_color'] ?? '#ffffff' ?>"></div>
            </div>
            <!-- Info -->
            <div class="p-4">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 rounded-lg flex-shrink-0 flex items-center justify-center" style="background:<?= $typeInfo['color'] ?>15">
                        <i data-lucide="<?= $typeInfo['icon'] ?>" class="w-3.5 h-3.5" style="color:<?= $typeInfo['color'] ?>"></i>
                    </div>
                    <span class="text-xs font-bold text-neutral-500 uppercase tracking-wide"><?= $code['type'] ?></span>
                </div>
                <h4 class="font-bold text-sm text-neutral-800 truncate mb-1"><?= htmlspecialchars($code['title'] ?: $typeInfo['name']) ?></h4>
                <p class="text-xs text-neutral-400 truncate font-medium"><?= htmlspecialchars(substr($code['content'], 0, 38)) ?></p>

                <div class="flex items-center justify-between mt-3 pt-3 border-t border-neutral-100">
                    <span class="text-xs text-neutral-400 font-medium"><?= date('M j', strtotime($code['created_at'])) ?></span>
                    <div class="flex items-center gap-1.5">
                        <a href="<?= url('/generate?type=' . $code['type']) ?>"
                           class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-400 hover:text-blue-500 hover:bg-blue-50 transition-all"
                           title="Generate similar">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        </a>
                        <form method="POST" class="inline" onsubmit="return confirm('Delete this QR code?')">
                            <input type="hidden" name="delete_id" value="<?= $code['id'] ?>">
                            <button type="submit"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-400 hover:text-red-500 hover:bg-red-50 transition-all"
                                    title="Delete">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-2 a-up d3">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?><?= $filterType ? "&type={$filterType}" : '' ?>" class="btn btn-s btn-sm">
            <i data-lucide="chevron-left" class="w-4 h-4"></i> Prev
        </a>
        <?php endif; ?>
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <a href="?page=<?= $i ?><?= $filterType ? "&type={$filterType}" : '' ?>"
           class="btn btn-sm <?= $i === $page ? 'btn-p' : 'btn-s' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?><?= $filterType ? "&type={$filterType}" : '' ?>" class="btn btn-s btn-sm">
            Next <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.qr-mini').forEach(el => {
        const c = el.dataset.content, fg = el.dataset.fg || '#000', bg = el.dataset.bg || '#fff';
        if (c) new QRCode(el, {text:c, width:100, height:100, colorDark:fg, colorLight:bg, correctLevel:QRCode.CorrectLevel.M});
    });
    lucide.createIcons();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
