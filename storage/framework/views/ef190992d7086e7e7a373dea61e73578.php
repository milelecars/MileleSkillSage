<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    
    <form method="POST" action="<?php echo e(route('login')); ?>" class="w-full max-w-sm mx-auto my-2">
        <?php echo csrf_field(); ?>

        
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input 
                id="email" 
                type="email" 
                name="email" 
                value="<?php echo e(old('email')); ?>"
                required 
                autofocus 
                autocomplete="username" 
                class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
            >
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-500 text-xs italic mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
       
        
        <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
            <input 
                id="password" 
                type="password" 
                name="password" 
                required 
                autocomplete="current-password" 
                class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
            >
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-500 text-xs italic mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
       
        
        <div class="mb-6">
            <label for="OTP" class="block text-gray-700 text-sm font-bold mb-2">OTP</label>
            <div class="flex">
                <input 
                    id="OTP" 
                    type="text" 
                    name="OTP" 
                    placeholder="Enter OTP sent to your email"
                    required 
                    autocomplete="one-time-code" 
                    class="appearance-none border border-r-0 text-sm rounded-l-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?php $__errorArgs = ['OTP'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                >
                
                <button 
                    type="button" 
                    id="otp-button"
                    onclick="handleOtpAction()" 
                    class="text-white border border-neutral-500 border-l-0 bg-theme font-bold px-3 rounded-r-lg focus:outline-none focus:shadow-outline text-xs flex items-center">
                    
                    <span id="otp-label"><?php echo e(session('otp_generated') ? 'Resend' : 'Generate'); ?></span>
                    
                    
                    <svg id="regenerate-icon" xmlns="http://www.w3.org/2000/svg" 
                        class="<?php echo e(session('otp_generated') ? '' : 'hidden'); ?> h-4 w-4 ml-1" 
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
            <?php $__errorArgs = ['OTP'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php if(session('error')): ?>
                <p class="text-red-500 text-xs mt-1"><?php echo e(session('error')); ?></p>
            <?php endif; ?>
            <?php if(session('success')): ?>
                <p class="text-green-500 text-xs mt-1"><?php echo e(session('success')); ?></p>
            <?php endif; ?>
        </div>

        
        <?php if($errors->has('authentication')): ?>
            <div class="mb-4 text-red-500 text-sm">
                <?php echo e($errors->first('authentication')); ?>

            </div>
        <?php endif; ?>

        
        <div class="flex items-center justify-between">
            
            <button type="submit" class="bg-blue-700 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline">
                Log in
            </button>
           
            <a href="<?php echo e(route('register')); ?>" class="inline-block align-baseline font-bold text-sm text-theme hover:text-blue-600">
                Need an account?
            </a>
        </div>
    </form>

    
    <form id="generate-otp-form" method="POST" action="<?php echo e(route('generate.otp')); ?>" style="display: none;">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="email" id="otp-email-field">
    </form>

    <script>
        function handleOtpAction() {
            const emailField = document.getElementById('email');
            document.getElementById('otp-email-field').value = emailField.value;
            
            
            const otpLabel = document.getElementById('otp-label');
            const regenerateIcon = document.getElementById('regenerate-icon');
            otpLabel.textContent = 'Resend';
            regenerateIcon.classList.remove('hidden');
            
            
            document.getElementById('generate-otp-form').submit();
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/auth/login.blade.php ENDPATH**/ ?>