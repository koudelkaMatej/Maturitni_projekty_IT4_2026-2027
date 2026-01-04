
</div>

<div id="ticket-detail-modal" class="hidden fixed inset-0 z-50 transition-opacity">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeDetail()"></div>
    <div class="absolute inset-x-0 bottom-0 md:inset-y-0 md:left-auto md:right-0 bg-white w-full md:w-[600px] h-[90vh] md:h-full rounded-t-3xl md:rounded-l-3xl md:rounded-tr-none shadow-2xl overflow-y-auto transform transition-transform duration-300 translate-y-full md:translate-y-0 md:translate-x-full flex flex-col"
         id="ticket-modal-content">

        <div class="md:hidden w-full flex justify-center pt-3 pb-1 flex-shrink-0">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full"></div>
        </div>

        <!-- Dynamic Content will be injected here -->
        <div id="ticket-modal-inner" class="flex flex-col h-full"></div>

    </div>
</div>
