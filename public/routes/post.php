<?php

declare(strict_types=1);

$action = (string)($_POST['_action'] ?? '');

if ($action === 'auth.login') {
    csrf_verify_or_abort();

    $username = (string)($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $next = (string)($_POST['next'] ?? '');
    $next = trim($next);
    if ($next === '') {
        $next = app_url('/?page=dashboard');
    }

    if (!auth_login_attempt($username, $password)) {
        flash_set('error', 'Username atau password salah.');
        redirect(app_url('/?page=login'));
    }

    $u = auth_user();
    if ($u && (int)$u['must_change_password'] === 1) {
        flash_set('success', 'Berhasil login. Silakan ganti password.');
        redirect(app_url('/?page=user_edit&id=' . (int)$u['id']));
    }

    redirect($next);
}

if ($action === 'auth.logout') {
    csrf_verify_or_abort();
    auth_require();
    auth_logout();
    flash_set('success', 'Anda telah logout.');
    redirect(app_url('/?page=login'));
}

if ($action === 'settings.pendaftaran.update') {
    csrf_verify_or_abort();

    $prefix = strtoupper(trim((string)($_POST['prefix'] ?? 'REG')));
    $yearToken = (string)($_POST['year_token'] ?? '{YYYY}');
    $separator = (string)($_POST['separator'] ?? '-');
    $seqLength = (int)($_POST['seq_length'] ?? 5);
    $resetPolicy = (string)($_POST['reset_policy'] ?? 'yearly');

    if ($prefix === '') {
        $prefix = 'REG';
    }
    if (!in_array($yearToken, ['{YYYY}', '{YY}', ''], true)) {
        $yearToken = '{YYYY}';
    }
    if (!in_array($resetPolicy, ['yearly', 'monthly', 'never'], true)) {
        $resetPolicy = 'yearly';
    }
    $seqLength = max(1, min(10, $seqLength));

    settings_set('pendaftaran.prefix', $prefix);
    settings_set('pendaftaran.year_token', $yearToken);
    settings_set('pendaftaran.separator', $separator);
    settings_set('pendaftaran.seq_length', (string)$seqLength);
    settings_set('pendaftaran.reset_policy', $resetPolicy);

    flash_set('success', 'Pengaturan nomor pendaftaran disimpan.');
    redirect(app_url('/?page=settings'));
}

if ($action === 'settings.kop.update') {
    csrf_verify_or_abort();

    $nama = trim((string)($_POST['kop_nama'] ?? ''));
    $alamat = trim((string)($_POST['kop_alamat'] ?? ''));
    $kontak = trim((string)($_POST['kop_kontak'] ?? ''));
    $kota = trim((string)($_POST['kop_kota'] ?? ''));
    $penanggungJawab = trim((string)($_POST['kop_penanggung_jawab'] ?? ''));

    if ($nama === '') {
        $nama = 'Siaman';
    }

    $newLogoPath = null;
    $file = $_FILES['kop_logo'] ?? null;
    if (is_array($file) && isset($file['error']) && (int)$file['error'] !== UPLOAD_ERR_NO_FILE) {
        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            flash_set('error', 'Upload logo gagal.');
            redirect(app_url('/?page=settings'));
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        $size = (int)($file['size'] ?? 0);
        if ($tmpName === '' || $size <= 0) {
            flash_set('error', 'File logo tidak valid.');
            redirect(app_url('/?page=settings'));
        }
        if ($size > 2_000_000) {
            flash_set('error', 'Ukuran logo maksimal 2MB.');
            redirect(app_url('/?page=settings'));
        }

        $mime = null;
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmpName);
        }
        if (!is_string($mime) || $mime === '') {
            if (function_exists('mime_content_type')) {
                $mime = mime_content_type($tmpName);
            }
        }
        $ext = '';
        if ($mime === 'image/png') $ext = 'png';
        if ($mime === 'image/jpeg') $ext = 'jpg';
        if ($mime === 'image/webp') $ext = 'webp';
        if ($ext === '') {
            flash_set('error', 'Tipe logo harus PNG/JPG/WEBP.');
            redirect(app_url('/?page=settings'));
        }

        $publicDir = dirname(__DIR__);
        $uploadDir = $publicDir . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                flash_set('error', 'Folder upload tidak bisa dibuat.');
                redirect(app_url('/?page=settings'));
            }
        }

        $rand = bin2hex(random_bytes(8));
        $filename = 'logo-' . date('Ymd-His') . '-' . $rand . '.' . $ext;
        $dest = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($tmpName, $dest)) {
            flash_set('error', 'Gagal menyimpan file logo.');
            redirect(app_url('/?page=settings'));
        }

        $newLogoPath = '/uploads/' . $filename;

        $old = settings_get('kop.logo_path', '');
        if (is_string($old) && $old !== '' && str_starts_with($old, '/uploads/')) {
            $oldFile = $publicDir . str_replace('/', DIRECTORY_SEPARATOR, $old);
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }
    }

    settings_set('kop.nama', $nama);
    settings_set('kop.alamat', $alamat);
    settings_set('kop.kontak', $kontak);
    settings_set('kop.kota', $kota);
    settings_set('kop.penanggung_jawab', $penanggungJawab);
    if ($newLogoPath !== null) {
        settings_set('kop.logo_path', $newLogoPath);
    }

    flash_set('success', 'Kop surat invoice disimpan.');
    redirect(app_url('/?page=settings'));
}

