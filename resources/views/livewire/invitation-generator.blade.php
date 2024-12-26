<div class="flex">                    
    <input type="text" name="invitation_link" id="invitation_link" readonly class="appearance-none border border-r-0 rounded-l-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ $invitationLink }}">
    
    <button wire:click.prevent="generateLink" class="text-white border border-neutral-500 border-l-0 bg-theme font-bold px-3 rounded-r-lg focus:outline-none focus:shadow-outline text-xs">
        <pre>Generate Link</pre>
    </button>
</div>