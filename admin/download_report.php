<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    exit('Akses ditolak.');
}
require '../vendor/autoload.php';
require '../config/koneksi.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
$spreadsheet = new Spreadsheet();
$kategori_query = mysqli_query($conn, "SELECT DISTINCT kategori FROM surat ORDER BY kategori ASC");
$kategori_list = [];
while($row = mysqli_fetch_assoc($kategori_query)) {
    $kategori_list[] = $row['kategori'];
}
$sheetIndex = 0;
foreach ($kategori_list as $kategori) {
    if ($sheetIndex > 0) {
        $spreadsheet->createSheet();
    }
    $sheet = $spreadsheet->setActiveSheetIndex($sheetIndex);
    $sheet->setTitle($kategori);
    $header = ['Tanggal Pengajuan', 'Nomor Surat', 'Kategori', 'Kepada', 'Perihal', 'Nama Pengaju', 'Konseptor', 'Status Arsip', 'TTD'];
    $sheet->fromArray($header, NULL, 'A1');
    $stmt = mysqli_prepare($conn, "SELECT tanggal_pengajuan, nomor_surat, kategori, kepada, perihal, nama_pengaju, konseptor, file_arsip, ttd_status FROM surat WHERE kategori = ? ORDER BY tanggal_pengajuan ASC");
    mysqli_stmt_bind_param($stmt, 's', $kategori);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rowNumber = 2;
    while ($row = mysqli_fetch_assoc($result)) {
        $statusArsip = !empty($row['file_arsip']) ? 'Sudah Upload' : 'Belum Upload';
        $statusTTD = !empty($row['ttd_status']) ? $row['ttd_status'] : 'Belum Diisi';
        $sheet->setCellValue('A' . $rowNumber, $row['tanggal_pengajuan']);
        $sheet->setCellValue('B' . $rowNumber, $row['nomor_surat']);
        $sheet->setCellValue('C' . $rowNumber, $row['kategori']);
        $sheet->setCellValue('D' . $rowNumber, $row['kepada']);
        $sheet->setCellValue('E' . $rowNumber, $row['perihal']);
        $sheet->setCellValue('F' . $rowNumber, $row['nama_pengaju']);
        $sheet->setCellValue('G' . $rowNumber, $row['konseptor']);
        $sheet->setCellValue('H' . $rowNumber, $statusArsip);
        $sheet->setCellValue('I' . $rowNumber, $statusTTD);
        $rowNumber++;
    }
    $sheet->getStyle('A1:I1')->getFont()->setBold(true);
    foreach (range('A', 'I') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    $sheetIndex++;
}

$spreadsheet->setActiveSheetIndex(0);
$filename = "laporan_bon_nomor_" . date('Y-m') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>