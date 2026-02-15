<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt with QR</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
            display: flex;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .receipt-section {
            flex: 1;
            padding: 35px;
            border-right: 2px dashed #e2e8f0;
            position: relative;
            background: linear-gradient(to bottom, #ffffff, #f8fafc);
        }

        .qr-section {
            width: 400px;
            padding: 35px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 2px solid #f1f5f9;
        }

        .receipt-header h1 {
            color: #205691;
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .receipt-header p {
            color: #205691;
            font-size: 14px;
            font-weight: 500;
        }

        .receipt-details {
            margin-bottom: 30px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 18px;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-label {
            color: #205691;
            font-size: 14px;
            font-weight: 500;
        }

        .detail-value {
            color: #1e293b;
            font-weight: 600;
            text-align: right;
        }

        .amount-section {
            background: linear-gradient(135deg, #eaa259 0%, #f5b16d 100%);
            padding: 25px;
            border-radius: 16px;
            margin: 25px 0;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.1);
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .amount-section:hover {
            transform: translateY(-5px);
        }

        .amount {
            font-size: 36px;
            font-weight: 700;
            color: white;
            text-align: center;
            margin: 10px 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .amount-section .detail-label {
            color: rgba(255, 255, 255, 0.9);
        }

        .qr-container {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.1);
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }

        .qr-container:hover {
            transform: scale(1.02);
        }

        #qr-code {
            padding: 15px;
            background: white;
            border-radius: 16px;
            margin-bottom: 15px;
        }

        .payment-apps {
            margin-top: 25px;
            text-align: center;
        }

        .apps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .app-icon {
            width: 65px;
            height: 65px;
            padding: 10px;
            background: white;
            border-radius: 12px;
            margin: 0 auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
        }

        .app-icon:hover {
            transform: scale(1.1);
        }

        .timer {
            margin-top: 25px;
            text-align: center;
            color: #205691;
            font-weight: 500;
            font-size: 14px;
            background: white;
            padding: 10px 20px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(99, 102, 241, 0.1);
        }

        .secure-badge {
            position: absolute;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 8px;
            color: #205691;
            font-size: 13px;
            background: white;
            padding: 8px 16px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(99, 102, 241, 0.1);
        }

        .instructions {
            text-align: center;
            color: #205691;
            font-size: 16px;
            font-weight: 500;
            margin-top: 15px;
            margin-bottom: 10px;
        }

      .instructions{
        font-size:18px

      }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                margin: 20px;
            }

            .receipt-section {
                border-right: none;
                border-bottom: 2px dashed #e2e8f0;
            }

            .qr-section {
                width: 100%;
            }

            .secure-badge {
                position: relative;
                margin-top: 25px;
                transform: none;
                left: auto;
                bottom: auto;
                justify-content: center;
            }
        }

        /* Add these new animation effects */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            animation: fadeIn 0.6s ease-out;
        }

        .amount-section,
        .qr-container {
            position: relative;
            overflow: hidden;
        }

        .amount-section::after,
        .qr-container::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .amount-section:hover::after,
        .qr-container:hover::after {
            opacity: 1;
        }
.circle-wrapper {
  position: relative;
  width: 70px;
  height: 70px;
  margin: 10px;
}

.iconCheck {
  position: absolute;
  color: #fff;
  font-size: 55px;
  top: 35px;
  left: 35px;
  transform: translate(-50%, -50%);
}
.iconCheck svg {
    width: 40px;
    height: 40px;
}

.circle {
  display: block;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  padding: 2.5px;
  background-clip: content-box;
  animation: spin 10s linear infinite;
}

.circle-wrapper:active .circle {
  animation: spin 2s linear infinite;
}

.success {
  background-color: #4BB543;
  border: 2.5px dashed #4BB543;
}
@keyframes spin {
  100% {
    transform: rotateZ(360deg);
  }
}
    </style>
