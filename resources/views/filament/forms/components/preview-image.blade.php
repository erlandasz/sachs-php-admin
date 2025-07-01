@php
    // Try to get the record from the Livewire context
    $record = $this->getRecord() ?? null;
@endphp

<img
    src="{{ $record ? $record->cloudinary_url : asset('noPic.png') }}"
    alt="Preview"
    style="max-width: 400px; max-height: 600px;"
/>
