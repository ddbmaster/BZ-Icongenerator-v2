<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>BZ Icon Generator</title>
    <link rel="stylesheet" href="bz-icon-generator.css">  
</head>
<body>

<h1>BZ Icon Generator</h1>

<div class="wrapper">

    <div class="controls">
        <label>Icon Größe auswählen:</label>
<select id="sizeSelect">
    <option value="100">100 px</option>
    <option value="150" selected>150 px</option>
    <option value="200">200 px</option>
    <option value="250">250 px</option>
    <option value="300">300 px</option>
    <option value="350">350 px</option>
    <option value="400">400 px</option>
    <option value="450">450 px</option>
    <option value="500">500 px</option>
</select>


        <label>Icon hochladen:Aber bitte Transparent</label>
        <input type="file" id="iconInput" accept="image/*">

        <label>Icon Skalierung: <span id="scaleValue">1.00x</span></label>
        <input type="range" id="iconScale" min="0.2" max="3" step="0.01" value="1">

        <label>BZ Vorlagen“auswählen”:</label>
        <div id="templateList" style="display:flex; flex-wrap:wrap; gap:10px;"></div>

        <button id="downloadBtn" disabled>PNG herunterladen</button>
    </div>

   <div class="preview">
    <canvas id="iconCanvas" width="512" height="512"></canvas>
    <div style="text-align:center; margin-top:8px; font-size:0.8rem; color:#0371a4;">
        Icon bewegen: Klick & Ziehen • Größe ändern: Slider
    </div>
</div>


</div>

<script>
    const sizeRange = document.getElementById("sizeRange");
    const sizeValue = document.getElementById("sizeValue");
    const iconInput = document.getElementById("iconInput");
    const iconScaleSlider = document.getElementById("iconScale");
    const scaleValue = document.getElementById("scaleValue");
    const downloadBtn = document.getElementById("downloadBtn");

    const canvas = document.getElementById("iconCanvas");
    const ctx = canvas.getContext("2d");

    const templateImg = new Image();
    const iconImg = new Image();

    let templateLoaded = false;
    let iconLoaded = false;

    let iconX = 0;
    let iconY = 0;
    let autoCenter = true; // Icon immer mittig
    let isDragging = false;
    let dragOffsetX = 0;
    let dragOffsetY = 0;
    let iconScale = 1;

    function resizeCanvas(size) {
        canvas.width = size;
        canvas.height = size;
        redraw();
    }

    function redraw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if (templateLoaded)
            ctx.drawImage(templateImg, 0, 0, canvas.width, canvas.height);

        if (iconLoaded) {
            const baseSize = Math.min(iconImg.width, iconImg.height);
            const size = baseSize * iconScale * (canvas.width / 512);
            ctx.drawImage(
                iconImg,
                (iconImg.width - baseSize) / 2,
                (iconImg.height - baseSize) / 2,
                baseSize,
                baseSize,
                iconX,
                iconY,
                size,
                size
            );
        }

        downloadBtn.disabled = !(templateLoaded || iconLoaded);
    }

   const sizeSelect = document.getElementById("sizeSelect");
sizeSelect.addEventListener("change", () => {
    const size = parseInt(sizeSelect.value);
    resizeCanvas(size);
});


    iconInput.addEventListener("change", () => {
        const file = iconInput.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = e => {
            iconImg.onload = () => {
                iconLoaded = true;
                iconX = canvas.width / 4;
                iconY = canvas.width / 4;
                redraw();
            };
            iconImg.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    iconScaleSlider.addEventListener("input", () => {
        iconScale = parseFloat(iconScaleSlider.value);
        scaleValue.textContent = iconScale.toFixed(2) + "x";
        redraw();
    });

    canvas.addEventListener("mousedown", e => {
        if (!iconLoaded) return;

        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const baseSize = Math.min(iconImg.width, iconImg.height);
        const size = baseSize * iconScale * (canvas.width / 512);

        if (x >= iconX && x <= iconX + size && y >= iconY && y <= iconY + size) {
            isDragging = true;
            dragOffsetX = x - iconX;
            dragOffsetY = y - iconY;
        }
    });

    window.addEventListener("mousemove", e => {
        if (!isDragging) return;

        const rect = canvas.getBoundingClientRect();
        iconX = e.clientX - rect.left - dragOffsetX;
        iconY = e.clientY - rect.top - dragOffsetY;

        redraw();
    });

    window.addEventListener("mouseup", () => {
        isDragging = false;
    });

    downloadBtn.addEventListener("click", () => {
        canvas.toBlob(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `icon-${canvas.width}px.png`;
            a.click();
            URL.revokeObjectURL(url);
        });
    });

    function loadTemplates() {
        const list = document.getElementById("templateList");

        const templates = [
            "vorlagen/bz.png",
            "vorlagen/bzpro.png",
            "vorlagen/bzprofemale.png"
        ];

        templates.forEach(path => {
            const img = document.createElement("img");
            img.src = path;

            img.addEventListener("click", () => {
                templateImg.onload = () => {
                    templateLoaded = true;
                    redraw();
                };
                templateImg.src = path;
            });

            list.appendChild(img);
        });
    }

    loadTemplates();

    resizeCanvas(512);
</script>

</body>
</html>
