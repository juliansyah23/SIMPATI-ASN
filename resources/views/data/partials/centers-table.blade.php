@foreach ($centers as $center)
    <tr>
        <td class="px-3 py-4 font-semibold text-gray-800">{{ $center['name'] }}</td>
        <td class="px-3 py-4 text-center text-blue-600 font-semibold">{{ $center['respons'] }}</td>
        <td class="px-3 py-4 text-center">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $center['produktivitas'] >= 4.5 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                {{ number_format($center['produktivitas'], 1) }}
            </span>
        </td>
        <td class="px-3 py-4 text-center">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $center['kolaborasi'] >= 4.5 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                {{ number_format($center['kolaborasi'], 1) }}
            </span>
        </td>
        <td class="px-3 py-4 text-center">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $center['wlb'] >= 4.5 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                {{ number_format($center['wlb'], 1) }}
            </span>
        </td>
        <td class="px-3 py-4 text-center">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $center['stres'] <= 2.0 ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' }}">
                {{ number_format($center['stres'], 1) }}
            </span>
        </td>
    </tr>
@endforeach
