<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SalesReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public const PRESETS = [
        'hari-ini' => 'Hari ini',
        '7-hari' => '7 hari terakhir',
        'bulan-ini' => 'Bulan ini',
        'bulan-lalu' => 'Bulan lalu',
    ];

    public function index(Request $request): View
    {
        [$from, $to, $preset] = $this->period($request);
        $report = new SalesReport($from, $to);

        return view('admin.reports.index', [
            'report' => $report,
            'totals' => $report->totals(),
            'daily' => $report->daily(),
            'hourly' => $report->hourly(),
            'payments' => $report->byPaymentMethod(),
            'categories' => $report->byCategory(),
            'topProducts' => $report->topProducts(),
            'preset' => $preset,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);
        $report = new SalesReport($from, $to);

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tanggal', 'Transaksi', 'Pendapatan'], ';');
            foreach ($report->daily() as $day) {
                fputcsv($out, [$day['date']->format('Y-m-d'), $day['count'], $day['revenue']], ';');
            }
            fputcsv($out, [], ';');
            fputcsv($out, ['Produk', 'Kategori', 'Terjual', 'Pendapatan', 'Laba kotor'], ';');
            foreach ($report->topProducts(1000) as $product) {
                fputcsv($out, [$product['name'], $product['category'], $product['quantity'], $product['revenue'], $product['profit']], ';');
            }
            fclose($out);
        }, 'laporan-antara-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{0: Carbon, 1: Carbon, 2: ?string} */
    private function period(Request $request): array
    {
        $preset = $request->input('periode', $request->hasAny(['from', 'to']) ? null : 'bulan-ini');

        [$from, $to] = match ($preset) {
            'hari-ini' => [today(), today()],
            '7-hari' => [today()->subDays(6), today()],
            'bulan-ini' => [today()->startOfMonth(), today()],
            'bulan-lalu' => [today()->subMonthNoOverflow()->startOfMonth(), today()->subMonthNoOverflow()->endOfMonth()],
            default => [$request->date('from') ?? today()->startOfMonth(), $request->date('to') ?? today()],
        };

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        // Batasi satu tahun supaya halaman tetap ringan.
        if ($from->diffInDays($to) > 366) {
            $from = $to->copy()->subDays(366);
        }

        return [$from->copy()->startOfDay(), $to->copy()->endOfDay(), $preset];
    }
}
