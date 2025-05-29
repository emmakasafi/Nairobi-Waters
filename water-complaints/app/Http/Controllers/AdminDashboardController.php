<?php

namespace App\Http\Controllers;

use App\Models\WaterSentiment;
use App\Models\TwitterData;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminDashboardController extends Controller
{
    /**
     * Apply filters to combined WaterSentiment and TwitterData query.
     *
     * @param Request $request
     * @param bool $applyDefaultOrder
     * @return \Illuminate\Database\Query\Builder
     */
    private function applyFilters(Request $request, $applyDefaultOrder = true)
    {
        // Base query for WaterSentiment
        $waterSentimentQuery = DB::table('water_sentiments')
            ->select(
                'original_caption',
                'processed_caption',
                'timestamp',
                'complaint_category',
                'subcounty',
                'ward',
                'overall_sentiment',
                'source',
                'status',
                'user_name',
                'user_email',
                'user_phone'
            );

        // Base query for TwitterData, mapping to the same fields
        $twitterDataQuery = DB::table('twitter_data')
            ->select(
                'original_caption',
                'processed_caption',
                'timestamp',
                'complaint_category',
                DB::raw('NULL::text as subcounty'),
                DB::raw('NULL::text as ward'),
                'overall_sentiment',
                DB::raw('\'Twitter\'::text as source'),
                DB::raw('NULL::text as status'),
                DB::raw('NULL::text as user_name'),
                DB::raw('NULL::text as user_email'),
                DB::raw('NULL::text as user_phone')
            );

        // Combine queries using UNION
        $query = $waterSentimentQuery->union($twitterDataQuery);

        // Apply filters to the combined query
        $query = DB::table(DB::raw("({$query->toSql()}) as combined"))
            ->mergeBindings($query);

        if ($applyDefaultOrder) {
            $query->orderBy('timestamp', 'desc');
        }

        if ($request->has('category') && $request->category && $request->category !== 'All Categories') {
            $query->where('complaint_category', $request->category);
        }
        if ($request->has('subcounty') && $request->subcounty && $request->subcounty !== 'All Subcounties') {
            $query->where('subcounty', $request->subcounty);
        }
        if ($request->has('ward') && $request->ward && $request->ward !== 'All Wards') {
            $query->where('ward', $request->ward);
        }
        if ($request->has('sentiment') && $request->sentiment && $request->sentiment !== 'All Sentiments') {
            $query->where('overall_sentiment', $request->sentiment);
        }
        if ($request->has('source') && $request->source && $request->source !== 'All Sources') {
            $query->where('source', $request->source);
        }
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            $query->whereBetween('timestamp', [$request->start_date, $request->end_date]);
        }

        return $query;
    }

    /**
     * Apply filters to TwitterData query only.
     *
     * @param Request $request
     * @param bool $applyDefaultOrder
     * @return \Illuminate\Database\Query\Builder
     */
    private function applyTwitterFilters(Request $request, $applyDefaultOrder = true)
    {
        $query = DB::table('twitter_data')
            ->select(
                'original_caption',
                'processed_caption',
                'timestamp',
                'complaint_category',
                'overall_sentiment',
                DB::raw('\'Twitter\'::text as source')
            );

        if ($applyDefaultOrder) {
            $query->orderBy('timestamp', 'desc');
        }

        if ($request->has('category') && $request->category && $request->category !== 'All Categories') {
            $query->where('complaint_category', $request->category);
        }
        if ($request->has('sentiment') && $request->sentiment && $request->sentiment !== 'All Sentiments') {
            $query->where('overall_sentiment', $request->sentiment);
        }
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            $query->whereBetween('timestamp', [$request->start_date, $request->end_date]);
        }

        return $query;
    }

    public function index(Request $request)
    {
        // Apply filters to the base query with default timestamp ordering
        $filteredQuery = $this->applyFilters($request);

        // Fetch recent complaints (top 3, explicitly ordered by timestamp desc)
        $recentComplaints = $filteredQuery->orderBy('timestamp', 'desc')->take(3)->get()->map(function ($item) {
            return (object) [
                'original_caption' => htmlspecialchars($item->original_caption ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                'processed_caption' => htmlspecialchars($item->processed_caption ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                'timestamp' => $item->timestamp ? \Carbon\Carbon::parse($item->timestamp) : null,
                'complaint_category' => htmlspecialchars($item->complaint_category ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'subcounty' => htmlspecialchars($item->subcounty ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'ward' => htmlspecialchars($item->ward ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'overall_sentiment' => htmlspecialchars($item->overall_sentiment ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'source' => htmlspecialchars($item->source ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'status' => htmlspecialchars($item->status ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'user_name' => htmlspecialchars($item->user_name ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                'user_email' => htmlspecialchars($item->user_email ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                'user_phone' => htmlspecialchars($item->user_phone ?? 'N/A', ENT_QUOTES, 'UTF-8'),
            ];
        });

        // Total number of complaints with filters
        $totalComplaints = $filteredQuery->count();

        // Fetch complaint statuses without default timestamp ordering
        $complaintStatuses = $this->applyFilters($request, false)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => htmlspecialchars($item->status ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                    'count' => (int) $item->count
                ];
            });

        // Fetch sentiment data without default timestamp ordering
        $sentimentData = $this->applyFilters($request, false)
            ->select('overall_sentiment', DB::raw('COUNT(*) as count'))
            ->groupBy('overall_sentiment')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'overall_sentiment' => htmlspecialchars($item->overall_sentiment ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                    'count' => (int) $item->count
                ];
            });

        // Fetch sentiment trend data without default timestamp ordering
        $sentimentTrendData = $this->applyFilters($request, false)
            ->select(DB::raw('DATE(timestamp) as date'), DB::raw('COUNT(*) as count'), 'overall_sentiment')
            ->groupBy('date', 'overall_sentiment')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date ?? 'Unknown',
                    'overall_sentiment' => htmlspecialchars($item->overall_sentiment ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                    'count' => (int) $item->count
                ];
            });

        // Fetch complaints per subcounty without default timestamp ordering
        $complaintsPerSubcounty = $this->applyFilters($request, false)
            ->select('subcounty', DB::raw('COUNT(*) as count'))
            ->groupBy('subcounty')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'subcounty' => htmlspecialchars($item->subcounty ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                    'count' => (int) $item->count
                ];
            });

        // Fetch complaints per ward without default timestamp ordering
        $complaintsPerWard = $this->applyFilters($request, false)
            ->select('ward', DB::raw('COUNT(*) as count'))
            ->groupBy('ward')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'ward' => htmlspecialchars($item->ward ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                    'count' => (int) $item->count
                ];
            });

        // Fetch complaints per category without default timestamp ordering
        $complaintsPerCategory = $this->applyFilters($request, false)
            ->select('complaint_category', DB::raw('COUNT(*) as count'))
            ->groupBy('complaint_category')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'complaint_category' => htmlspecialchars($item->complaint_category ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                    'count' => (int) $item->count
                ];
            });

        // Fetch unique values for filters with sanitization
        $categories = $this->applyFilters($request, false)
            ->select('complaint_category')
            ->distinct()
            ->get()
            ->pluck('complaint_category')
            ->filter()
            ->map(function ($category) {
                return htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
            })->values();

        $subcounties = $this->applyFilters($request, false)
            ->select('subcounty')
            ->distinct()
            ->get()
            ->pluck('subcounty')
            ->filter()
            ->map(function ($subcounty) {
                return htmlspecialchars($subcounty, ENT_QUOTES, 'UTF-8');
            })->values();

        $wards = $this->applyFilters($request, false)
            ->select('ward')
            ->distinct()
            ->get()
            ->pluck('ward')
            ->filter()
            ->map(function ($ward) {
                return htmlspecialchars($ward, ENT_QUOTES, 'UTF-8');
            })->values();

        $sentiments = $this->applyFilters($request, false)
            ->select('overall_sentiment')
            ->distinct()
            ->get()
            ->pluck('overall_sentiment')
            ->filter()
            ->map(function ($sentiment) {
                return htmlspecialchars($sentiment, ENT_QUOTES, 'UTF-8');
            })->values();

        $sources = $this->applyFilters($request, false)
            ->select('source')
            ->distinct()
            ->get()
            ->pluck('source')
            ->filter()
            ->map(function ($source) {
                return htmlspecialchars($source, ENT_QUOTES, 'UTF-8');
            })->values();

        // Fetch source counts for sourceChart
        $sourceCounts = $this->applyFilters($request, false)
            ->select('source', DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count');

        // Fetch departments
        $departments = Department::all();

        // Total users and new users today (not filtered, as they are user-related)
        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', today())->count();

        return view('admin-dashboard', compact(
            'recentComplaints',
            'totalComplaints',
            'totalUsers',
            'newUsersToday',
            'sentimentData',
            'sentimentTrendData',
            'complaintsPerSubcounty',
            'complaintsPerWard',
            'complaintsPerCategory',
            'categories',
            'subcounties',
            'wards',
            'sentiments',
            'sources',
            'complaintStatuses',
            'departments',
            'sourceCounts'
        ));
    }

    /**
     * Display Twitter Data Dashboard.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function twitterDashboard(Request $request)
    {
        // Apply filters to Twitter data
        $filteredQuery = $this->applyTwitterFilters($request);

        // Fetch recent Twitter complaints (top 5)
        $recentComplaints = $filteredQuery->orderBy('timestamp', 'desc')->take(5)->get()->map(function ($item) {
            return (object) [
                'original_caption' => htmlspecialchars($item->original_caption ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                'processed_caption' => htmlspecialchars($item->processed_caption ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                'timestamp' => $item->timestamp ? \Carbon\Carbon::parse($item->timestamp) : null,
                'complaint_category' => htmlspecialchars($item->complaint_category ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'overall_sentiment' => htmlspecialchars($item->overall_sentiment ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'source' => htmlspecialchars($item->source ?? 'Twitter', ENT_QUOTES, 'UTF-8'),
            ];
        });

        // Total number of Twitter complaints
        $totalComplaints = $filteredQuery->count();

        // Sentiment data
        $sentimentData = $this->applyTwitterFilters($request, false)
            ->select('overall_sentiment', DB::raw('COUNT(*) as count'))
            ->groupBy('overall_sentiment')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'overall_sentiment' => htmlspecialchars($item->overall_sentiment ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                    'count' => (int) $item->count
                ];
            });

        // Sentiment trend data
        $sentimentTrendData = $this->applyTwitterFilters($request, false)
            ->select(DB::raw('DATE(timestamp) as date'), DB::raw('COUNT(*) as count'), 'overall_sentiment')
            ->groupBy('date', 'overall_sentiment')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date ?? 'Unknown',
                    'overall_sentiment' => htmlspecialchars($item->overall_sentiment ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                    'count' => (int) $item->count
                ];
            });

        // Complaints per category
        $complaintsPerCategory = $this->applyTwitterFilters($request, false)
            ->select('complaint_category', DB::raw('COUNT(*) as count'))
            ->groupBy('complaint_category')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'complaint_category' => htmlspecialchars($item->complaint_category ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                    'count' => (int) $item->count
                ];
            });

        // Fetch unique values for filters
        $categories = $this->applyTwitterFilters($request, false)
            ->select('complaint_category')
            ->distinct()
            ->get()
            ->pluck('complaint_category')
            ->filter()
            ->map(function ($category) {
                return htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
            })->values();

        $sentiments = $this->applyTwitterFilters($request, false)
            ->select('overall_sentiment')
            ->distinct()
            ->get()
            ->pluck('overall_sentiment')
            ->filter()
            ->map(function ($sentiment) {
                return htmlspecialchars($sentiment, ENT_QUOTES, 'UTF-8');
            })->values();

        return view('admin.twitter-dashboard', compact(
            'recentComplaints',
            'totalComplaints',
            'sentimentData',
            'sentimentTrendData',
            'complaintsPerCategory',
            'categories',
            'sentiments'
        ));
    }

    /**
     * Fetch wards for a given subcounty via AJAX.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWardsBySubcounty(Request $request)
    {
        $subcounty = $request->query('subcounty');

        $wardsQuery = $this->applyFilters($request, false)
            ->select('ward')
            ->distinct();

        if ($subcounty && $subcounty !== 'All Subcounties') {
            $wardsQuery->where('subcounty', $subcounty);
        }

        $wards = $wardsQuery->get()
            ->pluck('ward')
            ->filter()
            ->map(function ($ward) {
                return htmlspecialchars($ward, ENT_QUOTES, 'UTF-8');
            })->values();

        return response()->json($wards);
    }

    public function exportCsv(Request $request)
    {
        $query = $this->applyFilters($request);
        $data = $query->orderBy('timestamp', 'desc')->get();

        $filename = 'complaints_' . now()->format('Ymd_His') . '.csv';
        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Caption',
                'Processed Caption',
                'Date',
                'Category',
                'Subcounty',
                'Ward',
                'Sentiment',
                'Source',
                'Status',
                'User Name',
                'User Email',
                'User Phone'
            ]);

            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->original_caption ?? 'N/A',
                    $row->processed_caption ?? 'N/A',
                    $row->timestamp ? \Carbon\Carbon::parse($row->timestamp)->format('Y-m-d H:i:s') : 'N/A',
                    $row->complaint_category ?? 'Unknown',
                    $row->subcounty ?? 'Unknown',
                    $row->ward ?? 'Unknown',
                    $row->overall_sentiment ?? 'Unknown',
                    $row->source ?? 'Unknown',
                    $row->status ?? 'Unknown',
                    $row->user_name ?? 'N/A',
                    $row->user_email ?? 'N/A',
                    $row->user_phone ?? 'N/A'
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new class($request) implements FromCollection, WithHeadings {
            protected $request;

            public function __construct(Request $request)
            {
                $this->request = $request;
            }

            public function collection()
            {
                $query = app(\App\Http\Controllers\AdminDashboardController::class)->applyFilters($this->request);
                return $query->orderBy('timestamp', 'desc')->get()->map(function ($row) {
                    return [
                        'Caption' => $row->original_caption ?? 'N/A',
                        'Processed Caption' => $row->processed_caption ?? 'N/A',
                        'Date' => $row->timestamp ? \Carbon\Carbon::parse($row->timestamp)->format('Y-m-d H:i:s') : 'N/A',
                        'Category' => $row->complaint_category ?? 'Unknown',
                        'Subcounty' => $row->subcounty ?? 'Unknown',
                        'Ward' => $row->ward ?? 'Unknown',
                        'Sentiment' => $row->overall_sentiment ?? 'Unknown',
                        'Source' => $row->source ?? 'Unknown',
                        'Status' => $row->status ?? 'Unknown',
                        'User Name' => $row->user_name ?? 'N/A',
                        'User Email' => $row->user_email ?? 'N/A',
                        'User Phone' => $row->user_phone ?? 'N/A'
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Caption',
                    'Processed Caption',
                    'Date',
                    'Category',
                    'Subcounty',
                    'Ward',
                    'Sentiment',
                    'Source',
                    'Status',
                    'User Name',
                    'User Email',
                    'User Phone'
                ];
            }
        }, 'complaints_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = $this->applyFilters($request);
        $complaints = $query->orderBy('timestamp', 'desc')->get()->map(function ($item) {
            return (object) [
                'original_caption' => htmlspecialchars($item->original_caption ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                'processed_caption' => htmlspecialchars($item->processed_caption ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                'timestamp' => $item->timestamp ? \Carbon\Carbon::parse($item->timestamp) : null,
                'complaint_category' => htmlspecialchars($item->complaint_category ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'subcounty' => htmlspecialchars($item->subcounty ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'ward' => htmlspecialchars($item->ward ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'overall_sentiment' => htmlspecialchars($item->overall_sentiment ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'source' => htmlspecialchars($item->source ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'status' => htmlspecialchars($item->status ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'user_name' => htmlspecialchars($item->user_name ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                'user_email' => htmlspecialchars($item->user_email ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                'user_phone' => htmlspecialchars($item->user_phone ?? 'N/A', ENT_QUOTES, 'UTF-8'),
            ];
        });

        $pdf = Pdf::loadView('exports.complaints-pdf', compact('complaints'));
        return $pdf->download('complaints_' . now()->format('Ymd_His') . '.pdf');
    }

    public function dashboard()
    {
        $departments = Department::all();
        return view('admin.dashboard', compact('departments'));
    }
}