@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
@endpush

@php
    // Preview data until the dashboard API is available.
    $today = now('Asia/Amman')->startOfDay();
    $metrics = [
        ['Products', 24, 'box-seam', 'Catalog items'],
        ['Orders', 18, 'bag', 'Across all order statuses'],
        ['Customers', 12, 'people', 'Registered customer accounts'],
        ['Categories', 4, 'tags', 'Catalog categories'],
    ];
    $actions = [
        ['Manage Categories', 'Organize your catalog', 'tags', 'admin.categories.index'],
        ['Manage Products', 'Update prices, stock and details', 'box-seam', 'admin.products.index'],
        ['Manage Orders', 'Review orders and update their status', 'receipt', 'admin.orders.index'],
    ];
    $recentOrders = [
        [1058, 'Noor Saleh', 0, 84.90, 'Processing'],
        [1057, 'Omar Nasser', 0, 129.00, 'Delivered'],
        [1056, 'Lina Haddad', 1, 59.50, 'Pending'],
        [1055, 'Adam Khalil', 1, 98.50, 'Delivered'],
        [1054, 'Sara Hamdan', 2, 74.00, 'Delivered'],
    ];
    $revenue = [
        [1, 32.00], [0, 0.00], [2, 118.50], [1, 45.00], [1, 74.00], [1, 98.50], [1, 129.00],
    ];
    $weeklyRevenue = array_sum(array_column($revenue, 1));
    $weeklyOrders = array_sum(array_column($revenue, 0));
    $peakRevenue = max(1, ...array_column($revenue, 1));
@endphp

@section('topbar-description')
    <p class="dashboard-description">A quick overview of your store, orders, and daily tasks.</p>
@endsection

@section('topbar-end')
    <time class="dashboard-date" datetime="{{ $today->toDateString() }}">
        <i class="bi bi-calendar3" aria-hidden="true"></i>
        {{ $today->format('d M Y') }}
    </time>
@endsection

@section('content')
    <div class="dashboard">
        <dl class="dashboard-metrics" aria-label="Store totals">
            @foreach ($metrics as [$label, $value, $icon, $description])
                <div class="dashboard-card dashboard-metric">
                    <dt>{{ $label }}</dt>
                    <dd>{{ number_format($value) }}</dd>
                    <dd class="dashboard-metric-description">{{ $description }}</dd>
                    <i class="bi bi-{{ $icon }} dashboard-icon" aria-hidden="true"></i>
                </div>
            @endforeach
        </dl>

        <div class="dashboard-grid">
            <section class="dashboard-card" aria-labelledby="revenue-title">
                <header class="dashboard-card-heading">
                    <div>
                        <h2 id="revenue-title">Revenue Overview</h2>
                        <p>Last 7 days · delivered orders only</p>
                    </div>
                    <span class="dashboard-period">{{ $today->copy()->subDays(6)->format('d M') }} – {{ $today->format('d M') }}</span>
                </header>
                <p class="dashboard-revenue-total">{{ number_format($weeklyRevenue, 2) }} <small>JOD</small></p>
                <p class="dashboard-chart-note">Order totals by order date, including delivery fees.</p>

                <div class="dashboard-chart" aria-hidden="true">
                    @foreach ($revenue as [$count, $amount])
                        @php($day = $today->copy()->subDays(6 - $loop->index))
                        <div class="dashboard-chart-column">
                            <span>{{ number_format($amount, 2) }}</span>
                            <div class="dashboard-chart-track">
                                <div class="dashboard-chart-bar" style="--bar-height: {{ round($amount / $peakRevenue * 100, 2) }}%"></div>
                            </div>
                            <strong>{{ $day->format('D') }}</strong>
                            <small>{{ $day->format('d M') }}</small>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="dashboard-card dashboard-quick" aria-labelledby="quick-title">
                <header class="dashboard-card-heading">
                    <div><h2 id="quick-title">Quick Actions</h2><p>Jump to the main store management screens.</p></div>
                    <i class="bi bi-lightning-charge dashboard-icon" aria-hidden="true"></i>
                </header>
                <ul class="dashboard-actions">
                    @foreach ($actions as [$label, $description, $icon, $route])
                        <li>
                            @if (Route::has($route))
                                <a class="dashboard-action" href="{{ route($route) }}">
                            @else
                                <div class="dashboard-action" aria-disabled="true">
                            @endif
                                <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
                                <div><strong>{{ $label }}</strong><small>{{ $description }}</small></div>
                            @if (Route::has($route))
                                </a>
                            @else
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="dashboard-card" aria-labelledby="recent-title">
                <header class="dashboard-card-heading">
                    <div><h2 id="recent-title">Recent Orders</h2><p>The latest customer orders.</p></div>
                    @if (Route::has('admin.orders.index'))
                        <a class="dashboard-all-orders" href="{{ route('admin.orders.index') }}">View all orders <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </header>
                <div class="dashboard-table-wrap" role="region" aria-labelledby="recent-title" tabindex="0">
                    <table class="dashboard-table dashboard-orders">
                        <thead>
                            <tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Date</th><th scope="col">Total</th><th scope="col">Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as [$id, $customer, $daysAgo, $total, $status])
                                <tr>
                                    <th scope="row">#GC-{{ $id }}</th>
                                    <td>{{ $customer }}</td>
                                    <td>{{ $today->copy()->subDays($daysAgo)->format('d M Y') }}</td>
                                    <td>{{ number_format($total, 2) }} JOD</td>
                                    <td><span class="dashboard-status dashboard-status--{{ strtolower($status) }}">{{ $status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5">No orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="dashboard-card" aria-labelledby="daily-title">
                <header class="dashboard-card-heading">
                    <div><h2 id="daily-title">Daily Revenue</h2><p>By order date · delivered orders only</p></div>
                </header>
                <div class="dashboard-table-wrap">
                    <table class="dashboard-table">
                        <thead>
                            <tr><th scope="col">Date</th><th scope="col" class="dashboard-number">Delivered Orders</th><th scope="col" class="dashboard-number">Revenue (JOD)</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($revenue as [$count, $amount])
                                <tr>
                                    <th scope="row">{{ $today->copy()->subDays(6 - $loop->index)->format('d M') }}</th>
                                    <td class="dashboard-number">{{ $count }}</td>
                                    <td class="dashboard-number">{{ number_format($amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><th scope="row">7-day total</th><td class="dashboard-number">{{ $weeklyOrders }}</td><td class="dashboard-number">{{ number_format($weeklyRevenue, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
