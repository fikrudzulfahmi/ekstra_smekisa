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

class RekapNilaiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $laporan;
    protected string $namaKelas;
    protected ?string $tahunAjaran;

    public function __construct(
        Collection $laporan,
        string $namaKelas,
        ?string $tahunAjaran = null
    ) {
        $this->laporan = $laporan;
        $this->namaKelas = $namaKelas;
        $this->tahunAjaran = $tahunAjaran;
    }

    public function collection()
    {
        return $this->laporan;
    }

    public function title(): string
    {
        return 'Kelas ' . preg_replace('/[^A-Za-z0-9 ]/', '', $this->namaKelas);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'Ekstrakurikuler',
            'Rata-rata Nilai',
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $item->nama,
            $item->ekstra?->nama ?? '-',
            $item->rata_rata_nilai !== null ? Number_format((float)$item->rata_rata_nilai, 1) : '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 28,
            'C' => 22,
            'D' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0B1B36'], // navy brand
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A2:D{$highestRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("B2:B{$highestRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        return [];
    }
}
