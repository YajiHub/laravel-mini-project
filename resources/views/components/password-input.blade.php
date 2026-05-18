@props(['disabled' => false])

<div x-data="{ show: false }" class="relative">
    <input
        @disabled($disabled)
        :type="show ? 'text' : 'password'"
        {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}
    >
    <button type="button" @click="show = !show" class="absolute top-1/2 -translate-y-1/2 right-0 px-3 py-0 text-gray-400 hover:text-gray-600 focus:outline-none bg-transparent border-0 leading-none h-auto" tabindex="-1">
        <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
    </button>
</div>
