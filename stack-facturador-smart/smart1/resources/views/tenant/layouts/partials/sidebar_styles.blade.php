<tenant-configurations-visual :visual="{{ json_encode($visual) }}" :show-set-color="{{ json_encode(\App\Models\Tenant\Configuration::getConfig()->show_set_color) }}"
    :type-user="{{ json_encode(Auth::user()->type) }}"></tenant-configurations-visual>
