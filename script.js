$(document).ready(function() {

    // --- 1. LOGIKA NAVIGASI SPA ---
    const pages = $('.page');
    const navLinks = $('.nav-link');

    // Tampilkan halaman 'beranda' saat pertama kali dimuat
    pages.removeClass('active');
    $('#beranda').addClass('active');

    navLinks.on('click', function(e) {
        e.preventDefault();
        
        const targetId = $(this).attr('href');
        
        // Validasi hash
        if (!targetId || targetId === '#') return;
        
        // Sembunyikan semua halaman
        pages.removeClass('active');
        
        // Tampilkan halaman yang dituju
        $(targetId).addClass('active');
        
        // Update status 'active' di navigasi
        navLinks.removeClass('active');
        $(`.nav-link[href="${targetId}"]`).addClass('active');

        // Tutup menu mobile jika sedang terbuka
        $('#main-nav ul').removeClass('active');
        
        // Scroll ke atas halaman
        $('html, body').animate({ scrollTop: 0 }, 300);
    });

    // --- 2. LOGIKA MENU MOBILE ---
    $('#mobile-menu-toggle').on('click', function() {
        $('#main-nav ul').toggleClass('active');
    });
    
    // Tutup menu mobile saat klik di luar menu
    $(document).on('click', function(e) {
        if (!$(e.target).closest('header').length) {
            $('#main-nav ul').removeClass('active');
        }
    });

    // --- 3. LOGIKA VALIDASI FORMULIR & AJAX ---
    $('#contact-form').on('submit', function(e) {
        e.preventDefault();
        
        // Reset error
        $('.error-message').text('');
        let isValid = true;

        // Ambil nilai input
        const nama = $('#nama').val().trim();
        const email = $('#email').val().trim();
        const telepon = $('#telepon').val().trim();
        const pesan = $('#pesan').val().trim();

        // Validasi Nama
        if (nama === '') {
            $('#nama-error').text('Nama tidak boleh kosong.');
            isValid = false;
        } else if (nama.length < 3) {
            $('#nama-error').text('Nama minimal 3 karakter.');
            isValid = false;
        }

        // Validasi Email
        if (email === '') {
            $('#email-error').text('Email tidak boleh kosong.');
            isValid = false;
        } else if (!isValidEmail(email)) {
            $('#email-error').text('Format email tidak valid.');
            isValid = false;
        }

        // Validasi Telepon
        if (telepon === '') {
            $('#telepon-error').text('Nomor telepon tidak boleh kosong.');
            isValid = false;
        } else if (!/^[\d\s\-\+\(\)]+$/.test(telepon)) {
            $('#telepon-error').text('Format nomor telepon tidak valid.');
            isValid = false;
        }

        // Validasi Pesan
        if (pesan === '') {
            $('#pesan-error').text('Pesan tidak boleh kosong.');
            isValid = false;
        } else if (pesan.length < 10) {
            $('#pesan-error').text('Pesan minimal 10 karakter.');
            isValid = false;
        }

        // Jika semua valid, kirim via AJAX
        if (isValid) {
            sendFormData(nama, email, telepon, pesan);
        }
    });

    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    function sendFormData(nama, email, telepon, pesan) {
        const formStatus = $('#form-status');
        
        $.ajax({
            url: 'kontak.php',
            type: 'POST',
            dataType: 'json',
            data: {
                nama: nama,
                email: email,
                telepon: telepon,
                pesan: pesan
            },
            beforeSend: function() {
                formStatus.removeClass('error success').text('Mengirim pesan...').show();
                $('#contact-form button[type="submit"]').prop('disabled', true).text('Mengirim...');
            },
            success: function(response) {
                if (response.status === 'sukses') {
                    formStatus.removeClass('error').addClass('success').text(response.message);
                    $('#contact-form')[0].reset();
                    $('.error-message').text('');
                    
                    // Sembunyikan pesan sukses setelah 5 detik
                    setTimeout(function() {
                        formStatus.fadeOut();
                    }, 5000);
                } else {
                    formStatus.removeClass('success').addClass('error').text(response.message || 'Terjadi kesalahan saat mengirim pesan.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                formStatus.removeClass('success').addClass('error').text('Gagal terhubung ke server. Silakan coba lagi.');
            },
            complete: function() {
                $('#contact-form button[type="submit"]').prop('disabled', false).text('Kirim Pesan');
            }
        });
    }

    // --- 4. SMOOTH SCROLL untuk anchor links ---
    $('a[href^="#"]').on('click', function(e) {
        const target = $(this).attr('href');
        if (target && target !== '#' && $(target).length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $(target).offset().top - 80
            }, 500);
        }
    });

    // --- 5. ANIMASI SLIDER (sudah ditangani CSS, tapi kita bisa pause on hover) ---
    $('.testimonial-slider').hover(
        function() {
            $(this).find('.slider-track').css('animation-play-state', 'paused');
        },
        function() {
            $(this).find('.slider-track').css('animation-play-state', 'running');
        }
    );

    // --- 6. LOGIKA HERO CAROUSEL (KODE BARU) ---
    if ($('.hero-slides').length > 0) {
        setInterval(function() {
            const slides = $('.hero-slide');
            const current = slides.filter('.active');
            let next = current.next('.hero-slide');

            if (next.length === 0) {
                next = slides.first();
            }
            
            current.removeClass('active');
            next.addClass('active');
        }, 5000); // Ganti gambar setiap 5 detik
    }

});