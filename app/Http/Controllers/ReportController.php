<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filtersFromRequest($request);
        $report = $this->reportData($filters['period'], $filters['department_id']);
        $report['departments'] = Department::orderBy('name')->get();

        return view('admin.reports.index', $report);
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->filtersFromRequest($request);
        $report = $this->reportData($filters['period'], $filters['department_id']);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Kehadiran');
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'Laporan Kehadiran Pegawai');
        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'Periode '.$this->periodLabel($report['period']));

        $headers = ['No', 'Kode Pegawai', 'Nama Pegawai', 'Departemen', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Cuti', 'Alpha', 'Kehadiran'];
        $sheet->fromArray($headers, null, 'A4');

        foreach ($report['rekap'] as $index => $row) {
            $rowNumber = $index + 5;
            $sheet->fromArray([
                $index + 1,
                null,
                null,
                null,
                $row['hadir'],
                $row['terlambat'],
                $row['izin'],
                $row['sakit'],
                $row['cuti'],
                $row['alpha'],
                $row['persen'].'%',
            ], null, "A{$rowNumber}");
            $sheet->setCellValueExplicit("B{$rowNumber}", $row['code'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$rowNumber}", $row['name'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$rowNumber}", $row['dept'], DataType::TYPE_STRING);
        }

        $lastRow = max(4, $report['rekap']->count() + 4);
        $sheet->getStyle('A1:K1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A4:K4')->getFont()->setBold(true);
        $sheet->getStyle("A4:K{$lastRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("E5:K{$lastRow}")->getAlignment()->setHorizontal('center');
        $sheet->freezePane('A5');
        $sheet->setAutoFilter("A4:K{$lastRow}");

        foreach (range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = "laporan-kehadiran-{$report['period']}.xlsx";

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->filtersFromRequest($request);
        $report = $this->reportData($filters['period'], $filters['department_id']);
        $options = new Options;
        $options->setIsRemoteEnabled(false);
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('admin.reports.pdf', $report)->render(), 'UTF-8');
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="laporan-kehadiran-'.$report['period'].'.pdf"',
        ]);
    }

    private function filtersFromRequest(Request $request): array
    {
        $validated = $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        return [
            'period' => $validated['period'] ?? null,
            'department_id' => isset($validated['department_id']) ? (int) $validated['department_id'] : null,
        ];
    }

    private function reportData(?string $period, ?int $departmentId): array
    {
        $period ??= now()->format('Y-m');
        $startDate = Carbon::createFromFormat('!Y-m', $period);
        $endDate = $startDate->copy()->endOfMonth();

        $attendanceCounts = Attendance::query()
            ->select('employee_id', 'status', DB::raw('COUNT(*) as total'))
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereHas('employee', fn ($query) => $query
                ->active()
                ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId)))
            ->groupBy('employee_id', 'status')
            ->get()
            ->groupBy('employee_id');

        $employees = Employee::active()
            ->with('department')
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->orderBy('employee_code')
            ->get();

        $rekap = $employees->map(function (Employee $employee) use ($attendanceCounts) {
            $counts = [
                'hadir' => 0,
                'terlambat' => 0,
                'izin' => 0,
                'sakit' => 0,
                'cuti' => 0,
                'alpha' => 0,
            ];

            foreach ($attendanceCounts->get($employee->id, collect()) as $attendance) {
                if (array_key_exists($attendance->status, $counts)) {
                    $counts[$attendance->status] = (int) $attendance->total;
                }
            }

            $total = array_sum($counts);

            return [
                'name' => $employee->full_name,
                'code' => $employee->employee_code,
                'dept' => $employee->department->name ?? 'Tanpa Departemen',
                ...$counts,
                'persen' => $total > 0 ? (int) round(($counts['hadir'] + $counts['terlambat']) / $total * 100) : 0,
            ];
        });

        $perDepartemen = $rekap->groupBy('dept')
            ->map(fn ($rows, $name) => ['name' => $name, 'percent' => (int) round($rows->avg('persen'))])
            ->sortByDesc('percent')
            ->values();

        $stats = [
            'total_pegawai' => $employees->count(),
            'rata_kehadiran' => $rekap->count() ? (int) round($rekap->avg('persen')) : 0,
            'total_terlambat' => $rekap->sum('terlambat'),
            'cuti_disetujui' => Leave::query()
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $endDate->toDateString())
                ->whereDate('end_date', '>=', $startDate->toDateString())
                ->whereHas('employee', fn ($query) => $query
                    ->active()
                    ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId)))
                ->count(),
        ];

        return compact('period', 'departmentId', 'stats', 'perDepartemen', 'rekap');
    }

    private function periodLabel(string $period): string
    {
        return Carbon::createFromFormat('!Y-m', $period)->translatedFormat('F Y');
    }
}
