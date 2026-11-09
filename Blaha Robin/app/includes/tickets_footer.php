
</div><!-- closes .flex-1.overflow-y-auto from sidebar -->

<!-- Ticket detail modal -->
<div id="ticket-detail-modal"
     class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300 ease-out"
     style="opacity:0; visibility:hidden">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300" onclick="closeDetail()"></div>

    <!-- Panel -->
    <div id="ticket-modal-content"
         class="absolute inset-x-0 bottom-0 md:inset-y-0 md:left-auto md:right-0 bg-white w-full md:w-[640px] lg:w-[680px] h-[92vh] md:h-full rounded-t-3xl md:rounded-l-3xl md:rounded-tr-none shadow-modal overflow-y-auto transform transition-all duration-400 ease-out flex flex-col"
         style="transform: translateY(100%) md:translateY(0) md:translateX(100%);">

        <!-- Mobile handle -->
        <div class="md:hidden w-full flex justify-center pt-3 pb-1 flex-shrink-0">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full"></div>
        </div>

        <div id="ticket-modal-inner" class="flex flex-col h-full"></div>
    </div>
</div>
