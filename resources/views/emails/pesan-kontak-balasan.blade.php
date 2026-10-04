<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Balasan Pesan Kontak - KITB</title>
</head>

<body
    style="margin:0;padding:0;width:100%;background-color:#eef4fb;font-family:Arial,Helvetica,sans-serif;color:#0f172a;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">

    {{-- Preheader --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;font-size:1px;line-height:1px;color:#eef4fb;">
        Balasan atas pesan Anda: {{ $pesanKontak->subjek }}
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
        style="width:100%;margin:0;padding:0;background-color:#eef4fb;">
        <tr>
            <td align="center" style="padding:38px 14px;">

                {{-- MAIN CARD --}}
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0"
                    style="width:100%;max-width:640px;background-color:#ffffff;border:1px solid #dbe5f0;border-radius:20px;overflow:hidden;">

                    {{-- TOP ACCENT --}}
                    <tr>
                        <td style="height:5px;line-height:5px;font-size:0;background-color:#2563eb;">
                            &nbsp;
                        </td>
                    </tr>

                    {{-- HEADER --}}
                    <tr>
                        <td style="padding:0;background-color:#0f172a;">

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                style="width:100%;">
                                <tr>
                                    <td style="height:20px;line-height:20px;font-size:0;">
                                        &nbsp;
                                    </td>
                                </tr>

                                {{-- DECORATIVE SHAPE --}}
                                <tr>
                                    <td align="right" style="padding:0 22px;height:0;line-height:0;font-size:0;">
                                        <div
                                            style="display:inline-block;width:120px;height:120px;background-color:#2563eb;border-radius:50%;opacity:.18;">
                                            &nbsp;
                                        </div>
                                    </td>
                                </tr>

                                {{-- HEADER CONTENT --}}
                                <tr>
                                    <td style="padding:0 32px 32px;">

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                            border="0">
                                            <tr>

                                                {{-- LOGO --}}
                                                <td valign="middle" style="width:64px;padding-right:18px;">
                                                    <table role="presentation" width="58" height="58"
                                                        cellspacing="0" cellpadding="0" border="0"
                                                        style="width:58px;height:58px;background-color:#ffffff;border-radius:14px;">
                                                        <tr>
                                                            <td align="center" valign="middle"
                                                                style="width:58px;height:58px;">
                                                                <img src="https://tanjungbuton-industrial.co.id/logoside.png"
                                                                    width="48" alt="KITB"
                                                                    style="display:block;width:48px;height:auto;max-width:48px;border:0;outline:none;text-decoration:none;">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>

                                                {{-- HEADER TEXT --}}
                                                <td valign="middle">

                                                    <div
                                                        style="margin:0 0 5px;font-size:11px;line-height:1.4;font-weight:700;letter-spacing:.4px;color:#93c5fd;">
                                                        PT KAWASAN INDUSTRI TANJUNG BUTON
                                                    </div>

                                                    <div
                                                        style="margin:0;font-size:24px;line-height:1.25;font-weight:700;color:#ffffff;">
                                                        Balasan Pesan Anda
                                                    </div>

                                                    <div
                                                        style="margin-top:8px;font-size:12px;line-height:1.5;color:#cbd5e1;">
                                                        Terima kasih telah menghubungi KITB.
                                                    </div>

                                                </td>

                                            </tr>
                                        </table>

                                    </td>
                                </tr>

                            </table>

                        </td>
                    </tr>

                    {{-- CONTENT --}}
                    <tr>
                        <td style="padding:32px;">

                            {{-- GREETING --}}
                            <div style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#0f172a;">
                                Yth.
                                <strong style="color:#0f172a;">
                                    {{ $pesanKontak->nama }}
                                </strong>,
                            </div>

                            {{-- INTRO --}}
                            <div style="margin:0 0 25px;font-size:14px;line-height:1.8;color:#475569;">
                                Terima kasih telah menghubungi
                                <strong style="color:#1e293b;">
                                    PT Kawasan Industri Tanjung Buton (KITB)
                                </strong>.
                                Berikut tanggapan kami atas pesan yang Anda kirim melalui
                                formulir kontak website.
                            </div>

                            {{-- SUBJECT --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                style="width:100%;margin-bottom:16px;border:1px solid #dbe5f0;border-radius:14px;background-color:#ffffff;">
                                <tr>
                                    <td style="padding:15px 17px;border-left:4px solid #2563eb;">

                                        <div
                                            style="font-size:10px;line-height:1.4;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:#64748b;">
                                            Subjek Pesan
                                        </div>

                                        <div
                                            style="margin-top:6px;font-size:14px;line-height:1.6;font-weight:600;color:#0f172a;word-break:break-word;">
                                            {{ $pesanKontak->subjek }}
                                        </div>

                                    </td>
                                </tr>
                            </table>

                            {{-- ORIGINAL MESSAGE --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                style="width:100%;margin-bottom:16px;border:1px solid #dbe5f0;border-radius:16px;background-color:#f8fafc;">
                                <tr>
                                    <td style="padding:20px;">

                                        <div
                                            style="margin-bottom:10px;font-size:11px;line-height:1.4;font-weight:700;letter-spacing:.7px;color:#64748b;">
                                            PESAN ANDA
                                        </div>

                                        <div
                                            style="font-size:14px;line-height:1.85;color:#334155;white-space:pre-line;word-break:break-word;">
                                            {{ $pesanKontak->pesan }}
                                        </div>

                                    </td>
                                </tr>
                            </table>

                            {{-- REPLY --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                style="width:100%;margin-bottom:26px;border:1px solid #bfdbfe;border-radius:16px;background-color:#eff6ff;">
                                <tr>
                                    <td style="padding:21px;border-left:4px solid #2563eb;">

                                        <div
                                            style="margin-bottom:10px;font-size:11px;line-height:1.4;font-weight:700;letter-spacing:.7px;color:#1d4ed8;">
                                            BALASAN KITB
                                        </div>

                                        <div
                                            style="font-size:14px;line-height:1.85;color:#1e293b;white-space:pre-line;word-break:break-word;">
                                            {{ $balasan }}
                                        </div>

                                    </td>
                                </tr>
                            </table>

                            {{-- INFO STRIP --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                style="width:100%;margin-bottom:26px;border:1px solid #e2e8f0;border-radius:14px;background-color:#f8fafc;">
                                <tr>
                                    <td style="padding:16px 17px;">

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                            border="0">
                                            <tr>

                                                <td valign="top" style="width:28px;padding-right:10px;">
                                                    <div
                                                        style="width:22px;height:22px;line-height:22px;text-align:center;border-radius:50%;background-color:#dbeafe;color:#2563eb;font-size:13px;font-weight:700;">
                                                        ✓
                                                    </div>
                                                </td>

                                                <td valign="middle"
                                                    style="font-size:12px;line-height:1.7;color:#64748b;">
                                                    Jika masih terdapat pertanyaan atau informasi
                                                    yang ingin disampaikan, Anda dapat membalas
                                                    email ini. Tim KITB akan dengan senang hati
                                                    membantu.
                                                </td>

                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            {{-- DIVIDER --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                border="0">
                                <tr>
                                    <td style="height:1px;line-height:1px;font-size:0;background-color:#e2e8f0;">
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>

                            {{-- CLOSING --}}
                            <div style="margin-top:24px;font-size:14px;line-height:1.8;color:#475569;">
                                Hormat kami,
                            </div>

                            <div style="margin-top:5px;font-size:14px;line-height:1.7;color:#334155;">
                                <strong style="color:#0f172a;">
                                    PT Kawasan Industri Tanjung Buton
                                </strong>
                                <br>

                                <span style="font-size:13px;color:#64748b;">
                                    Tim Informasi &amp; Layanan
                                </span>
                            </div>

                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td style="padding:24px 32px;border-top:1px solid #e2e8f0;background-color:#f8fafc;">

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                border="0">
                                <tr>
                                    <td>

                                        <div style="font-size:13px;line-height:1.6;font-weight:700;color:#334155;">
                                            PT Kawasan Industri Tanjung Buton (KITB)
                                        </div>

                                        <div style="margin-top:7px;font-size:12px;line-height:1.7;color:#64748b;">
                                            Email ini dikirim melalui
                                            <strong style="color:#475569;">
                                                {{ config('mail.from.address') }}
                                            </strong>.
                                        </div>

                                        <div style="margin-top:5px;font-size:11px;line-height:1.6;color:#94a3b8;">
                                            &copy; {{ date('Y') }}
                                            PT Kawasan Industri Tanjung Buton.
                                            Seluruh hak cipta dilindungi.
                                        </div>

                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                </table>

                {{-- OUTSIDE FOOTER --}}
                <div
                    style="max-width:640px;margin:15px auto 0;padding:0 10px;box-sizing:border-box;text-align:center;font-size:10px;line-height:1.6;color:#94a3b8;">
                    Pesan ini ditujukan kepada
                    {{ $pesanKontak->email }}.
                </div>

            </td>
        </tr>
    </table>

</body>

</html>
