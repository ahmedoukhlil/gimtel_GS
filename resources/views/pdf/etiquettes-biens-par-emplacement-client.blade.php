<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étiquettes par Emplacement - {{ $emplacement->Emplacement ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        .header-info {
            font-size: 14px;
            color: #666;
        }
        .controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .btn {
            padding: 12px 24px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        .btn-primary {
            background: #4f46e5;
            color: white;
        }
        .btn-primary:hover {
            background: #4338ca;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .status {
            text-align: center;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            font-size: 14px;
        }
        .status.info {
            background: #dbeafe;
            color: #1e40af;
        }
        .status.success {
            background: #d1fae5;
            color: #065f46;
        }
        .preview {
            display: none;
            margin-top: 20px;
        }
        .preview.active {
            display: block;
        }
        #pdfContainer {
            width: 100%;
            height: 800px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4f46e5;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .breadcrumb {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .breadcrumb nav {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .breadcrumb a {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: #4f46e5;
            font-size: 14px;
            transition: color 0.2s;
        }
        .breadcrumb a:hover {
            color: #4338ca;
        }
        .breadcrumb svg {
            width: 16px;
            height: 16px;
            margin-right: 4px;
        }
        .breadcrumb span {
            color: #6b7280;
            font-size: 14px;
        }
        .breadcrumb .current {
            color: #374151;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Breadcrumb --}}
        <div class="breadcrumb">
            <nav aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    Dashboard
                </a>
                <span>/</span>
                <a href="{{ route('biens.index') }}">Immobilisations</a>
                <span>/</span>
                <span class="current">Impression des étiquettes</span>
            </nav>
        </div>

        <div class="header">
            <h1>Étiquettes - {{ $emplacement->Emplacement ?? 'N/A' }}</h1>
            <div class="header-info">
                <div>Total: {{ count($biensData) }} immobilisation(s)</div>
            </div>
        </div>

        <div class="controls">
            <button class="btn btn-primary" onclick="generatePDF()">
                Générer le PDF
            </button>
            <button class="btn btn-secondary" onclick="printPDF()" id="printBtn" style="display: none;">
                Imprimer
            </button>
            <button class="btn btn-secondary" onclick="window.close()">
                Fermer
            </button>
        </div>

        <div class="status info" id="status">
            Cliquez sur "Générer le PDF" pour créer les étiquettes (33 par page A4 - 3 colonnes × 11 lignes)
        </div>

        <div class="preview" id="preview">
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <div>Génération du PDF en cours...</div>
            </div>
            <iframe id="pdfContainer" style="display: none;"></iframe>
        </div>
    </div>

    {{-- Bibliothèques JavaScript --}}
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>

    <script>
        // Données des biens
        const biensData = @json($biensData);
        const emplacementName = @json($emplacement->Emplacement ?? 'Emplacement');

        // Dimensions A4 en points (1 point = 1/72 inch = 0.352778mm)
        // A4: 210mm × 297mm = 595.28 × 841.89 points
        const A4_WIDTH = 595.28;  // 210mm
        const A4_HEIGHT = 841.89; // 297mm
        
        // Marges en points (10mm = 28.35 points)
        const MARGIN = 28.35;
        
        // Zone utilisable
        const USABLE_WIDTH = A4_WIDTH - (MARGIN * 2);
        const USABLE_HEIGHT = A4_HEIGHT - (MARGIN * 2);
        
        // Grille 3 colonnes × 11 lignes = 33 étiquettes
        const COLS = 3;
        const ROWS = 11;
        const TOTAL_LABELS = COLS * ROWS; // 33
        
        // Espacement entre étiquettes en points (3mm = 8.5 points pour éviter les chevauchements)
        const GAP = 8.5;
        
        // Dimensions d'une étiquette (en tenant compte des espacements)
        const LABEL_WIDTH = (USABLE_WIDTH - (GAP * (COLS - 1))) / COLS;
        const LABEL_HEIGHT = (USABLE_HEIGHT - (GAP * (ROWS - 1))) / ROWS;
        
        // Taille fixe du code-barres (identique pour tous)
        const BARCODE_FIXED_WIDTH = LABEL_WIDTH - 8; // 8 points de marge de chaque côté
        const BARCODE_FIXED_HEIGHT = 35; // Hauteur fixe en points

        async function generatePDF() {
            const statusDiv = document.getElementById('status');
            const previewDiv = document.getElementById('preview');
            const loadingDiv = document.getElementById('loading');
            const pdfContainer = document.getElementById('pdfContainer');
            const printBtn = document.getElementById('printBtn');

            try {
                statusDiv.className = 'status info';
                statusDiv.textContent = 'Génération du PDF en cours...';
                previewDiv.classList.add('active');
                loadingDiv.style.display = 'block';
                pdfContainer.style.display = 'none';
                printBtn.style.display = 'none';

                // Créer un nouveau PDF
                const { PDFDocument } = PDFLib;
                const pdfDoc = await PDFDocument.create();

                // Charger la police une seule fois pour toutes les pages
                const font = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);
                const fontBold = await pdfDoc.embedFont(PDFLib.StandardFonts.HelveticaBold);

                // Calculer le nombre de pages nécessaires
                const totalPages = Math.ceil(biensData.length / TOTAL_LABELS);

                for (let pageIndex = 0; pageIndex < totalPages; pageIndex++) {
                    // Ajouter une nouvelle page
                    const page = pdfDoc.addPage([A4_WIDTH, A4_HEIGHT]);

                    // Calculer les biens pour cette page
                    const startIndex = pageIndex * TOTAL_LABELS;
                    const endIndex = Math.min(startIndex + TOTAL_LABELS, biensData.length);
                    const pageBiens = biensData.slice(startIndex, endIndex);

                    // Générer les codes-barres pour cette page
                    for (let i = 0; i < pageBiens.length; i++) {
                        const bien = pageBiens[i];
                        // Utiliser uniquement NumOrdre pour le code-barres Code 128
                        const barcodeValue = String(bien.barcode_value || bien.NumOrdre).trim();
                        // Le texte affiché est le code formaté complet
                        const displayText = String(bien.code_formate || bien.NumOrdre).trim();

                        if (!barcodeValue) continue;

                        // Calculer la position de l'étiquette (3 colonnes × 11 lignes)
                        const col = i % COLS;
                        const row = Math.floor(i / COLS);

                        // Position X: marge + (colonne × largeur) + (colonne × espacement)
                        const x = MARGIN + (col * (LABEL_WIDTH + GAP));
                        // Position Y: depuis le haut, donc A4_HEIGHT - marge - (ligne × hauteur) - (ligne × espacement) - hauteur
                        const y = A4_HEIGHT - MARGIN - ((row + 1) * LABEL_HEIGHT) - (row * GAP);

                        // Créer un canvas temporaire pour générer le code-barres
                        const canvas = document.createElement('canvas');
                        canvas.style.position = 'absolute';
                        canvas.style.left = '-9999px';
                        document.body.appendChild(canvas);

                        // Générer le code-barres avec jsbarcode
                        // Taille fixe pour tous les codes-barres
                        // Ajuster width pour que le code-barres s'adapte à BARCODE_FIXED_WIDTH
                        JsBarcode(canvas, barcodeValue, {
                            format: "CODE128",
                            width: 1.5, // Largeur des barres (sera redimensionné après)
                            height: BARCODE_FIXED_HEIGHT,
                            displayValue: false,
                            background: "#ffffff",
                            lineColor: "#000000",
                            margin: 0,
                            valid: function(valid) {
                                if (!valid) {
                                    console.warn('Code invalide pour Code 128:', barcodeValue);
                                }
                            }
                        });

                        // Attendre que le canvas soit prêt
                        await new Promise(resolve => setTimeout(resolve, 50));

                        // Convertir le canvas en image PNG
                        const barcodeDataUrl = canvas.toDataURL('image/png');
                        
                        // Charger l'image dans pdf-lib
                        const barcodeImage = await pdfDoc.embedPng(barcodeDataUrl);
                        
                        // Calculer le ratio d'aspect du code-barres généré
                        const barcodeAspectRatio = barcodeImage.width / barcodeImage.height;
                        
                        // Utiliser la taille fixe définie
                        // Ajuster la largeur pour respecter le ratio d'aspect
                        let finalBarcodeWidth = BARCODE_FIXED_WIDTH;
                        let finalBarcodeHeight = BARCODE_FIXED_WIDTH / barcodeAspectRatio;
                        
                        // Si la hauteur calculée dépasse la hauteur fixe, ajuster la largeur
                        if (finalBarcodeHeight > BARCODE_FIXED_HEIGHT) {
                            finalBarcodeHeight = BARCODE_FIXED_HEIGHT;
                            finalBarcodeWidth = BARCODE_FIXED_HEIGHT * barcodeAspectRatio;
                        }
                        
                        // Centrer le code-barres horizontalement et verticalement dans l'étiquette
                        // Le texte sera juste en dessous du code-barres
                        const textAreaHeight = 10; // Espace pour le texte sous le code-barres
                        const availableHeight = LABEL_HEIGHT - textAreaHeight - 4; // 4 points de padding en bas
                        
                        // Centrer verticalement le code-barres dans l'espace disponible
                        const barcodeX = x + (LABEL_WIDTH - finalBarcodeWidth) / 2;
                        // barcodeY est la position Y du bas du code-barres (dans pdf-lib, Y=0 est en bas)
                        const barcodeY = y + availableHeight - finalBarcodeHeight + 2; // 2 points de padding en haut

                        // Dessiner le code-barres
                        page.drawImage(barcodeImage, {
                            x: barcodeX,
                            y: barcodeY,
                            width: finalBarcodeWidth,
                            height: finalBarcodeHeight
                        });

                        // Ajouter le texte du code juste en dessous du code-barres
                        // Afficher le code formaté complet (pas seulement le NumOrdre)
                        const fontSize = 7;
                        page.setFont(font);
                        page.setFontSize(fontSize);
                        
                        // Positionner le texte juste sous le code-barres
                        // barcodeY est la position Y du bas du code-barres, donc le texte doit être en dessous
                        // Dans pdf-lib, Y=0 est en bas, donc on soustrait pour descendre
                        const textY = barcodeY - fontSize - 2; // 2 points d'espacement sous le code-barres
                        const textWidth = font.widthOfTextAtSize(displayText, fontSize);
                        const centeredTextX = x + (LABEL_WIDTH / 2) - (textWidth / 2);
                        
                        // Dessiner le texte centré (code formaté complet)
                        page.drawText(displayText, {
                            x: centeredTextX,
                            y: textY,
                            size: fontSize,
                            color: PDFLib.rgb(0, 0, 0)
                        });

                        // Nettoyer le canvas temporaire
                        document.body.removeChild(canvas);
                    }
                }

                // Générer le PDF
                const pdfBytes = await pdfDoc.save();
                const pdfBlob = new Blob([pdfBytes], { type: 'application/pdf' });
                const pdfUrl = URL.createObjectURL(pdfBlob);

                // Afficher le PDF dans l'iframe
                pdfContainer.src = pdfUrl;
                loadingDiv.style.display = 'none';
                pdfContainer.style.display = 'block';
                printBtn.style.display = 'inline-block';

                statusDiv.className = 'status success';
                statusDiv.textContent = `PDF généré avec succès ! ${totalPages} page(s) - ${biensData.length} étiquette(s)`;

                // Auto-impression après un court délai
                setTimeout(() => {
                    printPDF();
                }, 500);

            } catch (error) {
                console.error('Erreur lors de la génération du PDF:', error);
                statusDiv.className = 'status error';
                statusDiv.textContent = 'Erreur lors de la génération du PDF: ' + error.message;
                loadingDiv.style.display = 'none';
            }
        }

        // Fonction pour imprimer le PDF dans l'iframe
        function printPDF() {
            const pdfContainer = document.getElementById('pdfContainer');
            if (pdfContainer && pdfContainer.contentWindow) {
                try {
                    pdfContainer.contentWindow.print();
                } catch (error) {
                    console.error('Erreur lors de l\'impression du PDF:', error);
                    // Fallback: ouvrir le PDF dans une nouvelle fenêtre pour impression
                    const pdfUrl = pdfContainer.src;
                    if (pdfUrl) {
                        const printWindow = window.open(pdfUrl, '_blank');
                        if (printWindow) {
                            printWindow.onload = function() {
                                printWindow.print();
                            };
                        }
                    }
                }
            }
        }

        // Générer automatiquement au chargement de la page
        window.addEventListener('DOMContentLoaded', () => {
            generatePDF();
        });
    </script>
</body>
</html>