</head>
<body>
    <div class="container">
        <div class="receipt-section">
            <div class="receipt-header">
                <div style="display: flex; align-items: center; justify-content: center;">
                    <div class="circle-wrapper">
                    <div class="success circle"></div>
                    <div class="iconCheck">
                    <x-heroicon-o-check class="icon" />
                    </div>
                </div>
                </div>
                <h1>Payment Receipt</h1>
                <p>Transaction Details</p>
            </div>

            <div class="receipt-details">
                <div class="detail-row">
                    <span class="detail-label">Date</span>
                    <span class="detail-value" id="payee-name">{{ $pembayaran[0]['tanggal_pembayaran'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payments ID</span>
                    <span class="detail-value" id="transaction-id">
                        {{ $pembayaran[0]['nomor_bayar'] }}
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Student Name</span>
                    <span class="detail-value" id="transaction-note">{{ $pembayaran[0]['siswa']['nama_siswa'] }}</span>
                </div>
            </div>
                @php
                    $totalPembayaran = 0;
                    foreach($pembayaran as $itemPembayaran) {
                        $totalPembayaran += $itemPembayaran['jumlah_dibayar'];
                    }
                @endphp
            <div class="amount-section">
                <div class="detail-label" style="text-align: center;">Total Amount</div>
                <div class="amount">Rp<span id="amount-display">{{ number_format($totalPembayaran, 0, ',', '.') }}</span></div>
            </div>

           <div class="instructions">
                Payment received! Thank's for your payments
            </div>
        </div>

        <div class="qr-section">
          <div class="instructions">
                Moslem Binus International School
            </div>
            <div class="qr-container">
                <img id="qr-code" src="{{ asset('storage/' . $pengaturan->logo_sekolah) }}" style="width: 100%; max-width: 250px;">
                {{-- <p style="color: #64748b; margin-top: 10px;">Scan with any UPI app</p> --}}
            </div>

            <div class="payment-apps">
                <div class="detail-label">Synergistic System</div>
                <div class="apps-grid">
                    <div class="app-icon">
                        <img src="https://mbis-batam.sch.id/wp-content/uploads/2025/10/Logo-Ummi-Transparent-06-2048x2048.png.webp" alt="Ummi" style="width: 100%;">
                    </div>
                    <div class="app-icon">
                        <img src="https://mbis-batam.sch.id/wp-content/uploads/2025/10/Logo-UM-CCID110-06-2048x1024.png.webp" alt="cambridge" style="width: 100%;">
                    </div>
                    <div class="app-icon">
                        <img src="https://mbis-batam.sch.id/wp-content/uploads/2023/12/Logo-Kurikulum-Merdeka-01-2048x2048.png.webp" alt="UPI" style="width: 100%;">
                    </div>
                </div>
            </div>


        </div>
    </div>

    {{-- <script>
        const upiId = "hello sir"; // Your UPI ID
        const payeeName = decodeURIComponent(new URLSearchParams(window.location.search).get('payeeName')) || 'Default Name';
        const amount = new URLSearchParams(window.location.search).get('amount') || '0';
        const transactionNote = decodeURIComponent(new URLSearchParams(window.location.search).get('transactionNote')) || '';
        const currency = "INR";

        // Generate random transaction ID
        const transactionId = 'TXN' + Math.random().toString(36).substr(2, 9).toUpperCase();

        // Update display elements
        document.getElementById('payee-name').textContent = payeeName;
        document.getElementById('transaction-note').textContent = transactionNote;
        document.getElementById('amount-display').textContent = amount;
        document.getElementById('transaction-id').textContent = transactionId;

        // Generate UPI payment URL
        const upiUrl = `upi://pay?pa=${upiId}&pn=${encodeURIComponent(payeeName)}&am=${amount}&tn=${encodeURIComponent(transactionNote)}&cu=${currency}`;

        // Generate QR code using QR Server API
        const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(upiUrl)}`;
        document.getElementById('qr-code').src = qrCodeUrl;

        // Countdown timer
        function startCountdown(duration, display) {
            let timer = duration, minutes, seconds;
            const countdown = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(countdown);
                    display.textContent = "Expired";
                }
            }, 1000);
        }


    </script> --}}
</body>
</html>
