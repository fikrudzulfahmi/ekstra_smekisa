<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;

class LaporanPerKelasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $laporan;
    protected string $namaKelas;
    protected ?string $tahunAjaran;
    protected ?string $tglMulai;
    protected ?string $tglSelesai;

    /**
     * @param Collection $laporan     Koleksi data yang SAMA PERSIS dengan yang dikirim ke Vue
     *                                (properti: nama, ekstra->nama, hadir_count, izin_count,
     *                                sakit_count, alpha_count, total_count)
     */
    public function __construct(
        Collection $laporan,
        string $namaKelas,
        ?string $tahunAjaran = null,
        ?string $tglMulai = null,
        ?string $tglSelesai = null
    ) {
        $this->laporan = $laporan;
        $this->namaKelas = $namaKelas;
        $this->tahunAjaran = $tahunAjaran;
        $this->tglMulai = $tglMulai;
        $this->tglSelesai = $tglSelesai;
    }

    public function collection()
    {
        return $this->laporan;
    }

    public function title(): string
    {
        // Nama sheet Excel, maks 31 karakter & tanpa karakter terlarang
        return 'Kelas ' . preg_replace('/[^A-Za-z0-9 ]/', '', $this->namaKelas);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'Ekstrakurikuler',
            'Hadir',
            'Izin',
            'Sakit',
            'Alpha',
            'Total Kegiatan',
            '% Kehadiran',
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;

        $total = $item->total_count ?? 0;
        $persen = $total > 0 ? round(($item->hadir_count / $total) * 100) : 0;

        return [
            $no,
            $item->nama,
            $item->ekstra?->nama ?? '-',
            $item->hadir_count,
            $item->izin_count,
            $item->sakit_count,
            $item->alpha_count,
            $total,
            $persen . '%',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 28,
            'C' => 22,
            'D' => 10,
            'E' => 10,
            'F' => 10,
            'G' => 10,
            'H' => 14,
            'I' => 14,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Baris judul laporan (disisipkan manual di atas tabel, lihat AfterSheet jika ingin judul besar)
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0B1B36'], // navy brand
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A2:I{$highestRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("B2:B{$highestRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        return [];
    }
}
