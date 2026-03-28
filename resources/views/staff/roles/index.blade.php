<x-app-layout>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-[#0a1233]">All Roles</h2>
        <a href="{{ route('staff.roles.create') }}" class="flex items-center px-4 py-2 bg-white border border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors text-sm font-semibold">
            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add New
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#4634ff] text-white">
                    <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider">Name</th>
                    <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider text-center">Created At</th>
                    <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($roles as $role)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $role->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 text-center">{{ $role->created_at->format('Y-m-d h:i A') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                @if($role->name !== 'admin')
                                    <a href="{{ route('staff.roles.edit', $role) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-blue-500 text-blue-600 rounded-md hover:bg-blue-50 transition-colors text-[11px] font-bold whitespace-nowrap">
                                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-gray-500 italic">No roles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $roles->links() }}
        </div>
    </div>
</x-app-layout>
