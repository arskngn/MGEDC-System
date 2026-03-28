<x-app-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-[#0a1233]">Add New Role</h2>
    </div>

    <form action="{{ route('staff.roles.store') }}" method="POST">
        @csrf
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-6">
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm" placeholder="Role Name">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 space-y-1">
                <h3 class="text-md font-bold text-[#0a1233]">Set Permissions</h3>
                <p class="text-xs text-gray-500 leading-relaxed">
                    <strong>Manage Products</strong> in the sidebar appears if a user has <strong>All Product</strong> and/or <strong>All Categorys</strong>, <strong>All Brands</strong>, or <strong>All Unit</strong>.
                    Sub-actions (add, edit, delete, import CSV) use <strong>Store / Delete / Import</strong> permissions under each module (<strong>Category</strong>, <strong>Brand</strong>, <strong>Unit</strong>, <strong>Product</strong>).
                    <strong>Manage Warehouse</strong> uses the <strong>Warehouse</strong> module (<strong>All Warehouses</strong> to show the menu, plus <strong>Store / Delete / Import Warehouses</strong> for actions).
                </p>
            </div>
            <div class="p-6 space-y-6">
                @foreach($modules as $module => $permissions)
                    <div class="bg-gray-50/50 rounded-lg p-4 border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                            <div class="md:col-span-1">
                                <h4 class="text-sm font-bold text-gray-700 uppercase tracking-tight">{{ $module }}</h4>
                            </div>
                            <div class="md:col-span-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($permissions as $permission)
                                        <label class="flex items-center space-x-3 cursor-pointer group">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="h-4 w-4 text-[#4634ff] border-gray-300 rounded focus:ring-[#4634ff] transition-colors">
                                            <span class="text-[13px] text-gray-600 group-hover:text-gray-900 transition-colors">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                <button type="submit" class="w-full py-2.5 bg-[#4634ff] text-white rounded-lg hover:bg-blue-700 transition-colors font-bold shadow-md uppercase tracking-wider text-sm">Submit</button>
            </div>
        </div>
    </form>
</x-app-layout>
