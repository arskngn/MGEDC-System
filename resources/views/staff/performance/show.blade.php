<x-app-layout>
    <div class="space-y-8 p-4 sm:p-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('staff.index') }}" class="p-2 bg-white rounded-lg shadow-sm border border-gray-200 text-gray-500 hover:text-[#0a1233] transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-[#0a1233]">{{ $user->name }}'s Performance</h2>
                    <p class="text-sm text-gray-500 mt-1">Staff individual productivity and sales targets</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2 bg-white p-1 rounded-lg shadow-sm border border-gray-200">
                <a href="{{ route('staff.performance.show', [$user->id, 'type' => 'daily']) }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $targetType === 'daily' ? 'bg-[#0a1233] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Daily
                </a>
                <a href="{{ route('staff.performance.show', [$user->id, 'type' => 'weekly']) }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $targetType === 'weekly' ? 'bg-[#0a1233] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Weekly
                </a>
                <a href="{{ route('staff.performance.show', [$user->id, 'type' => 'monthly']) }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $targetType === 'monthly' ? 'bg-[#0a1233] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    Monthly
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: User Profile & Current Target -->
            <div class="space-y-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center">
                    <div class="h-24 w-24 rounded-full bg-[#4634ff] flex items-center justify-center overflow-hidden border-4 border-gray-50 shadow-md mb-4">
                        @if($user->image)
                            <img src="{{ asset($user->image) }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-3xl font-extrabold text-white uppercase">
                                {{ collect(explode(' ', $user->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                            </span>
                        @endif
                    </div>
                    <h3 class="text-xl font-bold text-[#0a1233]">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->roles->first()->name ?? 'Staff' }}</p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full {{ $user->isOnline() ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                        <span class="text-xs font-medium text-gray-600 uppercase">{{ $user->isOnline() ? 'Online' : 'Offline' }}</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h4 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Current Period Status</h4>
                    <div class="space-y-6">
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-500 font-medium">Progress</span>
                                <span class="font-bold text-[#0a1233]">{{ $performance->progress }}%</span>
                            </div>
                            @php
                                $barColor = match($performance->color) {
                                    'green' => 'bg-green-500',
                                    'blue' => 'bg-blue-500',
                                    'red' => 'bg-red-500',
                                    default => 'bg-gray-500'
                                };
                                $progressWidth = min($performance->progress, 100);
                            @endphp
                            <div class="h-3 bg-gray-100 rounded-full overflow-hidden" x-data="{ width: {{ $progressWidth }} }">
                                <div class="h-full rounded-full transition-all duration-1000 {{ $barColor }}" 
                                     x-bind:style="{ width: width + '%' }" 
                                     style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="text-[10px] font-bold text-gray-400 uppercase mb-1">Target</div>
                                <div class="text-lg font-bold text-[#0a1233]">{{ formatCurrency($performance->target_amount) }}</div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="text-[10px] font-bold text-gray-400 uppercase mb-1">Actual</div>
                                <div class="text-lg font-bold text-[#0a1233]">{{ formatCurrency($performance->actual_sales) }}</div>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-50">
                            <button type="button"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    data-target-amount="{{ $performance->target_amount }}"
                                    data-target-type="{{ $targetType }}"
                                    x-on:click="handleSetTarget($el)" 
                                    class="w-full py-3 bg-[#0a1233] text-white rounded-lg font-bold text-sm hover:bg-blue-900 transition-colors uppercase tracking-wider">
                                Update Period Target
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sales History & Target Logs -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Monthly Performance Chart / Summary -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50">
                        <h3 class="font-bold text-[#0a1233]">Annual Sales Overview ({{ now()->year }})</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                            @foreach(range(1, 12) as $month)
                                @php
                                    $monthData = $salesHistory->firstWhere('month', $month);
                                    $total = $monthData ? (float)$monthData->total : 0;
                                    $monthName = DateTime::createFromFormat('!m', $month)->format('M');
                                @endphp
                                <div class="flex flex-col items-center p-3 rounded-lg border {{ $total > 0 ? 'border-blue-100 bg-blue-50/30' : 'border-gray-50 bg-gray-50/30' }}">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase">{{ $monthName }}</span>
                                    <span class="text-sm font-bold text-[#0a1233] mt-1">{{ formatCurrency($total) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Target History Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                        <h3 class="font-bold text-[#0a1233]">Target History</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 font-bold">
                                    <th class="px-6 py-4">Period</th>
                                    <th class="px-6 py-4">Type</th>
                                    <th class="px-6 py-4 text-center">Amount</th>
                                    <th class="px-6 py-4">Created At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($targets as $target)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $target->start_date->format('M d') }} - {{ $target->end_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-[10px] font-bold uppercase tracking-wider">
                                            {{ $target->target_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-[#0a1233]">
                                        {{ formatCurrency($target->target_amount) }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-400">
                                        {{ $target->created_at->format('M d, Y H:i') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">No target history found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Set Target Modal (Same as Dashboard) -->
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
