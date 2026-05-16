// stats + charts

<canvas id="stockChart" height="100"></canvas>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('stockChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($chartData->pluck('date')),
        datasets: [
            {
                label: 'Stock In',
                data: @json($chartData->pluck('stock_in')),
                backgroundColor: '#1D9E75',
            },
            {
                label: 'Stock Out',
                data: @json($chartData->pluck('stock_out')),
                backgroundColor: '#D85A30',
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } }
    }
});
</script>
@endpush