if ($action === 'jamaah.create') {
    csrf_verify_or_abort();

    $namaLengkap = trim((string)($_POST['nama_lengkap'] ?? ''));
    $namaBapak = trim((string)($_POST['nama_bapak_kandung'] ?? ''));
    $nik = preg_replace('/\s+/', '', (string)($_POST['nik'] ?? ''));
    $nomorKk = preg_replace('/\s+/', '', (string)($_POST['nomor_kk'] ?? ''));
    $tempatLahir = trim((string)($_POST['tempat_lahir'] ?? ''));
    $tanggalLahir = trim((string)($_POST['tanggal_lahir'] ?? ''));
    $jenisKelamin = trim((string)($_POST['jenis_kelamin'] ?? ''));
    $statusPernikahan = trim((string)($_POST['status_pernikahan'] ?? ''));
    $pendidikan = trim((string)($_POST['pendidikan'] ?? ''));
    $pekerjaan = trim((string)($_POST['pekerjaan'] ?? ''));
    $alamatLengkap = trim((string)($_POST['alamat_lengkap'] ?? ''));
    $hp = trim((string)($_POST['hp'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));

    $errors = [];
    if ($namaLengkap === '') $errors[] = 'Nama lengkap wajib diisi.';
    if ($namaBapak === '') $errors[] = 'Nama bapak kandung wajib diisi.';
    if ($nik === '') $errors[] = 'NIK wajib diisi.';
    if ($nomorKk === '') $errors[] = 'Nomor KK wajib diisi.';
    if ($tempatLahir === '') $errors[] = 'Tempat lahir wajib diisi.';
    if ($tanggalLahir === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalLahir)) $errors[] = 'Tanggal lahir wajib format YYYY-MM-DD.';
    if ($jenisKelamin === '') $errors[] = 'Jenis kelamin wajib diisi.';
    if ($statusPernikahan === '') $errors[] = 'Status pernikahan wajib diisi.';
    if ($pendidikan === '') $errors[] = 'Pendidikan wajib diisi.';
    if ($pekerjaan === '') $errors[] = 'Pekerjaan wajib diisi.';
    if ($alamatLengkap === '') $errors[] = 'Alamat lengkap wajib diisi.';
    if ($hp === '') $errors[] = 'Nomor HP/WhatsApp wajib diisi.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect(app_url('/?page=jamaah_create'));
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $now = new DateTimeImmutable('now');

        $idJamaah = generate_id_jamaah();
        $nomorPendaftaran = generate_nomor_pendaftaran($now);

        $stmt = $pdo->prepare('
            INSERT INTO jamaah (
                id_jamaah, nomor_pendaftaran,
                nama_lengkap, nama_bapak_kandung, nik, nomor_kk, tempat_lahir, tanggal_lahir,
                jenis_kelamin, status_pernikahan, pendidikan, pekerjaan, alamat_lengkap, hp, email, status
            ) VALUES (
                :id_jamaah, :nomor_pendaftaran,
                :nama_lengkap, :nama_bapak_kandung, :nik, :nomor_kk, :tempat_lahir, :tanggal_lahir,
                :jenis_kelamin, :status_pernikahan, :pendidikan, :pekerjaan, :alamat_lengkap, :hp, :email, :status
            )
        ');
        $stmt->execute([
            'id_jamaah' => $idJamaah,
            'nomor_pendaftaran' => $nomorPendaftaran,
            'nama_lengkap' => $namaLengkap,
            'nama_bapak_kandung' => $namaBapak,
            'nik' => $nik,
            'nomor_kk' => $nomorKk,
            'tempat_lahir' => $tempatLahir,
            'tanggal_lahir' => $tanggalLahir,
            'jenis_kelamin' => $jenisKelamin,
            'status_pernikahan' => $statusPernikahan,
            'pendidikan' => $pendidikan,
            'pekerjaan' => $pekerjaan,
            'alamat_lengkap' => $alamatLengkap,
            'hp' => $hp,
            'email' => $email !== '' ? $email : null,
            'status' => 'aktif',
        ]);

        $newId = (int)$pdo->lastInsertId();
        $pdo->commit();

        flash_set('success', 'Jamaah berhasil ditambahkan: ' . $idJamaah);
        redirect(app_url('/?page=jamaah_detail&id=' . $newId));
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash_set('error', 'Gagal menyimpan jamaah. Pastikan NIK/No Pendaftaran unik.');
        redirect(app_url('/?page=jamaah_create'));
    }
}

