<?php
$pageTitle = 'Professional QR Code Generator';
require_once __DIR__ . '/includes/header.php';
$types = getQRTypes();
?>

<section class="relative overflow-hidden hero-gradient">
    <div class="absolute top-24 left-10 w-20 h-20 rounded-2xl bg-brand-200/30 rotate-12 animate-float" style="animation-delay:0s"></div>
    <div class="absolute top-40 right-20 w-14 h-14 rounded-full bg-lilac-200/30 animate-float" style="animation-delay:1s"></div>
    <div class="absolute bottom-32 left-1/4 w-16 h-16 rounded-xl bg-sky-200/30 -rotate-12 animate-float" style="animation-delay:2s"></div>
    <div class="absolute top-60 right-1/3 w-10 h-10 rounded-lg bg-peach-200/30    rotate-45 animate-float" style="animation-delay:0.5s"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 md:py-32 relative z-10">
        <div class="text-center max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 bg-white/70 backdrop-blur-sm rounded-full px-5 py-2 mb-8 border border-brand-100 shadow-soft animate-fade-in-up">
                <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                <span class="text-xs font-semibold text-brand-600 tracking-wide uppercase">24+ QR Types • Bulk Generator • 100% Free</span>
            </div>
            
            <h1 class="font-display text-5xl md:text-7xl font-extrabold tracking-tight mb-6 text-slate-900 animate-fade-in-up delay-100">
                Generate <span class="gradient-text">Professional</span><br>QR Codes Instantly
            </h1>
            
            <p class="text-lg md:text-xl text-slate-500 max-w-2xl mx-auto mb-10 leading-relaxed animate-fade-in-up delay-200">
                Create URL, WiFi, vCard, UPI, social media, and 20+ more QR code types. Customize colors, generate in bulk, export as PDF — all in one beautiful place.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 animate-fade-in-up delay-300">
                <?php if ($currentUser): ?>
                    <a href="<?= url('/generate') ?>" class="group px-8 py-4 rounded-2xl font-display font-bold btn-primary text-base flex items-center gap-2">
                        Start Generating <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i></a>
                    <a href="<?= url('/dashboard') ?>" class="px-8 py-4 rounded-2xl font-display font-semibold bg-white border border-slate-200 text-slate-700 hover:border-brand-300 hover:shadow-soft transition-all">Go to Dashboard</a>
                <?php else: ?>
                    <a href="<?= url('/register') ?>" class="group px-8 py-4 rounded-2xl font-display font-bold btn-primary text-base flex items-center gap-2">
                        Get Started Free <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i></a>
                    <a href="<?= url('/login') ?>" class="px-8 py-4 rounded-2xl font-display font-semibold bg-white border border-slate-200 text-slate-700 hover:border-brand-300 hover:shadow-soft transition-all">Log In</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-16 flex justify-center animate-fade-in-up delay-400">
            <div class="bg-white rounded-3xl p-8 text-center shadow-elevated border border-slate-100 animate-float" style="animation-delay:0.5s">
                <div id="demo-qr" class="mx-auto mb-4"></div>
                <p class="text-xs text-slate-400 font-medium">Try it! Scan this QR code</p>
            </div>
        </div>
    </div>
</section>

<section class="py-10 bg-white border-y border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div><div class="font-display text-4xl font-extrabold text-brand-500">24+</div><div class="text-sm text-slate-500 mt-1 font-medium">QR Code Types</div></div>
            <div><div class="font-display text-4xl font-extrabold text-lilac-500">∞</div><div class="text-sm text-slate-500 mt-1 font-medium">Free Generations</div></div>
            <div><div class="font-display text-4xl font-extrabold text-sky-500">100</div><div class="text-sm text-slate-500 mt-1 font-medium">Bulk at Once</div></div>
            <div><div class="font-display text-4xl font-extrabold text-mint-500">PDF</div><div class="text-sm text-slate-500 mt-1 font-medium">Export Support</div></div>
        </div>
    </div>
