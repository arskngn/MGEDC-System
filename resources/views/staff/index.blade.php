<x-app-layout>
    <div id="staff-page" x-data="{ 
        addModalOpen: false, 
        editModalOpen: false, 
        banModalOpen: false,
        unbanToLoginModalOpen: false,
        selectedStaff: null,
        staffData: { name: '', email: '', role_id: '', password: '' },
        userStatuses: {},
        init() {
            this.fetchStatuses();
            setInterval(() => this.fetchStatuses(), 30000); // Poll every 30 seconds
        },
        fetchStatuses() {
            fetch('{{ route('staff.statuses') }}')
                .then(response => response.json())
                .then(data => {
                    this.userStatuses = data;
                });
        },
        openUnbanToLoginModal(staff) {
            this.selectedStaff = staff;
            this.unbanToLoginModalOpen = true;
        },
        generatePassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            this.staffData.password = password;
        },
        openEditModal(staff) {
            this.selectedStaff = staff;
            this.staffData = { 
                name: staff.name, 
                email: staff.email, 
                role_id: staff.roles.length > 0 ? staff.roles[0].id : '', 
                password: '' 
            };
            this.editModalOpen = true;
        },
        openBanModal(staff) {
            this.selectedStaff = staff;
            this.banModalOpen = true;
        }
    }">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-[#0a1233]">All Staff</h2>
            <div class="flex items-center space-x-3">
                <form action="{{ route('staff.index') }}" method="GET" class="flex items-center space-x-2">
                    <!-- Role Filter -->
                    <select name="role" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm bg-white">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>{{ ucwords($role->name) }}</option>
                        @endforeach
                    </select>

                    <!-- Status Filter -->
                    <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm bg-white">
                        <option value="">All Status</option>
                        <option value="online" {{ request('status') === 'online' ? 'selected' : '' }}>Online</option>
                        <option value="offline" {{ request('status') === 'offline' ? 'selected' : '' }}>Offline</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                    </select>

                    <!-- Search Bar -->
                    <div class="relative flex">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name/Email" class="pl-4 pr-10 py-2 border border-gray-200 rounded-l-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm w-64">
                        <button type="submit" class="px-3 bg-[#4634ff] text-white rounded-r-lg hover:bg-blue-700 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Sort Order Toggle -->
                    <input type="hidden" name="sort" id="sort-input" value="{{ request('sort', 'desc') }}">
                    <button type="button" onclick="toggleSort()" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors" title="Toggle Sort Order">
                        @if(request('sort', 'desc') === 'asc')
                            <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" />
                            </svg>
                        @endif
                    </button>
                </form>
                <button @click="addModalOpen = true; staffData = { name: '', email: '', role_id: '', password: '' }" class="flex items-center px-4 py-2 bg-white border border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors text-sm font-semibold">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-visible">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#4634ff] text-white">
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider">S.N.</th>
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider">NAME</th>
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider">EMAIL</th>
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider">ROLE</th>
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider">STATUS</th>
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider text-right">ACTION</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($staffs as $index => $staff)
                        <tr class="hover:bg-gray-50 transition-colors" 
                            x-data="{ showTooltip: false, mouseX: 0, mouseY: 0 }"
                            @mouseenter="showTooltip = true"
                            @mouseleave="showTooltip = false"
                            @mousemove="mouseX = $event.clientX; mouseY = $event.clientY">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $staffs->firstItem() + $index }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                <div class="flex items-center space-x-3">
                                    <div class="h-8 w-8 rounded-full bg-[#4634ff] flex items-center justify-center overflow-hidden border border-gray-200 shadow-sm flex-none">
                                        @if($staff->image)
                                            <img src="{{ asset($staff->image) }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-[10px] font-extrabold text-white uppercase">
                                                {{ collect(explode(' ', $staff->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="relative">
                                        <a href="{{ route('staff.performance.show', $staff->id) }}" 
                                           class="hover:text-[#4634ff] hover:underline transition-colors decoration-2 underline-offset-4">
                                            {{ $staff->name }}
                                        </a>

                                        <!-- Hover Tooltip/Modal -->
                                        <div x-show="showTooltip" 
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="fixed z-[999] w-64 bg-white rounded-xl shadow-2xl border border-gray-100 p-4 pointer-events-none"
                                             :style="'left: ' + (mouseX + 15) + 'px; top: ' + (mouseY + 15) + 'px;'">
                                            <div class="flex flex-col items-center text-center">
                                                <div class="h-16 w-16 rounded-full bg-[#4634ff] flex items-center justify-center overflow-hidden border-2 border-gray-50 shadow-sm mb-3">
                                                    @if($staff->image)
                                                        <img src="{{ asset($staff->image) }}" class="h-full w-full object-cover">
                                                    @else
                                                        <span class="text-xl font-extrabold text-white uppercase">
                                                            {{ collect(explode(' ', $staff->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <h4 class="font-bold text-[#0a1233] text-base">{{ $staff->name }}</h4>
                                                <p class="text-[11px] text-gray-500 mb-3">{{ $staff->email }}</p>
                                                
                                                <div class="w-full grid grid-cols-2 gap-2 border-t border-gray-50 pt-3">
                                                    <div class="flex flex-col">
                                                        <span class="text-[9px] font-bold text-gray-400 uppercase">Target (Monthly)</span>
                                                        <span class="text-[11px] font-bold text-gray-700">{{ formatCurrency($staff->performance->target_amount) }}</span>
                                                    </div>
                                                    <div class="flex flex-col border-l border-gray-50 pl-2">
                                                        <span class="text-[9px] font-bold text-gray-400 uppercase">Actual Sales</span>
                                                        <span class="text-[11px] font-bold text-[#0a1233]">{{ formatCurrency($staff->performance->actual_sales) }}</span>
                                                    </div>
                                                </div>
                                                <div class="mt-2 w-full">
                                                    <div class="flex justify-between items-center mb-1">
                                                        <span class="text-[9px] font-bold text-gray-400 uppercase">Progress</span>
                                                        <span class="text-[10px] font-bold text-blue-600">{{ $staff->performance->progress }}%</span>
                                                    </div>
                                                    @php
                                                        $barColor = match($staff->performance->color) {
                                                            'green' => 'bg-green-500',
                                                            'blue' => 'bg-blue-500',
                                                            'red' => 'bg-red-500',
                                                            default => 'bg-gray-500'
                                                        };
                                                    @endphp
                                                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden" x-data="{ p: {{ min($staff->performance->progress, 100) }} }">
                                                        <div class="h-full rounded-full transition-all duration-500 {{ $barColor }}" x-bind:style="{ width: p + '%' }" style="width: 0%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $staff->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $staff->roles->pluck('name')->map(fn($n) => ucwords($n))->implode(', ') ?: 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <template x-if="userStatuses[{{ $staff->id }}] === 'banned'">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold border border-red-500 text-red-500">
                                        Banned
                                    </span>
                                </template>
                                <template x-if="userStatuses[{{ $staff->id }}] === 'you'">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold border border-blue-500 text-blue-500">
                                        You
                                    </span>
                                </template>
                                <template x-if="userStatuses[{{ $staff->id }}] === 'online'">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold border border-green-500 text-green-500">
                                        Online
                                    </span>
                                </template>
                                <template x-if="userStatuses[{{ $staff->id }}] === 'offline' || !userStatuses[{{ $staff->id }}]">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold border border-gray-400 text-gray-400">
                                        Offline
                                    </span>
                                </template>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end space-x-2">
                                    @php
                                        $isSelf = $staff->id === auth()->id();
                                    @endphp

                                    <button @click="openEditModal({{ $staff->toJson() }})" class="inline-flex items-center px-3 py-1.5 bg-white border border-blue-500 text-blue-600 rounded-md hover:bg-blue-50 transition-colors text-[11px] font-bold whitespace-nowrap">
                                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>

                                    @if(!$isSelf)
                                        <button @click="openBanModal({{ $staff->toJson() }})" class="inline-flex items-center px-3 py-1.5 bg-white border border-red-500 text-red-500 rounded-md hover:bg-red-50 transition-colors text-[11px] font-bold whitespace-nowrap">
                                            <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            {{ $staff->status ? 'Ban' : 'Unban' }}
                                        </button>

                                        @if($staff->status == 0)
                                            <button @click="openUnbanToLoginModal({{ $staff->toJson() }})" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-800 text-gray-800 rounded-md hover:bg-gray-50 transition-colors text-[11px] font-bold whitespace-nowrap">
                                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                                Login
                                            </button>
                                        @else
                                            <form action="{{ route('staff.login-as', $staff) }}" method="POST" class="m-0 p-0">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-800 text-gray-800 rounded-md hover:bg-gray-50 transition-colors text-[11px] font-bold whitespace-nowrap">
                                                    <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                    </svg>
                                                    Login
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500 italic">No staff members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $staffs->links() }}
            </div>
        </div>

        <!-- Add Staff Modal -->
        <div x-show="addModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-[#0a1233]">Add New Staff</h3>
                        <button @click="addModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form action="{{ route('staff.store') }}" method="POST">
                        @csrf
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="staffData.name" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" x-model="staffData.email" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                                <select name="role_id" x-model="staffData.role_id" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm appearance-none bg-no-repeat bg-[right_1rem_center] bg-[length:1em_1em]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23666%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22/%3E%3C/svg%3E');">
                                    <option value="">Select One</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                                <div class="flex">
                                    <input type="text" name="password" x-model="staffData.password" required class="flex-1 px-4 py-2 border border-gray-200 rounded-l-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                                    <button type="button" @click="generatePassword()" class="px-4 py-2 bg-gray-100 border border-l-0 border-gray-200 rounded-r-lg text-sm font-semibold hover:bg-gray-200 transition-colors">Generate</button>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button type="submit" class="w-full py-2.5 bg-[#4634ff] text-white rounded-lg hover:bg-blue-700 transition-colors font-bold">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Staff Modal -->
        <div x-show="editModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-[#0a1233]">Edit Staff</h3>
                        <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form :action="'{{ route('staff.update', ['staff' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', selectedStaff ? selectedStaff.id : '')" method="POST">
                        @csrf
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="staffData.name" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" x-model="staffData.email" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                                <select name="role_id" x-model="staffData.role_id" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm appearance-none bg-no-repeat bg-[right_1rem_center] bg-[length:1em_1em]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23666%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22/%3E%3C/svg%3E');">
                                    <option value="">Select One</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Password (Leave blank to keep current)</label>
                                <div class="flex">
                                    <input type="text" name="password" x-model="staffData.password" class="flex-1 px-4 py-2 border border-gray-200 rounded-l-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                                    <button type="button" @click="generatePassword()" class="px-4 py-2 bg-gray-100 border border-l-0 border-gray-200 rounded-r-lg text-sm font-semibold hover:bg-gray-200 transition-colors">Generate</button>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button type="submit" class="w-full py-2.5 bg-[#4634ff] text-white rounded-lg hover:bg-blue-700 transition-colors font-bold">Update Staff</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Ban Confirmation Modal -->
        <div x-show="banModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-[#0a1233]">Confirmation Alert!</h3>
                        <button @click="banModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-600">Are you sure to <span x-text="selectedStaff && selectedStaff.status ? 'ban' : 'unban'"></span> this staff?</p>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                        <button @click="banModalOpen = false" class="px-6 py-2 bg-[#0a1233] text-white rounded-md hover:bg-opacity-90 transition-all text-sm font-bold">No</button>
                        <form :action="'{{ route('staff.toggle-status', ['staff' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', selectedStaff ? selectedStaff.id : '')" method="POST">
                            @csrf
                            <button type="submit" class="px-6 py-2 bg-[#4634ff] text-white rounded-md hover:bg-blue-700 transition-all text-sm font-bold">Yes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unban to Login Modal -->
        <div x-show="unbanToLoginModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-[#0a1233]">Confirmation Alert!</h3>
                        <button @click="unbanToLoginModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-600">The user is currently banned. Unban to login?</p>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                        <button @click="unbanToLoginModalOpen = false" class="px-6 py-2 bg-[#0a1233] text-white rounded-md hover:bg-opacity-90 transition-all text-sm font-bold">Cancel</button>
                        <form :action="'{{ route('staff.toggle-status', ['staff' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', selectedStaff ? selectedStaff.id : '')" method="POST">
                            @csrf
                            <input type="hidden" name="redirect_to_login" value="1">
                            <button type="submit" class="px-6 py-2 bg-[#4634ff] text-white rounded-md hover:bg-blue-700 transition-all text-sm font-bold">Unban</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSort() {
            const input = document.getElementById('sort-input');
            input.value = input.value === 'asc' ? 'desc' : 'asc';
            input.form.submit();
        }
    </script>
</x-app-layout>
