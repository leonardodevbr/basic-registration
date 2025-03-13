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
    const selfieInput = document.getElementById("selfie");
    let selfiePreview = document.getElementById("selfie-preview");
    let errorMessage = document.getElementById("error-message");

    let isFlipped = false;
    let camera;
    let faceMesh;
    let facesDetected = 0;
    let originalSelfie = selfieInput.value;
    let faceBox = null; // Guarda as coordenadas do rosto

    async function setupFaceDetection() {
        faceMesh = new FaceMesh({
            locateFile: (file) =>
                `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`,
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
                const faceLandmarks = results.multiFaceLandmarks[0];

                // Pegando os pontos extremos do rosto (superior, inferior, esquerdo, direito)
                let minX = Math.min(...faceLandmarks.map((p) => p.x));
                let maxX = Math.max(...faceLandmarks.map((p) => p.x));
                let minY = Math.min(...faceLandmarks.map((p) => p.y));
                let maxY = Math.max(...faceLandmarks.map((p) => p.y));

                // Convertendo de proporção para coordenadas de tela
                minX *= canvasElement.width;
                maxX *= canvasElement.width;
                minY *= canvasElement.height;
                maxY *= canvasElement.height;

                // **CORREÇÃO PARA QUANDO A CÂMERA ESTIVER INVERTIDA**
                if (isFlipped) {
                    const flippedMinX = canvasElement.width - maxX;
                    const flippedMaxX = canvasElement.width - minX;
                    minX = flippedMinX;
                    maxX = flippedMaxX;
                }

                faceBox = { minX, maxX, minY, maxY };
            } else {
                faceBox = null;
            }
        });
    }

    function drawFaceGuide() {
        if (!canvasElement) return;
        const ctx = canvasElement.getContext("2d");
        ctx.clearRect(0, 0, canvasElement.width, canvasElement.height);

        // Desenha as linhas de orientação para centralização
        ctx.strokeStyle = "#00FF00";
        ctx.lineWidth = 1;
        ctx.beginPath();

        // Linha vertical central
        ctx.moveTo(canvasElement.width / 2, 0);
        ctx.lineTo(canvasElement.width / 2, canvasElement.height);

        // Linha horizontal central
        ctx.moveTo(0, canvasElement.height / 2);
        ctx.lineTo(canvasElement.width, canvasElement.height / 2);

        ctx.stroke();

        // Se houver rosto detectado, desenha um retângulo ao redor
        if (faceBox) {
            const { minX, maxX, minY, maxY } = faceBox;

            // Verifica se o rosto está centralizado
            const faceCenterX = (minX + maxX) / 2;
            const faceCenterY = (minY + maxY) / 2;
            const canvasCenterX = canvasElement.width / 2;
            const canvasCenterY = canvasElement.height / 2;

            const offsetX = Math.abs(faceCenterX - canvasCenterX);
            const offsetY = Math.abs(faceCenterY - canvasCenterY);

            // Se o rosto estiver bem centralizado (dentro de uma margem de erro)
            const isCentered = offsetX < 50 && offsetY < 50;

            ctx.strokeStyle = isCentered ? "#00FF00" : "#FF0000"; // Verde = OK, Vermelho = Ajuste necessário
            ctx.lineWidth = 2;
            ctx.strokeRect(minX, minY, maxX - minX, maxY - minY);
        }

        requestAnimationFrame(drawFaceGuide);
    }

    function startCamera() {
        videoElement.style.display = "block";
        canvasElement.style.display = "block";

        if (!camera) {
            camera = new Camera(videoElement, {
                onFrame: async () => {
                    if (faceMesh) {
                        await faceMesh.send({ image: videoElement });
                    }
                },
                width: 500,
                height: 500,
            });
        }
        camera.start();
        drawFaceGuide(); // Começa a desenhar as guias no canvas
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
            data[i] = factor * (data[i] - 128) + 128 + brightness; // R
            data[i + 1] = factor * (data[i + 1] - 128) + 128 + brightness; // G
            data[i + 2] = factor * (data[i + 2] - 128) + 128 + brightness; // B
        }
        return imageData;
    }

    if (!selfieInput.value || selfieInput.value === "") {
        await setupFaceDetection();
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
        const videoContainer = document.querySelector(".video-container");

        if (videoElement.style.display === "none" || selfiePreview.style.display === "block") {
            // Ativa o vídeo e oculta a selfie
            selfiePreview.style.display = "none";
            videoContainer.style.display = "block"; // Mostra a video-container
            videoElement.style.display = "block";
            canvasElement.style.display = "block";

            startCamera(); // Reinicia a câmera corretamente
            captureButton.innerText = "Tirar Selfie";
            cancelButton.style.display = "block";
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

        // Captura a imagem do vídeo
        canvasCtx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

        if (isFlipped) {
            canvasCtx.setTransform(1, 0, 0, 1, 0, 0);
        }

        // Aplicar ajustes de brilho e contraste
        let imageData = canvasCtx.getImageData(0, 0, canvasElement.width, canvasElement.height);
        imageData = adjustBrightnessContrast(imageData, 15, 20); // Ajuste de iluminação
        canvasCtx.putImageData(imageData, 0, 0);

        const imageDataUrl = canvasElement.toDataURL("image/png");
        selfieInput.value = imageDataUrl;
        showSelfie(imageDataUrl);

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
        hideError();
    });
});
