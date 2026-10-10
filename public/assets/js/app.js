/**
 * Lost & Found SMK Informatika Sumedang
 * Main JavaScript (Vanilla JS)
 */

document.addEventListener('DOMContentLoaded', () => {
    // APK download is Android-only; make this clear before starting the download.
    const androidApkLinks = document.querySelectorAll('[data-android-apk-download]');
    androidApkLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            const message = 'File APK hanya dapat dipasang di Android. Admin tetap menggunakan website. Lanjutkan mengunduh?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // 1. Image Preview on File Input Change
    const imageInputs = document.querySelectorAll('input[type="file"][data-preview]');
    imageInputs.forEach(input => {
        const previewTargetId = input.getAttribute('data-preview');
        const previewContainer = document.getElementById(previewTargetId);
        
        if (previewContainer) {
            input.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file maksimal adalah 2MB.');
                        input.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        let img = previewContainer.querySelector('img');
                        if (!img) {
                            previewContainer.innerHTML = '';
                            img = document.createElement('img');
                            previewContainer.appendChild(img);
                        }
                        img.src = event.target.result;
                        previewContainer.style.display = 'flex';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });

    // 2. Generic Delete Confirmation Modal Handler
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const message = btn.getAttribute('data-confirm-delete') || 'Apakah Anda yakin ingin menghapus data ini?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // 3. Auto-hide alerts after 6 seconds
    const autoDismissAlerts = document.querySelectorAll('.alert-dismissible');
    autoDismissAlerts.forEach(alertEl => {
        setTimeout(() => {
            try {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                bsAlert.close();
            } catch (err) {
                alertEl.style.display = 'none';
            }
        }, 6000);
    });
});
