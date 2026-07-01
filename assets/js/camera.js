// assets/js/camera.js

document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('camera-preview');
    const captureBtn = document.getElementById('capture-btn');
    const canvas = document.getElementById('camera-canvas');
    const form = document.getElementById('absenForm');
    
    if (!video || !captureBtn) return; // Not on the attendance page

    let stream = null;
    const type = document.getElementById('absen_type').value; // 'masuk' or 'pulang'

    // 1. Initialize WebRTC Camera
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            width: { ideal: 640 }, 
            height: { ideal: 480 },
            facingMode: "user" 
        }, 
        audio: false 
    })
    .then(function (mediaStream) {
        stream = mediaStream;
        video.srcObject = mediaStream;
        video.play();
    })
    .catch(function (err) {
        console.error("Gagal mengakses kamera: ", err);
        Swal.fire({
            icon: 'error',
            title: 'Kamera Tidak Terdeteksi',
            text: 'Sistem membutuhkan akses kamera untuk mengambil bukti kehadiran. Pastikan izin kamera telah diberikan.',
            confirmButtonColor: '#2563EB',
            allowOutsideClick: false
        }).then(() => {
            window.location.href = 'dashboard.php';
        });
    });

    // 2. Capture and Submit
    captureBtn.addEventListener('click', function () {
        if (!stream) {
            Swal.fire('Kamera Error', 'Kamera belum siap, silakan tunggu.', 'warning');
            return;
        }

        // Play shutter animation (flash)
        const flash = document.querySelector('.camera-shutter-flash');
        if (flash) {
            flash.classList.add('flash-active');
            setTimeout(() => flash.classList.remove('flash-active'), 300);
        }

        // Draw image onto canvas
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Horizontal flip for mirror view
        context.translate(canvas.width, 0);
        context.scale(-1, 1);
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Reset transform
        context.setTransform(1, 0, 0, 1, 0, 0);

        // Convert to base64
        const photoData = canvas.toDataURL('image/jpeg', 0.9);
        
        // Disable button & show spinner
        captureBtn.disabled = true;
        captureBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses Absen...';

        // Prepare request
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;
        const formData = new FormData();
        formData.append('type', type);
        formData.append('photo', photoData);
        formData.append('csrf_token', csrfToken);

        // Submit via AJAX
        fetch('../ajax/process-absen.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Absensi Berhasil!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    // Turn off camera stream
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                    }
                    window.location.href = 'dashboard.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Absensi Gagal',
                    text: data.message,
                    confirmButtonColor: '#2563EB'
                });
                captureBtn.disabled = false;
                captureBtn.innerHTML = '<i class="fa-solid fa-camera me-2"></i>Ambil Foto & Kirim';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error Sistem',
                text: 'Terjadi kegagalan koneksi ke server.',
                confirmButtonColor: '#2563EB'
            });
            captureBtn.disabled = false;
            captureBtn.innerHTML = '<i class="fa-solid fa-camera me-2"></i>Ambil Foto & Kirim';
        });
    });
});