if ($action === 'jamaah.update') {
    csrf_verify_or_abort();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        flash_set('error', 'ID tidak valid.');
        redirect(app_url('/?page=jamaah'));
    }

    $namaLengkap = trim((string)($_POST['nama_lengkap'] ?? ''));
    $namaBapak = trim((string)($_POST['nama_bapak_kandung'] ?? ''));
    $nik = preg_replace('/\s+/', '', (string)($_POST['nik'] ?? ''));
    $nomorKk = preg_replace('/\s+/', '', (string)($_POST['nomor_kk'] ?? ''));
    $tempatLahir = trim((string)($_POST['tempat_lahir'] ?? ''));
    $tanggalLahir = trim((string)($_POST['tanggal_lahir'] ?? ''));
    $jenisKelamin = trim((string)($_POST['jenis_kelamin'] ?? ''));
    $statusPernikahan = trim((string)($_POST['status_pernikahan'] ?? ''));
    $pendidikan = trim((string)($_POST['pendidikan'] ?? ''));
    $pekerjaan = trim((string)($_POST['pekerjaan'] ?? ''));
    $alamatLengkap = trim((string)($_POST['alamat_lengkap'] ?? ''));
    $hp = trim((string)($_POST['hp'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $status = trim((string)($_POST['status'] ?? 'aktif'));

    $errors = [];
    if ($namaLengkap === '') $errors[] = 'Nama lengkap wajib diisi.';
    if ($namaBapak === '') $errors[] = 'Nama bapak kandung wajib diisi.';
    if ($nik === '') $errors[] = 'NIK wajib diisi.';
    if ($nomorKk === '') $errors[] = 'Nomor KK wajib diisi.';
    if ($tempatLahir === '') $errors[] = 'Tempat lahir wajib diisi.';
    if ($tanggalLahir === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalLahir)) $errors[] = 'Tanggal lahir wajib format YYYY-MM-DD.';
    if ($jenisKelamin === '') $errors[] = 'Jenis kelamin wajib diisi.';
    if ($statusPernikahan === '') $errors[] = 'Status pernikahan wajib diisi.';
    if ($pendidikan === '') $errors[] = 'Pendidikan wajib diisi.';
    if ($pekerjaan === '') $errors[] = 'Pekerjaan wajib diisi.';
    if ($alamatLengkap === '') $errors[] = 'Alamat lengkap wajib diisi.';
    if ($hp === '') $errors[] = 'Nomor HP/WhatsApp wajib diisi.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (!in_array($status, ['aktif', 'nonaktif'], true)) $errors[] = 'Status tidak valid.';

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect(app_url('/?page=jamaah_edit&id=' . $id));
    }

    try {
        jamaah_update($id, [
            'nama_lengkap' => $namaLengkap,
            'nama_bapak_kandung' => $namaBapak,
            'nik' => $nik,
            'nomor_kk' => $nomorKk,
            'tempat_lahir' => $tempatLahir,
            'tanggal_lahir' => $tanggalLahir,
            'jenis_kelamin' => $jenisKelamin,
            'status_pernikahan' => $statusPernikahan,
            'pendidikan' => $pendidikan,
            'pekerjaan' => $pekerjaan,
            'alamat_lengkap' => $alamatLengkap,
            'hp' => $hp,
            'email' => $email,
            'status' => $status,
        ]);
    } catch (Throwable $e) {
        flash_set('error', 'Gagal update jamaah. Pastikan NIK unik.');
        redirect(app_url('/?page=jamaah_edit&id=' . $id));
    }

    flash_set('success', 'Data jamaah berhasil diupdate.');
    redirect(app_url('/?page=jamaah_detail&id=' . $id));
}

