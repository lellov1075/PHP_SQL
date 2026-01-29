<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAP Business One - Entrata Merci</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(45deg, #1e3c72, #2a5298);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .form-container {
            padding: 40px;
        }
        
        .auth-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #007bff;
        }
        
        .auth-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }
        
        .auth-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .document-section {
            margin-bottom: 30px;
        }
        
        .document-section h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 1.5rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }
        
        .doc-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .lines-section {
            background: #fff3cd;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #ffc107;
            margin-bottom: 30px;
        }
        
        .lines-section h2 {
            color: #856404;
            margin-bottom: 20px;
        }
        
        .lines-control {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .lines-control input {
            width: 100px;
            text-align: center;
        }
        
        .btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.4);
        }
        
        .items-container {
            margin-top: 20px;
        }
        
        .item-row {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .item-header {
            background: linear-gradient(45deg, #6c757d, #495057);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .item-fields {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .remove-btn:hover {
            background: #c82333;
        }
        
        .submit-section {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-top: 30px;
        }
        
        .submit-btn {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,123,255,0.3);
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,123,255,0.4);
        }
        
        @media (max-width: 768px) {
            .auth-row,
            .doc-row {
                grid-template-columns: 1fr;
            }
            
            .item-fields {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .form-container {
                padding: 20px;
            }
        }
        
        .required {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SAP Business One</h1>
            <p>Sistema di Gestione Originali V.Pazzi</p>
        </div>
        
        <div class="form-container">
            <form id="goodsReceiptForm" method="POST" action="carico_originale_pazzi_post.php">
                <!-- Sezione Autenticazione SAP -->
                <div class="auth-section">
                    <h2>🔐 Credenziali SAP</h2>
                    <div class="auth-row">
                        <div class="form-group">
                            <label for="username">Nome Utente <span class="required">*</span></label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <input type="password" id="password" name="password" required>
                        </div>
                    </div>
                </div>
                
                <!-- Sezione Documento -->
                <div class="document-section">
                    <h2>📄 Informazioni Documento</h2>
                    <div class="doc-row">
                        <div class="form-group">
                            <label for="fornitore">Fornitore (entrata merci)<span class="required">*</span></label>
                            <input type="text" id="fornitore" name="fornitore" placeholder="Codice fornitore" required>
                        </div>
                        <div class="form-group">
                            <label for="docNumber">Numero Documento Fornitore <span class="required">*</span></label>
                            <input type="text" id="docNumber" name="docNumber" placeholder="Es: DOC-2025-001" required>
                        </div>
                        <div class="form-group">
                            <label for="docDate">Data Documento Fornitore <span class="required">*</span></label>
                            <input type="date" id="docDate" name="docDate" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="osservazioni">Osservazioni</label>
                        <textarea id="osservazioni" name="osservazioni" placeholder="Inserisci eventuali note o osservazioni..."></textarea>
                    </div>
                    <div class="form-group">
                    <br>
                    <label for="cliente">Cliente per consegna</label>
                    <?php
                    include 'C:\xampp\htdocs\Raffaele\connections.php';
                    $string = 'SELECT DISTINCT t0."CardCode",t0."CardName",t1."Address" FROM U_GAUTO."OCRD" t0,U_GAUTO."CRD1" t1 WHERE t1."CardCode" =t0."CardCode" and t0."CardCode" in (\'C003125\',\'C001907\',\'C004541\') ORDER BY t0."CardName"';
                    $query = odbc_prepare($conn2,$string);
                    $success = odbc_execute($query);
                    echo '<select name="Cliente">';
                    while($row = odbc_fetch_array($query))
                    {
                        $codice = htmlspecialchars($row['CardCode']);
                        $descrizione = htmlspecialchars($row['CardName']);
                        $indirizzo = htmlspecialchars($row['Address']);
                        $value = $codice . '|' . $indirizzo;
                        echo "<option value=\"$value\">$codice - $descrizione - $indirizzo</option>";
                     }
                    echo '</select>';
                    ?>
                    </div>
                </div>
                
                <!-- Sezione Righe -->
                <div class="lines-section">
                    <h2>📦 Gestione Righe Documento</h2>
                    <div class="lines-control">
                        <label for="numLines">Numero di righe:</label>
                        <input type="number" id="numLines" min="1" max="20" value="1">
                        <button type="button" class="btn" onclick="generateLines()">Genera Righe</button>
                    </div>
                    <div id="itemsContainer" class="items-container">
                        <!-- Le righe verranno generate dinamicamente -->
                    </div>
                </div>
                
                <!-- Sezione Submit -->
                <div class="submit-section">
                    <button type="submit" class="submit-btn">🚀 Crea Entrata Merci & DDT</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Imposta la data di oggi come default
        document.getElementById('docDate').valueAsDate = new Date();
        
        // Genera una riga iniziale
        generateLines();
        
        function generateLines() {
            const numLines = parseInt(document.getElementById('numLines').value);
            const container = document.getElementById('itemsContainer');
            
            // Pulisci container
            container.innerHTML = '';
            
            for (let i = 1; i <= numLines; i++) {
                const itemRow = document.createElement('div');
                itemRow.className = 'item-row';
                itemRow.innerHTML = `
                    <div class="item-header">
                        📦 Riga ${i}
                    </div>
                    ${numLines > 1 ? `<button type="button" class="remove-btn" onclick="removeLine(this)" title="Rimuovi riga">×</button>` : ''}
                    <div class="item-fields">
                        <div class="form-group">
                            <label for="itemCode_${i}">Item Code <span class="required">*</span></label>
                            <input type="text" id="itemCode_${i}" name="itemCode_${i}" placeholder="Codice articolo" required value="AV-2">
                        </div>
                        <div class="form-group">
                            <label for="descrizione_${i}">Descrizione <span class="required">*</span></label>
                            <input type="text" id="descrizione_${i}" name="descrizione_${i}" placeholder="Descrizione articolo" required>
                        </div>
                        <div class="form-group">
                            <label for="quantita_${i}">Quantità <span class="required">*</span></label>
                            <input type="number" id="quantita_${i}" name="quantita_${i}" step="1" min="1" placeholder="1" required>
                        </div>
                        <div class="form-group">
                            <label for="prezzo_${i}">Prezzo Unitario (€) <span class="required">*</span></label>
                            <input type="number" id="prezzo_${i}" name="prezzo_${i}" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label for="sconto_${i}">Sconto (%) Acq</label>
                            <input type="number" id="sconto_${i}" name="sconto_${i}" step="0.01" min="0" max="100" placeholder="0.00" value="0">
                        </div>
                        <div class="form-group">
                            <label for="totale_${i}">Totale Riga (€)</label>
                            <input type="text" id="totale_${i}" name="totale_${i}" readonly style="background-color: #e9ecef; color: #6c757d;">
                        </div>
                         <div class="form-group">
                            <label for="scontov_${i}">Sconto 1 (%) Vend</label>
                            <input type="number" id="scontov_${i}" name="scontov_${i}" step="0.01" min="0" max="100" placeholder="0.00" value="0">
                        </div>
                         <div class="form-group">
                            <label for="scontov2_${i}">Sconto 2 (%) Vend</label>
                            <input type="number" id="scontov2_${i}" name="scontov2_${i}" step="0.01" min="0" max="100" placeholder="0.00" value="0">
                        </div>
                        <div class="form-group">
                            <label for="riferimento_${i}">Riferimento Pratica</label>
                            <input type="text" id="riferimento_${i}" name="riferimento_${i}">
                        </div>
                    </div>
                `;
                
                container.appendChild(itemRow);
                
                // Aggiungi event listeners per calcolo automatico
                const quantita = document.getElementById(`quantita_${i}`);
                const prezzo = document.getElementById(`prezzo_${i}`);
                const sconto = document.getElementById(`sconto_${i}`);
                const totale = document.getElementById(`totale_${i}`);
                
                function calcolaTotale() {
                    const qty = parseFloat(quantita.value) || 0;
                    const price = parseFloat(prezzo.value) || 0;
                    const discount = parseFloat(sconto.value) || 0;
                    
                    const subtotal = qty * price;
                    const discountAmount = subtotal * (discount / 100);
                    const total = subtotal - discountAmount;
                    
                    totale.value = total.toFixed(2);
                }
                
                quantita.addEventListener('input', calcolaTotale);
                prezzo.addEventListener('input', calcolaTotale);
                sconto.addEventListener('input', calcolaTotale);
            }
        }
        
        function removeLine(button) {
            const itemRow = button.closest('.item-row');
            itemRow.remove();
            
            // Rinumera le righe rimaste
            const rows = document.querySelectorAll('.item-row');
            rows.forEach((row, index) => {
                const header = row.querySelector('.item-header');
                header.textContent = `📦 Riga ${index + 1}`;
            });
            
            // Aggiorna il numero di righe
            document.getElementById('numLines').value = rows.length;
        }
    </script>
</body>
</html>