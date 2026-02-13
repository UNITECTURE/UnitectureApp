@props(['show' => false])

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('manualAttendanceModal', () => ({
            show: false,
            date: '',
            startTime: '09:30', // Native time input uses 24h format HH:mm
            endTime: '18:15',
            duration: '0h 0m',
            reasonDescription: '',
            hasOverlap: false,
            overlapMessage: '',
            checkingOverlap: false,

            dragging: false,
            dragX: 0,
            dragY: 0,
            dragStartX: 0,
            dragStartY: 0,

            get wordCount() {
                const text = this.reasonDescription.trim();
                return text ? text.split(/\s+/).length : 0;
            },

            get isValid() {
                return this.date !== '' &&
                    this.reasonDescription.trim().length > 0 &&
                    this.wordCount <= 30 &&
                    this.duration !== 'Invalid Range' &&
                    this.duration !== '...' &&
                    !this.hasOverlap &&
                    !this.checkingOverlap;
            },

            init() {
                this.$watch('show', value => {
                    document.body.style.overflow = value ? 'hidden' : '';
                });

                // Listen for opening event
                window.addEventListener('open-manual-attendance-modal', () => {
                    this.show = true;
                });

                // Auto-open modal if there are validation errors
                @if ($errors->any())
                    this.show = true;
                @endif

                this.$nextTick(() => {
                    if (typeof flatpickr !== 'undefined') {
                        flatpickr(this.$refs.dateInput, {
                            dateFormat: 'Y-m-d',
                            minDate: '{{ now()->subDays(4)->format("Y-m-d") }}',
                            maxDate: '{{ now()->endOfMonth()->format("Y-m-d") }}',
                            onChange: (selectedDates, dateStr) => { this.date = dateStr; }
                        });
                    }
                    this.calculateDuration();
                });

                // Watchers for time calculation and overlap checking
                this.$watch('startTime', () => {
                    this.calculateDuration();
                    this.checkOverlap();
                });
                this.$watch('endTime', () => {
                    this.calculateDuration();
                    this.checkOverlap();
                });
                this.$watch('date', () => this.checkOverlap());
            },

            calculateDuration() {
                if (!this.startTime || !this.endTime) {
                    this.duration = '...';
                    return;
                }

                const parseTime = (timeStr) => {
                    const [hours, minutes] = timeStr.split(':').map(Number);
                    return hours * 60 + minutes;
                };

                const start = parseTime(this.startTime);
                const end = parseTime(this.endTime);

                if (isNaN(start) || isNaN(end)) {
                    this.duration = '...';
                    return;
                }

                let diff = end - start;
                if (diff < 0) {
                    this.duration = 'Invalid Range';
                    return;
                }

                const h = Math.floor(diff / 60);
                const m = diff % 60;
                this.duration = `${h}h ${m}m`;
            },

            close() {
                this.show = false;
                this.$dispatch('close');
            },

            startDrag(e) {
                this.dragging = true;
                this.dragStartX = e.clientX - this.dragX;
                this.dragStartY = e.clientY - this.dragY;
            },

            handleDrag(e) {
                if (this.dragging) {
                    this.dragX = e.clientX - this.dragStartX;
                    this.dragY = e.clientY - this.dragStartY;
                }
            },

            stopDrag() {
                this.dragging = false;
            },

            async checkOverlap() {
                if (!this.date || !this.startTime || !this.endTime) {
                    this.hasOverlap = false;
                    this.overlapMessage = '';
                    return;
                }

                this.checkingOverlap = true;
                try {
                    const response = await fetch('{{ route("attendance.check-overlap") }}?' + new URLSearchParams({
                        user_id: '{{ Auth::id() }}',
                        date: this.date,
                        start_time: this.startTime,
                        end_time: this.endTime
                    }));
                    const data = await response.json();
                    this.hasOverlap = data.has_overlap || false;
                    this.overlapMessage = data.message || '';
                } catch (error) {
                    this.hasOverlap = false;
                    this.overlapMessage = '';
                }
                this.checkingOverlap = false;
            }
        }))
    })
</script>

