import asyncio
import cv2
import numpy as np
import json
import base64
import time
from fastapi import FastAPI, WebSocket, WebSocketDisconnect, UploadFile, File
from ultralytics import YOLO
import uvicorn
from fastapi.middleware.cors import CORSMiddleware

app = FastAPI()
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:8088"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Inisialisasi Model YOLO (Hanya dimuat sekali)
print("[SYSTEM] Memuat model YOLO...")
model = YOLO("yolov8n.pt")
print("[SYSTEM] Model siap digunakan!")


# ==========================================
# 1. LOGIKA INTI (SHARED FUNCTION)
# Fungsi ini dipakai bersama oleh HTTP & WS
# ==========================================
def process_image_and_detect(img_bytes) -> dict:
    start_time = time.time()

    # Decode biner ke OpenCV
    np_arr = np.frombuffer(img_bytes, dtype=np.uint8)
    frame = cv2.imdecode(np_arr, cv2.IMREAD_COLOR)

    if frame is None:
        return {"status": "error", "message": "Gagal decode gambar"}

    height, width, _ = frame.shape

    # Jalankan YOLO
    yolo_start = time.time()
    results = model(frame, verbose=False)
    yolo_duration = (time.time() - yolo_start) * 1000

    # Ekstrak hasil
    detections = []
    detected_labels = []
    for result in results:
        for box in result.boxes:
            x1, y1, x2, y2 = box.xyxy[0].tolist()
            conf = float(box.conf[0])
            cls = int(box.cls[0])
            label = model.names[cls]

            detections.append(
                {
                    "label": label,
                    "confidence": round(conf, 2),
                    "box": [int(x1), int(y1), int(x2 - x1), int(y2 - y1)],
                }
            )
            detected_labels.append(f"{label} ({round(conf * 100)}%)")

    print(
        f"[YOLO] Terdeteksi: {', '.join(detected_labels) if detected_labels else 'Tidak ada'}"
    )
    print(
        f"[TIME] Inference: {yolo_duration:.1f} ms | Total: {(time.time() - start_time) * 1000:.1f} ms"
    )

    return {
        "status": "success",
        "frame_width": width,
        "frame_height": height,
        "detections": detections,
    }


# ==========================================
# 2. JALUR HTTP (POST /upload)
# ==========================================
@app.post("/upload")
async def upload_image(file: UploadFile = File(...)):
    print(f"\n--- [HTTP POST] Menerima gambar dari {file.filename} ---")
    img_bytes = await file.read()
    print(f"[RECV] Ukuran Payload: {len(img_bytes) / 1024:.2f} KB")

    # Panggil fungsi utama
    result = process_image_and_detect(img_bytes)
    return result


# ==========================================
# 3. JALUR WEBSOCKET (ws://)
# ==========================================
@app.websocket("/ws")
async def websocket_endpoint(websocket: WebSocket):
    await websocket.accept()
    client_ip = websocket.client.host
    print(f"\n[CONN] Client WS terhubung dari -> {client_ip}")

    frame_count = 0
    try:
        while True:
            # Menerima data (Bisa biner langsung atau JSON teks)
            message = await websocket.receive()
            frame_count += 1

            print(f"\n--- [WS FRAME #{frame_count}] Menerima Data ---")

            # Cek tipe data masuk
            if "bytes" in message:
                # Skenario 1: Biner Murni (Sangat Cepat & Efisien!)
                img_bytes = message["bytes"]
                print(f"[RECV] Tipe: BINARY | Ukuran: {len(img_bytes) / 1024:.2f} KB")
            else:
                # Skenario 2: JSON Base64 (Warisan kode lamamu)
                try:
                    data = json.loads(message["text"])
                    base64_data = data.get("image", "")
                    if "," in base64_data:
                        base64_data = base64_data.split(",")[1]
                    img_bytes = base64.b64decode(base64_data)
                    print(
                        f"[RECV] Tipe: JSON BASE64 | Ukuran: {len(img_bytes) / 1024:.2f} KB"
                    )
                except Exception as e:
                    await websocket.send_json(
                        {
                            "status": "error",
                            "message": f"Format JSON/Base64 tidak valid: {str(e)}",
                        }
                    )
                    continue

            # Panggil fungsi utama yang sama
            result = process_image_and_detect(img_bytes)

            # Kirim balik respons via WebSocket
            await websocket.send_json(result)

    except WebSocketDisconnect:
        print(f"[CONN] Client WS {client_ip} terputus secara normal.")
    except Exception as e:
        print(f"[CONN] WS Error: {e}")


if __name__ == "__main__":
    # Menjalankan server di port 8765
    uvicorn.run(app, host="0.0.0.0", port=8765)

