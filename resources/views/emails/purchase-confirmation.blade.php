<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Satın Alımınız Tamamlandı</title>


    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f5f6f7;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
                Helvetica, Arial, sans-serif;
            color: #333;
        }

        .wrapper {
            max-width: 620px;
            margin: 40px auto;
            padding: 0 15px;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
            padding: 40px;
        }

        .check-icon {
            width: 70px;
            height: 70px;
            background: #206d4e;
            border-radius: 50%;
            color: #fff;
            font-size: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        h1 {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #222;
            margin-bottom: 20px;
        }

        .section {
            background: #fafafa;
            padding: 18px 22px;
            border-left: 4px solid #206d4e;
            border-radius: 8px;
            margin: 25px 0;
        }

        .section h3 {
            margin-top: 0;
            color: #206d4e;
        }

        .order-box {
            background: #eef8f0;
            padding: 18px 22px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .order-box strong {
            color: #206d4e;
        }

        .download-box {
            background: #fff8e6;
            border: 1px solid #ffe9b5;
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
        }

        .file-item {
            background: #fff;
            border: 1px solid #ffe9b5;
            padding: 15px;
            border-radius: 8px;
            margin-top: 12px;
        }

        .download-btn {
            display: inline-block;
            background: #206d4e;
            color: #fff !important;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 8px;
        }

        .note-box {
            background: #e8f7fa;
            border: 1px solid #c1ebf2;
            padding: 18px 22px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 13px;
            color: #777;
        }

        .footer a {
            color: #206d4e;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="card">

            <!-- ✔ REAL ELZA LOGO -->
            <div style="text-align:center; margin-bottom:20px;">
              <img src="{{ asset('images/green-elza.svg') }}" alt="Elza Darya">
            </div>

            <div class="check-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check">
                    <path d="M20 6 9 17l-5-5" />
                </svg>
            </div>

            <h1>Satın Alımınız Tamamlandı</h1>

            <p>Merhaba,</p>
            <p>
                Satın alımınız başarıyla işleme alındı. Ödemeniz onaylandı ve siparişiniz
                hazırlandı.
            </p>

            <div class="section">
                <h3>📘 Satın Aldığınız Ürün</h3>
                <p><strong>Tür:</strong> {{ ucfirst($order->purchasable_type === 'book' ? 'Kitap' : 'Şiir') }}</p>
                <p><strong>Başlık:</strong> {{ $product->title }}</p>
                <p><strong>Fiyat:</strong> {{ number_format($order->amount, 2) }} TL</p>
                <p><strong>Tarih:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>
            </div>

            <div class="order-box">
                <h3 style="margin-top: 0;">🧾 Sipariş Bilgileri</h3>
                <p><strong>Sipariş ID:</strong> #{{ $order->id }}</p>
                <p><strong>E-posta:</strong> {{ $order->email }}</p>
                @if($order->transaction_id)
                <p><strong>İşlem ID:</strong> {{ $order->transaction_id }}</p>
                @endif
            </div>

            <div class="download-box">
                <h3 style="margin-top: 0;">📎 İndirme Linkleri</h3>

                @if(!empty($downloadUrls) && count($downloadUrls) > 0)
                    <p>Dosyalarınızı aşağıdaki güvenli bağlantılardan indirebilirsiniz:</p>

                    @foreach($downloadUrls as $file)
                        <div class="file-item">
                            <strong>📄 {{ $file['name'] }}</strong>
                            @if(!empty($file['size']))
                                <span style="color:#777; font-size:12px;">({{ $file['size'] }})</span>
                            @endif
                            <br />
                            <a href="{{ $file['url'] }}" class="download-btn">📥 İndir</a>
                        </div>
                    @endforeach

                    <p style="font-size:13px; margin-top:10px; color:#a17300;">
                        ⚠️ Bu linkler güvenlik nedeniyle sınırlı süre geçerlidir. Lütfen en kısa sürede dosyalarınızı indirin.
                    </p>
                @else
                    <p style="color:#8a6a00;">Dosyanız hazırlanıyor. Kısa süre içinde size iletilecektir.</p>
                @endif
            </div>

            <div class="note-box">
                <h3 style="margin-top: 0;">ℹ️ Önemli Notlar</h3>
                <ul>
                    <li>PDF dosyalarınızı güvenli bir yerde saklayın.</li>
                    <li>Bu e-postadaki linkler ile dosyanızı yeniden indirebilirsiniz.</li>
                    <li>Her türlü soru için bizimle iletişime geçebilirsiniz.</li>
                </ul>
            </div>

            <p>Keyifli okumalar dileriz,</p>
            <p><strong style="color:#206d4e;">Elza Darya Ekibi</strong></p>

            <div class="footer">
                <p>Bu e-posta otomatik olarak gönderilmiştir.</p>
                <p>İletişim: <a href="mailto:info@elza-darya.com">info@elza-darya.com</a></p>
                <p>&copy; {{ date('Y') }} Elza Darya — Tüm Hakları Saklıdır.</p>
            </div>

        </div>
    </div>
</body>
</html>
