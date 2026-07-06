/**
 * analysis.js
 * Interaksi halaman AI Analysis: pilih/drag foto, tombol analisis,
 * animasi "menganalisis", lalu tampilkan panel hasil.
 *
 * CATATAN: bagian "kirim ke backend AI" ditandai TODO di bawah.
 * Saat ini hasil masih memakai data contoh di HTML (resultState)
 * supaya tampilan bisa langsung dicek sebelum API-nya siap.
 */
document.addEventListener('DOMContentLoaded', function () {
    const dropzone       = document.getElementById('dropzone');
    const fileInput       = document.getElementById('fileInput');
    const chooseFileBtn   = document.getElementById('chooseFileBtn');
    const previewBox      = document.getElementById('previewBox');
    const previewImg      = document.getElementById('previewImg');
    const removePreviewBtn= document.getElementById('removePreviewBtn');
    const analyzeBtn      = document.getElementById('analyzeBtn');
    const uploadState      = document.getElementById('uploadState');
    const tipsCard         = document.getElementById('tipsCard');
    const analyzingOverlay = document.getElementById('analyzingOverlay');
    const analyzingStep    = document.getElementById('analyzingStep');
    const resultState       = document.getElementById('resultState');
    const resultThumb       = document.getElementById('resultThumb');
    const newAnalysisBtn    = document.getElementById('newAnalysisBtn');
    const saveResultBtn     = document.getElementById('saveResultBtn');

    if (!dropzone) return; // halaman ini tidak sedang dirender

    const steps = ['Mendeteksi warna kulit', 'Mengenali jenis kulit', 'Menganalisis area concern', 'Menyusun rekomendasi'];

    function handleFile(file) {
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            previewBox.classList.remove('is-hidden');
            dropzone.classList.add('is-hidden');
            analyzeBtn.disabled = false;
        };
        reader.readAsDataURL(file);
    }

    chooseFileBtn.addEventListener('click', () => fileInput.click());
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
        previewBox.classList.add('is-hidden');
        dropzone.classList.remove('is-hidden');
        previewImg.src = '';
        fileInput.value = '';
        analyzeBtn.disabled = true;
    });

    analyzeBtn.addEventListener('click', () => {
        uploadState.classList.add('is-hidden');
        tipsCard.classList.add('is-hidden');
        analyzingOverlay.classList.remove('is-hidden');

        let i = 0;
        const stepTimer = setInterval(() => {
            i++;
            if (i < steps.length) {
                analyzingStep.textContent = steps[i];
            }
        }, 700);

        // TODO: ganti simulasi ini dengan panggilan fetch() ke endpoint AI
        // yang sesungguhnya, lalu isi elemen di dalam #resultState
        // (resultToneName, tone-swatch, concern-tags, dst) dari respons API.
        setTimeout(() => {
            clearInterval(stepTimer);
            resultThumb.src = previewImg.src;
            analyzingOverlay.classList.add('is-hidden');
            resultState.classList.remove('is-hidden');
        }, 2900);
    });

    newAnalysisBtn.addEventListener('click', () => {
        resultState.classList.add('is-hidden');
        uploadState.classList.remove('is-hidden');
        tipsCard.classList.remove('is-hidden');
        removePreviewBtn.click();
        analyzingStep.textContent = steps[0];
    });

    saveResultBtn.addEventListener('click', () => {
        // TODO: kirim hasil ke endpoint "saved results" (mis. via fetch POST)
        saveResultBtn.innerHTML = '&#10003; Tersimpan';
        saveResultBtn.disabled = true;
    });
});