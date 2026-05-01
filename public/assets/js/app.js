document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity .5s';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 5000);
    });

    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-auto-submit]').forEach(function (el) {
        el.addEventListener('change', function () {
            if (el.form) el.form.submit();
        });
    });

    // Auto-fill from URL params on product create form
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('barcode')) {
        const barcodeInput = document.getElementById('productBarcode');
        if (barcodeInput) barcodeInput.value = urlParams.get('barcode');
    }
    if (urlParams.get('name')) {
        const nameInput = document.querySelector('input[name="name"]');
        if (nameInput) nameInput.value = urlParams.get('name');
    }
});

// --- Barcode Camera Scanner (for product form) ---
let html5Scanner = null;
let scannerMode = '';

function openBarcodeScanner(mode) {
    scannerMode = mode;
    const overlay = document.getElementById('scannerOverlay');
    const container = document.getElementById('scannerContainer');
    const result = document.getElementById('scannerResult');
    if (!overlay) return;
    overlay.classList.remove('d-none');
    if (result) result.classList.add('d-none');
    if (container) container.innerHTML = '';

    function startScanner() {
        Html5Qrcode.getCameras().then(function (cameras) {
            if (cameras.length === 0) { alert('No camera found.'); return; }
            var camId = cameras[cameras.length - 1].id;
            html5Scanner = new Html5Qrcode('scannerContainer');
            html5Scanner.start(camId, { fps: 10, qrbox: { width: 250, height: 150 } },
                function (decodedText) {
                    closeBarcodeScanner();
                    var input = document.getElementById('productBarcode');
                    if (input) input.value = decodedText;
                    if (scannerMode === 'product') lookupBarcode();
                },
                function () {}
            ).catch(function (err) {
                alert('Camera error: ' + err);
                closeBarcodeScanner();
            });
        }).catch(function (err) {
            alert('Camera access denied: ' + err);
            closeBarcodeScanner();
        });
    }

    if (typeof Html5Qrcode === 'undefined') {
        loadScript('https://cdn.jsdelivr.net/npm/html5-qrcode/dist/html5-qrcode.min.js', startScanner);
        return;
    }
    startScanner();
}

function loadScript(url, callback) {
    var script = document.createElement('script');
    script.src = url;
    script.onload = callback;
    document.head.appendChild(script);
}

function closeBarcodeScanner() {
    if (html5Scanner) {
        html5Scanner.stop().then(function () { html5Scanner.clear(); html5Scanner = null; }).catch(function () {});
    }
    const overlay = document.getElementById('scannerOverlay');
    if (overlay) overlay.classList.add('d-none');
}

function lookupBarcode() {
    const input = document.getElementById('productBarcode');
    if (!input || !input.value) return;
    const barcode = input.value.trim();
    if (!barcode) return;

    fetch('https://world.openfoodfacts.org/api/v0/product/' + barcode + '.json')
        .then(r => r.json())
        .then(data => {
            if (data.status === 1 && data.product) {
                const p = data.product;
                const nameInput = document.querySelector('input[name="name"]');
                if (nameInput && !nameInput.value) nameInput.value = p.product_name || '';
                if (p.categories_tags && p.categories_tags.length > 0) {
                    const cat = p.categories_tags[0].replace('en:', '');
                    // Try to auto-select matching category
                    const catSelect = document.querySelector('select[name="category_id"]');
                    if (catSelect) {
                        const opts = catSelect.options;
                        for (let i = 0; i < opts.length; i++) {
                            if (opts[i].text.toLowerCase().includes(cat.toLowerCase())) {
                                opts[i].selected = true;
                                break;
                            }
                        }
                    }
                }
                alert('Product info filled from barcode lookup!');
            } else {
                alert('Product not found in Open Food Facts.');
            }
        })
        .catch(function () {
            alert('Lookup failed. Check internet connection.');
        });
}
