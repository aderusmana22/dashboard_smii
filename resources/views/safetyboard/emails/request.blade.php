@php
    /**
     * Fungsi helper ini mem-parsing string HTML dari editor dan menerapkan gaya inline
     * pada semua tag <img> untuk memastikan kompatibilitas email.
     * @param string|null $htmlContent Konten HTML mentah.
     * @return string Konten HTML yang sudah diberi gaya.
     */
    function style_editor_images($htmlContent) {
        if (empty(trim($htmlContent))) {
            return '';
        }

        $dom = new \DOMDocument();
        // Menggunakan @ untuk menekan error dari HTML yang mungkin tidak valid & memastikan encoding UTF-8
        @$dom->loadHtml(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            // Terapkan gaya inline langsung ke elemen gambar
            $img->setAttribute('style', 'max-height: 200px; width: auto; height: auto; display: block; margin: 10px auto;');
        }
        
        return $dom->saveHTML();
    }

    // Proses semua field yang berisi HTML dari editor
    $uraian_kejadian_styled = style_editor_images($laporan->uraian_kejadian);
    $analisa_masalah_styled = style_editor_images($laporan->analisa_masalah);
    $tindakan_pencegahan_styled = style_editor_images($laporan->tindakan_pencegahan);
    $rekomendasi_styled = style_editor_images($laporan->rekomendasi);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Action Required: Accident Report Approval #{{ $laporan->nomor_form }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e9e9e9;
            color: #333333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        /* Style dasar untuk gambar, akan ditimpa oleh style inline dari PHP */
        .prose img {
            max-width: 100% !important;
            height: auto !important;
        }
    </style>