if ($action === 'jamaah.import') {
    csrf_verify_or_abort();

    if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
        flash_set('error', 'File tidak ditemukan.');
        redirect(app_url('/?page=jamaah_import'));
    }

    $f = $_FILES['file'];
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        flash_set('error', 'Gagal upload file.');
        redirect(app_url('/?page=jamaah_import'));
    }

    $tmp = (string)($f['tmp_name'] ?? '');
    if ($tmp === '' || !is_file($tmp)) {
        flash_set('error', 'File upload tidak valid.');
        redirect(app_url('/?page=jamaah_import'));
    }

    $fh = fopen($tmp, 'rb');
    if ($fh === false) {
        flash_set('error', 'Tidak bisa membaca file.');
        redirect(app_url('/?page=jamaah_import'));
    }

    $delimiter = ';';
    $header = fgetcsv($fh, 0, $delimiter);
    if (!is_array($header) || !$header) {
        fclose($fh);
        flash_set('error', 'CSV kosong atau header tidak valid.');
        redirect(app_url('/?page=jamaah_import'));
    }

    if (isset($header[0])) {
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$header[0]);
    }

    $headerNorm = [];
    foreach ($header as $h) {
        $h = strtolower(trim((string)$h));
        $headerNorm[] = $h;
    }

    $required = [
        'nama_lengkap',
        'nama_bapak_kandung',
        'nik',
        'nomor_kk',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'status_pernikahan',
        'pendidikan',
        'pekerjaan',
        'alamat_lengkap',
        'hp',
        'email',
        'status',
    ];

    foreach ($required as $req) {
        if (!in_array($req, $headerNorm, true)) {
            fclose($fh);
            flash_set('error', 'Header CSV tidak sesuai template. Kolom wajib: ' . $req);
            redirect(app_url('/?page=jamaah_import'));
        }
    }

    $idx = [];
    foreach ($headerNorm as $i => $h) {
        $idx[$h] = $i;
    }

    $pdo = db();
    $inserted = 0;
    $skipped = 0;
    $failed = 0;

    $pdo->beginTransaction();
    try {
        $stmtExists = $pdo->prepare('SELECT id FROM jamaah WHERE nik = :nik LIMIT 1');
        $stmtInsert = $pdo->prepare('
            INSERT INTO jamaah (
                id_jamaah, nomor_pendaftaran,
                nama_lengkap, nama_bapak_kandung, nik, nomor_kk, tempat_lahir, tanggal_lahir,
                jenis_kelamin, status_pernikahan, pendidikan, pekerjaan, alamat_lengkap, hp, email, status
            ) VALUES (
                :id_jamaah, :nomor_pendaftaran,
                :nama_lengkap, :nama_bapak_kandung, :nik, :nomor_kk, :tempat_lahir, :tanggal_lahir,
                :jenis_kelamin, :status_pernikahan, :pendidikan, :pekerjaan, :alamat_lengkap, :hp, :email, :status
            )
        ');

        while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
            if (!is_array($row)) {
                $failed++;
                continue;
            }

            $namaLengkap = trim((string)($row[$idx['nama_lengkap']] ?? ''));
            $namaBapak = trim((string)($row[$idx['nama_bapak_kandung']] ?? ''));
            $nik = preg_replace('/\s+/', '', (string)($row[$idx['nik']] ?? ''));
            $nomorKk = preg_replace('/\s+/', '', (string)($row[$idx['nomor_kk']] ?? ''));
            $tempatLahir = trim((string)($row[$idx['tempat_lahir']] ?? ''));
            $tanggalLahir = trim((string)($row[$idx['tanggal_lahir']] ?? ''));
            $jenisKelamin = trim((string)($row[$idx['jenis_kelamin']] ?? ''));
            $statusPernikahan = trim((string)($row[$idx['status_pernikahan']] ?? ''));
            $pendidikan = trim((string)($row[$idx['pendidikan']] ?? ''));
            $pekerjaan = trim((string)($row[$idx['pekerjaan']] ?? ''));
            $alamatLengkap = trim((string)($row[$idx['alamat_lengkap']] ?? ''));
            $hp = trim((string)($row[$idx['hp']] ?? ''));
            $email = trim((string)($row[$idx['email']] ?? ''));
            $status = trim((string)($row[$idx['status']] ?? 'aktif'));

            $errors = [];
            if ($namaLengkap === '') $errors[] = 'nama_lengkap';
            if ($namaBapak === '') $errors[] = 'nama_bapak_kandung';
            if ($nik === '') $errors[] = 'nik';
            if ($nomorKk === '') $errors[] = 'nomor_kk';
            if ($tempatLahir === '') $errors[] = 'tempat_lahir';
            if ($tanggalLahir === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalLahir)) $errors[] = 'tanggal_lahir';
            if ($jenisKelamin === '') $errors[] = 'jenis_kelamin';
            if ($statusPernikahan === '') $errors[] = 'status_pernikahan';
            if ($pendidikan === '') $errors[] = 'pendidikan';
            if ($pekerjaan === '') $errors[] = 'pekerjaan';
            if ($alamatLengkap === '') $errors[] = 'alamat_lengkap';
            if ($hp === '') $errors[] = 'hp';
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email';
            if (!in_array($status, ['aktif', 'nonaktif'], true)) $errors[] = 'status';

            if ($errors) {
                $failed++;
                continue;
            }

            $stmtExists->execute(['nik' => $nik]);
            $exists = $stmtExists->fetch();
            if ($exists) {
                $skipped++;
                continue;
            }

            $now = new DateTimeImmutable('now');
            $idJamaah = generate_id_jamaah();
            $nomorPendaftaran = generate_nomor_pendaftaran($now);

            try {
                $stmtInsert->execute([
                    'id_jamaah' => $idJamaah,
                    'nomor_pendaftaran' => $nomorPendaftaran,
                    'nama_lengkap' => $namaLengkap,
                    'nama_bapak_kandung' => $namaBapak,
                    'nik' => $nik,
                    'nomor_kk' => $nomorKk,
                    'tempat_lahir' => $tempatLahir,
                    'tanggal_lahir' => $tanggalLahir,
                    'jenis_kelamin' => $jenisKelamin,
                    'status_pernikahan' => $statusPernikahan,
                    'pendidikan' => $pendidikan,
                    'pekerjaan' => $pekerjaan,
                    'alamat_lengkap' => $alamatLengkap,
                    'hp' => $hp,
                    'email' => $email !== '' ? $email : null,
                    'status' => $status !== '' ? $status : 'aktif',
                ]);
                $inserted++;
            } catch (Throwable $e) {
                $failed++;
                continue;
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        fclose($fh);
        flash_set('error', 'Import gagal. ' . $e->getMessage());
        redirect(app_url('/?page=jamaah_import'));
    }

    fclose($fh);
    flash_set('success', "Import selesai. Berhasil: {$inserted}, Skip (duplikat NIK): {$skipped}, Gagal: {$failed}.");
    redirect(app_url('/?page=jamaah'));
}

