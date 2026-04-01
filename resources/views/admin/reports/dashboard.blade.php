@extends('layouts.app')

@section('title', 'التقارير والتحليلات')

@section('heading', 'التقارير والتحليلات المتقدمة')

@section('content')
    <div class="card mb-6">
        <form method="get" class="grid gap-3 md:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="label-dark">من</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="input-dark">
            </div>
            <div>
                <label class="label-dark">إلى</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="input-dark">
            </div>
            @if (auth()->user()?->isAdmin())
                <div>
                    <label class="label-dark">موظف</label>
                    <select name="employee_id" class="input-dark">
                        <option value="">الكل</option>
                        @foreach ($employees as $em)
                            <option value="{{ $em->id }}" @selected(($filters['employee_id'] ?? null) == $em->id)>{{ $em->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="label-dark">نوع الخدمة</label>
                <select name="service_type" class="input-dark">
                    <option value="">الكل</option>
                    @foreach ($serviceTypes as $st)
                        <option value="{{ $st }}" @selected(($filters['service_type'] ?? '') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-gold w-full">تحديث</button>
            </div>
        </form>
    </div>

    @php
        $k = $data['kpis'];
    @endphp

    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card border-[#d4af37]/25 bg-gradient-to-br from-zinc-900 to-black">
            <p class="text-xs uppercase tracking-widest text-[#d4af37]/80">إجمالي السجلات</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ number_format($k['total']) }}</p>
        </div>
        <div class="card">
            <p class="text-xs uppercase tracking-widest text-zinc-500">اليوم / الأسبوع / الشهر</p>
            <p class="mt-2 text-lg font-semibold text-white">
                {{ number_format($k['today']) }}
                <span class="text-zinc-500">/</span>
                {{ number_format($k['week']) }}
                <span class="text-zinc-500">/</span>
                {{ number_format($k['month']) }}
            </p>
        </div>
        <div class="card">
            <p class="text-xs uppercase tracking-widest text-zinc-500">الموظفون النشطون</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ number_format($k['active_employees']) }}</p>
        </div>
        <div class="card">
            <p class="text-xs uppercase tracking-widest text-zinc-500">معدل رفض سابق</p>
            <p class="mt-2 text-3xl font-bold text-amber-200">
                {{ $k['rejection_rate'] !== null ? $k['rejection_rate'].'%' : '—' }}
            </p>
            <p class="text-xs text-zinc-500">متوسط العمر: {{ $k['avg_age'] ?? '—' }}</p>
        </div>
    </div>

    @if (! empty($data['employee_performance']))
        <div class="card mb-8 overflow-x-auto">
            <h3 class="mb-2 text-sm font-semibold text-[#d4af37]">مؤشرات أداء الموظفين</h3>
            <p class="mb-4 text-xs text-zinc-500">هيكل صفوف جاهز لتصدير CSV/Excel مستقبلًا (user_id، الاسم، عدد التقديمات، متوسط العمر).</p>
            <table class="min-w-full divide-y divide-zinc-800 text-sm">
                <thead class="bg-zinc-900/60 text-xs uppercase text-zinc-500">
                    <tr>
                        <th class="px-3 py-2 text-right">المعرّف</th>
                        <th class="px-3 py-2 text-right">الاسم</th>
                        <th class="px-3 py-2 text-right">التقديمات</th>
                        <th class="px-3 py-2 text-right">متوسط عمر العملاء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @foreach ($data['employee_performance'] as $row)
                        <tr class="hover:bg-zinc-900/40">
                            <td class="px-3 py-2 text-zinc-500" dir="ltr">{{ $row['user_id'] }}</td>
                            <td class="px-3 py-2 font-medium text-white">{{ $row['name'] }}</td>
                            <td class="px-3 py-2">{{ number_format($row['submissions']) }}</td>
                            <td class="px-3 py-2">{{ $row['avg_age'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="card">
            <h3 class="mb-4 text-sm font-semibold text-[#d4af37]">اتجاه التقديمات (يومي)</h3>
            <canvas id="chartTrendDay" height="120"></canvas>
        </div>
        <div class="card">
            <h3 class="mb-4 text-sm font-semibold text-[#d4af37]">اتجاه التقديمات (أسبوعي)</h3>
            <canvas id="chartTrendWeek" height="120"></canvas>
        </div>
        <div class="card">
            <h3 class="mb-4 text-sm font-semibold text-[#d4af37]">توزيع أنواع الخدمة</h3>
            <canvas id="chartService" height="140"></canvas>
        </div>
        <div class="card">
            <h3 class="mb-4 text-sm font-semibold text-[#d4af37]">توزيع الحالات</h3>
            <canvas id="chartStatus" height="140"></canvas>
        </div>
        <div class="card">
            <h3 class="mb-4 text-sm font-semibold text-[#d4af37]">قمع الحالات (تحويل)</h3>
            <canvas id="chartFunnel" height="160"></canvas>
        </div>
        <div class="card">
            <h3 class="mb-4 text-sm font-semibold text-[#d4af37]">أعلى الموظفين إنتاجًا</h3>
            <canvas id="chartEmployees" height="160"></canvas>
        </div>
        <div class="card">
            <h3 class="mb-4 text-sm font-semibold text-[#d4af37]">الجنسية / التأشيرات / الرفض</h3>
            <canvas id="chartMix" height="200"></canvas>
        </div>
        <div class="card">
            <h3 class="mb-4 text-sm font-semibold text-[#d4af37]">متزوجون مقابل غير متزوجين</h3>
            <canvas id="chartMarital" height="200"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const gold = '#d4af37';
            const grid = 'rgba(212, 175, 55, 0.15)';
            const text = '#e4e4e7';
            const data = @json($data);

            const common = {
                plugins: {
                    legend: {
                        labels: { color: text },
                    },
                },
                scales: {
                    x: {
                        ticks: { color: text },
                        grid: { color: grid },
                    },
                    y: {
                        ticks: { color: text },
                        grid: { color: grid },
                    },
                },
            };

            const trendDayLabels = Object.keys(data.trend_daily);
            const trendDayValues = Object.values(data.trend_daily);
            new Chart(document.getElementById('chartTrendDay'), {
                type: 'line',
                data: {
                    labels: trendDayLabels,
                    datasets: [{
                        label: 'عدد التقديمات',
                        data: trendDayValues,
                        borderColor: gold,
                        backgroundColor: 'rgba(212, 175, 55, 0.15)',
                        tension: 0.35,
                        fill: true,
                    }],
                },
                options: { ...common, scales: common.scales },
            });

            const trendWeekLabels = Object.keys(data.trend_weekly);
            const trendWeekValues = Object.values(data.trend_weekly);
            new Chart(document.getElementById('chartTrendWeek'), {
                type: 'bar',
                data: {
                    labels: trendWeekLabels,
                    datasets: [{
                        label: 'أسبوع',
                        data: trendWeekValues,
                        backgroundColor: 'rgba(212, 175, 55, 0.45)',
                        borderColor: gold,
                        borderWidth: 1,
                    }],
                },
                options: { ...common, scales: common.scales },
            });

            const svcLabels = Object.keys(data.by_service);
            const svcValues = Object.values(data.by_service).map((v) => Number(v));
            new Chart(document.getElementById('chartService'), {
                type: 'doughnut',
                data: {
                    labels: svcLabels,
                    datasets: [{
                        data: svcValues,
                        backgroundColor: [gold, '#9ca3af', '#fbbf24', '#d97706'],
                        borderWidth: 1,
                        borderColor: '#18181b',
                    }],
                },
                options: { plugins: { legend: { labels: { color: text } } } },
            });

            const stLabels = Object.keys(data.by_status);
            const stValues = Object.values(data.by_status).map((v) => Number(v));
            new Chart(document.getElementById('chartStatus'), {
                type: 'pie',
                data: {
                    labels: stLabels,
                    datasets: [{
                        data: stValues,
                        backgroundColor: ['#facc15', '#a3e635', '#f97316', '#22c55e', '#ef4444'],
                        borderWidth: 1,
                        borderColor: '#18181b',
                    }],
                },
                options: { plugins: { legend: { labels: { color: text } } } },
            });

            const funnelLabels = Object.keys(data.funnel);
            const funnelValues = Object.values(data.funnel).map((v) => Number(v));
            new Chart(document.getElementById('chartFunnel'), {
                type: 'bar',
                data: {
                    labels: funnelLabels,
                    datasets: [{
                        label: 'عدد حسب الحالة',
                        data: funnelValues,
                        backgroundColor: 'rgba(212, 175, 55, 0.35)',
                        borderColor: gold,
                        borderWidth: 1,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    scales: {
                        x: { ticks: { color: text }, grid: { color: grid } },
                        y: { ticks: { color: text }, grid: { color: grid } },
                    },
                    plugins: { legend: { labels: { color: text } } },
                },
            });

            const topLabels = Object.keys(data.top_employees);
            const topValues = Object.values(data.top_employees).map((v) => Number(v));
            new Chart(document.getElementById('chartEmployees'), {
                type: 'bar',
                data: {
                    labels: topLabels,
                    datasets: [{
                        label: 'سجلات',
                        data: topValues,
                        backgroundColor: 'rgba(212, 175, 55, 0.5)',
                        borderColor: gold,
                        borderWidth: 1,
                    }],
                },
                options: {
                    scales: {
                        x: { ticks: { color: text }, grid: { color: grid } },
                        y: { ticks: { color: text }, grid: { color: grid } },
                    },
                    plugins: { legend: { labels: { color: text } } },
                },
            });

            const natLabels = Object.keys(data.nationality_split);
            const natValues = Object.values(data.nationality_split).map((v) => Number(v));
            const visaYes = Number(data.previous_visa['نعم'] ?? 0);
            const visaNo = Number(data.previous_visa['لا'] ?? 0);
            const rejYes = Number(data.previous_rejection['نعم'] ?? 0);
            const rejNo = Number(data.previous_rejection['لا'] ?? 0);
            new Chart(document.getElementById('chartMix'), {
                type: 'bar',
                data: {
                    labels: [...natLabels, 'تأشيرات سابقة (نعم)', 'تأشيرات (لا)', 'رفض (نعم)', 'رفض (لا)'],
                    datasets: [{
                        label: 'عدد',
                        data: [
                            ...natValues.map((v) => Number(v)),
                            visaYes,
                            visaNo,
                            rejYes,
                            rejNo,
                        ],
                        backgroundColor: 'rgba(212, 175, 55, 0.35)',
                        borderColor: gold,
                        borderWidth: 1,
                    }],
                },
                options: {
                    scales: {
                        x: { ticks: { color: text }, grid: { color: grid } },
                        y: { ticks: { color: text }, grid: { color: grid } },
                    },
                    plugins: { legend: { labels: { color: text } } },
                },
            });

            const mLabels = Object.keys(data.married_vs_single);
            const mValues = Object.values(data.married_vs_single).map((v) => Number(v));
            new Chart(document.getElementById('chartMarital'), {
                type: 'doughnut',
                data: {
                    labels: mLabels,
                    datasets: [{
                        data: mValues,
                        backgroundColor: [gold, '#4b5563'],
                        borderWidth: 1,
                        borderColor: '#18181b',
                    }],
                },
                options: { plugins: { legend: { labels: { color: text } } } },
            });
        });
    </script>
@endpush
