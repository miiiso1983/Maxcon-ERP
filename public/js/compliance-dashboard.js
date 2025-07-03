// Compliance Dashboard Charts
function initializeComplianceTrendsChart(trendsData) {
    if (!trendsData || trendsData.length === 0) {
        return;
    }

    const trendsCtx = document.getElementById('complianceTrendsChart').getContext('2d');
    const trendsChart = new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: trendsData.map(item => item.month),
            datasets: [
                {
                    label: window.translations?.newItems || 'New Items',
                    data: trendsData.map(item => item.total_items),
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.1
                },
                {
                    label: window.translations?.violations || 'Violations',
                    data: trendsData.map(item => item.violations),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.1
                },
                {
                    label: window.translations?.inspections || 'Inspections',
                    data: trendsData.map(item => item.inspections),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Auto-refresh dashboard every 10 minutes
function initializeDashboardAutoRefresh() {
    setInterval(function() {
        location.reload();
    }, 600000);
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboardAutoRefresh();
});
