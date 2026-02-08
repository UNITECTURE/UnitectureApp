@props(['status'])

@php
    $steps = [
        'employee' => ['label' => 'Employee', 'status' => 'complete'], // Always complete if it exists
        'supervisor' => ['label' => 'Supervisor/Admin', 'status' => 'pending'],
    ];

    if ($status === 'approved') {
        $steps['supervisor']['status'] = 'complete';
    } elseif ($status === 'rejected') {
        // Rejection handled, flow stops
    }
@endphp

<div class="w-full py-4">
    <div class="relative flex items-center justify-between w-full">
        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-slate-200 -z-10"></div>
        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-blue-500 transition-all duration-500 -z-10"
             style="width: {{ $status === 'approved' ? '100%' : '0%' }}"></div>

        <!-- Employee Step -->
        <div class="flex flex-col items-center bg-white px-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $steps['employee']['status'] === 'complete' ? 'bg-green-500 text-white' : 'bg-slate-200 text-slate-500' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="text-xs font-medium mt-1 text-slate-600">Employee</span>
        </div>

        <!-- Supervisor/Admin Step -->
        <div class="flex flex-col items-center bg-white px-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $steps['supervisor']['status'] === 'complete' ? 'bg-green-500 text-white' : 'bg-slate-200 text-slate-500' }}">
                @if($steps['supervisor']['status'] === 'complete')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                @else
                    <span class="text-xs">2</span>
                @endif
            </div>
            <span class="text-xs font-medium mt-1 text-slate-600 text-center">Supervisor/Admin</span>
        </div>
    </div>
</div>
