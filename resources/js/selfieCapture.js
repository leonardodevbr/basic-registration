import { Camera } from "@mediapipe/camera_utils";
import { FaceMesh } from "@mediapipe/face_mesh";

document.addEventListener("DOMContentLoaded", async () => {
    const formElement = document.getElementById("benefit-delivery-register-form");
    if (!formElement) return;

    const videoElement = document.getElementById("video");
    const canvasElement = document.getElementById("canvas");
    const captureButton = document.getElementById("capture-btn");
    const cancelButton = document.getElementById("cancel-btn");
    const flipButton = document.getElementById("flip-btn");
    const switchCameraButton = document.getElementById("switch-camera-btn"); // botão para trocar câmera
    const selfieInput = document.getElementById("selfie");
    let selfiePreview = document.getElementById("selfie-preview");
    let errorMessage = document.getElementById("error-message");
    let faceLandmarks = null;

    let isFlipped = false;
    let camera;
    let faceMesh;
    let facesDetected = 0;
    let originalSelfie = selfieInput.value;
    let faceBox = null; // Guarda as coordenadas do rosto


    // Variável para controlar o modo da câmera
    // Inicialmente, se for mobile, usamos a traseira, senão a frontal
    let currentFacingMode = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ? "environment" : "user";

    async function setupFaceDetection() {
        if (faceMesh) return; // Se já existe, não recria
        faceMesh = new FaceMesh({
            locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`,
        });

        faceMesh.setOptions({
            maxNumFaces: 1,
            refineLandmarks: true,
            minDetectionConfidence: 0.7,
            minTrackingConfidence: 0.7,
        });

        faceMesh.onResults((results) => {
            facesDetected = results.multiFaceLandmarks.length;

            if (facesDetected > 0) {
                faceLandmarks = results.multiFaceLandmarks[0];

                // Pegando os pontos extremos do rosto
                let minX = Math.min(...faceLandmarks.map((p) => p.x));
                let maxX = Math.max(...faceLandmarks.map((p) => p.x));
                let minY = Math.min(...faceLandmarks.map((p) => p.y));
                let maxY = Math.max(...faceLandmarks.map((p) => p.y));

                // Convertendo de proporção para coordenadas de tela
                minX *= canvasElement.width;
                maxX *= canvasElement.width;
                minY *= canvasElement.height;
                maxY *= canvasElement.height;

                if (isFlipped) {
                    const flippedMinX = canvasElement.width - maxX;
                    const flippedMaxX = canvasElement.width - minX;
                    minX = flippedMinX;
                    maxX = flippedMaxX;
                }

                faceBox = { minX, maxX, minY, maxY };
            } else {
                faceLandmarks = null;
                faceBox = null;
            }
        });
    }


    function drawFaceGuide() {
        if (!canvasElement || !videoElement || !captureButton) return;
        const ctx = canvasElement.getContext("2d");

        if (canvasElement.width !== videoElement.videoWidth || canvasElement.height !== videoElement.videoHeight) {
            canvasElement.width = videoElement.videoWidth;
            canvasElement.height = videoElement.videoHeight;
        }

        ctx.clearRect(0, 0, canvasElement.width, canvasElement.height);
        ctx.beginPath();

        ctx.lineWidth = 1;
        ctx.strokeStyle = "#00FF00";
        ctx.moveTo(canvasElement.width / 2, 0);
        ctx.lineTo(canvasElement.width / 2, canvasElement.height);
        ctx.moveTo(0, canvasElement.height / 2);
        ctx.lineTo(canvasElement.width, canvasElement.height / 2);
        ctx.stroke();

        let isCentered = false;
        let isFacingFront = false;

        if (faceBox && faceLandmarks) {
            const { minX, maxX, minY, maxY } = faceBox;
            const faceCenterX = (minX + maxX) / 2;
            const faceCenterY = (minY + maxY) / 2;
            const canvasCenterX = canvasElement.width / 2;
            const canvasCenterY = canvasElement.height / 2;

            const offsetX = Math.abs(faceCenterX - canvasCenterX);
            const offsetY = Math.abs(faceCenterY - canvasCenterY);
            isCentered = offsetX < 50 && offsetY < 50;

            const leftEye = faceLandmarks[33];
            const rightEye = faceLandmarks[263];
            const nose = faceLandmarks[1];
            const chin = faceLandmarks[152];

            const eyeDiffX = Math.abs(leftEye.x - rightEye.x);
            const noseDiffX = Math.abs(nose.x - (leftEye.x + rightEye.x) / 2);
            const faceTilt = Math.abs(nose.y - chin.y);

            isFacingFront = eyeDiffX > 0.15 && noseDiffX < 0.02 && faceTilt > 0.1;

            ctx.strokeStyle = isCentered && isFacingFront ? "#00FF00" : "#FF0000";
            ctx.lineWidth = 2;
            ctx.strokeRect(minX, minY, maxX - minX, maxY - minY);
        }

        const canCapture = isCentered && isFacingFront;
        captureButton.disabled = !canCapture;
        captureButton.style.opacity = canCapture ? "1" : "0.5";

        requestAnimationFrame(drawFaceGuide);
    }

    async function startCamera() {
        videoElement.style.display = "block";
        canvasElement.style.display = "block";

        // Se já houver uma câmera iniciada, interrompe-a
        if (camera) {
            camera.stop();
        }

        // Garante que só tente iniciar depois que o elemento estiver visível e renderizado
        await new Promise((resolve) => setTimeout(resolve, 100));

        camera = new Camera(videoElement, {
            onFrame: async () => {
                if (faceMesh) {
                    await faceMesh.send({ image: videoElement });
                }
            },
            width: 640,
            height: 640,
            facingMode: currentFacingMode,
        });

        camera.start();
        drawFaceGuide();
    }

    function stopCamera() {
        if (camera) {
            camera.stop();
        }
        videoElement.style.display = "none";
        canvasElement.style.display = "none";
    }

    function showSelfie(imageSrc) {
        selfiePreview.src = imageSrc;
        selfiePreview.style.display = "block";
        videoElement.style.display = "none";
        canvasElement.style.display = "none";
        // Oculta os botões que não fazem sentido quando a selfie está sendo exibida
        flipButton.style.display = "none";
        switchCameraButton.style.display = "none";
    }

    function showError(message) {
        if (errorMessage) {
            errorMessage.innerText = message;
            errorMessage.style.display = "block";
        }
    }

    function hideError() {
        if (errorMessage) {
            errorMessage.style.display = "none";
            errorMessage.innerText = "";
        }
    }

    function adjustBrightnessContrast(imageData, brightness = 10, contrast = 15) {
        const data = imageData.data;
        const factor = (259 * (contrast + 255)) / (255 * (259 - contrast));

        for (let i = 0; i < data.length; i += 4) {
            data[i] = factor * (data[i] - 128) + 128 + brightness;
            data[i + 1] = factor * (data[i + 1] - 128) + 128 + brightness;
            data[i + 2] = factor * (data[i + 2] - 128) + 128 + brightness;
        }
        return imageData;
    }

    if (!selfieInput.value || selfieInput.value === "") {
        await setupFaceDetection();
        startCamera();
        selfiePreview.style.display = "none";
        videoElement.style.display = "block";
        captureButton.innerText = "Tirar Selfie";
        cancelButton.style.display = "none";
    } else {
    selfiePreview.style.display = "block";
        videoElement.style.display = "none";
        // Mantemos o botão de captura com o mesmo texto ("Tirar Selfie") se for necessário,
        // mas exibimos o botão de cancelar com o novo nome "Refazer"
        captureButton.innerText = "Tirar Selfie";
        cancelButton.style.display = "block";
        cancelButton.innerText = "Refazer";
    }


    flipButton.addEventListener("click", () => {
        isFlipped = !isFlipped;
        videoElement.style.transform = isFlipped ? "scaleX(-1)" : "scaleX(1)";
    });

    // Evento para trocar a câmera
    switchCameraButton.addEventListener("click", () => {
        // Alterna entre "user" e "environment"
        currentFacingMode = currentFacingMode === "user" ? "environment" : "user";
        startCamera(); // Reinicia a câmera com o novo modo
    });

    captureButton.addEventListener("click", () => {
        const videoContainer = document.querySelector(".video-container");

        // Se a visualização da selfie estiver ativa, reativa o vídeo
        if (videoElement.style.display === "none" || selfiePreview.style.display === "block") {
            selfiePreview.style.display = "none";
            videoContainer.style.display = "block";
            videoElement.style.display = "block";
            canvasElement.style.display = "block";

            // Exibe os botões de flip e troca de câmera quando a câmera é iniciada
            flipButton.style.display = "block";
            switchCameraButton.style.display = "block";

            startCamera();
            captureButton.innerText = "Tirar Selfie";
            cancelButton.style.display = "block";
            cancelButton.innerText = "Refazer";
            hideError();
            setupFaceDetection();
            return;
        }

        if (facesDetected === 0) {
            showError("Nenhum rosto detectado! Tente novamente.");
            return;
        }

        hideError();

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

        let imageData = canvasCtx.getImageData(0, 0, canvasElement.width, canvasElement.height);
        imageData = adjustBrightnessContrast(imageData, 15, 20);
        canvasCtx.putImageData(imageData, 0, 0);

        const imageDataUrl = canvasElement.toDataURL("image/png");
        selfieInput.value = imageDataUrl;
        showSelfie(imageDataUrl);

        // Após capturar a selfie, mantemos o botão de captura com "Tirar Selfie"
        // e exibimos o botão de cancelar (que agora serve como "Refazer")
        captureButton.innerText = "Tirar Selfie";
        cancelButton.style.display = "block";
        cancelButton.innerText = "Refazer";
        stopCamera();

        // Oculta os botões de flip e troca de câmera após capturar a selfie
        flipButton.style.display = "none";
        switchCameraButton.style.display = "none";
    });

    cancelButton.addEventListener("click", () => {
        // Exibe o container do vídeo e remove a classe 'hidden' do botão de captura
        const videoContainer = document.querySelector(".video-container");
        if (videoContainer) {
            videoContainer.style.display = "block";
        }
        captureButton.classList.remove("hidden"); // Remove a classe que oculta o botão
        selfiePreview.style.display = "none";
        videoElement.style.display = "block";
        canvasElement.style.display = "block";
        flipButton.style.display = "block";
        switchCameraButton.style.display = "block";
        startCamera();
        setupFaceDetection();

        captureButton.innerText = "Tirar Selfie";
        cancelButton.style.display = "none";
        hideError();
    });

});
