<?php

namespace App\Http\Controllers;

use App\Actions\Reports\ExportAttendanceRecap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AttendanceReportPdfController extends Controller
{
    public function __invoke(Request $request, ExportAttendanceRecap $export)
    {
        Gate::authorize('access-pengurus-area');

        return $export->pdf(
            $request->filled('event') ? (int) $request->query('event') : null,
            $request->filled('from') ? (string) $request->query('from') : null,
            $request->filled('to') ? (string) $request->query('to') : null,
            $request->filled('class') ? (int) $request->query('class') : null,
        );
    }
}
