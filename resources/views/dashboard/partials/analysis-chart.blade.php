@php
    $isPercent = ($metric ?? ($type === 'bar' ? 'count' : 'percent')) === 'percent';
    $valueLabel = fn ($count) => $isPercent ? number_format($total > 0 ? $count / $total * 100 : 0, 2, ',', '.') . '%' : $count . ' orang';
@endphp
@if ($type === 'tree')
    @php
        $sorted = collect($table)->where('count', '>', 0)->sortByDesc('count')->values();
        $groups = [$sorted->take(2), $sorted->slice(2)];
    @endphp
    <div class="tree-chart" role="img" aria-label="Diagram pohon: luas kotak sebanding dengan frekuensi">
        @foreach ($groups as $group)
            @if ($group->sum('count') > 0)
                <div class="tree-row" style="flex: {{ $group->sum('count') }}">
                    @foreach ($group as $row)
                        <div class="tree-cell" style="flex: {{ $row['count'] }}; background: {{ $palette[$row['scale']] }}; color: {{ $row['scale'] == 3 ? '#172b4d' : '#fff' }}" title="{{ $names[$row['scale']] }}: {{ $row['count'] }} responden">
                            <span>{{ $names[$row['scale']] }}</span><b>{{ $valueLabel($row['count']) }}</b>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
@elseif ($type === 'bar')
    @php
        $step = $isPercent ? 20 : max(1, (int) ceil(max(array_column($table, 'count')) / 5));
        $ceiling = $step * 5;
    @endphp
    <svg viewBox="0 0 380 290" role="img" aria-label="{{ $isPercent ? 'Persentase tiap kelas' : 'Frekuensi tiap kelas' }}" class="bar-chart">
        @for ($tick = 0; $tick <= 5; $tick++)
            <line x1="52" y1="{{ 210 - $tick * 35 }}" x2="370" y2="{{ 210 - $tick * 35 }}" stroke="#eef0f2" />
            <text x="44" y="{{ 214 - $tick * 35 }}" text-anchor="end" font-size="11">{{ $tick * $step }}</text>
        @endfor
        <path d="M52 35 V210 H370" fill="none" stroke="#cbd5e1" />
        <text transform="translate(13 125) rotate(-90)" text-anchor="middle" font-size="11">{{ $isPercent ? 'Persentase (%)' : 'Frekuensi (Orang)' }}</text>
        @foreach ($table as $row)
            @php
                $x = 66 + $loop->index * 63;
                $value = $isPercent ? ($total > 0 ? $row['count'] / $total * 100 : 0) : $row['count'];
                $height = $value / $ceiling * 175;
            @endphp
            <g><title>{{ $names[$row['scale']] }}: {{ $row['count'] }} responden</title>
                <rect x="{{ $x }}" y="{{ 210 - $height }}" width="33" height="{{ $height }}" rx="3" fill="{{ $palette[$row['scale']] }}" />
                <text x="{{ $x + 16.5 }}" y="{{ 202 - $height }}" text-anchor="middle" font-size="11" font-weight="700">{{ $isPercent ? $valueLabel($row['count']) : $row['count'] }}</text>
                <text x="{{ $x + 16.5 }}" y="228" text-anchor="middle" font-size="10">
                    @foreach (explode(' ', $names[$row['scale']]) as $word)<tspan x="{{ $x + 16.5 }}" dy="{{ $loop->first ? 0 : 11 }}">{{ $word }}</tspan>@endforeach
                </text>
            </g>
        @endforeach
        <text x="210" y="285" text-anchor="middle" font-size="11">Kelas</text>
    </svg>
@else
    <div class="pie-layout">
        <svg viewBox="0 0 220 220" role="img" aria-label="{{ $isPercent ? 'Persentase tiap kelas' : 'Frekuensi tiap kelas' }}">
            @php
                $angle = -90;
            @endphp
            @if ($total === 0)<circle cx="110" cy="110" r="100" fill="#e5e7eb" />@endif
            @foreach ($table as $row)
                @if ($row['count'] > 0 && $total > 0)
                    @php
                        $sweep = $row['count'] / $total * 360;
                        $end = $angle + $sweep;
                        $middle = deg2rad($angle + $sweep / 2);
                        $percent = number_format($row['count'] / $total * 100, 2, ',', '.');
                    @endphp
                    <g><title>{{ $names[$row['scale']] }}: {{ $row['count'] }} ({{ $percent }}%)</title>
                        @if ($row['count'] == $total)
                            <circle cx="110" cy="110" r="100" fill="{{ $palette[$row['scale']] }}" />
                        @else
                            <path d="M110 110 L{{ 110 + 100 * cos(deg2rad($angle)) }} {{ 110 + 100 * sin(deg2rad($angle)) }} A100 100 0 {{ $sweep > 180 ? 1 : 0 }} 1 {{ 110 + 100 * cos(deg2rad($end)) }} {{ 110 + 100 * sin(deg2rad($end)) }} Z" fill="{{ $palette[$row['scale']] }}" stroke="white" />
                        @endif
                        @if ($sweep >= 12)<text x="{{ 110 + 73 * cos($middle) }}" y="{{ 114 + 73 * sin($middle) }}" text-anchor="middle" fill="{{ $row['scale'] == 3 ? '#334155' : '#fff' }}" font-size="12" font-weight="700">{{ $isPercent ? $percent . '%' : $row['count'] }}</text>@endif
                    </g>
                    @php
                        $angle = $end;
                    @endphp
                @endif
            @endforeach
            @if ($type === 'doughnut')<circle cx="110" cy="110" r="35" fill="white" />@endif
        </svg>
        <ul class="chart-legend">@foreach ($table as $row)<li><i style="background: {{ $palette[$row['scale']] }}"></i><span>{{ $names[$row['scale']] }} ({{ $valueLabel($row['count']) }})</span></li>@endforeach</ul>
    </div>
@endif
@if ($total === 0)<p class="chart-empty">Belum ada data responden.</p>@endif