<div x-data="manualAttendanceModal" @mousemove.window="handleDrag($event)" @mouseup.window="stopDrag()"
    @keydown.escape.window="close()" x-show="show" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">

    {{-- Backdrop --}}
    <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/75 transition-opacity" @click="close()">
    </div>

    {{-- Modal Panel --}}
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            :style="dragging || dragX !== 0 || dragY !== 0 ? { transform: `translate(${dragX}px, ${dragY}px)` } : {}"
            class="relative transform rounded-2xl bg-white text-left shadow-[0_20px_50px_rgba(8,_112,_184,_0.07)] transition-all sm:my-8 sm:w-full sm:max-w-xl font-sans border border-slate-200 ring-1 ring-slate-900/5 flex flex-col max-h-[85vh]">

            {{-- Header --}}
            <div @mousedown="startDrag($event)"
                class="bg-white px-8 py-5 rounded-t-2xl border-b border-slate-100 cursor-move select-none flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Apply Manual Attendance</h3>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Log your attendance manually if you missed it.
                    </p>
                </div>
                {{-- Close Icon for aesthetics --}}
                <button @click="close()"
                    class="text-slate-400 hover:text-slate-600 transition-colors p-1 bg-slate-50 rounded-lg hover:bg-slate-100 border border-transparent hover:border-slate-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('attendance.manual.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ Auth::id() ?? 1 }}">
                <input type="hidden" name="duration" :value="duration">

                {{-- Error Messages Display --}}
                @if ($errors->any())
                    <div class="mx-8 mt-6 mb-0 bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-red-800">
                                    {{ $errors->has('error') ? 'Validation Error' : 'Please correct the following errors:' }}
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Body (Scrollable) --}}
                <div class="px-8 py-6 space-y-6 overflow-y-auto custom-scrollbar">

                    {{-- Date Field --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-1.5">Date <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <input x-ref="dateInput" name="date" type="text"
                                class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm pl-4 pr-10 py-3 text-slate-800 bg-slate-50 placeholder:text-slate-400 transition-all duration-200"
                                placeholder="Select date" :required="true">
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Native Time Range Fields --}}
                    <div class="grid grid-cols-2 gap-4">
                        {{-- From --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-800 mb-1.5">From</label>
                            <input name="start_time" type="time" x-model="startTime"
                                class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm px-4 py-3 text-slate-800 bg-slate-50 transition-all duration-200"
                                required>
                        </div>

                        {{-- To --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-800 mb-1.5">To</label>
                            <input name="end_time" type="time" x-model="endTime"
                                class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm px-4 py-3 text-slate-800 bg-slate-50 transition-all duration-200"
                                required>
                        </div>
                    </div>

                    {{-- Duration Display --}}
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-sm font-semibold text-slate-800">Duration</span>
                        <div class="flex-1 mx-4 border-t border-slate-200 h-px"></div>
                        <span class="text-sm font-bold"
                            :class="duration === 'Invalid Range' ? 'text-red-500' : 'text-slate-800'"
                            x-text="duration">...</span>
                    </div>

                    {{-- Overlap Warning --}}
                    <div x-show="hasOverlap" class="bg-red-50 border border-red-200 rounded-xl p-3.5" x-transition>
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800" x-text="overlapMessage"></p>
                            </div>
                        </div>
                    </div>


                    {{-- Reason Description --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-1.5">Reason for Manual Attendance
                            <span class="text-red-500">*</span></label>
                        <textarea name="reason" rows="3" x-model="reasonDescription"
                            class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm p-4 text-slate-800 bg-slate-50 placeholder:text-slate-400 resize-none transition-all duration-200"
                            placeholder="Briefly explain the reason..." required></textarea>
                        <p x-show="wordCount > 30" class="text-red-500 text-xs mt-1 font-medium" x-transition>
                            Reason cannot exceed 30 words. (Current: <span x-text="wordCount"></span>)
                        </p>
                    </div>

                </div>

                {{-- Footer (Fixed) --}}
                <div class="px-8 py-6 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl shrink-0 flex justify-end">
                    <button type="submit" :disabled="!isValid"
                        :class="isValid ? 'opacity-100 cursor-pointer hover:bg-blue-700' : 'opacity-50 cursor-not-allowed'"
                        class="w-full rounded-xl bg-blue-600 px-3 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 transition-all duration-200">
                        Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
</div>
</div>
</div>