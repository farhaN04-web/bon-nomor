document.addEventListener('DOMContentLoaded', function() {
    const formPengajuan = document.getElementById('form-pengajuan');
    const loadingSpinner = document.getElementById('loading-spinner');
    
    if (formPengajuan) {
        formPengajuan.addEventListener('submit', function() {
            if (loadingSpinner) {
                loadingSpinner.style.display = 'flex';
            }
        });
    }

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

    const uploadModal = document.getElementById('upload-result-modal');
    function closeUploadModal() {
        if (uploadModal) {
            uploadModal.classList.add('hiding');
            setTimeout(function() {
                uploadModal.classList.remove('show');
                uploadModal.classList.remove('hiding');
            }, 500);
        }
    }
    if (uploadModal && uploadModal.classList.contains('show')) {
        setTimeout(function() {
            closeUploadModal();
        }, 1000); // 1 detik
    }
});