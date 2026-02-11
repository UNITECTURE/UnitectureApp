@props(['show' => false, 'users' => []])

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('detailedAttendanceModal', () => ({
            show: false,
            userId: '',
            month: '{{ now()->month }}',
            year: '{{ now()->year }}',

            init() {
                this.$watch('show', value => {
                    document.body.style.overflow = value ? 'hidden' : '';
                });

                window.addEventListener('open-detailed-attendance-modal', () => {
                    this.show = true;
                });
            },

            close() {
                this.show = false;
            }
        }))
    })
</script>

<div x-data="detailedAttendanceModal" @keydown.escape.window="close()" x-show="show" class="relative z-[100]"
    style="display: none;">

    {{-- Backdrop --}}
    <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/75 transition-opacity" @click="close()">
    </div>

    {{-- Modal Panel --}}
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform rounded-2xl bg-white text-left shadow-[0_20px_50px_rgba(8,_112,_184,_0.07)] transition-all sm:my-8 sm:w-full sm:max-w-xl font-sans border border-slate-200 ring-1 ring-slate-900/5 flex flex-col">

                {{-- Header --}}
                <div
                    class="bg-white px-8 py-5 rounded-t-2xl border-b border-slate-100 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Detailed Attendance Report</h3>
                        <p class="text-xs text-slate-500 mt-1 font-medium">Download detailed daily attendance for an
                            employee.</p>
                    </div>
                    <button @click="close()"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1 bg-slate-50 rounded-lg hover:bg-slate-100 border border-transparent hover:border-slate-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('attendance.export') }}" method="GET">
                    <input type="hidden" name="type" value="employee_monthly">

                    <div class="px-8 py-6 space-y-6">
                        {{-- Employee Select --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-800 mb-1.5">Select Employee</label>
                            <div class="relative">
                                <select name="user_id" required
                                    class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm pl-4 pr-10 py-3 text-slate-800 bg-slate-50 transition-all duration-200 appearance-none cursor-pointer">
                                    <option value="" disabled selected>Choose an employee...</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->full_name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                    <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            {{-- Month --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-1.5">Month</label>
                                <div class="relative">
                                    <select name="month" x-model="month"
                                        class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm pl-4 pr-10 py-3 text-slate-800 bg-slate-50 transition-all duration-200 appearance-none cursor-pointer">
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                        <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Year --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-1.5">Year</label>
                                <div class="relative">
                                    <select name="year" x-model="year"
                                        class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm pl-4 pr-10 py-3 text-slate-800 bg-slate-50 transition-all duration-200 appearance-none cursor-pointer">
                                        @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                        <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div
                        class="px-8 py-6 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl shrink-0 flex justify-end gap-3">
                        <button type="button" @click="close()"
                            class="px-4 py-3.5 text-sm font-bold text-slate-600 hover:text-slate-800 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" @click="setTimeout(() => close(), 500)"
                            class="rounded-xl bg-blue-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 transition-all duration-200 hover:bg-blue-700">
                            Download Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>