<x-app-layout>
    <div class="space-y-8 p-4 sm:p-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-[#0a1233]">Staff Performance Dashboard</h2>
                <p class="text-sm text-gray-500 mt-1">Monitoring operations for {{ ucfirst($targetType) }} period ({{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }})</p>
            </div>
            
            <div class="flex items-center gap-2 bg-white p-1 rounded-lg shadow-sm border border-gray-200">
                <a href="{{ route('staff.performance.dashboard', ['type' => 'daily']) }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $targetType === 'daily' ? 'bg-[#0a1233] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Daily
                </a>
                <a href="{{ route('staff.performance.dashboard', ['type' => 'weekly']) }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $targetType === 'weekly' ? 'bg-[#0a1233] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Weekly
                </a>
                <a href="{{ route('staff.performance.dashboard', ['type' => 'monthly']) }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $targetType === 'monthly' ? 'bg-[#0a1233] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Monthly
                </a>
            </div>
        </div>

        <!-- Leaderboard / Top Performers -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                <h3 class="text-lg font-bold text-[#0a1233] flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    Top Performers Leaderboard
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 p-6">
                @foreach($topPerformers as $index => $perf)
                <div class="relative flex flex-col items-center p-4 rounded-xl border {{ $index === 0 ? 'bg-yellow-50 border-yellow-200' : 'bg-white border-gray-100' }}">
                    <div class="absolute -top-3 -left-3 w-8 h-8 rounded-full flex items-center justify-center font-bold text-white {{ $index === 0 ? 'bg-yellow-500' : ($index === 1 ? 'bg-gray-400' : ($index === 2 ? 'bg-orange-400' : 'bg-blue-400')) }}">
                        {{ $index + 1 }}
                    </div>
                    <div class="h-16 w-16 rounded-full bg-[#4634ff] flex items-center justify-center overflow-hidden border-2 border-white shadow-sm mb-3">
                        @if($perf->user->image)
                            <img src="{{ asset($perf->user->image) }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-xl font-extrabold text-white uppercase">
                                {{ collect(explode(' ', $perf->user->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                            </span>
                        @endif
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-[#0a1233] truncate w-32">{{ $perf->user->name }}</div>
                        <div class="text-xs font-bold text-blue-600 mt-1">{{ formatCurrency($perf->actual_sales) }}</div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider mt-1">{{ $perf->progress }}% Achieved</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Detailed Performance Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 font-bold">
                            <th class="px-6 py-4">Staff Member</th>
                            <th class="px-6 py-4 text-center">Target ({{ ucfirst($targetType) }})</th>
                            <th class="px-6 py-4 text-center">Actual Sales</th>
                            <th class="px-6 py-4">Progress</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($staffPerformance as $perf)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-[#4634ff] flex items-center justify-center overflow-hidden border border-gray-100 shadow-sm flex-none">
                                        @if($perf->user->image)
                                            <img src="{{ asset($perf->user->image) }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-xs font-extrabold text-white uppercase">
                                                {{ collect(explode(' ', $perf->user->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-[#0a1233]">{{ $perf->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $perf->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-700">
                                {{ formatCurrency($perf->target_amount) }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-[#0a1233]">
                                {{ formatCurrency($perf->actual_sales) }}
                            </td>
                            <td class="px-6 py-4 min-w-[200px]">
                                @php
                                    $barColor = match($perf->color) {
                                        'green' => 'bg-green-500',
                                        'blue' => 'bg-blue-500',
                                        'red' => 'bg-red-500',
                                        default => 'bg-gray-500'
                                    };
                                    $progressWidth = min($perf->progress, 100);
                                @endphp
                                <div class="flex items-center gap-3" x-data="{ width: {{ $progressWidth }} }">
                                    <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500 {{ $barColor }}" 
                                             x-bind:style="{ width: width + '%' }"
                                             style="width: 0%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-gray-600">{{ $perf->progress }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $badgeClasses = match($perf->color) {
                                        'green' => 'bg-green-100 text-green-700 border-green-200',
                                        'blue' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'red' => 'bg-red-100 text-red-700 border-red-200',
                                        default => 'bg-gray-100 text-gray-700 border-gray-200'
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeClasses }}">
                                    {{ $perf->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button type="button"
                                        data-user-id="{{ $perf->user->id }}"
                                        data-user-name="{{ $perf->user->name }}"
                                        data-target-amount="{{ $perf->target_amount }}"
                                        data-target-type="{{ $targetType }}"
                                        x-on:click="handleSetTarget($el)" 
                                        class="text-blue-600 hover:text-blue-800 font-bold text-xs uppercase tracking-wider">
                                    Set Target
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Set Target Modal -->
    <div id="targetModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeTargetModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('staff.performance.assign') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" id="modal_user_id">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-bold text-[#0a1233] mb-4" id="modal_staff_name">Set Target for Staff</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Target Amount (PHP)</label>
                                <input type="number" name="target_amount" id="modal_target_amount" required step="0.01" class="w-full border-gray-300 rounded-lg focus:ring-[#0a1233] focus:border-[#0a1233]">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Target Type</label>
                                <select name="target_type" id="modal_target_type" class="w-full border-gray-300 rounded-lg focus:ring-[#0a1233] focus:border-[#0a1233]">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Start Date</label>
                                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" required class="w-full border-gray-300 rounded-lg focus:ring-[#0a1233] focus:border-[#0a1233]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">End Date</label>
                                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" required class="w-full border-gray-300 rounded-lg focus:ring-[#0a1233] focus:border-[#0a1233]">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-[#0a1233] text-base font-bold text-white hover:bg-blue-900 sm:ml-3 sm:w-auto sm:text-sm uppercase tracking-wider">
                            Save Target
                        </button>
                        <button type="button" onclick="closeTargetModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm uppercase tracking-wider">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function handleSetTarget(button) {
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            const targetAmount = button.getAttribute('data-target-amount');
            const targetType = button.getAttribute('data-target-type');
            openTargetModal(userId, userName, targetAmount, targetType);
        }

        function openTargetModal(userId, name, amount = 0, type = 'monthly') {
            document.getElementById('modal_user_id').value = userId;
            document.getElementById('modal_staff_name').innerText = 'Set Target for ' + name;
            document.getElementById('modal_target_amount').value = amount;
            document.getElementById('modal_target_type').value = type;
            document.getElementById('targetModal').classList.remove('hidden');
        }

        function closeTargetModal() {
            document.getElementById('targetModal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