if ($action === 'client.create') {
    csrf_verify_or_abort();

    $namaPerusahaan = trim((string)($_POST['nama_perusahaan'] ?? ''));
    $namaPic = trim((string)($_POST['nama_pic'] ?? ''));
    $alamat = trim((string)($_POST['alamat'] ?? ''));
    $noTlp = trim((string)($_POST['no_tlp'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $status = trim((string)($_POST['status'] ?? 'aktif'));

    $errors = [];
    if ($namaPerusahaan === '') $errors[] = 'Nama perusahaan wajib diisi.';
    if ($namaPic === '') $errors[] = 'Nama PIC wajib diisi.';
    if ($alamat === '') $errors[] = 'Alamat wajib diisi.';
    if ($noTlp === '') $errors[] = 'No Tlp wajib diisi.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (!in_array($status, ['aktif', 'nonaktif'], true)) $errors[] = 'Status tidak valid.';

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect(app_url('/?page=client_create'));
    }

    try {
        $newId = client_create([
            'nama_perusahaan' => $namaPerusahaan,
            'nama_pic' => $namaPic,
            'alamat' => $alamat,
            'no_tlp' => $noTlp,
            'email' => $email,
            'status' => $status,
        ]);
    } catch (Throwable $e) {
        flash_set('error', 'Gagal menyimpan klien.');
        redirect(app_url('/?page=client_create'));
    }

    flash_set('success', 'Klien berhasil ditambahkan.');
    redirect(app_url('/?page=client_edit&id=' . $newId));
}

if ($action === 'client.update') {
    csrf_verify_or_abort();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        flash_set('error', 'ID tidak valid.');
        redirect(app_url('/?page=clients'));
    }

    $namaPerusahaan = trim((string)($_POST['nama_perusahaan'] ?? ''));
    $namaPic = trim((string)($_POST['nama_pic'] ?? ''));
    $alamat = trim((string)($_POST['alamat'] ?? ''));
    $noTlp = trim((string)($_POST['no_tlp'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $status = trim((string)($_POST['status'] ?? 'aktif'));

    $errors = [];
    if ($namaPerusahaan === '') $errors[] = 'Nama perusahaan wajib diisi.';
    if ($namaPic === '') $errors[] = 'Nama PIC wajib diisi.';
    if ($alamat === '') $errors[] = 'Alamat wajib diisi.';
    if ($noTlp === '') $errors[] = 'No Tlp wajib diisi.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (!in_array($status, ['aktif', 'nonaktif'], true)) $errors[] = 'Status tidak valid.';

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect(app_url('/?page=client_edit&id=' . $id));
    }

    try {
        client_update($id, [
            'nama_perusahaan' => $namaPerusahaan,
            'nama_pic' => $namaPic,
            'alamat' => $alamat,
            'no_tlp' => $noTlp,
            'email' => $email,
            'status' => $status,
        ]);
    } catch (Throwable $e) {
        flash_set('error', 'Gagal update klien.');
        redirect(app_url('/?page=client_edit&id=' . $id));
    }

    flash_set('success', 'Data klien berhasil diupdate.');
    redirect(app_url('/?page=client_edit&id=' . $id));
}

if ($action === 'role.create') {
    csrf_verify_or_abort();
    auth_require_admin_or_staff();

    $name = (string)($_POST['name'] ?? '');
    try {
        role_create($name);
        flash_set('success', 'Role berhasil ditambahkan.');
    } catch (Throwable $e) {
        flash_set('error', 'Gagal menambah role. Pastikan nama unik.');
    }
    redirect(app_url('/?page=users'));
}

if ($action === 'user.create') {
    csrf_verify_or_abort();
    auth_require_admin_or_staff();

    $username = (string)($_POST['username'] ?? '');
    $roleId = (int)($_POST['role_id'] ?? 0);
    $password = (string)($_POST['password'] ?? '');
    $status = (string)($_POST['status'] ?? 'aktif');

    try {
        user_create([
            'username' => $username,
            'password' => $password,
            'role_id' => $roleId,
            'status' => $status,
        ]);
        flash_set('success', 'User berhasil ditambahkan.');
        redirect(app_url('/?page=users'));
    } catch (Throwable $e) {
        flash_set('error', 'Gagal menambah user. Pastikan username unik dan role dipilih.');
        redirect(app_url('/?page=user_create'));
    }
}

if ($action === 'user.update') {
    csrf_verify_or_abort();
    auth_require_admin_or_staff();

    $id = (int)($_POST['id'] ?? 0);
    $username = (string)($_POST['username'] ?? '');
    $roleId = (int)($_POST['role_id'] ?? 0);
    $status = (string)($_POST['status'] ?? 'aktif');
    $mustChange = (int)($_POST['must_change_password'] ?? 0);
    $newPassword = (string)($_POST['new_password'] ?? '');
    $newPasswordConfirm = (string)($_POST['new_password_confirm'] ?? '');

    try {
        user_update($id, [
            'username' => $username,
            'role_id' => $roleId,
            'status' => $status,
            'must_change_password' => $mustChange,
        ]);

        if ($newPassword !== '') {
            if ($newPassword !== $newPasswordConfirm) {
                flash_set('error', 'Konfirmasi password tidak sama.');
                redirect(app_url('/?page=user_edit&id=' . $id));
            }
            user_set_password($id, $newPassword);
        }

        $me = auth_user();
        if ($me && (int)$me['id'] === $id) {
            $_SESSION['auth']['must_change_password'] = 0;
        }

        flash_set('success', 'User berhasil diupdate.');
        redirect(app_url('/?page=users'));
    } catch (Throwable $e) {
        flash_set('error', 'Gagal update user. Pastikan username unik dan role valid.');
        redirect(app_url('/?page=user_edit&id=' . $id));
    }
}

if ($action === 'paket.create') {
    csrf_verify_or_abort();

    $nama = trim((string)($_POST['nama'] ?? ''));
    $kode = trim((string)($_POST['kode'] ?? ''));
    $durasiHari = (int)($_POST['durasi_hari'] ?? 0);
    $tanggalBerangkat = trim((string)($_POST['tanggal_berangkat'] ?? ''));
    $harga = trim((string)($_POST['harga'] ?? '0'));
    $currency = strtoupper(trim((string)($_POST['currency'] ?? 'IDR')));
    $deskripsi = trim((string)($_POST['deskripsi'] ?? ''));
    $status = trim((string)($_POST['status'] ?? 'aktif'));

    $errors = [];
    if ($nama === '') $errors[] = 'Nama paket wajib diisi.';
    if ($durasiHari <= 0) $errors[] = 'Durasi wajib > 0.';
    if ($tanggalBerangkat !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalBerangkat)) $errors[] = 'Tanggal berangkat harus format YYYY-MM-DD.';
    if ($currency === '') $errors[] = 'Mata uang wajib diisi.';
    if (!in_array($status, ['aktif', 'nonaktif'], true)) $errors[] = 'Status tidak valid.';
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $harga)) $errors[] = 'Harga harus angka (contoh: 35000000 atau 35000000.00).';

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect(app_url('/?page=paket_create'));
    }

    try {
        $newId = paket_create([
            'kode' => $kode,
            'nama' => $nama,
            'durasi_hari' => $durasiHari,
            'tanggal_berangkat' => $tanggalBerangkat,
            'harga' => $harga,
            'currency' => $currency,
            'deskripsi' => $deskripsi,
            'status' => $status,
        ]);
    } catch (Throwable $e) {
        flash_set('error', 'Gagal menyimpan paket. Pastikan kode paket unik (jika diisi).');
        redirect(app_url('/?page=paket_create'));
    }

    flash_set('success', 'Paket berhasil ditambahkan.');
    redirect(app_url('/?page=paket_edit&id=' . $newId));
}

