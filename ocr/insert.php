<?php
include_once('koneksi.php');

if(isset($_FILES['gambar'])){
    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    $uploadDir = 'gambar/';
    
    // Pastikan folder ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Tambahkan timestamp agar nama unik
    $filePath = $uploadDir . time() . "_" . $gambar;

    if(move_uploaded_file($tmp, $filePath)){
        // Simpan ke database
        $Quploud = "INSERT INTO datafoto (gambar) VALUES ('$filePath')";
        $Ruploud = mysqli_query($conn, $Quploud);

        // Jalankan OCR dengan Tesseract
        $outputText = shell_exec("tesseract " . escapeshellarg($filePath) . " stdout");

        $debugOutput = shell_exec("tesseract " . escapeshellarg($filePath) . " stdout 2>&1");
file_put_contents("debug.txt", $debugOutput);


        echo json_encode([
            "status" => "success",
            "imagePath" => $filePath,
            "text" => trim($outputText)
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal mengupload gambar."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Permintaan tidak valid."]);
}
?>
