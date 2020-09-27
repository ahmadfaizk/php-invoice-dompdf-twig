<?php
    require 'vendor/autoload.php';
    use Dompdf\Dompdf;
    include 'config.php';

    //id pengajuan reimburse
    $id = 16;

    //Query untuk mendapatkan data pengajuan reimburse
    $sql = 'SELECT r.*,  p.nama_pegawai, d.nama_department, kp.nama_kategori_pengajuan, c.nama_cabang
        FROM t_pengajuan_reimburse AS r
        JOIN m_pegawai AS p ON r.id_pegawai = p.id_pegawai
        JOIN m_department AS d ON r.id_department = d.id_department
        JOIN m_kategori_pengajuan AS kp ON r.id_kategori_pengajuan = kp.id_kategori_pengajuan
        JOIN m_cabang AS c ON r.id_cabang = c.id_cabang
        WHERE r.id_pengajuan_reimburse = ' . $id . '
        LIMIT 1';
    $result = $conn->query($sql);
    $reimburse = $result->fetch_assoc();

    //Query untuk mendapatkan rincian pengajuan reimbuse
    $sqlRincian = 'SELECT r.*, kb.nama_kategori_biaya
        FROM t_rincian_realisasi_reimburse AS r
        JOIN m_kategori_biaya AS kb ON r.id_kategori_biaya = kb.id_kategori_biaya
        WHERE id_pengajuan_reimburse = ' . $id;
    $resultRincian = $conn->query($sqlRincian);
    $rincian = array();
    while($row = $resultRincian->fetch_assoc()) {
        array_push($rincian, $row);
    }

    //Inisialisasi object dompdf dan twig
    $dompdf = new Dompdf();
    $loader = new \Twig\Loader\FilesystemLoader('templates');
    $twig = new \Twig\Environment($loader);

    //Encoding file logo dari png ke base64 karena dompdf tidak suport gambar, hanya support text
    $path = 'assets/logo.png';
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

    //Merencer Template kosong (invoice.html) menggunakan twig dan menghasilkan kode html
    $html = $twig->render('invoice.html', array(
        'reimburse' => $reimburse,
        'rincian' => $rincian,
        'logo' => $base64
    ));
    
    //Proses Merender kode HTML kedalam bentuk PDF
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("dokumentku", array("Attachment" => 0));

    //Menutup koneksi ke database
    $conn->close();