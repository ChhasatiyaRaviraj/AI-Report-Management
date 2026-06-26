<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AI-powered business report generator with executive summaries and PDF export">
    <title>AI Report Generator — Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Custom animated background */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(108, 92, 231, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(0, 206, 201, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(108, 92, 231, 0.03) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
            animation: bgPulse 8s ease-in-out infinite alternate;
        }
        @keyframes bgPulse {
            0% { opacity: 0.6; }
            100% { opacity: 1; }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3); border-top: 2px solid #fff;
            border-radius: 50%; animation: spin-custom 0.8s linear infinite;
        }
        @keyframes spin-custom { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="font-['Inter'] bg-[#0a0a1a] text-[#e8e8f0] min-h-screen relative overflow-x-hidden">

<div class="relative z-10 max-w-6xl mx-auto px-6">

    <!-- Navbar -->
    <nav class="flex flex-col sm:flex-row items-center justify-between py-5 border-b border-[#6c5ce7]/15 mb-10 gap-3">
        <a href="{{ route('reports.index') }}" class="flex items-center gap-3 decoration-transparent">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl bg-gradient-to-br from-[#6c5ce7] to-[#00cec9] shadow-[0_0_20px_rgba(108,92,231,0.3)]">📊</div>
            <div class="text-xl font-bold tracking-tight text-[#e8e8f0]">
                <span class="bg-gradient-to-br from-[#6c5ce7] to-[#00cec9] text-transparent bg-clip-text">AI Report</span> Generator
            </div>
        </a>
        <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#00cec9]/10 border border-[#00cec9]/20 rounded-full text-xs font-medium text-[#00cec9]">
            <span class="w-1.5 h-1.5 bg-[#00cec9] rounded-full animate-pulse"></span>
            AI-Powered
        </div>
    </nav>

    <!-- Page Header -->
    <header class="text-center mb-10">
        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-3 bg-gradient-to-br from-[#e8e8f0] to-[#9898b8] text-transparent bg-clip-text">Business Performance Reports</h1>
        <p class="text-[#9898b8] max-w-2xl mx-auto text-base leading-relaxed">Generate styled PDF reports with detailed aggregated figures and an LLM AI-written executive summary tailored to your business.</p>
    </header>

    <!-- Generator Card -->
    <section class="mb-10">
        <div class="bg-[#16163a] border border-[#6c5ce7]/15 rounded-2xl overflow-hidden transition hover:border-[#6c5ce7]/40 hover:shadow-[0_0_30px_rgba(108,92,231,0.15)]">
            <div class="px-7 pt-6 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#6c5ce7]/15 flex items-center justify-center text-base">📅</div>
                <h2 class="text-lg font-bold tracking-tight text-[#e8e8f0]">Generate Report</h2>
            </div>
            <div class="p-7">
                {{-- Flash Messages --}}
                @if(session('error'))
                    <div class="p-3.5 mb-5 rounded-lg bg-[#e17055]/10 border border-[#e17055]/25 text-[#ff8a75] flex items-center gap-2.5 text-sm font-medium animate-[slideIn_0.3s_ease-out]">
                        <span class="text-lg shrink-0">⚠️</span>
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="p-3.5 mb-5 rounded-lg bg-[#00b894]/10 border border-[#00b894]/25 text-[#2de8c0] flex items-center gap-2.5 text-sm font-medium animate-[slideIn_0.3s_ease-out]">
                        <span class="text-lg shrink-0">✅</span>
                        {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="p-3.5 mb-5 rounded-lg bg-[#e17055]/10 border border-[#e17055]/25 text-[#ff8a75] flex items-start gap-2.5 text-sm font-medium animate-[slideIn_0.3s_ease-out]">
                        <span class="text-lg shrink-0">⚠️</span>
                        <div>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('reports.generate') }}" method="POST" id="reportForm">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-[13px] font-semibold text-[#9898b8] mb-2 uppercase tracking-wide" for="from_date">From Date</label>
                            <input type="date" id="from_date" name="from_date" class="w-full px-4 py-3 bg-[#1a1a3e] border border-[#6c5ce7]/15 rounded-lg text-[#e8e8f0] text-sm font-medium transition focus:border-[#6c5ce7] focus:ring-2 focus:ring-[#6c5ce7]/30 outline-none [color-scheme:dark]" value="{{ old('from_date', now()->subDays(30)->format('Y-m-d')) }}" required>
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-[#9898b8] mb-2 uppercase tracking-wide" for="to_date">To Date</label>
                            <input type="date" id="to_date" name="to_date" class="w-full px-4 py-3 bg-[#1a1a3e] border border-[#6c5ce7]/15 rounded-lg text-[#e8e8f0] text-sm font-medium transition focus:border-[#6c5ce7] focus:ring-2 focus:ring-[#6c5ce7]/30 outline-none [color-scheme:dark]" value="{{ old('to_date', now()->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="text-xs text-[#6868a0] mb-6">Changing dates updates the performance metrics below dynamically. Max range: 1 year.</div>

                    <button type="submit" class="w-full py-3.5 px-6 bg-gradient-to-br from-[#6c5ce7] to-[#00cec9] text-white rounded-lg font-bold text-[15px] flex items-center justify-center gap-2.5 transition hover:-translate-y-[1px] hover:shadow-[0_4px_20px_rgba(108,92,231,0.3)] group disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none overflow-hidden relative" id="generateBtn">
                        <div class="absolute top-0 left-[-100%] w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent transition-all duration-500 ease-in-out group-hover:left-[100%] z-0"></div>
                        <span class="group-[.loading]:hidden flex items-center gap-2 z-10">📄 Generate & Download PDF</span>
                        <span class="hidden group-[.loading]:flex items-center gap-2 z-10">Generating report...</span>
                        <span class="hidden group-[.loading]:block spinner z-10"></span>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Stats Grid -->
    <section class="mb-10">
        <h3 class="text-base font-bold text-[#9898b8] uppercase tracking-wide mb-4 flex items-center gap-2 after:content-[''] after:h-px after:bg-[#6c5ce7]/15 after:flex-grow after:ml-3">Period Metrics</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-[#16163a] border border-[#6c5ce7]/15 rounded-xl p-6 relative overflow-hidden transition hover:border-[#6c5ce7]/40 hover:-translate-y-0.5 hover:shadow-[0_0_30px_rgba(108,92,231,0.15)] group">
                <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-[#6c5ce7] to-[#00cec9] opacity-0 transition group-hover:opacity-100"></div>
                <div class="w-11 h-11 rounded-lg bg-[#6c5ce7]/15 flex items-center justify-center text-xl mb-4">💰</div>
                <div class="text-[26px] font-extrabold tracking-tight text-[#6c5ce7] mb-1 transition duration-300 [&.loading]:opacity-50 [&.loading]:blur-[2px]" id="stat-revenue">${{ number_format($stats['total_revenue'], 2) }}</div>
                <div class="text-[13px] text-[#6868a0] font-medium uppercase tracking-wide">Revenue</div>
            </div>
            <div class="bg-[#16163a] border border-[#6c5ce7]/15 rounded-xl p-6 relative overflow-hidden transition hover:border-[#6c5ce7]/40 hover:-translate-y-0.5 hover:shadow-[0_0_30px_rgba(108,92,231,0.15)] group">
                <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-[#00b894] to-[#00cec9] opacity-0 transition group-hover:opacity-100"></div>
                <div class="w-11 h-11 rounded-lg bg-[#00b894]/15 flex items-center justify-center text-xl mb-4">📦</div>
                <div class="text-[26px] font-extrabold tracking-tight text-[#00b894] mb-1 transition duration-300 [&.loading]:opacity-50 [&.loading]:blur-[2px]" id="stat-orders">{{ number_format($stats['total_orders']) }}</div>
                <div class="text-[13px] text-[#6868a0] font-medium uppercase tracking-wide">Orders</div>
            </div>
            <div class="bg-[#16163a] border border-[#6c5ce7]/15 rounded-xl p-6 relative overflow-hidden transition hover:border-[#6c5ce7]/40 hover:-translate-y-0.5 hover:shadow-[0_0_30px_rgba(108,92,231,0.15)] group">
                <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-[#fdcb6e] to-[#e17055] opacity-0 transition group-hover:opacity-100"></div>
                <div class="w-11 h-11 rounded-lg bg-[#fdcb6e]/15 flex items-center justify-center text-xl mb-4">🔄</div>
                <div class="text-[26px] font-extrabold tracking-tight text-[#fdcb6e] mb-1 transition duration-300 [&.loading]:opacity-50 [&.loading]:blur-[2px]" id="stat-returns">{{ number_format($stats['total_returns']) }}</div>
                <div class="text-[13px] text-[#6868a0] font-medium uppercase tracking-wide">Returns</div>
            </div>
            <div class="bg-[#16163a] border border-[#6c5ce7]/15 rounded-xl p-6 relative overflow-hidden transition hover:border-[#6c5ce7]/40 hover:-translate-y-0.5 hover:shadow-[0_0_30px_rgba(108,92,231,0.15)] group">
                <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-[#74b9ff] to-[#6c5ce7] opacity-0 transition group-hover:opacity-100"></div>
                <div class="w-11 h-11 rounded-lg bg-[#74b9ff]/15 flex items-center justify-center text-xl mb-4">🏷️</div>
                <div class="text-[26px] font-extrabold tracking-tight text-[#74b9ff] mb-1 transition duration-300 [&.loading]:opacity-50 [&.loading]:blur-[2px]" id="stat-products">{{ number_format($stats['products_count']) }}</div>
                <div class="text-[13px] text-[#6868a0] font-medium uppercase tracking-wide">Products Sold</div>
            </div>
        </div>
    </section>

    <!-- Details Grid -->
    <section class="grid grid-cols-1 lg:grid-cols-[1.2fr_0.8fr] gap-6 mb-16">
        <!-- Info Card -->
        <div class="bg-[#16163a] border border-[#6c5ce7]/15 rounded-2xl overflow-hidden transition hover:border-[#6c5ce7]/40 hover:shadow-[0_0_30px_rgba(108,92,231,0.15)] h-full">
            <div class="px-7 pt-6 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#6c5ce7]/15 flex items-center justify-center text-base">ℹ️</div>
                <h2 class="text-lg font-bold tracking-tight text-[#e8e8f0]">What's Included</h2>
            </div>
            <div class="p-7">
                <ul class="flex flex-col">
                    <li class="flex items-start gap-3 py-3 border-b border-[#6c5ce7]/10 text-sm text-[#9898b8] leading-relaxed">
                        <span class="shrink-0 w-7 h-7 rounded-md bg-[#6c5ce7]/10 flex items-center justify-center text-sm mt-px">🧠</span>
                        <span><strong class="text-[#e8e8f0]">AI Executive Summary</strong> — An LLM-generated narrative analyzing your key business metrics, trends, and actionable insights.</span>
                    </li>
                    <li class="flex items-start gap-3 py-3 border-b border-[#6c5ce7]/10 text-sm text-[#9898b8] leading-relaxed">
                        <span class="shrink-0 w-7 h-7 rounded-md bg-[#6c5ce7]/10 flex items-center justify-center text-sm mt-px">📈</span>
                        <span><strong class="text-[#e8e8f0]">Key Metrics Dashboard</strong> — Total revenue, order count, return rate, and refund totals at a glance.</span>
                    </li>
                    <li class="flex items-start gap-3 py-3 border-b border-[#6c5ce7]/10 text-sm text-[#9898b8] leading-relaxed">
                        <span class="shrink-0 w-7 h-7 rounded-md bg-[#6c5ce7]/10 flex items-center justify-center text-sm mt-px">📊</span>
                        <span><strong class="text-[#e8e8f0]">Revenue by Category</strong> — Breakdown of revenue and order distribution across all product categories.</span>
                    </li>
                    <li class="flex items-start gap-3 py-3 border-b border-[#6c5ce7]/10 text-sm text-[#9898b8] leading-relaxed">
                        <span class="shrink-0 w-7 h-7 rounded-md bg-[#6c5ce7]/10 flex items-center justify-center text-sm mt-px">🏆</span>
                        <span><strong class="text-[#e8e8f0]">Top Products</strong> — Your top 5 performing products ranked by revenue with order counts.</span>
                    </li>
                    <li class="flex items-start gap-3 pt-3 text-sm text-[#9898b8] leading-relaxed">
                        <span class="shrink-0 w-7 h-7 rounded-md bg-[#6c5ce7]/10 flex items-center justify-center text-sm mt-px">📑</span>
                        <span><strong class="text-[#e8e8f0]">PDF Export</strong> — Professionally formatted PDF ready for stakeholder presentations.</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- How It Works Card -->
        <div class="bg-[#16163a] border border-[#6c5ce7]/15 rounded-2xl p-7 flex flex-col justify-center h-full">
            <h2 class="text-xl font-bold mb-6 text-[#e8e8f0] flex items-center gap-2.5">⚙️ Process Flow</h2>
            <div class="flex flex-col gap-5">
                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-[#6c5ce7]/15 border border-[#6c5ce7]/15 flex items-center justify-center text-base font-bold text-[#7c6ef7] shrink-0">1</div>
                    <div class="flex flex-col">
                        <div class="text-[14px] font-bold text-[#e8e8f0] mb-1">Select Dates</div>
                        <div class="text-[13px] text-[#6868a0] leading-relaxed">Choose report start and end dates.</div>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-[#6c5ce7]/15 border border-[#6c5ce7]/15 flex items-center justify-center text-base font-bold text-[#7c6ef7] shrink-0">2</div>
                    <div class="flex flex-col">
                        <div class="text-[14px] font-bold text-[#e8e8f0] mb-1">Data Aggregation</div>
                        <div class="text-[13px] text-[#6868a0] leading-relaxed">Laravel compiles sales, orders, and refund transactions.</div>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-[#6c5ce7]/15 border border-[#6c5ce7]/15 flex items-center justify-center text-base font-bold text-[#7c6ef7] shrink-0">3</div>
                    <div class="flex flex-col">
                        <div class="text-[14px] font-bold text-[#e8e8f0] mb-1">AI Analysis</div>
                        <div class="text-[13px] text-[#6868a0] leading-relaxed">The LLM analyzes the aggregated data for performance trends.</div>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-[#6c5ce7]/15 border border-[#6c5ce7]/15 flex items-center justify-center text-base font-bold text-[#7c6ef7] shrink-0">4</div>
                    <div class="flex flex-col">
                        <div class="text-[14px] font-bold text-[#e8e8f0] mb-1">PDF Download</div>
                        <div class="text-[13px] text-[#6868a0] leading-relaxed">Python formats a clean, presentation-ready PDF report.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center pb-10 pt-6 border-t border-[#6c5ce7]/15 text-[#6868a0] text-[13px]">
        <p>AI Report Generator &mdash; Built with Laravel, Python, Grok AI & ReportLab</p>
    </footer>

</div>

<script>
    // Loading state for the generate button and fetch PDF
    document.getElementById('reportForm').addEventListener('submit', async function (e) {
        e.preventDefault(); // Stop default form submit to handle download manually
        
        const btn = document.getElementById('generateBtn');
        btn.classList.add('loading');
        btn.disabled = true;

        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: this.method,
                body: formData,
                headers: {
                    'Accept': 'application/pdf',
                }
            });
            
            if (response.ok) {
                let filename = 'report.pdf';
                const disposition = response.headers.get('Content-Disposition');
                if (disposition && disposition.indexOf('attachment') !== -1) {
                    const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                    if (matches != null && matches[1]) { 
                        filename = matches[1].replace(/['"]/g, '');
                    }
                }
                
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                
                window.URL.revokeObjectURL(url);
                a.remove();
            } else {
                alert('Failed to generate report. Please try again or check your dates.');
            }
        } catch (error) {
            console.error('Download error:', error);
            alert('An error occurred while generating the report.');
        } finally {
            btn.classList.remove('loading');
            btn.disabled = false;
        }
    });

    // Dynamic stats fetching
    const fromInput = document.getElementById('from_date');
    const toInput = document.getElementById('to_date');
    const statRevenue = document.getElementById('stat-revenue');
    const statOrders = document.getElementById('stat-orders');
    const statReturns = document.getElementById('stat-returns');
    const statProducts = document.getElementById('stat-products');

    async function updateStats() {
        const from = fromInput.value;
        const to = toInput.value;

        if (!from || !to) return;
        if (from > to) return;

        statRevenue.classList.add('loading');
        statOrders.classList.add('loading');
        statReturns.classList.add('loading');
        statProducts.classList.add('loading');

        try {
            const url = `{{ route('reports.stats') }}?from_date=${from}&to_date=${to}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();

            statRevenue.textContent = data.total_revenue;
            statOrders.textContent = data.total_orders;
            statReturns.textContent = data.total_returns;
            statProducts.textContent = data.products_count;
        } catch (error) {
            console.error('Failed to fetch stats:', error);
        } finally {
            statRevenue.classList.remove('loading');
            statOrders.classList.remove('loading');
            statReturns.classList.remove('loading');
            statProducts.classList.remove('loading');
        }
    }

    fromInput.addEventListener('change', updateStats);
    toInput.addEventListener('change', updateStats);

    toInput.addEventListener('change', function () {
        const from = fromInput.value;
        const to = this.value;

        if (from && to && from > to) {
            this.setCustomValidity('End date must be on or after the start date');
        } else {
            this.setCustomValidity('');
        }
    });

    fromInput.addEventListener('change', function () {
        const to = toInput.value;

        if (to && this.value > to) {
            toInput.setCustomValidity('End date must be on or after the start date');
        } else {
            toInput.setCustomValidity('');
        }
    });

    if (fromInput.value && toInput.value) {
        updateStats();
    }
</script>
</body>
</html>
