<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>YOLO Detection Test</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #222;
        }

        header {
            background: #1f2937;
            color: white;
            padding: 20px 30px;
        }

        header h1 {
            margin: 0 0 5px;
        }

        header p {
            margin: 0;
            color: #cbd5e1;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        .upload-area {
            border: 2px dashed #94a3b8;
            border-radius: 10px;
            padding: 35px;
            text-align: center;
        }

        input[type="file"],
        input[type="text"] {
            margin: 15px 0;
        }

        input[type="text"] {
            width: 100%;
            max-width: 500px;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #1d4ed8;
        }

        button:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        .camera-button {
            background: #16a34a;
            margin-top: 5px;
        }

        .camera-button:hover {
            background: #15803d;
        }

        .status {
            margin-top: 15px;
            padding: 12px;
            border-radius: 8px;
            background: #f1f5f9;
        }

        .result-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .image-container {
            position: relative;
            background: #111827;
            border-radius: 10px;
            overflow: hidden;
            min-height: 300px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /*
     * GAMBAR HASIL YOLO
     *
     * Bounding box sudah digambar oleh backend.
     * Jadi tidak membutuhkan canvas.
     */

        #resultImage {
            max-width: 100%;
            height: auto;
            display: none;
        }

        .image-placeholder {
            color: #94a3b8;
            text-align: center;
            padding: 40px;
        }

        .detection-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .detection-item {
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .empty {
            color: #64748b;
            text-align: center;
            padding: 30px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .stat {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .stat strong {
            display: block;
            font-size: 24px;
            color: #2563eb;
        }

        @media (max-width: 800px) {
            .result-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
    ```

</head>

<body>


    <header>
        <h1>YOLO Object Detection</h1>
        <p>Website → FastAPI → YOLO → Bounding Box</p>
    </header>


    <div class="container">

        <!-- ==========================================
         UPLOAD
    =========================================== -->

        <div class="card">

            <h2>Upload Gambar</h2>

            <div class="upload-area">

                <p>
                    Pilih gambar secara manual atau ambil foto menggunakan kamera HP.
                </p>


                <!-- ==========================================
                 UPLOAD MANUAL
            =========================================== -->

                <label for="imageInput">
                    <strong>Upload Manual</strong>
                </label>

                <br>

                <input
                    type="file"
                    id="imageInput"
                    accept="image/*">


                <br>
                <br>


                <!-- ==========================================
                 KAMERA
            =========================================== -->

                <label
                    for="cameraInput"
                    class="camera-button" 
                    
                    style="
        display: inline-block;
        background: #16a34a;
        color: white;
        padding: 12px 22px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 15px;
    ">
                    📷 Ambil Foto
                </label>

                <input
                    type="file"
                    id="cameraInput"
                    accept="image/*"
                    capture="environment"
                    style="
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    ">


                <br>
                <br>


                <!-- ==========================================
                 ALAMAT FASTAPI
            =========================================== -->

                <label for="alamatIP">
                    Alamat FastAPI
                </label>

                <br>

                <input
                    type="text"
                    id="alamatIP"
                    placeholder="http://localhost:8765"
                    value="http://localhost:8765">


                <br>
                <br>


                <!-- ==========================================
                 DETEKSI
            =========================================== -->

                <button
                    type="button"
                    id="detectButton"
                    onclick="detectImage()">
                    Deteksi dengan YOLO
                </button>


                <div
                    class="status"
                    id="status">
                    Status: Menunggu gambar...
                </div>

            </div>

        </div>


        <!-- ==========================================
         HASIL
    =========================================== -->

        <div class="card">

            <h2>Hasil Deteksi</h2>

            <div class="result-layout">


                <!-- ==================================
                 IMAGE HASIL YOLO
            =================================== -->

                <div>

                    <div class="image-container">

                        <div
                            id="imagePlaceholder"
                            class="image-placeholder">
                            Belum ada hasil deteksi.
                        </div>


                        <!--
                        Backend mengirim:

                        result.image

                        yang sudah berisi bounding box.
                    -->

                        <img
                            id="resultImage"
                            alt="Hasil YOLO">

                    </div>


                    <!-- ==================================
                     STATISTIK
                =================================== -->

                    <div class="stats">

                        <div class="stat">

                            <strong id="objectCount">
                                0
                            </strong>

                            Object

                        </div>


                        <div class="stat">

                            <strong id="imageWidth">
                                -
                            </strong>

                            Width

                        </div>


                        <div class="stat">

                            <strong id="imageHeight">
                                -
                            </strong>

                            Height

                        </div>

                    </div>

                </div>


                <!-- ==================================
                 DETECTION LIST
            =================================== -->

                <div>

                    <h3>
                        Objects
                    </h3>

                    <div
                        id="detectionList"
                        class="detection-list">

                        <div class="empty">
                            Belum ada hasil deteksi.
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <script>
        // =====================================================
        // ELEMENT
        // =====================================================

        const imageInput =
            document.getElementById("imageInput");

        const cameraInput =
            document.getElementById("cameraInput");

        const resultImage =
            document.getElementById("resultImage");

        const imagePlaceholder =
            document.getElementById("imagePlaceholder");

        const status =
            document.getElementById("status");

        const detectionList =
            document.getElementById("detectionList");

        const objectCount =
            document.getElementById("objectCount");

        const imageWidth =
            document.getElementById("imageWidth");

        const imageHeight =
            document.getElementById("imageHeight");

        const detectButton =
            document.getElementById("detectButton");

        const alamatIP =
            document.getElementById("alamatIP");


        // =====================================================
        // BUKA KAMERA
        // =====================================================



        // =====================================================
        // HASIL FOTO DARI KAMERA
        // =====================================================

        cameraInput.addEventListener(
            "change",
            function() {

                const file =
                    this.files[0];

                if (!file) {
                    return;
                }


                // ---------------------------------------------
                // Masukkan foto kamera ke imageInput
                // ---------------------------------------------

                const dataTransfer =
                    new DataTransfer();

                dataTransfer.items.add(file);

                imageInput.files =
                    dataTransfer.files;


                // ---------------------------------------------
                // Tampilkan preview
                // ---------------------------------------------

                const imageURL =
                    URL.createObjectURL(file);

                resultImage.src =
                    imageURL;

                resultImage.style.display =
                    "block";

                imagePlaceholder.style.display =
                    "none";


                // ---------------------------------------------
                // Ukuran gambar
                // ---------------------------------------------

                resultImage.onload =
                    function() {

                        imageWidth.textContent =
                            resultImage.naturalWidth;

                        imageHeight.textContent =
                            resultImage.naturalHeight;

                    };


                // ---------------------------------------------
                // Status
                // ---------------------------------------------

                status.textContent =
                    "Status: Foto dari kamera siap dikirim.";

            }
        );


        // =====================================================
        // PREVIEW UPLOAD MANUAL
        // =====================================================

        imageInput.addEventListener(
            "change",
            function() {

                const file =
                    this.files[0];

                if (!file) {
                    return;
                }


                // ---------------------------------------------
                // Preview gambar
                // ---------------------------------------------

                const imageURL =
                    URL.createObjectURL(file);

                resultImage.src =
                    imageURL;

                resultImage.style.display =
                    "block";

                imagePlaceholder.style.display =
                    "none";


                // ---------------------------------------------
                // Ukuran gambar
                // ---------------------------------------------

                resultImage.onload =
                    function() {

                        imageWidth.textContent =
                            resultImage.naturalWidth;

                        imageHeight.textContent =
                            resultImage.naturalHeight;

                    };


                // ---------------------------------------------
                // Status
                // ---------------------------------------------

                status.textContent =
                    "Status: Gambar siap dikirim.";

            }
        );


        // =====================================================
        // DETEKSI GAMBAR
        // =====================================================

        async function detectImage() {

            const file =
                imageInput.files[0];


            // -----------------------------------------------
            // Cek file
            // -----------------------------------------------

            if (!file) {

                alert(
                    "Silakan pilih gambar terlebih dahulu."
                );

                return;

            }


            // -----------------------------------------------
            // Ambil alamat FastAPI
            // -----------------------------------------------

            let API_URL =
                alamatIP.value.trim();


            if (!API_URL) {

                alert(
                    "Masukkan alamat FastAPI terlebih dahulu."
                );

                return;

            }


            // -----------------------------------------------
            // Tambahkan http:// jika belum ada
            // -----------------------------------------------

            if (
                !API_URL.startsWith("http://") &&
                !API_URL.startsWith("https://")
            ) {

                API_URL =
                    "http://" + API_URL;

            }


            // -----------------------------------------------
            // Hilangkan / di akhir
            // -----------------------------------------------

            API_URL =
                API_URL.replace(/\/+$/, "");


            // -----------------------------------------------
            // UI
            // -----------------------------------------------

            detectButton.disabled =
                true;

            status.textContent =
                "Status: Mengirim gambar ke FastAPI...";


            // -----------------------------------------------
            // FormData
            // -----------------------------------------------

            const formData =
                new FormData();

            formData.append(
                "file",
                file
            );


            try {

                // ==========================================
                // POST /upload
                // ==========================================

                const response =
                    await fetch(
                        API_URL + "/upload", {
                            method: "POST",
                            body: formData
                        }
                    );


                // ==========================================
                // HTTP ERROR
                // ==========================================

                if (!response.ok) {

                    throw new Error(
                        "HTTP Error: " +
                        response.status
                    );

                }


                // ==========================================
                // JSON RESPONSE
                // ==========================================

                const result =
                    await response.json();


                console.log(
                    "Response FastAPI:",
                    result
                );


                // ==========================================
                // CEK STATUS YOLO
                // ==========================================

                if (
                    result.status !== "success"
                ) {

                    throw new Error(
                        result.message ||
                        "Deteksi gagal"
                    );

                }


                // ==========================================
                // TAMPILKAN HASIL
                // ==========================================

                displayResults(
                    result
                );


                status.textContent =
                    "Status: Deteksi berhasil!";


            } catch (error) {

                console.error(
                    "ERROR DETAIL:",
                    error
                );

                status.textContent =
                    "Error: " +
                    error.message;

                alert(
                    "Error dari browser:\n\n" +
                    error.message
                );

            } finally {

                detectButton.disabled =
                    false;

            }

        }


        // =====================================================
        // TAMPILKAN HASIL DETEKSI
        // =====================================================

        function displayResults(result) {

            const detections =
                result.detections || [];


            // ==========================================
            // GAMBAR HASIL BOUNDING BOX
            // ==========================================

            if (result.image) {

                resultImage.src =
                    result.image;

                resultImage.style.display =
                    "block";

                imagePlaceholder.style.display =
                    "none";

            }


            // ==========================================
            // JUMLAH OBJECT
            // ==========================================

            objectCount.textContent =
                detections.length;


            // ==========================================
            // UKURAN FRAME
            // ==========================================

            imageWidth.textContent =
                result.frame_width || "-";

            imageHeight.textContent =
                result.frame_height || "-";


            // ==========================================
            // DETECTION LIST
            // ==========================================

            detectionList.innerHTML =
                "";


            // ==========================================
            // TIDAK ADA OBJECT
            // ==========================================

            if (detections.length === 0) {

                detectionList.innerHTML = `
                <div class="empty">
                    Tidak ada object terdeteksi.
                </div>
            `;

                return;

            }


            // ==========================================
            // LOOP DETECTION
            // ==========================================

            detections.forEach(
                (detection, index) => {

                    const label =
                        detection.label;

                    const confidence =
                        detection.confidence;

                    const box =
                        detection.box;


                    // ----------------------------------
                    // Detection item
                    // ----------------------------------

                    const item =
                        document.createElement(
                            "div"
                        );

                    item.className =
                        "detection-item";


                    item.innerHTML = `
                    <strong>
                        ${index + 1}.
                        ${label}
                    </strong>

                    <br>

                    Confidence:
                    ${(confidence * 100).toFixed(0)}%

                    <br>

                    Box:
                    [${box.join(", ")}]
                `;


                    detectionList.appendChild(
                        item
                    );

                }
            );

        }
    </script>

</body>

</html>
