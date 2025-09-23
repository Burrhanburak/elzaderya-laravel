<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satın Alımınız Tamamlandı</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #206d4e;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            color: #206d4e;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .success-icon {
            background: #206d4e;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 24px;
        }
        .product-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #206d4e;
        }
        .order-details {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .highlight {
            color: #206d4e;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Elza Darya</div>
            <div class="success-icon">✓</div>
            <h1>Satın Alımınız Başarıyla Tamamlandı!</h1>
        </div>

        <p>Merhaba,</p>
        
        <p>Satın alımınız için teşekkür ederiz. Siparişiniz başarıyla işleme alınmış ve ödemeniz onaylanmıştır.</p>

        <div class="product-info">
            <h3>Satın Aldığınız Ürün:</h3>
            <p><strong>{{ ucfirst($order->purchasable_type === 'book' ? 'Kitap' : 'Şiir') }}:</strong> {{ $product->title }}</p>
            <p><strong>Fiyat:</strong> {{ number_format($order->amount, 2) }} TL</p>
            <p><strong>Tarih:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>
        </div>

        <div class="order-details">
            <h3>Sipariş Detayları:</h3>
            <p><strong>Sipariş ID:</strong> <span class="highlight">#{{ $order->id }}</span></p>
            <p><strong>E-posta:</strong> {{ $order->email }}</p>
            @if($order->transaction_id)
            <p><strong>İşlem ID:</strong> {{ $order->transaction_id }}</p>
            @endif
        </div>

        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <h3 style="color: #856404; margin-top: 0;">📎 PDF Dosyanız</h3>
            <p style="color: #856404; margin-bottom: 0;">
                Satın aldığınız {{ $order->purchasable_type === 'book' ? 'kitabın' : 'şiirin' }} tam PDF dosyası bu e-postaya eklenmiştir. 
                Ek dosyalar bölümünden indirebilirsiniz.
            </p>
        </div>

        <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <h3 style="color: #0c5460; margin-top: 0;">💡 Önemli Notlar</h3>
            <ul style="color: #0c5460; margin-bottom: 0;">
                <li>PDF dosyasını güvenli bir yerde saklayın</li>
                <li>Dosyayı kaybederseniz, bu e-postadan tekrar indirebilirsiniz</li>
                <li>Herhangi bir sorunuz varsa bizimle iletişime geçmekten çekinmeyin</li>
            </ul>
        </div>

        <p>Keyifli okumalar dileriz!</p>
        
        <p>Saygılarımızla,<br>
        <strong class="highlight">Elza Darya Ekibi</strong></p>

        <div class="footer">
            <p>Bu e-posta otomatik olarak gönderilmiştir.</p>
            <p>Sorularınız için: <a href="mailto:info@elza-darya.com" style="color: #206d4e;">info@elza-darya.com</a></p>
            <p>&copy; {{ date('Y') }} Elza Darya. Tüm hakları saklıdır.</p>
        </div>
    </div>
</body>
</html>
