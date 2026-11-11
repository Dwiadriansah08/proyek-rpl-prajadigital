<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-[#FF3B30] border border-transparent rounded-[10px] font-semibold text-sm text-white tracking-tight hover:bg-[#FF453A] active:bg-[#D70015] focus:outline-none focus:ring-2 focus:ring-[#FF3B30] focus:ring-offset-2 transition-all duration-150']) }}>
    {{ $slot }}
</button>