if ($action === 'paket.update') {
    csrf_verify_or_abort();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        flash_set('error', 'ID tidak valid.');
        redirect(app_url('/?page=paket'));
    }

    $nama = trim((string)($_POST['nama'] ?? ''));
    $kode = trim((string)($_POST['kode'] ?? ''));
    $durasiHari = (int)($_POST['durasi_hari'] ?? 0);
    $tanggalBerangkat = trim((string)($_POST['tanggal_berangkat'] ?? ''));
    $harga = trim((string)($_POST['harga'] ?? '0'));
    $currency = strtoupper(trim((string)($_POST['currency'] ?? 'IDR')));
    $deskripsi = trim((string)($_POST['deskripsi'] ?? ''));
    $status = trim((string)($_POST['status'] ?? 'aktif'));

    $errors = [];
    if ($nama === '') $errors[] = 'Nama paket wajib diisi.';
    if ($durasiHari <= 0) $errors[] = 'Durasi wajib > 0.';
    if ($tanggalBerangkat !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalBerangkat)) $errors[] = 'Tanggal berangkat harus format YYYY-MM-DD.';
    if ($currency === '') $errors[] = 'Mata uang wajib diisi.';
    if (!in_array($status, ['aktif', 'nonaktif'], true)) $errors[] = 'Status tidak valid.';
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $harga)) $errors[] = 'Harga harus angka (contoh: 35000000 atau 35000000.00).';

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect(app_url('/?page=paket_edit&id=' . $id));
    }

    try {
        paket_update($id, [
            'kode' => $kode,
            'nama' => $nama,
            'durasi_hari' => $durasiHari,
            'tanggal_berangkat' => $tanggalBerangkat,
            'harga' => $harga,
            'currency' => $currency,
            'deskripsi' => $deskripsi,
            'status' => $status,
        ]);
    } catch (Throwable $e) {
        flash_set('error', 'Gagal update paket. Pastikan kode paket unik (jika diisi).');
        redirect(app_url('/?page=paket_edit&id=' . $id));
    }

    flash_set('success', 'Paket berhasil diupdate.');
    redirect(app_url('/?page=paket_edit&id=' . $id));
}

