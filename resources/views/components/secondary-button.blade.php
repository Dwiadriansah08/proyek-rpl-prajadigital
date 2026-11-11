<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-white border border-[#D2D2D7] rounded-[10px] font-semibold text-sm text-[#1D1D1F] tracking-tight shadow-sm hover:bg-[#F5F5F7] focus:outline-none focus:ring-2 focus:ring-[#0071E3] focus:ring-offset-2 disabled:opacity-40 transition-all duration-150']) }}>
    {{ $slot }}
</button>
