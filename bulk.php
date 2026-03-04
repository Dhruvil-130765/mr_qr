<?php
// ── Auth BEFORE any HTML output ───────────────────────────────────────────────
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$types = getQRTypes();

// ── Now safe to output HTML ────────────────────────────────────────────────────
$pageTitle = 'Bulk QR Generator';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-5 py-10">

    <div class="mb-8 a-up">
        <p class="section-tag mb-1">Power Tool</p>
        <h1 class="font-display font-extrabold text-3xl sm:text-4xl text-neutral-900">Bulk QR Generator</h1>
        <p class="text-neutral-500 mt-1">Generate up to 100 QR codes at once from a list or CSV file</p>
    </div>

    <div class="grid lg:grid-cols-5 gap-6">

        <!-- Left: Config + Input -->
        <div class="lg:col-span-3 space-y-5">

            <!-- Config card -->
            <div class="card p-6 a-up d1" style="border-radius:20px">
                <h3 class="font-display font-bold text-base text-neutral-900 mb-5 flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4" style="color:var(--orange)"></i> Configuration
                </h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label">QR Type</label>
                        <select id="bulk-type" class="field">
                            <option value="url">URL / Website</option>
                            <option value="text">Plain Text</option>
                            <option value="phone">Phone Number</option>
                            <option value="email">Email Address</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Size</label>
                        <select id="bulk-size" class="field">
                            <option value="128">128×128</option>
                            <option value="256" selected>256×256</option>
                            <option value="512">512×512</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Foreground Color</label>
                        <input type="color" id="bulk-fg" value="#000000">
                    </div>
                    <div>
                        <label class="label">Background Color</label>
                        <input type="color" id="bulk-bg" value="#ffffff">
                    </div>
                </div>
            </div>

            <!-- Input card -->
            <div class="card p-6 a-up d2" style="border-radius:20px">
                <div class="flex items-center gap-1 p-1 rounded-xl mb-5" style="background:var(--bg2)">
                    <button onclick="switchTab('manual')" id="tab-manual"
                            class="flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-all"
                            style="background:white;color:var(--text);box-shadow:0 1px 4px rgba(0,0,0,.08)">
                        ✏️ Manual Entry
                    </button>
                    <button onclick="switchTab('csv')" id="tab-csv"
                            class="flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-all text-neutral-500">
                        📄 CSV Upload
                    </button>
                </div>

                <div id="panel-manual">
                    <label class="label">Enter one item per line <span class="font-normal text-neutral-400">(max 100)</span></label>
                    <textarea id="bulk-input" rows="12" class="field font-mono text-sm" style="resize:vertical"
                              placeholder="https://example.com&#10;https://google.com&#10;...one per line"></textarea>
                    <p class="text-xs text-neutral-400 mt-2 font-medium" id="line-count">0 items</p>
                </div>

                <div id="panel-csv" class="hidden">
                    <div onclick="document.getElementById('csv-file').click()"
                         class="border-2 border-dashed rounded-xl p-10 text-center cursor-pointer transition-all hover:border-orange-300 hover:bg-orange-50"
                         style="border-color:var(--border2)">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-3" style="background:var(--orange-light)">
                            <i data-lucide="upload-cloud" class="w-6 h-6" style="color:var(--orange)"></i>
                        </div>
                        <p class="text-sm font-semibold text-neutral-600 mb-1">Drop your CSV here or click to browse</p>
                        <p class="text-xs text-neutral-400">Supports .csv and .txt files</p>
                        <input type="file" id="csv-file" accept=".csv,.txt" class="hidden" onchange="handleCSV(this)">
                    </div>
                    <div id="csv-status" class="mt-3 text-xs"></div>
                </div>
            </div>

            <button onclick="generateBulk()" class="btn btn-p btn-lg w-full justify-center a-up d3">
                <i data-lucide="zap" class="w-5 h-5"></i> Generate All QR Codes
            </button>
        </div>

        <!-- Right: Output -->
        <div class="lg:col-span-2 space-y-5">

            <div class="card p-6 a-up d1" style="border-radius:20px">
                <h3 class="font-display font-bold text-base text-neutral-900 mb-5 flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4" style="color:var(--orange)"></i> Output
                </h3>

                <div id="bulk-status" class="text-center py-10">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3" style="background:var(--bg2)">
                        <i data-lucide="layers" class="w-7 h-7 text-neutral-300"></i>
                    </div>
                    <p class="text-sm text-neutral-400 font-medium">QR codes will appear here</p>
                </div>

                <div id="bulk-progress" class="hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-neutral-600 font-semibold">Generating...</span>
                        <span id="progress-text" class="text-sm font-mono" style="color:var(--orange)">0/0</span>
                    </div>
                    <div class="w-full h-2.5 rounded-full overflow-hidden" style="background:var(--bg2)">
                        <div id="progress-bar" class="h-full rounded-full transition-all duration-300" style="width:0%;background:var(--orange)"></div>
                    </div>
                </div>

                <div id="bulk-complete" class="hidden text-center py-4">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 bg-green-50">
                        <i data-lucide="check-circle-2" class="w-7 h-7 text-green-500"></i>
                    </div>
                    <p class="font-display font-bold text-lg text-neutral-900 mb-1" id="complete-text">Done!</p>
                    <p class="text-xs text-neutral-400 mb-5">All QR codes generated successfully</p>
                    <button onclick="downloadAllBulk()" class="btn btn-p w-full justify-center">
                        <i data-lucide="download" class="w-4 h-4"></i> Download ZIP
                    </button>
                </div>
            </div>

            <!-- Thumbnails -->
            <div id="bulk-thumbnails" class="card p-5 hidden" style="border-radius:20px">
                <p class="text-xs font-bold uppercase tracking-widest text-neutral-400 mb-3">Preview</p>
                <div id="thumb-grid" class="grid grid-cols-4 gap-2 max-h-56 overflow-y-auto"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script>
