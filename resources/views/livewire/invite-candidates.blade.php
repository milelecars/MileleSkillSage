<div>
    <div class="space-y-4 mb-4">
        @foreach($emailList as $index => $email)
            <div class="flex justify-between items-center bg-gray-50 p-3 rounded">
                <span class="text-gray-700">{{ $email }}</span>
                <button wire:click="removeEmail({{ $index }})" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        @endforeach
    </div>

    <form wire:submit.prevent="addEmail" class="flex gap-2">
        <input type="email" 
               wire:model.live="newEmail" 
               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
               placeholder="Enter email address">
        
        <button type="submit" 
                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
        </button>
    </form>

    @error('newEmail') 
        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
    @enderror

    @if($emailList)
        <div class="mt-6">
            <button wire:click="submitInvitations" 
                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                Send Invitations
            </button>
        </div>
    @endif

    @error('submission') 
        <div class="text-red-500 mt-2">{{ $message }}</div>
    @enderror
</div>