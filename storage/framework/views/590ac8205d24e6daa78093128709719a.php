<div class="flex">                    
    <input type="text" name="invitation_link" id="invitation_link" readonly class="shadow appearance-none border border-r-0 rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo e($invitationLink); ?>">
    
    <button wire:click.prevent="generateLink" class="text-slate-900 bg-slate-300 hover:bg-slate-200 border border-neutral-500 font-bold px-3 rounded-r shadow focus:outline-none focus:shadow-outline text-xs">
        <pre>Generate Link</pre>
    </button>
</div><?php /**PATH /Users/heliahaghighi/Desktop/Projects/AGCT-Software/resources/views/livewire/invitation-generator.blade.php ENDPATH**/ ?>