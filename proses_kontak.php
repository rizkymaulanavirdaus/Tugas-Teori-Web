<?php
// Aktifkan error reporting untuk development (matikan di production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Set header ke JSON
header('Content-Type: application/json; charset=utf-8');

// Izinkan CORS jika diperlukan (untuk testing)
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: POST');
// header('Access-Control-Allow-Headers: Content-Type');

$response = [];

// Hanya proses jika metodenya POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Ambil data POST dengan filter
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telepon = isset($_POST['telepon']) ? trim($_POST['telepon']) : '';
    $pesan = isset($_POST['pesan']) ? trim($_POST['pesan']) : '';

    // 2. Validasi di Sisi Server
    $errors = [];
    
    // Validasi Nama
    if (empty($nama)) {
        $errors[] = 'Nama tidak boleh kosong.';
    } elseif (strlen($nama) < 3) {
        $errors[] = 'Nama minimal 3 karakter.';
    } elseif (strlen($nama) > 100) {
        $errors[] = 'Nama maksimal 100 karakter.';
    }
    
    // Validasi Email
    if (empty($email)) {
        $errors[] = 'Email tidak boleh kosong.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    
    // Validasi Telepon
    if (empty($telepon)) {
        $errors[] = 'Nomor telepon tidak boleh kosong.';
    } elseif (!preg_match('/^[\d\s\-\+\(\)]+$/', $telepon)) {
        $errors[] = 'Format nomor telepon tidak valid.';
    }
    
    // Validasi Pesan
    if (empty($pesan)) {
        $errors[] = 'Pesan tidak boleh kosong.';
    } elseif (strlen($pesan) < 10) {
        $errors[] = 'Pesan minimal 10 karakter.';
    } elseif (strlen($pesan) > 1000) {
        $errors[] = 'Pesan maksimal 1000 karakter.';
    }
    
    // Jika ada error validasi
    if (!empty($errors)) {
        $response['status'] = 'error';
        $response['message'] = implode(' ', $errors);
    } else {
        
        // 3. Sanitasi Data (keamanan penting!)
        $nama_bersih = htmlspecialchars(strip_tags($nama), ENT_QUOTES, 'UTF-8');
        $email_bersih = filter_var($email, FILTER_SANITIZE_EMAIL);
        $telepon_bersih = htmlspecialchars(strip_tags($telepon), ENT_QUOTES, 'UTF-8');
        $pesan_bersih = htmlspecialchars(strip_tags($pesan), ENT_QUOTES, 'UTF-8');

        // 4. Proses data (pilih salah satu):
        
        // OPSI A: Kirim Email
        /*
        $to = "admin@shoeclean.com";
        $subject = "Pesan Baru dari Website - " . $nama_bersih;
        $message = "
        Nama: $nama_bersih
        Email: $email_bersih
        Telepon: $telepon_bersih
        
        Pesan:
        $pesan_bersih
        ";
        $headers = "From: $email_bersih\r\n";
        $headers .= "Reply-To: $email_bersih\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        if (mail($to, $subject, $message, $headers)) {
            $response['status'] = 'sukses';
            $response['message'] = 'Pesan Anda telah terkirim! Terima kasih, ' . $nama_bersih . '.';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Gagal mengirim email. Silakan coba lagi.';
        }
        */
        
        // OPSI B: Simpan ke Database
        /*
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=shoeclean', 'username', 'password');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "INSERT INTO kontak (nama, email, telepon, pesan, tanggal) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama_bersih, $email_bersih, $telepon_bersih, $pesan_bersih]);
            
            $response['status'] = 'sukses';
            $response['message'] = 'Pesan Anda telah tersimpan! Terima kasih, ' . $nama_bersih . '.';
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = 'Gagal menyimpan data. Silakan coba lagi.';
            // Log error untuk debugging
            error_log("Database Error: " . $e->getMessage());
        }
        */
        
        // OPSI C: Simpan ke File (untuk demo/testing)
        $timestamp = date('Y-m-d H:i:s');
        $logData = "
====================
Tanggal: $timestamp
Nama: $nama_bersih
Email: $email_bersih
Telepon: $telepon_bersih
Pesan: $pesan_bersih
====================

";
        
        // Simpan ke file log
        $logFile = 'kontak_log.txt';
        if (file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX)) {
            $response['status'] = 'sukses';
            $response['message'] = 'Pesan Anda telah terkirim! Terima kasih, ' . $nama_bersih . '. Kami akan segera menghubungi Anda.';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Gagal menyimpan pesan. Silakan coba lagi.';
        }
        
        // OPSI D: Simulasi sukses (untuk testing tanpa proses nyata)
        // $response['status'] = 'sukses';
        // $response['message'] = 'Pesan Anda telah terkirim! Terima kasih, ' . $nama_bersih . '.';
    }

} else {
    // Jika bukan metode POST
    $response['status'] = 'error';
    $response['message'] = 'Metode request tidak valid. Hanya POST yang diperbolehkan.';
}

// 6. Kembalikan respons sebagai JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
?>