let generatedQRs = [];

function switchTab(t) {
    document.getElementById('panel-manual').classList.toggle('hidden', t !== 'manual');
    document.getElementById('panel-csv').classList.toggle('hidden', t !== 'csv');
    const m = document.getElementById('tab-manual'), c = document.getElementById('tab-csv');
    if (t === 'manual') {
        m.style.background = 'white'; m.style.color = 'var(--text)'; m.style.boxShadow = '0 1px 4px rgba(0,0,0,.08)';
        c.style.background = 'transparent'; c.style.color = 'var(--text3)'; c.style.boxShadow = 'none';
    } else {
        c.style.background = 'white'; c.style.color = 'var(--text)'; c.style.boxShadow = '0 1px 4px rgba(0,0,0,.08)';
        m.style.background = 'transparent'; m.style.color = 'var(--text3)'; m.style.boxShadow = 'none';
    }
}

document.getElementById('bulk-input')?.addEventListener('input', function () {
    const l = this.value.trim().split('\n').filter(l => l.trim());
    document.getElementById('line-count').textContent = `${l.length} item${l.length !== 1 ? 's' : ''}`;
});

function handleCSV(i) {
    const f = i.files[0]; if (!f) return;
    const r = new FileReader();
    r.onload = function (e) {
        const l = e.target.result.split('\n').map(l => l.trim()).filter(l => l && !l.startsWith('#'));
        if (l.length > 1 && /^[a-zA-Z]/.test(l[0]) && /^https?:\/\//.test(l[1])) l.shift();
        document.getElementById('bulk-input').value = l.join('\n');
        document.getElementById('csv-status').innerHTML = `<span class="text-green-600 font-semibold">✓ Loaded ${l.length} items</span>`;
        document.getElementById('line-count').textContent = `${l.length} items`;
        switchTab('manual');
    };
    r.readAsText(f);
}

async function generateBulk() {
    const input = document.getElementById('bulk-input').value.trim();
    if (!input) return alert('Please enter some data first');
    const items = input.split('\n').map(l => l.trim()).filter(l => l);
    if (items.length > 100) return alert('Maximum 100 items allowed');
    if (!items.length) return alert('No valid items found');

    const type = document.getElementById('bulk-type').value;
    const size = parseInt(document.getElementById('bulk-size').value);
    const fg   = document.getElementById('bulk-fg').value;
    const bg   = document.getElementById('bulk-bg').value;

    document.getElementById('bulk-status').classList.add('hidden');
    document.getElementById('bulk-progress').classList.remove('hidden');
    document.getElementById('bulk-complete').classList.add('hidden');
    document.getElementById('bulk-thumbnails').classList.remove('hidden');
    document.getElementById('thumb-grid').innerHTML = '';
    generatedQRs = [];

    for (let i = 0; i < items.length; i++) {
        let content = items[i];
        if (type === 'url')   content = content.startsWith('http') ? content : 'https://' + content;
        if (type === 'phone') content = 'tel:' + content;
        if (type === 'email') content = 'mailto:' + content;

        document.getElementById('progress-bar').style.width = ((i + 1) / items.length * 100) + '%';
        document.getElementById('progress-text').textContent = `${i + 1}/${items.length}`;

        const canvas = await genQR(content, size, fg, bg);
        generatedQRs.push({
            name: `qr_${String(i + 1).padStart(3, '0')}_${items[i].replace(/[^a-zA-Z0-9]/g, '_').substring(0, 30)}.png`,
            canvas,
            content: items[i]
        });

        const t = document.createElement('div');
        t.className = 'rounded-lg overflow-hidden'; t.style.background = 'var(--bg2)';
        const img = document.createElement('img');
        img.src = canvas.toDataURL(); img.style.cssText = 'width:100%;height:auto;display:block';
        t.appendChild(img);
        document.getElementById('thumb-grid').appendChild(t);
        await new Promise(r => setTimeout(r, 50));
    }

    document.getElementById('bulk-progress').classList.add('hidden');
    document.getElementById('bulk-complete').classList.remove('hidden');
    document.getElementById('complete-text').textContent = `${items.length} QR codes generated!`;
}

function genQR(c, s, fg, bg) {
    return new Promise(r => {
        const d = document.createElement('div'); d.style.cssText = 'position:absolute;left:-9999px';
        document.body.appendChild(d);
        new QRCode(d, {text:c, width:s, height:s, colorDark:fg, colorLight:bg, correctLevel:QRCode.CorrectLevel.H});
        setTimeout(() => {
            const cv = d.querySelector('canvas');
            const f  = document.createElement('canvas'); f.width = s; f.height = s;
            const x  = f.getContext('2d'); x.imageSmoothingEnabled = false;
            x.drawImage(cv, 0, 0, s, s);
            document.body.removeChild(d); r(f);
        }, 100);
    });
}

async function downloadAllBulk() {
    if (!generatedQRs.length) return;
    const z = new JSZip(), f = z.folder('qr_codes');
    for (const q of generatedQRs)
        f.file(q.name, q.canvas.toDataURL('image/png').split(',')[1], {base64: true});
    f.file('manifest.txt', generatedQRs.map((q, i) => `${i + 1}. ${q.name} → ${q.content}`).join('\n'));
    const b = await z.generateAsync({type: 'blob'});
    saveAs(b, `qr_codes_bulk_${Date.now()}.zip`);
}

document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