if ($action === 'invoice.create') {
    csrf_verify_or_abort();

    $targetType = strtolower(trim((string)($_POST['target_type'] ?? 'jamaah')));
    $jamaahId = (int)($_POST['jamaah_id'] ?? 0);
    $clientId = (int)($_POST['client_id'] ?? 0);
    $paketId = (int)($_POST['paket_id'] ?? 0);
    $tanggal = trim((string)($_POST['tanggal'] ?? ''));
    $notes = trim((string)($_POST['notes'] ?? ''));

    $itemLabels = $_POST['item_label'] ?? [];
    $itemQtys = $_POST['item_qty'] ?? [];
    $itemPrices = $_POST['item_price'] ?? [];
    $diskon = trim((string)($_POST['diskon'] ?? '0'));
    $pajak = trim((string)($_POST['pajak'] ?? '0'));

    $errors = [];
    if (!in_array($targetType, ['jamaah', 'client'], true)) $errors[] = 'Target invoice tidak valid.';
    if ($targetType === 'jamaah' && $jamaahId <= 0) $errors[] = 'Jamaah wajib dipilih.';
    if ($targetType === 'client' && $clientId <= 0) $errors[] = 'Klien wajib dipilih.';
    if ($tanggal === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) $errors[] = 'Tanggal wajib format YYYY-MM-DD.';
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $diskon)) $errors[] = 'Diskon harus angka.';
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $pajak)) $errors[] = 'Pajak harus angka.';

    $paket = null;
    if ($targetType === 'jamaah' && $paketId > 0) {
        try {
            $paket = paket_find($paketId);
        } catch (Throwable $e) {
            $paket = null;
        }
        if (!$paket) {
            $errors[] = 'Paket tidak ditemukan.';
        }
    }

    if ($targetType === 'client' && $clientId > 0) {
        try {
            $client = client_find($clientId);
        } catch (Throwable $e) {
            $client = null;
        }
        if (!$client) {
            $errors[] = 'Klien tidak ditemukan.';
        } elseif ((string)$client['status'] !== 'aktif') {
            $errors[] = 'Klien nonaktif.';
        }
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect(app_url('/?page=invoice_create'));
    }

    $items = [];
    if (!is_array($itemLabels) || !is_array($itemQtys) || !is_array($itemPrices)) {
        flash_set('error', 'Item invoice tidak valid.');
        redirect(app_url('/?page=invoice_create'));
    }

    $max = max(count($itemLabels), count($itemQtys), count($itemPrices));
    for ($i = 0; $i < $max; $i++) {
        $label = trim((string)($itemLabels[$i] ?? ''));
        $qtyRaw = trim((string)($itemQtys[$i] ?? ''));
        $priceRaw = trim((string)($itemPrices[$i] ?? ''));

        if ($label === '' && $qtyRaw === '' && $priceRaw === '') {
            continue;
        }

        if ($label === '') {
            $errors[] = 'Label item wajib diisi.';
            continue;
        }
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $qtyRaw)) {
            $errors[] = 'Qty item harus angka.';
            continue;
        }
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $priceRaw)) {
            $errors[] = 'Harga item harus angka.';
            continue;
        }

        $qty = (float)$qtyRaw;
        $price = (float)$priceRaw;
        $items[] = [
            'label' => $label,
            'qty' => $qty,
            'price' => $price,
            'total' => $qty * $price,
        ];
    }

    if ($paket) {
        $paketLabel = (string)$paket['nama'];
        $paketPrice = (float)$paket['harga'];
        $hasPaketItem = false;
        foreach ($items as $it) {
            if (
                (string)$it['label'] === $paketLabel &&
                abs(((float)$it['qty']) - 1.0) < 0.00001 &&
                abs(((float)$it['price']) - $paketPrice) < 0.00001
            ) {
                $hasPaketItem = true;
                break;
            }
        }
        if (!$hasPaketItem) {
            array_unshift($items, [
                'label' => $paketLabel,
                'qty' => 1.0,
                'price' => $paketPrice,
                'total' => $paketPrice,
            ]);
        }
    }

    if (!$items) {
        $errors[] = 'Minimal 1 item invoice harus diisi.';
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect(app_url('/?page=invoice_create'));
    }

    try {
        $newId = invoice_create([
            'jamaah_id' => $targetType === 'jamaah' ? $jamaahId : null,
            'client_id' => $targetType === 'client' ? $clientId : null,
            'paket_id' => $paket ? (int)$paket['id'] : null,
            'tanggal' => $tanggal,
            'notes' => $notes,
            'items' => $items,
            'diskon' => (float)$diskon,
            'pajak' => (float)$pajak,
        ]);
    } catch (Throwable $e) {
        flash_set('error', 'Gagal membuat invoice.');
        redirect(app_url('/?page=invoice_create'));
    }

    flash_set('success', 'Invoice berhasil dibuat.');
    redirect(app_url('/?page=invoice_detail&id=' . $newId));
}