</section>

<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-wider mb-3">All Types Supported</p>
            <h2 class="font-display text-3xl md:text-5xl font-bold mb-4 text-slate-900">Every QR Code You'll Ever Need</h2>
            <p class="text-slate-500 max-w-xl mx-auto text-lg">From simple URLs to complex vCards, WiFi networks to cryptocurrency — we've got every type covered.</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($types as $key => $type): if ($key === 'bulk') continue; ?>
            <a href="<?= url('/generate?type=' . $key) ?>" class="bg-white rounded-2xl p-5 text-center group card-hover border border-slate-100">
                <div class="w-12 h-12 rounded-xl mx-auto mb-3 flex items-center justify-center transition-all duration-300 group-hover:scale-110" style="background: <?= $type['color'] ?>12;">
                    <i data-lucide="<?= $type['icon'] ?>" class="w-5 h-5" style="color: <?= $type['color'] ?>"></i>
                </div>
                <h3 class="text-sm font-semibold mb-1 text-slate-700"><?= $type['name'] ?></h3>
                <p class="text-xs text-slate-400 leading-snug"><?= $type['desc'] ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-20 bg-gradient-to-b from-brand-50/50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-wider mb-3">Features</p>
            <h2 class="font-display text-3xl md:text-5xl font-bold text-slate-900">Powerful & Easy to Use</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-3xl p-8 shadow-card border border-slate-100 card-hover">
                <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center mb-5"><i data-lucide="layers" class="w-7 h-7 text-brand-500"></i></div>
                <h3 class="font-display text-xl font-bold mb-3 text-slate-800">Bulk Generation</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Generate up to 100 QR codes at once. Upload a CSV or enter data manually. Download as a ZIP archive.</p>
            </div>
            <div class="bg-white rounded-3xl p-8 shadow-card border border-slate-100 card-hover">
                <div class="w-14 h-14 rounded-2xl bg-lilac-50 flex items-center justify-center mb-5"><i data-lucide="palette" class="w-7 h-7 text-lilac-500"></i></div>
                <h3 class="font-display text-xl font-bold mb-3 text-slate-800">Full Customization</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Customize foreground, background colors, error correction, size, and format. Match your brand perfectly.</p>
            </div>
            <div class="bg-white rounded-3xl p-8 shadow-card border border-slate-100 card-hover">
                <div class="w-14 h-14 rounded-2xl bg-sky-50 flex items-center justify-center mb-5"><i data-lucide="file-text" class="w-7 h-7 text-sky-500"></i></div>
                <h3 class="font-display text-xl font-bold mb-3 text-slate-800">PDF & Image Export</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Export QR codes as PNG, SVG, or embedded in PDF. Perfect for print materials and business cards.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <div class="relative bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 rounded-3xl p-12 sm:p-16 overflow-hidden shadow-elevated">
            <div class="absolute top-0 right-0 w-40 h-40 rounded-full bg-white/5 -translate-y-1/2 translate-x-1/4"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 rounded-full bg-white/5 translate-y-1/2 -translate-x-1/4"></div>
            <div class="relative z-10">
                <h2 class="font-display text-3xl md:text-4xl font-bold mb-4 text-white">Ready to Create?</h2>
                <p class="text-brand-200 mb-8 max-w-lg mx-auto text-lg">Join thousands of users generating professional QR codes. Free forever, no credit card needed.</p>
                <a href="<?= $currentUser ? url('/generate') : url('/register') ?>" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl font-display font-bold text-brand-600 bg-white hover:bg-brand-50 hover:scale-105 transition-all shadow-lg text-base">
                    Start Generating Now <i data-lucide="zap" class="w-5 h-5"></i></a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new QRCode(document.getElementById("demo-qr"), {
        text: "https://github.com/qrcode-pro", width: 180, height: 180,
        colorDark: "#4F46E5", colorLight: "#ffffff", correctLevel: QRCode.CorrectLevel.H
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
