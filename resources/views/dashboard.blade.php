<x-app-layout>
    <div class="md:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="text-sm text-gray-500 my-2 ml-2" aria-label="Breadcrumb">
                <ol class="list-reset flex">
                    <li><a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Dashboard</a></li>
                </ol>
            </nav>

            <div class="bg-white shadow-md rounded-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard</h2>

                <!-- Filtro por período -->
                <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
                    <div>
                        <label for="start_date" class="text-sm text-gray-600">Início</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}"
                               class="border rounded px-3 py-1.5 text-sm">
                    </div>
                    <div>
                        <label for="end_date" class="text-sm text-gray-600">Fim</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date', now()->endOfDay()->format('Y-m-d')) }}"
                               class="border rounded px-3 py-1.5 text-sm">
                    </div>
                    <button type="submit"
                            class="bg-indigo-600 text-white px-4 py-2 text-sm rounded hover:bg-indigo-700">Aplicar Filtro</button>
                </form>

                <!-- Cartões principais -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-8">
                    <div class="flex items-center bg-gray-100 p-4 rounded-md">
                        <i data-lucide="user" class="w-6 h-6 mr-3 text-gray-600"></i>
                        <div>
                            <p class="text-sm text-gray-500">Usuários</p>
                            <p class="text-xl font-semibold">{{ $totalUsers }}</p>
                        </div>
                    </div>
                    <div class="flex items-center bg-gray-100 p-4 rounded-md">
                        <i data-lucide="building" class="w-6 h-6 mr-3 text-gray-600"></i>
                        <div>
                            <p class="text-sm text-gray-500">Unidades</p>
                            <p class="text-xl font-semibold">{{ $totalUnits }}</p>
                        </div>
                    </div>
                    <div class="flex items-center bg-green-100 p-4 rounded-md">
                        <i data-lucide="package-check" class="w-6 h-6 mr-3 text-green-700"></i>
                        <div>
                            <p class="text-sm text-green-700">Tickets Emitidos</p>
                            <p class="text-xl font-semibold text-green-800">{{ $totalDelivered }}</p>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-md font-semibold text-gray-700 mb-2">Tickets por Unidade</h3>
                        <canvas id="deliveriesByUnitChart"></canvas>
                    </div>
                    <div>
                        <h3 class="text-md font-semibold text-gray-700 mb-2">Tickets por Status</h3>
                        <canvas id="deliveriesByStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const unitCtx = document.getElementById('deliveriesByUnitChart');
            new Chart(unitCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($deliveriesByUnit->keys()) !!},
                    datasets: [{
                        label: 'Entregas',
                        data: {!! json_encode($deliveriesByUnit->values()) !!},
                        backgroundColor: '#3B82F6'
                    }]
                },
                options: {
                    responsive: true,
                    indexAxis: 'y'
                }
            });

            const statusCtx = document.getElementById('deliveriesByStatusChart');
            new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: {!! json_encode($deliveriesByStatus->keys()->map(fn($s) => __(match($s) {
                        'PENDING' => 'Pendente',
                        'DELIVERED' => 'Entregue',
                        'EXPIRED' => 'Expirado',
                        'REISSUED' => 'Reemitido',
                        default => $s,
                    }))) !!},
                    datasets: [{
                        data: {!! json_encode($deliveriesByStatus->values()) !!},
                        backgroundColor: ['#F59E0B', '#EF4444', '#10B981', '#6366F1']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const value = context.parsed;
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${context.label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        </script>
    @endpush
</x-app-layout>
