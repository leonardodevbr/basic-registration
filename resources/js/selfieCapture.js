import { FaceMesh } from "@mediapipe/face_mesh";
import { Camera } from "@mediapipe/camera_utils";

document.addEventListener("DOMContentLoaded", async () => {
    const videoElement = document.getElementById("video");
    const canvasElement = document.getElementById("canvas");
    const captureButton = document.getElementById("capture-btn");
    const selfieInput = document.getElementById("selfie");

    const canvasCtx = canvasElement.getContext("2d");

    const faceMesh = new FaceMesh({
        locateFile: (file) =>
            `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`,
    });

    faceMesh.setOptions({
        maxNumFaces: 1,
        refineLandmarks: true,
        minDetectionConfidence: 0.5,
        minTrackingConfidence: 0.5,
    });

    faceMesh.onResults((results) => {
        canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);

        if (results.multiFaceLandmarks.length > 0) {
            const face = results.multiFaceLandmarks[0];

            // Desenha o vídeo no canvas
            canvasCtx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

            // Desenha um retângulo ao redor do rosto detectado
            canvasCtx.strokeStyle = "blue";
            canvasCtx.lineWidth = 2;
            const minX = Math.min(...face.map((p) => p.x)) * canvasElement.width;
            const minY = Math.min(...face.map((p) => p.y)) * canvasElement.height;
            const maxX = Math.max(...face.map((p) => p.x)) * canvasElement.width;
            const maxY = Math.max(...face.map((p) => p.y)) * canvasElement.height;
            canvasCtx.strokeRect(minX, minY, maxX - minX, maxY - minY);
        }
    });

    const camera = new Camera(videoElement, {
        onFrame: async () => {
            await faceMesh.send({ image: videoElement });
        },
        width: 640,
        height: 480,
    });

    camera.start();

    captureButton.addEventListener("click", () => {
        // Limpa o canvas antes de capturar
        canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);

        // Desenha apenas o vídeo sem os retângulos
        canvasCtx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

        // Converter a selfie para base64
        const imageData = canvasElement.toDataURL("image/png");
        selfieInput.value = imageData;

        captureButton.innerText = "Selfie Capturada!";
        captureButton.disabled = true;
    });
});
