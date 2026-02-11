<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Spp</title>
    <style>
        @font-face {
        font-family: 'Open Sans';
        font-style: normal;
        font-weight: normal;
        src: url(http://themes.googleusercontent.com/static/fonts/opensans/v8/cJZKeOuBrn4kERxqtaUH3aCWcynf_cDxXwCLxiixG1c.ttf) format('truetype');
        }
        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Open Sans", Calibri,Candara,Segoe,Segoe UI,Optima,Arial,sans-serif;
        }
        .kop-surat {
            width: 100%;
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
            display: inline-block;
        }
        .kop-surat img {
            float: left;
        }
        .kop-surat .header{
            display: block;
            padding:15px 0px;
        }
        .kop-surat h1 {
            font-size: 14pt;
            margin:0;
            padding: 0;
        }
        .kop-surat p {
            font-size: 9pt;
            margin:0;
            padding: 0;
        }
        div.center-paragraph {
            width: 100%;
            text-align: center;
        }
        p {
            font-size: 9pt;
        }
        .text-bold {
            font-weight: 500;
        }
        .tb-tagihan {
            width: 100%;
            border-collapse: collapse;
        }
        .tb-tagihan thead, .tb-tagihan tbody tr.summarize {
            /* background-color: rgb(13, 67, 137); */
            border: 1px solid rgb(117, 117, 117);
        }
        .tb-tagihan tbody tr.summarize td {
            font-weight: bold;
            font-size: 10pt;
            color: #222222;
        }
        .tb-tagihan thead tr th {
            text-align: left;
            color: #222222;
            font-size: 9pt;
            padding: 5px;
            border: 1px solid rgba(117, 117, 117);
        }
        .tb-tagihan thead tr, .tb-tagihan tbody tr {
            border: 1px solid rgba(117, 117, 117);
            margin:0;
            padding: 0;
            font-size: 9px;
        }
        .tb-tagihan tbody td {
            padding: 5px;
            font-size: 9pt;
            border: 1px solid rgba(117, 117, 117);
        }
        .mt-2 {
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .tb-summarize {
            width: 100%;
            border-collapse: collapse;
        }
        .tb-summarize tr {
            border-top: 1px solid rgba(117, 117, 117);
            border-bottom: 1px solid rgba(117, 117, 117);
        }
        .tb-summarize tr th {
            padding: 10px;
            color: #222222;
            /* background: #121e35; */
        }
        .qrcode {
            margin-top: 20px;
            width: 100%;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="kop-surat">
        <img src="{{ $logo }}" alt="" width="100px"/>
       <div class="header">
            <h1>{{ $dataSekolah['nama_sekolah'] }}</h1>
            <p>{{ $dataSekolah['alamat_sekolah'] }}</p>
            <p>Telp. {{ $dataSekolah['telepon_sekolah'] }} www.mbis-batam.sch.id</p>
       </div>
    </div>
    <div class="center-paragraph">
        <h4>Kartu Rincian Tagihan & Pembayaran Siswa</h4>
    </div>
    <div class="bio">
        <p class="text-bold">Nama : {{ $siswa['nama_siswa'] }}</p>
        <p class="text-bold">Kelas : {{ $siswa['kelas']['nama_kelas'] }}</p>
        {{-- <p class="text-bold">Alamat Sambung: Patam 2, Sekupang, Batam</p> --}}
    </div>
    @php
        $totalTagihan = 0;
        // Kelompokkan tagihan berdasarkan jenis_tagihan
        $tagihanBySewaktu = collect($siswa['tagihans'])->filter(fn($item) => $item['jenis_tagihan'] === 'sekali')->values();
        $tagihanBulanan = collect($siswa['tagihans'])->filter(fn($item) => $item['jenis_tagihan'] === 'bulanan')->values();
        $tagihanTahunan = collect($siswa['tagihans'])->filter(fn($item) => $item['jenis_tagihan'] === 'tahunan')->values();
    @endphp

    <!-- TABEL TAGIHAN SEKALI -->
    @if($tagihanBySewaktu->count() > 0)
        <table class="tb-tagihan">
            <thead style="color: #181818; background: #eaa259;">
                <tr>
                    <th colspan="6">RINCIAN TAGIHAN - SEKALI</th>
                </tr>
            </thead>
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Item</th>
                    <th>Jumlah Tagihan</th>
                    <th>Jumlah Diskon</th>
                    <th>Tagihan Netto</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php $totalSekali = 0; @endphp
                @foreach ($tagihanBySewaktu as $tagihan)
                    <tr style="{{$tagihan['status'] === 'lunas' ? 'background: #ccc;' : ''}}">
                        <td>
                            {{ \App\Models\Tagihan::BULAN[$tagihan['periode_bulan']] }} {{ $tagihan['periode_tahun'] }}
                        </td>
                        <td>{{ $tagihan['nama_tagihan'] }}</td>
                        <td>{{ number_format($tagihan['jumlah_tagihan'], 0, '', '.') }}</td>
                        <td>{{ number_format($tagihan['jumlah_diskon'], 0, '', '.') }}</td>
                        <td>{{ number_format($tagihan['tagihan_netto'], 0, '', '.') }}</td>
                        <td>{{ $tagihan['status'] }}</td>
                    </tr>
                    @php $totalSekali += $tagihan['tagihan_netto']; @endphp
                @endforeach
                <tr class="summarize">
                    <td colspan="4">Total Sekali</td>
                    <td>{{ number_format($totalSekali, 0, '', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <div class="mt-2"></div>
        @php $totalTagihan += $totalSekali; @endphp
    @endif

    <!-- TABEL TAGIHAN BULANAN -->
    @if($tagihanBulanan->count() > 0)
        <table class="tb-tagihan">
            <thead style="color: #181818; background: #eaa259;">
                <tr>
                    <th colspan="6">RINCIAN TAGIHAN - BULANAN</th>
                </tr>
            </thead>
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Item</th>
                    <th>Jumlah Tagihan</th>
                    <th>Jumlah Diskon</th>
                    <th>Tagihan Netto</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php $totalBulanan = 0; @endphp
                @foreach ($tagihanBulanan as $tagihan)
                    <tr style="{{$tagihan['status'] === 'lunas' ? 'background: #ccc;' : ''}}">
                        <td>
                            {{ \App\Models\Tagihan::BULAN[$tagihan['periode_bulan']] }} {{ $tagihan['periode_tahun'] }}
                        </td>
                        <td>{{ $tagihan['nama_tagihan'] }}</td>
                        <td>{{ number_format($tagihan['jumlah_tagihan'], 0, '', '.') }}</td>
                        <td>{{ number_format($tagihan['jumlah_diskon'], 0, '', '.') }}</td>
                        <td>{{ number_format($tagihan['tagihan_netto'], 0, '', '.') }}</td>
                        <td>{{ $tagihan['status'] }}</td>
                    </tr>
                    @php $totalBulanan += $tagihan['tagihan_netto']; @endphp
                @endforeach
                <tr class="summarize">
                    <td colspan="4">Total Bulanan</td>
                    <td>{{ number_format($totalBulanan, 0, '', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <div class="mt-2"></div>
        @php $totalTagihan += $totalBulanan; @endphp
    @endif

    <!-- TABEL TAGIHAN TAHUNAN -->
    @if($tagihanTahunan->count() > 0)
        <table class="tb-tagihan">
            <thead style="color: #181818; background: #eaa259;">
                <tr>
                    <th colspan="6">RINCIAN TAGIHAN - TAHUNAN</th>
                </tr>
            </thead>
            <thead>
                <tr>
                    <th>Tahun</th>
                    <th>Item</th>
                    <th>Jumlah Tagihan</th>
                    <th>Jumlah Diskon</th>
                    <th>Tagihan Netto</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php $totalTahunan = 0; @endphp
                @foreach ($tagihanTahunan as $tagihan)
                    <tr style="{{$tagihan['status'] === 'lunas' ? 'background: #ccc;' : ''}}">
                        <td>
                            {{ $tagihan['periode_tahun'] }}
                        </td>
                        <td>{{ $tagihan['nama_tagihan'] }}</td>
                        <td>{{ number_format($tagihan['jumlah_tagihan'], 0, '', '.') }}</td>
                        <td>{{ number_format($tagihan['jumlah_diskon'], 0, '', '.') }}</td>
                        <td>{{ number_format($tagihan['tagihan_netto'], 0, '', '.') }}</td>
                        <td>{{ $tagihan['status'] }}</td>
                    </tr>
                    @php $totalTahunan += $tagihan['tagihan_netto']; @endphp
                @endforeach
                <tr class="summarize">
                    <td colspan="4">Total Tahunan</td>
                    <td>{{ number_format($totalTahunan, 0, '', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <div class="mt-2"></div>
        @php $totalTagihan += $totalTahunan; @endphp
    @endif
    <div class="mt-2"></div>
    <table class="tb-tagihan">
        <thead style="background: #244ea7;">
            <tr>
                <th colspan="5" style="color: #fff;">RINCIAN PEMBAYARAN</th>
            </tr>
        </thead>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Tagihan</th>
                <th>Jumlah DIbayar</th>
                <th>Metode Pembayaran</th>
                <th>Operator</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalPembayaran = 0;
            @endphp
            @foreach($siswa['pembayarans'] as $pembayaran)
            @php
                $totalPembayaran += $pembayaran['jumlah_dibayar'];
                $itemTagihan = \App\Models\Tagihan::where('id', $pembayaran['tagihan_id'])->select('periode_bulan', 'periode_tahun', 'nama_tagihan')->first();
            @endphp
            <tr>
                <td>{{ $pembayaran['tanggal_pembayaran'] }}</td>
                <td> {{$itemTagihan->nama_tagihan}} {{ \App\Models\Tagihan::BULAN[$itemTagihan->periode_bulan].' '.$itemTagihan->periode_tahun }}</td>
                <td>{{ number_format($pembayaran['jumlah_dibayar'], 0, '', '.') }}</td>
                <td>{{ $pembayaran['metode_pembayaran'] }}</td>
                <td>{{ \App\Models\User::withTrashed()->where('id', $pembayaran['user_id'])->value('name') }}</td>
            </tr>
            @endforeach
            <tr class="summarize">
                <td colspan="2">Total</td>
                <td>{{ number_format($totalPembayaran, 0, '', '.') }}</td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>
<div class="mt-2"></div>
<div class="mt-2"></div>
    <table class="tb-summarize">
        <thead>
            <tr>
                <th>Jumlah Tagihan</th>
                <th>{{ number_format($totalTagihan, 0, '', '.') }}</th>
                <th>Jumlah Dibayar</th>
                <th>{{ number_format($totalPembayaran, 0, '', '.') }}</th>
            </tr>
        </thead>
    </table>
<div class="mt-2"></div>

    <table class="tb-summarize">
        <thead>
            <tr>
                <th style="font-size: 14pt;">Total Kekurangan</th>
                <th style="font-size: 14pt;">{{ number_format($totalTagihan-$totalPembayaran, 0, '', '.') }}</th>
            </tr>
        </thead>
    </table>
    <p style="font-style: italic;">Terbilang: {{ \App\Helpers\Terbilang::make((int) $totalTagihan-$totalPembayaran) }}</p>
    <div class="qrcode">
        {{-- <img src="data:image/svg+xml;base64,{{ $qrcode }}" alt="QR Code"/> --}}
    </div>
</body>
</html>
