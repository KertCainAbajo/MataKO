<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityController extends Controller
{
    /** @var array<string, array{0: string, 1: list<string>}> Filter tabs: label and the action prefixes they match. */
    public const TYPES = [
        'signins' => ['Sign-ins', ['login']],
        'users' => ['Users', ['user.']],
        'questions' => ['Questions', ['question.']],
        'content' => ['App content', ['content.']],
        'results' => ['Results', ['assessment.']],
    ];

    /** @var array<string, array{0: string, 1: int|null}> Time ranges: label and number of days (null = all time). */
    public const RANGES = [
        'today' => ['Today', 0],
        '7d' => ['Last 7 days', 7],
        '30d' => ['Last 30 days', 30],
        'all' => ['All time', null],
    ];

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $tz = config('app.display_timezone');

        $counts = [];
        foreach (array_keys(self::TYPES) as $key) {
            $counts[$key] = $this->filtered(['type' => $key] + $filters)->count();
        }

        // Actions per day for the last 14 days, in the display time zone.
        $since = now($tz)->startOfDay()->subDays(13);
        $perDay = AdminActivity::where('created_at', '>=', $since->copy()->utc())->get(['created_at'])
            ->countBy(fn (AdminActivity $activity): string => $activity->created_at->copy()->timezone($tz)->toDateString());
        $daily = collect(range(0, 13))->map(fn (int $offset): array => [
            'date' => $since->copy()->addDays($offset),
            'total' => $perDay[$since->copy()->addDays($offset)->toDateString()] ?? 0,
        ]);

        return view('admin.activity', [
            'activities' => $this->filtered($filters)->with('admin:id,name')->latest('created_at')->latest('id')->paginate(25)->withQueryString(),
            'filters' => $filters,
            'counts' => $counts,
            'daily' => $daily,
            'admins' => User::where('is_admin', true)->orderBy('name')->get(['id', 'name']),
            'stats' => [
                'total' => AdminActivity::count(),
                'today' => AdminActivity::where('created_at', '>=', now($tz)->startOfDay()->utc())->count(),
                'week' => AdminActivity::where('created_at', '>=', now()->subDays(7))->count(),
                'changes' => AdminActivity::where('action', '!=', 'login')->count(),
            ],
            'topAdmins' => AdminActivity::with('admin:id,name')
                ->select('admin_id', DB::raw('count(*) as total'), DB::raw('max(created_at) as last_at'))
                ->whereNotNull('admin_id')
                ->groupBy('admin_id')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * Download the filtered activity as a CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($this->filters($request))->with('admin:id,name')->oldest('created_at');
        $tz = config('app.display_timezone');

        return response()->streamDownload(function () use ($query, $tz): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Date', 'Time', 'Admin', 'Type', 'What happened']);
            $query->chunk(200, function ($activities) use ($output, $tz): void {
                foreach ($activities as $activity) {
                    $when = $activity->created_at->copy()->timezone($tz);
                    fputcsv($output, [$when->toDateString(), $when->format('H:i:s'), $activity->admin?->name ?? 'Removed admin', self::typeOf($activity->action), $activity->description]);
                }
            });
            fclose($output);
        }, 'matako-activity-'.now($tz)->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * The label of the filter type an action belongs to.
     */
    public static function typeOf(string $action): string
    {
        foreach (self::TYPES as [$label, $prefixes]) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($action, $prefix)) {
                    return $label;
                }
            }
        }

        return 'Other';
    }

    /**
     * @return array{type: ?string, range: string, admin: ?int, search: string}
     */
    private function filters(Request $request): array
    {
        return [
            'type' => array_key_exists($request->query('type'), self::TYPES) ? $request->query('type') : null,
            'range' => array_key_exists($request->query('range'), self::RANGES) ? $request->query('range') : 'all',
            'admin' => $request->integer('admin') ?: null,
            'search' => trim((string) $request->query('search', '')),
        ];
    }

    /**
     * @param  array{type: ?string, range: string, admin: ?int, search: string}  $filters
     * @return Builder<AdminActivity>
     */
    private function filtered(array $filters): Builder
    {
        $days = self::RANGES[$filters['range']][1];
        $tz = config('app.display_timezone');

        return AdminActivity::query()
            ->when($filters['type'], fn ($query, $type) => $query->where(function ($inner) use ($type) {
                foreach (self::TYPES[$type][1] as $prefix) {
                    $inner->orWhere('action', 'like', $prefix.'%');
                }
            }))
            ->when($days !== null, fn ($query) => $query->where('created_at', '>=', $days === 0 ? now($tz)->startOfDay()->utc() : now()->subDays($days)))
            ->when($filters['admin'], fn ($query, $admin) => $query->where('admin_id', $admin))
            ->when($filters['search'] !== '', fn ($query) => $query->where('description', 'like', '%'.$filters['search'].'%'));
    }
}
