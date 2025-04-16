<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plate & Weight Truck Scan</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tesseract.js/4.0.2/tesseract.min.js"></script>
    <style>
        * {
            border: none;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body>
    <div class="container" style="height: 100svh; width: 100%; padding: 50px;">
        <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
            <div style="display: flex; flex-direction: column; justify-content: center; gap: 40px 0">
                <h2 style="text-align: center;">Plate Number Scan</h2>
                <div style="display: flex; flex-direction: column; gap: 5px 0;">
                    <div class="result-container" style="background-color: #3E3F5B; color: white; border-radius: 3px; padding: 10px;">
                        <video id="video" autoplay style="width: 400px;">Your browser not support this feature. Please update browser.</video>
                        <div>
                            <img style="width: 400px;" id="imagePreview">
                        </div>
                        <h3 style="margin-top: 10px; display: flex; gap: 0 5px;">Scan Result :
                            <pre id="outputText"></pre>
                        </h3>
                    </div>
                    <button id="scanImage" style="width: 100%; background-color: #3E3F5B; color: white; padding: 3px 8px; border-radius: 3px; font-size: 20px; cursor: pointer;">Scan</button>
                </div>
            </div>
        </div>
        <!-- <button id="cameraButton">Gunakan Kamera</button> -->
        <!-- <button id="switchCamera" style="display: none;">Ganti Kamera</button> -->
    </div>

    <script async src="https://docs.opencv.org/4.5.1/opencv.js"></script>

    <script>
       document.addEventListener("DOMContentLoaded", function () {
    console.log("Script loaded!");
  
    // const switchButton = document.getElementById("switchCamera");
    const scanButton = document.getElementById("scanImage");
    // const deleteButton = document.getElementById("deleteButton");
    const video = document.getElementById("video");
    const img = document.getElementById("imagePreview");
    const outputText = document.getElementById("outputText");
    // const progress = document.querySelector(".progress");

    let stream = null;
    let usingBackCamera = true;
    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");

    async function startCamera() {
        try {
            const constraints = {
                video: { facingMode: usingBackCamera ? "environment" : "user" }
            };

            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            video.autoplay = true;  // 🔥 Kamera otomatis menyala
            video.style.display = "block";
            scanButton.style.display = "block";
            // switchButton.style.display = "block";
        } catch (error) {
            console.error("Error membuka kamera:", error);
        }
    }

    // 📸 Panggil kamera otomatis saat halaman dimuat
    startCamera();

    // switchButton.addEventListener("click", async () => {
    //     if (stream) {
    //         stream.getTracks().forEach(track => track.stop());
    //     }
    //     usingBackCamera = !usingBackCamera;
    //     await startCamera();
    // });

    scanButton.addEventListener("click", async () => {
        if (!stream) {
            alert("Aktifkan kamera terlebih dahulu");
            return;
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageBase64 = canvas.toDataURL("image/png").split(",")[1];

        img.src = canvas.toDataURL("image/png");
        img.style.display = "block";
        video.style.display = "none";
        // deleteButton.style.display = "block";

        stream.getTracks().forEach(track => track.stop());
        video.srcObject = null;
        stream = null;
        // progress.style.display = "block";
        outputText.innerText = "Menganalisis teks...";

        // let progressValue = 0;
        // const progressInterval = setInterval(() => {
        //     if (progressValue < 95) {
        //         progressValue += 5;
        //         progress.innerText = `Progress: ${progressValue}%`;
        //     }
        // }, 500);

        try {
            const apiKey = "K84167834388957";
            const formData = new FormData();
            formData.append("apikey", apiKey);
            formData.append("base64Image", `data:image/png;base64,${imageBase64}`);
            formData.append("language", "eng");
            formData.append("isOverlayRequired", true);
            formData.append("OCREngine", 2);

            const response = await fetch("https://api.ocr.space/parse/image", {
                method: "POST",
                body: formData,
            });

            const data = await response.json();
            // clearInterval(progressInterval);
            // progress.innerText = "Progress: 100%";

            if (data.ParsedResults && data.ParsedResults.length > 0) {
                const parsedText = data.ParsedResults[0].ParsedText.trim();
                outputText.innerText = parsedText.length > 0 ? parsedText : "Teks tidak terdeteksi.";

                if (data.ParsedResults[0].TextOverlay && data.ParsedResults[0].TextOverlay.Lines) {
                    const words = data.ParsedResults[0].TextOverlay.Lines.flatMap(line =>
                        line.Words.map(word => ({
                            text: word.WordText,
                            bbox: {
                                x0: word.Left,
                                y0: word.Top,
                                x1: word.Left + word.Width,
                                y1: word.Top + word.Height
                            }
                        }))
                    );
                    drawBoundingBoxes(words);
                }
            } else {
                outputText.innerText = "Teks tidak terdeteksi.";
            }
        } catch (error) {
            clearInterval(progressInterval);
            console.error("OCR Error:", error);
            outputText.innerText = "Gagal membaca teks.";
            progress.innerText = "Gagal memproses OCR";
        }
    });

    // deleteButton.addEventListener("click", () => {
    //     if (stream) {
    //         stream.getTracks().forEach(track => track.stop());
    //         video.srcObject = null;
    //         stream = null;
    //     }
    //     video.style.display = "none";
    //     img.style.display = "none";
    //     outputText.innerText = "Hasil OCR akan muncul di sini...";
    //     progress.innerText = "Progress: 0%";
    //     scanButton.style.display = "none";
    //     deleteButton.style.display = "none";
    //     // switchButton.style.display = "none";`
    // });

    function drawBoundingBoxes(words) {
        const imgElement = document.createElement("img");
        imgElement.src = img.src;
        imgElement.onload = function () {
            canvas.width = imgElement.width;
            canvas.height = imgElement.height;
            ctx.drawImage(imgElement, 0, 0);

            ctx.strokeStyle = "red";
            ctx.lineWidth = 2;
            ctx.font = "14px Arial";
            ctx.fillStyle = "rgba(255, 255, 0, 0.7)";

            words.forEach((word) => {
                const { x0, y0, x1, y1 } = word.bbox;
                const padding = 5;

                ctx.strokeRect(x0 - padding, y0 - padding, (x1 - x0) + 2 * padding, (y1 - y0) + 2 * padding);
                ctx.fillRect(x0, y0 - 20, x1 - x0, 20);
                ctx.fillStyle = "black";
                ctx.fillText(word.text, x0 + 2, y0 - 5);
            });

            img.src = canvas.toDataURL("image/png");
        };
    }
});

    </script>
</body>

</html>