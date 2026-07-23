@extends('layouts.app')

@section('title', 'Administrator Dashboard')

@push('styles')
    <style>
        .dashboard-hero {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.35), rgba(15, 23, 42, 0.7));
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1rem;
            padding: 1rem;
        }

        .metric-card {
            background: rgba(15, 23, 42, 0.76);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.95rem;
            padding: 1rem;
            height: 100%;
        }

        .metric-card__value {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
        }

        .metric-card__value--new { color: #6095df; }
        .metric-card__value--process { color: #f59e0b; }
        .metric-card__value--done { color: #22c55e; }
        .metric-card__value--hold { color: #ef4444; }

        .chart-card {
            background: rgba(15, 23, 42, 0.76);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1rem;
            padding: 1rem;
            height: 100%;
        }

        .chart-box { position: relative; min-height: 320px; }
        .date-filter-card {
            background: rgba(15, 23, 42, 0.76);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1rem;
            padding: 1rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.7rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .summary-row:last-child { border-bottom: 0; }
        .summary-row__name { color: #f8fafc; font-weight: 600; }
        .summary-row__meta { color: #cbd5e1; font-size: 0.9rem; }
    </style>
@endpush

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => 'Dashboard'])

    <main class="d-grid gap-3 flex-grow-1">
        <!-- <section class="dashboard-hero">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-start align-items-md-center">
                <div>
                    <p class="text-uppercase small text-info mb-1">Administrator Area</p>
                    <h1 class="h3 mb-2">Administrator Dashboard</h1>
                    <p class="mb-0 text-light-emphasis">Selamat datang, {{ auth()->user()->name }}. Dashboard ini mengikuti planner yang sedang login, dengan filter tanggal tetap aktif untuk statistik task detail.</p>
                </div>

                <div class="text-md-end">
                    <div class="small text-light-emphasis">Current planner</div>
                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                </div>
            </div>
        </section> -->

        <section class="date-filter-card">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-end">
                <div class="col-6 col-md-6 mt-1">
                    <label for="start_date" class="form-label small text-light mb-1">Start date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-6 col-md-6 mt-1">
                    <label for="end_date" class="form-label small text-light mb-1">End date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                </div>

                <input type="hidden" name="planned_by" value="{{ auth()->id() }}">

                <div class="col-3 col-md-2 d-grid gap-2 mt-3">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light">Reset</a>
                </div>
                <div class="col-9 col-md-2 d-grid gap-2 mt-3">
                    <button type="submit" class="btn btn-app">Apply</button>
                </div>
            </form>
        </section>

        <section class="row g-3">
            @foreach ($statusLabels as $status => $label)
                @php
                    $statusClass = match ($status) {
                        0 => 'metric-card__value--new',
                        1 => 'metric-card__value--process',
                        2 => 'metric-card__value--done',
                        3 => 'metric-card__value--hold',
                        default => '',
                    };
                @endphp
                <div class="col-6 col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <div class="small text-light text-uppercase">{{ $label }}</div>
                        <div class="metric-card__value mt-2 {{ $statusClass }}">{{ $statusCounts[$status] ?? 0 }} Task</div>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="row g-3">
            <div class="col-12 col-xl-8">
                <div class="chart-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="h5 mb-1">Planning vs Realization</h2>
                            <p class="small text-light-emphasis mb-0">Counts are grouped by day across the selected range.</p>
                        </div>
                    </div>
                    <div class="chart-box">
                        <canvas id="planningRealizationChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="chart-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="h5 mb-1">Task detail by category</h2>
                            <p class="small text-light-emphasis mb-0">Share of filtered task details.</p>
                        </div>
                    </div>
                    <div class="chart-box mb-3" style="min-height: 260px;">
                        <canvas id="categoryChart"></canvas>
                    </div>

                    <div>
                        @forelse ($categoryStats as $category)
                            <div class="summary-row">
                                <div>
                                    <div class="summary-row__name">{{ $category['name'] }}</div>
                                    <div class="summary-row__meta">{{ $category['percentage'] }}% of total</div>
                                </div>
                                <div class="fw-semibold">{{ $category['total'] }}</div>
                            </div>
                        @empty
                            <div class="text-light-emphasis small">No category data for the selected period.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
<!-- 
        <section class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h5 mb-1">Summary by Users</h2>
                    <p class="small text-light-emphasis mb-0">{{ $totalTaskDetails }} task detail record(s) matched the selected range.</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-borderless align-middle mb-0">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th class="text-end" width="20%">Total</th>
                            <th class="text-end" width="20%">Unfin.</th>
                            <th class="text-end" width="20%">Fin.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($summaryStats as $summary)
                            <tr>
                                <td>{{ $summary['name'] }}</td>
                                <td class="text-end">{{ $summary['total_task'] }}</td>
                                <td class="text-end">{{ $summary['unfinished'] }}</td>
                                <td class="text-end">{{ $summary['finished'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-light-emphasis">No summary data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section> -->
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script type="application/json" id="dashboard-chart-data">
        @php
            echo json_encode([
                'lineLabels' => $lineChartLabels,
                'planningSeries' => $planningSeries,
                'realizationSeries' => $realizationSeries,
                'categoryLabels' => $categoryChartLabels,
                'categoryTotals' => $categoryChartTotals,
            ], JSON_UNESCAPED_UNICODE);
        @endphp
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const planningRealizationCtx = document.getElementById('planningRealizationChart');
            const categoryCtx = document.getElementById('categoryChart');
            const dashboardChartDataElement = document.getElementById('dashboard-chart-data');
            const dashboardChartData = dashboardChartDataElement ? JSON.parse(dashboardChartDataElement.textContent || '{}') : {};

            const lineLabels = dashboardChartData.lineLabels || [];
            const planningSeries = dashboardChartData.planningSeries || [];
            const realizationSeries = dashboardChartData.realizationSeries || [];
            const categoryLabels = dashboardChartData.categoryLabels || [];
            const categoryTotals = dashboardChartData.categoryTotals || [];

            if (planningRealizationCtx) {
                new Chart(planningRealizationCtx, {
                    type: 'line',
                    data: {
                        labels: lineLabels,
                        datasets: [
                            {
                                label: 'Planning',
                                data: planningSeries,
                                borderColor: '#60a5fa',
                                backgroundColor: 'rgba(96, 165, 250, 0.15)',
                                tension: 0.35,
                                fill: false,
                            },
                            {
                                label: 'Realization',
                                data: realizationSeries,
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34, 197, 94, 0.15)',
                                tension: 0.35,
                                fill: false,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#f8fafc',
                                },
                            },
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: '#cbd5e1',
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.08)',
                                },
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#cbd5e1',
                                    precision: 0,
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.08)',
                                },
                            },
                        },
                    },
                });
            }

            if (categoryCtx) {
                new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: categoryLabels,
                        datasets: [{
                            data: categoryTotals,
                            backgroundColor: [
                                '#38bdf8',
                                '#22c55e',
                                '#f59e0b',
                                '#ef4444',
                                '#a855f7',
                                '#14b8a6',
                                '#f97316',
                                '#84cc16',
                            ],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#f8fafc',
                                },
                            },
                        },
                        cutout: '62%',
                    },
                });
            }
        });
    </script>
@endpush