</head>
<body style="font-family: Arial, sans-serif; background-color: #e9e9e9; color: #333333; line-height: 1.5; margin: 0; padding: 20px;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="800" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #ffffff; border: 1px solid #dddddd; margin: 0 auto;">
                    
                    <!-- Intro in English -->
                    <tr>
                        <td style="padding: 30px 40px; background-color: #f7fafc; border-bottom: 1px solid #e2e8f0;">
                            <h1 style="font-size: 24px; color: #1a202c; margin-top: 0;">Action Required: Report Approval</h1>
                            <p>Hello <strong>{{ $approver->name }}</strong>,</p>
                            <p>An accident report with number <strong>#{{ $laporan->nomor_form }}</strong> requires your approval. Please review the details below and take action.</p>
                        </td>
                    </tr>

                    <!-- Report Content -->
                    <tr>
                        <td style="padding: 30px 40px 40px 40px;">
                            <!-- Header -->
                            <table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; border: 2px solid black; font-size: 13px;">
                                <tr>
                                    <td width="25%" align="center" style="padding: 10px;">
                                        {{-- Pastikan APP_URL di .env sudah benar agar gambar ini muncul --}}
                                        <img src="{{ asset('assets/images/logohitam.png') }}" alt="Logo" style="height: 64px;">
                                        <p style="font-weight: bold; font-size: 11px; margin: 8px 0 0 0;">PT SINAR MEADOW<br>INTERNATIONAL INDONESIA</p>
                                    </td>
                                    <td width="50%" align="center" style="font-weight: bold;">
                                        <div style="border-bottom: 2px solid black; padding: 4px; font-size: 18px;">FORM</div>
                                        <div style="padding: 8px; font-size: 20px; line-height: 1.2;">LAPORAN INVESTIGASI KECELAKAAN KERJA</div>
                                    </td>
                                    <td width="25%" style="vertical-align: top; padding: 10px; font-size: 12px;">
                                        <strong>No</strong>: {{ $laporan->nomor_form ?? '-' }}<br>
                                        <strong>Revision</strong>: {{ $laporan->revision_number ?? 0 }}<br>
                                        <strong>Date</strong>: {{ optional($laporan->date)->format('d F Y') ?? '-' }}<br>
                                    </td>
                                </tr>
                            </table>

                            <!-- Body -->
                            <table width="100%" border="0" cellpadding="10" cellspacing="0" style="border: 2px solid black; border-top: none; font-size: 14px; line-height: 1.6;">
                                <tr><td style="padding: 20px;">
                                    
                                    <p><strong>1. Kategori Kecelakaan:</strong> {{ $laporan->kategori_kecelakaan ?? '-' }}</p>

                                    <p><strong>2. Tanggal & Jam Kecelakaan:</strong><br>
                                    <span style="padding-left: 20px;">Tanggal: {{ optional($laporan->waktu_kecelakaan)->format('d-M-Y') ?? '-' }}</span><br>
                                    <span style="padding-left: 20px;">Jam: {{ optional($laporan->waktu_kecelakaan)->format('H:i') ?? '-' }}</span></p>

                                    <p><strong>3. Lokasi Kecelakaan:</strong><br>
                                    <span style="padding-left: 20px;">Lokasi: {{ $laporan->lokasi_kecelakaan ?? '-' }}</span><br>
                                    <span style="padding-left: 20px;">Seksi / Departemen: {{ $laporan->departemen ?? '-' }}</span></p>

                                    <p><strong>4. Nama Korban:</strong><br>
                                    <span style="padding-left: 20px;">Nama: {{ $laporan->nama_korban ?? '-' }}</span><br>
                                    <span style="padding-left: 20px;">NIK: {{ $laporan->nik ?? '-' }}</span><br>
                                    <span style="padding-left: 20px;">Usia: {{ $laporan->usia ?? '-' }}</span><br>
                                    <span style="padding-left: 20px;">Masa Kerja: {{ $laporan->masa_kerja ?? '-' }}</span><br>
                                    <span style="padding-left: 20px;">Jabatan: {{ $laporan->jabatan ?? '-' }}</span></p>

                                    <p><strong>5. Diberi Pertolongan (P3K):</strong><br>
                                    <span style="padding-left: 20px;">Tempat: {{ $laporan->pertolongan ?? '-' }}</span><br>
                                    @if($laporan->p3k_oleh)<span style="padding-left: 20px;">Oleh: {{ $laporan->p3k_oleh }}</span><br>@endif
                                    @if($laporan->jam_p3k)<span style="padding-left: 20px;">Jam: {{ \Carbon\Carbon::parse($laporan->jam_p3k)->format('H:i') }}</span></p>@endif

                                    <p><strong>6. Akibat Kecelakaan:</strong> {{ $laporan->akibat_kecelakaan ?? '-' }}</p>

                                    <p><strong>7. Jumlah waktu hilang:</strong> {{ $laporan->waktu_hilang ?? '0' }} hari</p>

                                    @if($laporan->biayaPerawatan->isNotEmpty())
                                    <p><strong>8. Biaya Perawatan:</strong><br>
                                        @foreach($laporan->biayaPerawatan as $biaya)
                                            <span style="padding-left: 20px;">Rp {{ number_format($biaya->harga, 0, ',', '.') }} ({{ $biaya->kategori }})</span><br>
                                        @endforeach
                                        @if($laporan->biayaPerawatan->count() > 1)
                                            <span style="padding-left: 20px;"><strong>Total: Rp {{ number_format($laporan->biayaPerawatan->sum('harga'), 0, ',', '.') }}</strong></span>
                                        @endif
                                    </p>
                                    @endif

                                    @php $apds = $laporan->apd_data ?? []; @endphp
                                    @if(!empty($apds))
                                    <p><strong>9. Alat Pelindung Diri (APD) yang Diwajibkan & Dipakai:</strong><br>
                                    <span style="padding-left: 20px;">
                                        @forelse($apds as $key => $data)
                                            {{ Str::title(str_replace('_', ' ', $key)) }} (Used: {{ ucfirst($data['dipakai'] ?? 'no') }})@if(!$loop->last), @endif
                                        @empty
                                            -
                                        @endforelse
                                    </span></p>
                                    @endif

                                    <p><strong>10. Sebab Kecelakaan:</strong> {{ $laporan->sebab_kecelakaan ?? '-' }}</p>

                                    <div style="margin-top: 15px;">
                                        <strong>11. Uraian Kejadian:</strong>
                                        <div class="prose" style="padding-left: 20px; border-left: 3px solid #f0f0f0; margin-left: 5px;">{!! $uraian_kejadian_styled !!}</div>
                                    </div>

                                    <p style="margin-top: 15px;"><strong>12. Kategori Dampak:</strong> {{ $laporan->kategori_dampak ?? '-' }}</p>
                                    <p><strong>13. Tipe Kecelakaan:</strong> {{ $laporan->tipe_kecelakaan ?? '-' }}</p>
                                    <p><strong>14. Bagian badan yang terluka:</strong> {{ $laporan->bagian_terluka ?? '-' }}</p>

                                    @php
                                        $sebabUtamaA = collect($laporan->sebab_utama)->firstWhere('kategori', 'A');
                                        $sebabUtamaB = collect($laporan->sebab_utama)->firstWhere('kategori', 'B');
                                    @endphp
                                    <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid black;">
                                        <strong>15. Analisa Sebab Utama Kecelakaan:</strong>
                                        @if($sebabUtamaA)
                                            <p style="margin: 5px 0 0 20px;"><strong>A. Tindakan Berbahaya:</strong> {{ $sebabUtamaA['deskripsi'] }}</p>
                                        @endif
                                        @if($sebabUtamaB)
                                            <p style="margin: 5px 0 0 20px;"><strong>B. Keadaan Berbahaya:</strong> {{ $sebabUtamaB['deskripsi'] }}</p>
                                        @endif
                                        @if(!$sebabUtamaA && !$sebabUtamaB)
                                            <p style="margin: 5px 0 0 20px;">-</p>
                                        @endif
                                    </div>
                                    
                                    @if($laporan->analisa_masalah)
                                    <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid black;">
                                        <strong>16. Analisa Masalah:</strong>
                                        <div class="prose" style="padding-left: 20px; border-left: 3px solid #f0f0f0; margin-left: 5px;">{!! $analisa_masalah_styled !!}</div>
                                    </div>
                                    @endif

                                    @if($laporan->saranPerbaikan->isNotEmpty())
                                    <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid black;">
                                        <strong>17. Saran Perbaikan, PIC, Due Date:</strong>
                                        <table width="100%" border="1" cellpadding="5" cellspacing="0" style="margin-top: 5px; border-collapse: collapse; font-size: 13px;">
                                            <thead style="background-color: #f2f2f2;">
                                                <tr>
                                                    <th align="left">Tindakan Perbaikan</th>
                                                    <th align="center" width="100">PIC</th>
                                                    <th align="center" width="120">Due Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($laporan->saranPerbaikan as $saran)
                                                <tr>
                                                    <td>{{ $saran->tindakan }}</td>
                                                    <td align="center">{{ $saran->pic }}</td>
                                                    <td align="center">{{ optional($saran->due_date)->format('d-M-Y') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                    
                                    @if($laporan->tindakan_pencegahan)
                                    <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid black;">
                                        <strong>Tindakan Pencegahan:</strong>
                                        <div class="prose" style="padding-left: 20px; border-left: 3px solid #f0f0f0; margin-left: 5px;">{!! $tindakan_pencegahan_styled !!}</div>
                                    </div>
                                    @endif

                                    @if($laporan->rekomendasi)
                                    <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid black;">
                                        <strong>Rekomendasi:</strong>
                                        <div class="prose" style="padding-left: 20px; border-left: 3px solid #f0f0f0; margin-left: 5px;">{!! $rekomendasi_styled !!}</div>
                                    </div>
                                    @endif
                                    
                                </td></tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Action Buttons (Bottom) -->
                    <tr>
                        <td style="padding: 30px 40px; border-top: 1px solid #e2e8f0;">
                            <p style="margin-bottom: 20px;">Please provide your decision by clicking one of the buttons below.</p>
                             <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $approveUrl }}" style="display: inline-block; padding: 12px 25px; background-color: #28a745; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 5px; margin-right: 10px;">Approve Report</a>
                                        <a href="{{ $rejectUrl }}" style="display: inline-block; padding: 12px 25px; background-color: #dc3545; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 5px;">Reject Report</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px; text-align: center; font-size: 12px; color: #777777; background-color: #f7f7f7;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>