/**
 * analysis.js
 * Interaksi halaman AI Analysis: tab upload/kamera, ambil foto,
 * kirim ke backend (analyze_image.php) yang memanggil Gemini Vision,
 * lalu render hasil asli ke #resultState.
 */
document.addEventListener('DOMContentLoaded', function () {
    let cameraStream = null;
    let currentImageDataUrl = null;

    const dropzone         = document.getElementById('dropzone');
    const cameraBox         = document.getElementById('cameraBox');
    const fileInput         = document.getElementById('fileInput');
    const chooseFileBtn     = document.getElementById('chooseFileBtn');
    const previewBox        = document.getElementById('previewBox');
    const previewImg        = document.getElementById('previewImg');
    const removePreviewBtn  = document.getElementById('removePreviewBtn');
    const analyzeBtn        = document.getElementById('analyzeBtn');
    const uploadState       = document.getElementById('uploadState');
    const tipsCard          = document.getElementById('tipsCard');
    const analyzingOverlay  = document.getElementById('analyzingOverlay');
    const analyzingStep     = document.getElementById('analyzingStep');
    const resultState       = document.getElementById('resultState');
    const resultThumb       = document.getElementById('resultThumb');
    const newAnalysisBtn    = document.getElementById('newAnalysisBtn');
    const saveResultBtn     = document.getElementById('saveResultBtn');
    const cameraVideo       = document.getElementById('cameraVideo');
    const cameraCanvas      = document.getElementById('cameraCanvas');
    const captureBtn        = document.getElementById('captureBtn');
    const closeCameraBtn    = document.getElementById('closeCameraBtn');
    const tabs               = document.querySelectorAll('.upload-tab');

    if (!dropzone) return; // halaman ini tidak sedang dirender

    console.log('[analysis.js] loaded OK. cameraBox found:', !!cameraBox);

    const steps = ['Mendeteksi warna kulit', 'Mengenali jenis kulit', 'Menganalisis area concern', 'Menyusun rekomendasi'];

    // ---------------- TAB SWITCH (Upload vs Kamera) ----------------
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => { t.classList.remove('is-active'); t.setAttribute('aria-selected', 'false'); });
            tab.classList.add('is-active');
            tab.setAttribute('aria-selected', 'true');

            const mode = tab.dataset.mode;
            if (mode === 'camera') {
                dropzone.classList.add('is-hidden');
                previewBox.classList.add('is-hidden');
                cameraBox.classList.remove('is-hidden');
                startCamera();
            } else {
                cameraBox.classList.add('is-hidden');
                stopCamera();
                dropzone.classList.remove('is-hidden');
            }
        });
    });

    // ---------------- UPLOAD FILE ----------------
    function handleFile(file) {
        if (!file || !file.type.startsWith('image/')) return;
        if (!['image/jpeg', 'image/png'].includes(file.type)) {
            alert('Format tidak didukung. Gunakan JPG atau PNG.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran foto maksimal 10MB.');
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) { showPreview(e.target.result); };
        reader.readAsDataURL(file);
    }

    function showPreview(dataUrl) {
        currentImageDataUrl = dataUrl;
        previewImg.src = dataUrl;
        previewBox.classList.remove('is-hidden');
        dropzone.classList.add('is-hidden');
        cameraBox.classList.add('is-hidden');
        stopCamera();
        analyzeBtn.disabled = false;
    }

    chooseFileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.click();
    });
    dropzone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => handleFile(e.target.files[0]));

    ['dragenter', 'dragover'].forEach(evt =>
        dropzone.addEventListener(evt, (e) => { e.preventDefault(); dropzone.classList.add('is-drag-over'); })
    );
    ['dragleave', 'drop'].forEach(evt =>
        dropzone.addEventListener(evt, (e) => { e.preventDefault(); dropzone.classList.remove('is-drag-over'); })
    );
    dropzone.addEventListener('drop', (e) => handleFile(e.dataTransfer.files[0]));

    removePreviewBtn.addEventListener('click', () => {
        currentImageDataUrl = null;
        previewBox.classList.add('is-hidden');
        dropzone.classList.remove('is-hidden');
        previewImg.src = '';
        fileInput.value = '';
        analyzeBtn.disabled = true;
    });

    // ---------------- CAMERA ----------------
    async function startCamera() {
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
            cameraVideo.srcObject = cameraStream;
        } catch (err) {
            alert('Tidak bisa mengakses kamera. Pastikan izin kamera sudah diberikan.');
            cameraBox.classList.add('is-hidden');
            dropzone.classList.remove('is-hidden');
        }
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(t => t.stop());
            cameraStream = null;
        }
    }

    captureBtn.addEventListener('click', () => {
        cameraCanvas.width = cameraVideo.videoWidth;
        cameraCanvas.height = cameraVideo.videoHeight;
        cameraCanvas.getContext('2d').drawImage(cameraVideo, 0, 0);
        const dataUrl = cameraCanvas.toDataURL('image/jpeg', 0.85);
        showPreview(dataUrl);
    });

    closeCameraBtn.addEventListener('click', () => {
        stopCamera();
        cameraBox.classList.add('is-hidden');
        dropzone.classList.remove('is-hidden');
    });

    // ---------------- ANALYZE ----------------
    analyzeBtn.addEventListener('click', () => {
        if (!currentImageDataUrl) return;

        uploadState.classList.add('is-hidden');
        tipsCard.classList.add('is-hidden');
        analyzingOverlay.classList.remove('is-hidden');
        analyzeBtn.disabled = true;

        let i = 0;
        const stepTimer = setInterval(() => {
            i++;
            if (i < steps.length) analyzingStep.textContent = steps[i];
        }, 700);

        fetch('analyze_image.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ image: currentImageDataUrl }),
})
.then(async res => {
    const text = await res.text();

    console.log('HTTP STATUS:', res.status);
    console.log('RAW RESPONSE:', text);

    let data;

    try {
        data = JSON.parse(text);
    } catch (e) {
        throw new Error(
            'Server mengembalikan respons bukan JSON.\n\n' +
            'HTTP: ' + res.status +
            '\n\n' +
            text.substring(0, 1000)
        );
    }

    if (!res.ok) {
        throw new Error(
            data.message ||
            data.error ||
            ('HTTP Error ' + res.status)
        );
    }

    return data;
})
.then(data => {
    clearInterval(stepTimer);
    analyzingOverlay.classList.add('is-hidden');

    console.log('GEMINI RESULT:', data);

    if (!data.success) {
        alert(data.message || 'Analisis gagal.');
        uploadState.classList.remove('is-hidden');
        tipsCard.classList.remove('is-hidden');
        analyzeBtn.disabled = false;
        return;
    }

    resultThumb.src = currentImageDataUrl;
    renderResult(data.result);
    resultState.classList.remove('is-hidden');
})
.catch(err => {
    clearInterval(stepTimer);
    analyzingOverlay.classList.add('is-hidden');
    uploadState.classList.remove('is-hidden');
    tipsCard.classList.remove('is-hidden');
    analyzeBtn.disabled = false;

    console.error('ANALYSIS ERROR:', err);

    alert(
        'ERROR SEBENARNYA:\n\n' +
        err.message
    );
});
    });

    // ---------------- RENDER RESULT ----------------
    function hexRow(container, hexes) {
        if (!container) return;
        container.innerHTML = '';
        (hexes || []).forEach(hex => {
            const span = document.createElement('span');
            span.className = 'swatch-circle';
            span.style.background = hex;
            container.appendChild(span);
        });
    }

    function namedSwatchRow(container, items) {
        if (!container) return;
        container.innerHTML = '';
        (items || []).forEach(item => {
            const wrap = document.createElement('div');
            wrap.className = 'palette-swatch-item';
            const swatch = document.createElement('span');
            swatch.className = 'palette-swatch';
            swatch.style.background = item.hex;
            const name = document.createElement('span');
            name.className = 'palette-name';
            name.textContent = item.name;
            wrap.appendChild(swatch);
            wrap.appendChild(name);
            container.appendChild(wrap);
        });
    }

    function renderResult(r) {
        const el = (id) => document.getElementById(id);

        if (el('resultToneName')) el('resultToneName').textContent = r.season + ' (' + r.undertone + ')';
        if (el('resultDesc')) el('resultDesc').textContent = r.summary || '';

        if (el('skinToneValue')) el('skinToneValue').textContent = (r.skin_tone && r.skin_tone.label) || '-';
        hexRow(el('skinToneSwatches'), r.skin_tone && r.skin_tone.swatches);

        if (el('skinTypeValue')) el('skinTypeValue').textContent = (r.skin_type && r.skin_type.value) || '-';
        if (el('skinTypeNote')) el('skinTypeNote').textContent = (r.skin_type && r.skin_type.note) || '';

        const concernTags = el('concernTags');
        if (concernTags) {
            concernTags.innerHTML = '';
            (r.concerns || []).forEach(c => {
                const span = document.createElement('span');
                span.className = 'concern-tag';
                span.textContent = c.label + ' - ' + c.percentage + '%';
                concernTags.appendChild(span);
            });
        }

        if (el('colorSeasonBadge')) el('colorSeasonBadge').textContent = r.season + ' - ' + r.undertone + ' Palette';
        if (el('colorSectionDesc')) {
            const label = (r.skin_tone && r.skin_tone.label) || '';
            el('colorSectionDesc').innerHTML =
                'Berdasarkan skin tone <strong>' + label + '</strong>, palet berikut paling menonjolkan kecerahan wajahmu.';
        }

        const rec = r.recommendations || {};
        namedSwatchRow(el('clothingSwatches'), rec.clothing);
        namedSwatchRow(el('makeupSwatches'), rec.makeup);
        namedSwatchRow(el('avoidSwatches'), rec.avoid_color);
        if (el('avoidNote')) el('avoidNote').textContent = rec.avoid_note || '';

        hexRow(el('neutralsSwatches'), rec.neutrals);
        hexRow(el('blushSwatches'), rec.makeup_groups && rec.makeup_groups.blush);
        hexRow(el('lipSwatches'), rec.makeup_groups && rec.makeup_groups.lip);
        hexRow(el('eyeshadowSwatches'), rec.makeup_groups && rec.makeup_groups.eyeshadow);
        hexRow(el('accessorySwatches'), rec.accessories);
        hexRow(el('patternSwatches'), rec.patterns);
    }

    // ---------------- RESET / SAVE ----------------
    newAnalysisBtn.addEventListener('click', () => {
        currentImageDataUrl = null;
        resultState.classList.add('is-hidden');
        uploadState.classList.remove('is-hidden');
        tipsCard.classList.remove('is-hidden');
        removePreviewBtn.click();
        analyzingStep.textContent = steps[0];
        analyzeBtn.disabled = true;
    });

    saveResultBtn.addEventListener('click', () => {
        // Hasil sudah otomatis tersimpan ke database saat analisis selesai (lihat analyze_image.php).
        saveResultBtn.textContent = 'Tersimpan';
        saveResultBtn.disabled = true;
    });
});