document.addEventListener('DOMContentLoaded', function() {
    
    // Form Pengajuan Logic
    const formPengajuan = document.getElementById('form-pengajuan');
    const loadingSpinner = document.getElementById('loading-spinner');
    
    if (formPengajuan) {
        formPengajuan.addEventListener('submit', function() {
            if (loadingSpinner) {
                loadingSpinner.style.display = 'flex';
            }
        });
    }

    // Upload Page Logic
    const fileInput = document.getElementById('file_arsip');
    const fileNameDisplay = document.getElementById('file-name');

    if (fileInput && fileNameDisplay) {
        fileInput.addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                fileNameDisplay.textContent = fileInput.files[0].name;
            } else {
                fileNameDisplay.textContent = 'Pilih file untuk diupload';
            }
        });
    }

    // Pop Up
    const uploadModal = document.getElementById('upload-result-modal');

    // Fungsi untuk menutup pop-up dengan animasi
    function closeUploadModal() {
        if (uploadModal) {
            // 1. Tambahkan class untuk memicu animasi keluar
            uploadModal.classList.add('hiding');

            // 2. Setelah animasi selesai, sembunyikan pop-up sepenuhnya
            setTimeout(function() {
                uploadModal.classList.remove('show');
                uploadModal.classList.remove('hiding'); // Bersihkan class untuk pemunculan berikutnya
            }, 500); // Durasi ini (500ms) harus sama dengan durasi animasi di CSS (0.5s)
        }
    }

    // Cek apakah pop-upnya muncul saat halaman dimuat
    if (uploadModal && uploadModal.classList.contains('show')) {
        // Atur timer untuk menutupnya setelah 1 detik
        setTimeout(function() {
            closeUploadModal();
        }, 1000); // 1 detik
    }
});