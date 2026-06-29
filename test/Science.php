<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image with Hover Info</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
            width: 100%;
            padding: 0 5%;
            position: relative;
        }
        .big-image {
            width: 50%;
            height: auto;
        }
        .overlay-text {
            position: absolute;
            font-size: 18px;
            font-weight: bold;
            color: black;
            text-transform: uppercase;
            pointer-events: auto;
            cursor: pointer;
            transition: transform 0.3s ease-in-out; /* Smooth movement */
        }
        .overlay-text:hover {
            transform: scale(1.2) translateY(-5px); /* Moves text up slightly */
        }
        .info-box {
            width: 30%;
            padding: 20px;
            border: 1px solid #ccc;
            background: #f9f9f9;
            display: none;
            position: absolute;
            right: 5%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out; /* Fade and slide */
        }
        .info-box.show {
            opacity: 1;
            transform: translateY(-50%) translateX(-10px); /* Moves slightly when appearing */
        }
        .info-box h2 {
            margin-top: 0;
            animation: fadeIn 0.5s ease-in-out; /* Title animation */
        }
        .info-box p {
            animation: fadeIn 0.7s ease-in-out; /* Description animation */
        }

        /* Keyframe animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Keeping your exact element positions */
        .text-oh1 { top: 55.5%; left: 8%; }
        .text-oh2 { top: 61.5%; left: 40.5%; }
        .text-oh3 { top: 72.3%; left: 31.98% }
        .text-oh4 { top: 69.5%; left: 19.8% }
        .text-ch2oh1 {  top: 27.88%; left: 11%; }
        .text-ch2oh2 { top: 55.8%; left: 44%; }
        .text-h1 { top: 39%; left: 12.5%; }
        .text-h2 { top: 69.5%; left: 12.5%; }
        .text-h3 { top: 58%; left: 20.25%; }
        .text-o1 { top: 31.8%; left: 20.4%; }
        .text-o2 { top: 46.5%; left: 27.1%; }
        .text-o3 { top: 69%; left: 41.65%; }
        .text-h4 { top: 43%; left: 24.35%; }
        .text-ch2oh3 { top: 43%; left: 28.5%; }
        .text-h5 { top: 42%; left: 44.10%; }
        .text-oh5 { top: 58%; left: 12%; }
        .text-o4 { top: 35%; left: 37%; }
        .text-h6 { top: 42%; left: 8.60%; }
    </style>
</head>
<body>
    <div class="container">
        <img src="science.png" alt="Molecule" class="big-image">
        
        <div class="overlay-text text-oh1" data-title="Hydroxide OH/HO" data-description="-It is an important but usually minor constituent of water. It functions as a base, a ligand, a nucleophile, and a catalyst.">OH</div>
        <div class="overlay-text text-oh2" data-title="Hydroxide OH/HO" data-description="-It is an important but usually minor constituent of water. It functions as a base, a ligand, a nucleophile, and a catalyst.">HO</div>
        <div class="overlay-text text-oh3" data-title="Hydroxide OH/HO" data-description="-It is an important but usually minor constituent of water. It functions as a base, a ligand, a nucleophile, and a catalyst.">OH</div>
        <div class="overlay-text text-oh4" data-title="Hydroxide OH/HO" data-description="-It is an important but usually minor constituent of water. It functions as a base, a ligand, a nucleophile, and a catalyst.">OH</div>
        <div class="overlay-text text-ch2oh1" data-title="Methanol CH2OH" data-description="-a toxic alcohol that is used industrially as a solvent, pesticide, and alternative fuel source.">CH2OH</div>
        <div class="overlay-text text-ch2oh2" data-title="Methanol CH2OH" data-description="-a toxic alcohol that is used industrially as a solvent, pesticide, and alternative fuel source.">CH2OH</div>
        <div class="overlay-text text-h1" data-title="Hydrogen H" data-description="-It is the lightest element and, at standard conditions, is a gas of diatomic molecules with the formula H₂, sometimes called dihydrogen, hydrogen gas, molecular hydrogen, or simply hydrogen.">H</div>
        <div class="overlay-text text-h2" data-title="Hydrogen H" data-description="-It is the lightest element and, at standard conditions, is a gas of diatomic molecules with the formula H₂, sometimes called dihydrogen, hydrogen gas, molecular hydrogen, or simply hydrogen.">H</div>
        <div class="overlay-text text-h3" data-title="Hydrogen H" data-description="-It is the lightest element and, at standard conditions, is a gas of diatomic molecules with the formula H₂, sometimes called dihydrogen, hydrogen gas, molecular hydrogen, or simply hydrogen.">H</div>
        <div class="overlay-text text-o1" data-title="Oxygen O" data-description="-Oxygen plays a critical role in respiration, the energy-producing chemistry that drives the metabolisms of most living things.">O</div>
        <div class="overlay-text text-o2" data-title="Oxygen O" data-description="-Oxygen plays a critical role in respiration, the energy-producing chemistry that drives the metabolisms of most living things.">O</div>
        <div class="overlay-text text-o3" data-title="Oxygen O" data-description="-Oxygen plays a critical role in respiration, the energy-producing chemistry that drives the metabolisms of most living things.">O</div>
        <div class="overlay-text text-h4" data-title="Hydrogen H" data-description="-It is the lightest element and, at standard conditions, is a gas of diatomic molecules with the formula H₂, sometimes called dihydrogen, hydrogen gas, molecular hydrogen, or simply hydrogen.">H</div>
        <div class="overlay-text text-ch2oh3" data-title="Methanol CH2OH" data-description="-a toxic alcohol that is used industrially as a solvent, pesticide, and alternative fuel source.">CH2OH</div>
        <div class="overlay-text text-h5" data-title="Hydrogen H" data-description="-It is the lightest element and, at standard conditions, is a gas of diatomic molecules with the formula H₂, sometimes called dihydrogen, hydrogen gas, molecular hydrogen, or simply hydrogen.">H</div>
        <div class="overlay-text text-oh5" data-title="Hydroxide OH/HO" data-description="-It is an important but usually minor constituent of water. It functions as a base, a ligand, a nucleophile, and a catalyst.">OH</div>
        <div class="overlay-text text-o4" data-title="Oxygen O" data-description="-Oxygen plays a critical role in respiration, the energy-producing chemistry that drives the metabolisms of most living things.">O</div>
        <div class="overlay-text text-h6" data-title="Hydrogen H" data-description="-It is the lightest element and, at standard conditions, is a gas of diatomic molecules with the formula H₂, sometimes called dihydrogen, hydrogen gas, molecular hydrogen, or simply hydrogen.">H</div>
    </div>
    
    <div class="info-box" id="infoBox">
        <h2 id="infoTitle">Title</h2>
        <p id="infoDescription">Description</p>
    </div>

    <script>
        const overlayTexts = document.querySelectorAll('.overlay-text');
        const infoBox = document.getElementById('infoBox');
        const infoTitle = document.getElementById('infoTitle');
        const infoDescription = document.getElementById('infoDescription');

        overlayTexts.forEach(text => {
            text.addEventListener('mouseenter', () => {
                infoTitle.textContent = text.getAttribute('data-title');
                infoDescription.textContent = text.getAttribute('data-description');
                infoBox.classList.add('show'); // Adds animation class
                infoBox.style.display = 'block';
            });

            text.addEventListener('mouseleave', () => {
                infoBox.classList.remove('show'); // Removes animation class
                setTimeout(() => {
                    infoBox.style.display = 'none';
                }, 400);
            });
        });
    </script>
</body>
</html>