if ($action === 'invoice.delete') {
    csrf_verify_or_abort();
    auth_require_admin();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        flash_set('error', 'ID invoice tidak valid.');
        redirect(app_url('/?page=invoice'));
    }

    try {
        invoice_delete($id);
        flash_set('success', 'Invoice berhasil dihapus.');
    } catch (Throwable $e) {
        flash_set('error', 'Gagal menghapus invoice.');
    }
    redirect(app_url('/?page=invoice'));
}

if ($action === 'payment.create') {
    csrf_verify_or_abort();

    $invoiceId = (int)($_POST['invoice_id'] ?? 0);
    if ($invoiceId <= 0) {
        flash_set('error', 'Invoice tidak valid.');
        redirect(app_url('/?page=invoice'));
    }

    $tanggal = trim((string)($_POST['tanggal'] ?? ''));
    $metode = strtolower(trim((string)($_POST['metode'] ?? '')));
    $amount = trim((string)($_POST['amount'] ?? '0'));
    $pengirim = strtolower(trim((string)($_POST['pengirim'] ?? '')));
    $outlet = strtolower(trim((string)($_POST['outlet'] ?? '')));
    $sales = strtolower(trim((string)($_POST['sales'] ?? '')));
    $reference = trim((string)($_POST['reference'] ?? ''));

    $errors = [];
    if ($tanggal === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) $errors[] = 'Tanggal wajib format YYYY-MM-DD.';
    if ($metode === '') $errors[] = 'Metode wajib diisi.';
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $amount)) $errors[] = 'Jumlah harus angka.';
    if ((float)$amount <= 0) $errors[] = 'Jumlah harus lebih dari 0.';

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect(app_url('/?page=invoice_detail&id=' . $invoiceId));
    }

    try {
        $inv = invoice_find($invoiceId);
    } catch (Throwable $e) {
        $inv = null;
    }
    if (!$inv) {
        flash_set('error', 'Invoice tidak ditemukan.');
        redirect(app_url('/?page=invoice'));
    }
    $remain = (float)$inv['remaining_total'];
    if (((float)$amount) - $remain > 0.00001) {
        flash_set('error', 'Jumlah melebihi sisa tagihan: ' . rupiah($remain) . '.');
        redirect(app_url('/?page=invoice_detail&id=' . $invoiceId));
    }

    try {
        $paymentId = payment_create($invoiceId, [
            'tanggal' => $tanggal,
            'metode' => $metode,
            'amount' => $amount,
            'pengirim' => $pengirim,
            'outlet' => $outlet,
            'sales' => $sales,
            'reference' => $reference,
        ]);
    } catch (Throwable $e) {
        flash_set('error', 'Gagal menyimpan pembayaran.');
        redirect(app_url('/?page=invoice_detail&id=' . $invoiceId));
    }

    flash_set('success', 'Pembayaran tersimpan. Kuitansi dibuat.');
    redirect(app_url('/?page=kuitansi_detail&id=' . $paymentId));
}

if ($action === 'payment.void') {
    csrf_verify_or_abort();

    $paymentId = (int)($_POST['id'] ?? 0);
    $invoiceId = (int)($_POST['invoice_id'] ?? 0);
    $reason = trim((string)($_POST['reason'] ?? ''));

    if ($paymentId <= 0 || $invoiceId <= 0) {
        flash_set('error', 'Pembayaran tidak valid.');
        redirect(app_url('/?page=invoice'));
    }
    if (!payments_void_supported()) {
        flash_set('error', 'Database belum siap untuk fitur void. Jalankan migrasi 003_payments_void.sql.');
        redirect(app_url('/?page=invoice_detail&id=' . $invoiceId));
    }
    if ($reason === '') {
        flash_set('error', 'Alasan void wajib diisi.');
        redirect(app_url('/?page=payment_void&id=' . $paymentId));
    }

    try {
        $invId = payment_void($paymentId, $reason);
    } catch (Throwable $e) {
        flash_set('error', 'Gagal void pembayaran.');
        redirect(app_url('/?page=invoice_detail&id=' . $invoiceId));
    }

    if (!$invId) {
        flash_set('error', 'Pembayaran tidak ditemukan.');
        redirect(app_url('/?page=invoice_detail&id=' . $invoiceId));
    }

    flash_set('success', 'Pembayaran di-void.');
    redirect(app_url('/?page=invoice_detail&id=' . (int)$invId));
}

http_response_code(404);
echo 'Unknown action.';
exit;
