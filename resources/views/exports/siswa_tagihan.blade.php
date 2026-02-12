<table>
    <thead>
        {{-- Baris 1: Judul --}}
        <tr>
            <th colspan="{{ count($sekaliHeaders) + count($tahunanHeaders) + count($bulananHeaders) + 4 }}" style="font: bold; font-size: 16pt; text-align: center;">
                LAPORAN TUNGGAKAN HINGGA - {{ date('d F Y') }}
            </th>
        </tr>
        {{-- Baris 2: Kosong --}}
        <tr></tr>
        {{-- Baris 3: Header --}}
        <tr>
            <th style="background-color: #31beec; font-weight: bold; text-align: center; border: 1px solid #222;">Nama Siswa</th>
            <th style="background-color: #31beec; font-weight: bold; text-align: center; border: 1px solid #222;">Kelas</th>
            <th style="background-color: #31beec; font-weight: bold; text-align: center; border: 1px solid #222;">Jenjang</th>
            @foreach($sekaliHeaders as $h) <th style="background-color: #31beec; font-weight: bold; text-align: center; border: 1px solid #222;">{{ $h->nama_kategori }}</th> @endforeach
            @foreach($tahunanHeaders as $h) <th style="background-color: #31beec; font-weight: bold; text-align: center; border: 1px solid #222;">{{ $h->nama_kategori }}</th> @endforeach
            @foreach($bulananHeaders as $h)
                <th style="background-color: #31beec; font-weight: bold; text-align: center; border: 1px solid #222;">{{ $h->nama_kategori }} ({{ \Carbon\Carbon::create()->month($h->periode_bulan)->format('M') }})</th>
            @endforeach
            <th style="background-color: #31beec; font-weight: bold; text-align: center; border: 1px solid #222;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $index => $siswa)
            @php
                // Data siswa dimulai di Baris 4 Excel
                $currentRow = $index + 4;
            @endphp
            <tr>
                <td>{{ $siswa->nama_siswa }}</td>
                <td>{{ $siswa->nama_kelas }}</td>
                <td>{{ $siswa->jenjang }}</td>

                {{-- Data Nominal (Kolom D dan seterusnya) --}}
                @foreach($sekaliHeaders as $h)
                    <td style="text-align: right;">{{ $siswa->tagihans->where('jenis_tagihan', 'sekali')->where('kategori_biaya_id', $h->kategori_biaya_id)->sum(fn($t) => $t->tagihan_netto - $t->pembayaran->sum('jumlah_dibayar')) }}</td>
                @endforeach

                @foreach($tahunanHeaders as $h)
                    <td style="text-align: right;">{{ $siswa->tagihans->where('jenis_tagihan', 'tahunan')->where('kategori_biaya_id', $h->kategori_biaya_id)->sum(fn($t) => $t->tagihan_netto - $t->pembayaran->sum('jumlah_dibayar')) }}</td>
                @endforeach

                @foreach($bulananHeaders as $h)
                    @php
                        $t = $siswa->tagihans->where('jenis_tagihan', 'bulanan')->where('kategori_biaya_id', $h->kategori_biaya_id)->where('periode_bulan', $h->periode_bulan)->where('periode_tahun', $h->periode_tahun)->first();
                        $sisa = $t ? ($t->tagihan_netto - $t->pembayaran->sum('jumlah_dibayar')) : 0;
                    @endphp
                    <td style="text-align: right;">{{ $sisa }}</td>
                @endforeach

                {{-- RUMUS SUM BARIS: Menjumlahkan dari kolom D hingga kolom terakhir tagihan --}}
                <td style="font-weight: bold; text-align: right;">
                    =SUM(D{{ $currentRow }}:{{ $lastColumnLetter }}{{ $currentRow }})
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        @php
            $footerRow = count($students) + 4;
            $lastDataRow = $footerRow - 1;
        @endphp
        <tr style="font-weight: bold;">
            <td colspan="3" style="text-align: right;">TOTAL KESELURUHAN</td>

            {{-- RUMUS SUM KOLOM: Looping untuk setiap kolom tagihan --}}
            @php $totalColsCount = count($sekaliHeaders) + count($tahunanHeaders) + count($bulananHeaders) + 1; @endphp
            @for($i = 4; $i <= (3 + $totalColsCount); $i++)
                @php $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i); @endphp
                <td style="text-align: right;">=SUM({{ $colLetter }}4:{{ $colLetter }}{{ $lastDataRow }})</td>
            @endfor
        </tr>
    </tfoot>
</table>
