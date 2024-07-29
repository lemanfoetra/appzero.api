<?php

namespace App\Http\Controllers;

use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function pengeluaranHariIni()
    {
        try {
            $result = DB::table('expenses')
                ->selectRaw(DB::raw("sum(nominal) as jumlah"))
                ->where('id_users', auth()->id())
                ->where('date', date('Y-m-d'))
                ->first();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => $result->jumlah ?? 0,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => $th->getMessage(),
                'data'      => [],
            ], 500);
        }
    }


    public function pengeluaranMingguIni()
    {
        try {
            $weeks = $this->rangeWeek(date('Y-m-d'));
            $result = DB::table('expenses')
                ->selectRaw(DB::raw("sum(nominal) as jumlah"))
                ->where('id_users', auth()->id())
                ->whereBetween('date', [$weeks[0], $weeks[1]])
                ->first();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => $result->jumlah ?? 0,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => $th->getMessage(),
                'data'      => [],
            ], 500);
        }
    }


    public function pengeluaranBulanIni()
    {
        try {
            $result = DB::table('expenses')
                ->selectRaw(DB::raw("sum(nominal) as jumlah"))
                ->where('id_users', auth()->id())
                ->whereRaw(DB::raw("DATE_FORMAT(date, '%Y-%m') = '" . date('Y-m') . "' "))
                ->first();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => $result->jumlah ?? 0,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => $th->getMessage(),
                'data'      => [],
            ], 500);
        }
    }


    public function detailPengeluaranHariIni()
    {
        try {
            $results = DB::table('expenses')
                ->where('id_users', Auth::id())
                ->where('date', date('Y-m-d'))
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success'   => true,
                'message'   => '',
                'data'      => $results,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => $th->getMessage(),
                'data'      => [],
            ], 500);
        }
    }


    public function detailPengeluaranMingguIni()
    {
        try {
            $weeks = $this->rangeWeek(date('Y-m-d'));
            $results = DB::table('expenses')
                ->select([
                    "expenses.date",
                    DB::raw("(SELECT SUM(B.nominal) FROM expenses B where B.id_users = expenses.id_users AND B.date = expenses.date ) AS jumlah_nominal")
                ])
                ->where('id_users', Auth::id())
                ->whereBetween('date', [$weeks[0], $weeks[1]])
                ->orderBy('date', 'desc')
                ->groupBy('date')
                ->limit(30)
                ->get();

            return response()->json([
                'success'   => true,
                'message'   => '',
                'data'      => $results,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => $th->getMessage(),
                'data'      => [],
            ], 500);
        }
    }


    public function detailPengeluaranBulanIni()
    {
        try {
            $dateOfWeeks = $this->getWeeksInMonth(date('m'), date('Y'));
            foreach ($dateOfWeeks as $key => $week) {

                $result = DB::table('expenses')
                    ->selectRaw(DB::raw("sum(nominal) as nominal"))
                    ->where('id_users', auth()->id())
                    ->whereBetween('date', [$week['start'], $week['end']])
                    ->first();
                $dateOfWeeks[$key]['nominal'] = $result->nominal ?? 0;
            }

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => $dateOfWeeks,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => $th->getMessage(),
                'data'      => [],
            ], 500);
        }
    }


    private function rangeWeek($datestr)
    {
        date_default_timezone_set(date_default_timezone_get());
        $dt = strtotime($datestr);
        return [
            date('N', $dt) == 1 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('last monday', $dt)),
            date('N', $dt) == 7 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('next sunday', $dt))
        ];
    }


    private function getWeeksInMonth($month, $year)
    {
        $start = new DateTime("first day of $year-$month");
        $end = new DateTime("last day of $year-$month");
        $end = $end->modify('+1 day');

        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($start, $interval, $end);

        $weeks = [];
        $weekNumber = null;
        $weekStart = null;
        $weekEnd = null;

        foreach ($dateRange as $date) {
            $currentWeek = $date->format("W");
            if ($weekNumber !== $currentWeek) {
                if ($weekStart !== null && $weekEnd !== null) {
                    $weeks[] = [
                        'start' => $weekStart->format('Y-m-d'),
                        'end' => $weekEnd->format('Y-m-d')
                    ];
                }
                $weekNumber = $currentWeek;
                $weekStart = clone $date;
            }

            $weekEnd = clone $date;
        }

        // Add the last week
        if ($weekStart !== null) {
            $weeks[] = [
                'start' => $weekStart->format('Y-m-d'),
                'end' => $weekEnd->format('Y-m-d')
            ];
        }
        return $weeks;
    }
}
