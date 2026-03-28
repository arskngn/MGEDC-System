<x-app-layout>
    <div class="max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-[#0a1233]">Edit Expense Info</h2>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('expenses.update', $expense) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="expense_type_id" class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                    <select
                        id="expense_type_id"
                        name="expense_type_id"
                        required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                    >
                        <option value="">Select One</option>
                        @foreach($expenseTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('expense_type_id', $expense->expense_type_id) == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('expense_type_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date of Expense <span class="text-red-500">*</span></label>
                    <input
                        id="date"
                        type="date"
                        name="date"
                        value="{{ old('date', $expense->date->format('Y-m-d')) }}"
                        required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                    />
                    <p class="mt-1 text-xs text-gray-500">YYYY-Month-Date</p>
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-2.5 text-gray-600 font-medium">{{ currencySymbol() }}</span>
                        <input
                            id="amount"
                            type="number"
                            name="amount"
                            step="0.01"
                            min="0"
                            value="{{ old('amount', $expense->amount) }}"
                            placeholder="500"
                            required
                            class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                        />
                    </div>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="Utility for Office !"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm resize-none"
                    >{{ old('description', $expense->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
