@foreach ($stats as $stat)
    <x-data-stat-card
        :icon="$stat['icon']"
        :color="$stat['color']"
        :label="$stat['label']"
        :value="$stat['value']"
        :unit="$stat['unit']"
        :subtitle="$stat['subtitle']"
    />
@endforeach
