// Common Chart Defaults for Dark Theme
Chart.defaults.color = '#94A3B8';
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.plugins.tooltip.backgroundColor = '#111827';
Chart.defaults.plugins.tooltip.titleColor = '#F8FAFC';
Chart.defaults.plugins.tooltip.bodyColor = '#94A3B8';
Chart.defaults.plugins.tooltip.borderColor = '#1E293B';
Chart.defaults.plugins.tooltip.borderWidth = 1;
Chart.defaults.plugins.tooltip.padding = 12;
Chart.defaults.plugins.tooltip.cornerRadius = 8;
Chart.defaults.plugins.tooltip.displayColors = true;

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 300);
revenueGradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
revenueGradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Revenue',
            data: [42000, 48000, 51000, 49000, 58000, 62000, 65000, 61000, 72000, 78000, 82000, 89691],
            borderColor: '#2563EB',
            backgroundColor: revenueGradient,
            borderWidth: 2,
            pointBackgroundColor: '#2563EB',
            pointBorderColor: '#111827',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            tension: 0.4
        }, {
            label: 'Previous Period',
            data: [38000, 41000, 45000, 44000, 50000, 53000, 58000, 55000, 60000, 65000, 68000, 79800],
            borderColor: '#1E293B',
            borderWidth: 2,
            borderDash: [5, 5],
            pointRadius: 0,
            pointHoverRadius: 4,
            fill: false,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                }
            },
            y: {
                grid: {
                    color: '#1E293B',
                    drawBorder: false
                },
                ticks: {
                    callback: function(value) {
                        return '$' + (value / 1000) + 'k';
                    }
                }
            }
        }
    }
});

// Category Donut Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: ['Enterprise License', 'Pro Subscription', 'API Add-on', 'Custom Implementation'],
        datasets: [{
            data: [45200, 28450, 12800, 8500],
            backgroundColor: [
                '#2563EB', // Primary
                '#3B82F6', // Lighter primary
                '#60A5FA', // Even lighter
                '#1E293B'  // Border color as neutral
            ],
            borderWidth: 0,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    boxWidth: 8
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed);
                    }
                }
            }
        }
    }
});

// Orders Bar Chart
const ordersCtx = document.getElementById('ordersChart').getContext('2d');
new Chart(ordersCtx, {
    type: 'bar',
    data: {
        labels: ['Enterprise', 'Pro Sub', 'API Add-on', 'Custom'],
        datasets: [{
            label: 'Orders',
            data: [84, 569, 245, 12],
            backgroundColor: '#2563EB',
            borderRadius: 4,
            barThickness: 16
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                grid: {
                    color: '#1E293B',
                    drawBorder: false
                }
            },
            y: {
                grid: {
                    display: false,
                    drawBorder: false
                }
            }
        }
    }
});

// Button Loader Logic
function setupButtonLoader(buttonId, durationMs) {
    const btn = document.getElementById(buttonId);
    if (!btn) return;

    btn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Prevent multiple clicks
        if (btn.classList.contains('loading')) return;
        
        btn.classList.add('loading');
        
        // Simulate network request / report generation / download
        setTimeout(() => {
            btn.classList.remove('loading');
            
            // Optional: show a success state temporarily
            const btnText = btn.querySelector('.btn-text');
            if (btnText) {
                // Save original text if not already saved
                if (!btnText.dataset.originalText) {
                    btnText.dataset.originalText = btnText.innerText;
                }
                
                btnText.innerText = 'Completed!';
                
                setTimeout(() => {
                    btnText.innerText = btnText.dataset.originalText;
                }, 2000);
            }
            
        }, durationMs);
    });
}

// Setup loaders for buttons
setupButtonLoader('generateBtn', 2000);
setupButtonLoader('downloadBtn', 2500);
