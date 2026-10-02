<div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm max-w-2xl">
    <h2 class="text-lg font-bold text-slate-800 mb-4">Grafik Status Tugas</h2>
    
    <!-- Wadah Kanvas Grafik -->
    <div class="relative h-64 w-full flex justify-center">
        <canvas id="statusTugasChart"></canvas>
    </div>
</div>

<!-- Masukkan Script ini tepat SEBELUM </body> -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('statusTugasChart');
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending (50%)', 'Selesai (10%)', 'Revisi (40%)'],
                datasets: [{
                    label: 'Jumlah Tugas',
                    data: [5, 1, 4], // Data Anda: 5 Pending, 1 Selesai, 4 Revisi
                    backgroundColor: [
                        '#f59e0b', // Kuning (Pending)
                        '#10b981', // Hijau (Selesai)
                        '#ef4444'  // Merah (Revisi)
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                        }
                    }
                }
            }
        });
    });
</script>