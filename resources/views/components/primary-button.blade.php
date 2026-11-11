<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-[#0071E3] border border-transparent rounded-[10px] font-semibold text-sm text-white tracking-tight hover:bg-[#0077ED] active:bg-[#006ADB] focus:outline-none focus:ring-2 focus:ring-[#0071E3] focus:ring-offset-2 transition-all duration-150']) }}>
    {{ $slot }}
</button>
