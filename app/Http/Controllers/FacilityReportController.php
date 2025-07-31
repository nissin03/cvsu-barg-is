<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class FacilityReportController extends Controller
{
    public function index()
    {
        return view('admin.facilities.reports.facility');
    }

    public function data(Request $request)
    {
        try {
            $filter = $request->input('filter', 'monthly');
            $week = $request->input('week');
            $month = $request->input('month');
            $year = $request->input('year');

            \Log::info('Facility Report Request', [
                'filter' => $filter,
                'week' => $week,
                'month' => $month,
                'year' => $year
            ]);

            $query = Payment::where('status', 'completed');

            // Apply year filter if provided
            if ($year) {
                $query->whereYear('created_at', $year);
            }

            switch ($filter) {
                case 'daily':
                    // Use week-of-month logic: week 1 = 1st-7th, week 2 = 8th-14th, etc.
                    if (!$week || !$month || !$year) {
                        return response()->json([
                            'filter' => $filter,
                            'data' => []
                        ]);
                    }
                    $startOfMonth = Carbon::create($year, $month, 1);
                    $startOfWeek = $startOfMonth->copy()->addDays(7 * ($week - 1));
                    $endOfWeek = $startOfWeek->copy()->addDays(6);
                    $endOfMonth = $startOfMonth->copy()->endOfMonth();
                    if ($endOfWeek->gt($endOfMonth)) {
                        $endOfWeek = $endOfMonth;
                    }
                    $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                    $query->selectRaw('DAYNAME(created_at) as label, SUM(total_price) as total, (CASE DAYOFWEEK(created_at) WHEN 1 THEN 7 ELSE DAYOFWEEK(created_at) - 1 END) as day_index')
                        ->groupBy(DB::raw("DAYNAME(created_at)"), DB::raw("day_index"))
                        ->orderBy('day_index');
                    break;
                case 'weekly':
                    if ($month && $year) {
                        $query->whereYear('created_at', $year)
                            ->whereMonth('created_at', $month);
                    }
                    $query->selectRaw('WEEK(created_at, 1) as week, SUM(total_price) as total')
                        ->groupBy('week')
                        ->orderBy('week');
                    break;
                case 'yearly':
                    $query->selectRaw('YEAR(created_at) as label, SUM(total_price) as total')
                        ->groupBy('label')
                        ->orderBy('label');
                    break;
                case 'monthly':
                default:
                    if ($year) {
                        $query->whereYear('created_at', $year);
                    }
                    $query->selectRaw("MONTHNAME(created_at) as label, MONTH(created_at) as month_num, SUM(total_price) as total")
                        ->groupBy('label', 'month_num')
                        ->orderBy('month_num');
                    break;
            }

            $data = $query->get();

            \Log::info('Facility Report Query Result', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'count' => $data->count(),
                'data' => $data->toArray()
            ]);

            return response()->json([
                'filter' => $filter,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Facility report error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getFilterOptions()
    {
        // Get daily options (days of the week)
        $dailyOptions = DB::table('daily_names')->orderByRaw("FIELD(name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")->get();

        // Get weekly options
        $weeklyOptions = DB::table('week_names')->orderBy('week_number')->get();

        // Get monthly options
        $monthlyOptions = DB::table('month_names')->orderByRaw("FIELD(name, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')")->get();

        // Get year options (current year and 5 years back)
        $currentYear = now()->year;
        $yearOptions = [];
        for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
            $yearOptions[] = ['value' => $year, 'label' => $year];
        }

        return response()->json([
            'daily' => $dailyOptions,
            'weekly' => $weeklyOptions,
            'monthly' => $monthlyOptions,
            'years' => $yearOptions
        ]);
    }

    public function summary(Request $request)
    {
        $totalReservations = Payment::count();
        $totalAmountReserved = Payment::where('status', 'completed')->sum('total_price');
        $totalCompleted = Payment::where('status', 'completed')->count();
        $totalCanceled = Payment::where('status', 'canceled')->count();
        $totalPending = Payment::where('status', 'pending')->count();
        $totalFacilitiesReserved = Payment::distinct('availability_id')->count('availability_id');

        return response()->json([
            'total_reservations' => $totalReservations,
            'total_amount_reserved' => $totalAmountReserved,
            'total_completed' => $totalCompleted,
            'total_canceled' => $totalCanceled,
            'total_pending' => $totalPending,
            'total_facilities_reserved' => $totalFacilitiesReserved,
        ]);
    }

    public function downloadFacilityPdf(Request $request)
    {
        $filter = $request->input('filter', 'monthly');
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $week = $request->input('week', 1);

        $query = Payment::with(['user', 'availability.facility']);

        if ($year) {
            $query->whereYear('created_at', $year);
        }
        if ($filter === 'daily' && $week && $month && $year) {
            // Use week-of-month logic for PDF as well
            $startOfMonth = Carbon::create($year, $month, 1);
            $startOfWeek = $startOfMonth->copy()->addDays(7 * ($week - 1));
            $endOfWeek = $startOfWeek->copy()->addDays(6);
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            if ($endOfWeek->gt($endOfMonth)) {
                $endOfWeek = $endOfMonth;
            }
            $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
        } elseif ($month && $filter !== 'yearly') {
            $query->whereMonth('created_at', $month);
        }

        $payments = $query->get();

        $totalReservations = $payments->count();
        $completedReservations = $payments->where('status', 'completed')->count();
        $canceledReservations = $payments->where('status', 'canceled')->count();
        $pendingReservations = $payments->where('status', 'pending')->count();

        $totalReservedAmount = $payments->sum('total_price');
        $totalCompletedAmount = $payments->where('status', 'completed')->sum('total_price');
        $totalCanceledAmount = $payments->where('status', 'canceled')->sum('total_price');

        $facilityName = 'All Facilities';

        switch ($filter) {
            case 'daily':
                $reportPeriod = "Daily Report for Week $week of " . Carbon::create()->month($month)->format('F') . " $year";
                break;
            case 'weekly':
                $reportPeriod = "Weekly Report for " . Carbon::create()->month($month)->format('F') . " $year";
                break;
            case 'monthly':
                $reportPeriod = "Monthly Report for $year";
                break;
            case 'yearly':
                $reportPeriod = "Yearly Report for $year";
                break;
            default:
                $reportPeriod = ucfirst($filter) . " Report";
        }

        $reservations = $payments->map(function ($payment) {
            return (object)[
                'date' => $payment->created_at->format('Y-m-d'),
                'user_name' => $payment->user->name ?? '',
                'status' => $payment->status,
                'amount' => $payment->total_price,
            ];
        });

        $pdf = Pdf::loadView('admin.facilities.reports.pdf-report-facility', compact(
            'facilityName',
            'reportPeriod',
            'totalReservations',
            'completedReservations',
            'canceledReservations',
            'pendingReservations',
            'totalReservedAmount',
            'totalCompletedAmount',
            'totalCanceledAmount',
            'reservations'
        ));

        return $pdf->download('facility_reservation_report.pdf');
    }
}
