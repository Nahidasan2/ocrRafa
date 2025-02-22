<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body{
            justify-items: center;
        }
        .button{
            text-align: right;
        }
    </style>
</head>
<body>
<div class="container mt-5">
        <h1>OCR</h1>
        <div class="card mt-2 p-3">
            <form id="uploadForm" enctype="multipart/form-data">
                <label class="form-label">Masukkan Foto:</label>
                <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" required>
                <button type="submit" class="btn btn-primary mt-3">Upload</button>
            </form>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <h3>Gambar Anda:</h3>
                <img id="uploadedImage" src="" class="img-fluid" style="max-width: 200px; display: none;">
            </div>
            <div class="col-md-6">
                <h3>Hasil :</h3>
                <p id="ocr" class="border p-2"></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function () {
            $("#uploadForm").on("submit", function (e) {
                e.preventDefault();

                var formData = new FormData(this);
                $.ajax({
                    url: "insert.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        try {
                            var result = JSON.parse(response);
                            if (result.status == "success") {
                                $("#uploadedImage").attr("src", result.imagePath).show();
                                $("#ocr").text(result.text);
                            } else {
                                alert(result.message);
                            }
                        } catch (e) {
                            alert("Error parsing JSON: " + response);
                        }
                    },
                    error: function (xhr, status, error) {
                        alert("Terjadi kesalahan: " + error);
                    }
                });
            });
        });
    </script>
</body>
</html>