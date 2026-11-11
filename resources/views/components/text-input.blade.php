@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 text-[0.9375rem] text-gray-900 bg-[#F5F5F7] border-[1.5px] border-transparent rounded-[10px] outline-none transition-all duration-200 focus:bg-white focus:border-[#0071E3] focus:ring-0 focus:shadow-[0_0_0_3px_rgba(0,113,227,0.12)] placeholder-[#AEAEB2]']) }}>
