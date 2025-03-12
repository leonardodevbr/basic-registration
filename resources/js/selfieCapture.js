import { Camera } from "@mediapipe/camera_utils";

document.addEventListener("DOMContentLoaded", async () => {
    const formElement = document.getElementById("person-register-form");
    if (!formElement) return;

    const videoElement = document.getElementById("video");
    const canvasElement = document.getElementById("canvas");
    const captureButton = document.getElementById("capture-btn");
    const cancelButton = document.getElementById("cancel-btn");
    const flipButton = document.getElementById("flip-btn");
    const selfieInput = document.getElementById("selfie");
    let selfiePreview = document.getElementById("selfie-preview");

    let isFlipped = false;
    let camera;
    let originalSelfie = selfieInput.value; // Armazena a selfie original (caso tenha)

    function startCamera() {
        videoElement.style.display = "block";
        if (!camera) {
            camera = new Camera(videoElement, {
                onFrame: async () => {},
                width: 500,
                height: 500,
            });
        }
        camera.start();
    }

    function stopCamera() {
        if (camera) {
            camera.stop();
        }
        videoElement.style.display = "none";
    }

    function showSelfie(imageSrc) {
        selfiePreview.src = imageSrc;
        selfiePreview.style.display = "block";
        videoElement.style.display = "none";
        canvasElement.style.display = "none";
    }

    // 🔥 **Correção para iniciar a câmera automaticamente se não houver selfie**
    if (!selfieInput.value || selfieInput.value === "") {
        startCamera();
        selfiePreview.style.display = "none";
        videoElement.style.display = "block";
        captureButton.innerText = "Tirar Selfie";
    } else {
        selfiePreview.style.display = "block";
        videoElement.style.display = "none";
        captureButton.innerText = "Capturar Novamente";
        cancelButton.style.display = "none";
    }

    flipButton.addEventListener("click", () => {
        isFlipped = !isFlipped;
        videoElement.style.transform = isFlipped ? "scaleX(-1)" : "scaleX(1)";
    });

    captureButton.addEventListener("click", () => {
        if (videoElement.style.display === "none") {
            // Se já houver uma imagem carregada e for para capturar novamente
            videoElement.style.display = "block";
            selfiePreview.style.display = "none";
            startCamera();
            captureButton.innerText = "Tirar Selfie";
            cancelButton.style.display = "block";
            return;
        }

        // Captura a imagem e exibe no lugar do vídeo
        canvasElement.width = videoElement.videoWidth;
        canvasElement.height = videoElement.videoHeight;
        const canvasCtx = canvasElement.getContext("2d");

        if (isFlipped) {
            canvasCtx.translate(canvasElement.width, 0);
            canvasCtx.scale(-1, 1);
        }

        canvasCtx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

        if (isFlipped) {
            canvasCtx.setTransform(1, 0, 0, 1, 0, 0);
        }

        const imageData = canvasElement.toDataURL("image/png");
        selfieInput.value = imageData;
        showSelfie(imageData);

        captureButton.innerText = "Capturar Novamente";
        cancelButton.style.display = "block";
        stopCamera();
    });

    cancelButton.addEventListener("click", () => {
        if (originalSelfie) {
            showSelfie(originalSelfie);
            selfieInput.value = originalSelfie;
        } else {
            videoElement.style.display = "block";
            selfiePreview.style.display = "none";
            startCamera();
        }
        captureButton.innerText = "Capturar Novamente";
        cancelButton.style.display = "none";
    });
});
