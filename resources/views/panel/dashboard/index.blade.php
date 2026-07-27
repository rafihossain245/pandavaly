@extends('layout.app')

@section('meta-information')
    <title>Dashboard</title>
@endsection

@section('import-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection


@section('main-content')    
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Welcome John 👋</h1>
        <p class="text-gray-600">Look Out! Here's what's happening today.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-500">Total Sales</p>
                    <p class="text-2xl font-bold text-gray-800">$24,580</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-dollar-sign text-blue-600"></i>
                </div>
            </div>
            <p class="text-xs text-green-500 mt-2"><i class="fas fa-arrow-up mr-1"></i> 12.5% from last month</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-green-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-500">Total Users</p>
                    <p class="text-2xl font-bold text-gray-800">1,248</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-users text-green-600"></i>
                </div>
            </div>
            <p class="text-xs text-green-500 mt-2"><i class="fas fa-arrow-up mr-1"></i> 8.2% from last month</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-purple-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-500">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-800">856</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-shopping-cart text-purple-600"></i>
                </div>
            </div>
            <p class="text-xs text-red-500 mt-2"><i class="fas fa-arrow-down mr-1"></i> 2.3% from last month</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-yellow-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-500">Total Tickets Sold</p>
                    <p class="text-2xl font-bold text-gray-800">3,674</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-ticket-alt text-yellow-600"></i>
                </div>
            </div>
            <p class="text-xs text-green-500 mt-2"><i class="fas fa-arrow-up mr-1"></i> 15.7% from last month</p>
        </div>
    </div>
    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 p-4">
        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-lg font-semibold mb-2">Weekly Sales</h2>
            <canvas id="lineChart"></canvas>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-lg font-semibold mb-2">User Growth</h2>
            <canvas id="barChart"></canvas>
        </div>
    </div>
@endsection


@section('raw-script')
    <!-- Chart.js Scripts -->
    <script>
        // Line Chart
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Sales',
                    data: [120, 190, 300, 500, 200, 300, 400],
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            }
        });

        // Bar Chart
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Users',
                    data: [300, 400, 200, 500, 700, 600],
                    backgroundColor: 'rgba(34, 197, 94, 0.7)'
                }]
            }
        });
    </script>
@endsection
