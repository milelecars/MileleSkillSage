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
    <form method="POST" action="<?php echo e(route('register')); ?>" class="w-full max-w-sm mx-auto my-2">
        <?php echo csrf_field(); ?>

        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
            <input id="name" type="text" name="name" required autofocus autocomplete="name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input id="email" 
                type="email" 
                name="email" 
                required 
                autocomplete="username" 
                class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <p id="emailError" class="text-red-500 text-xs hidden">Please enter a valid @milele.com email address.</p>
        </div>


        <div class="mb-4">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" 
                class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <p id="passwordError" class="text-red-500 text-xs hidden">
                Password must be at least 8 characters long and contain at least one letter, one number, and one special character (@$!%*#?&).
            </p>
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" 
                class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <p id="confirmError" class="text-red-500 text-xs hidden">Passwords do not match.</p>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-700 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline">
                Register
            </button>
            <a href="<?php echo e(route('login')); ?>" class="inline-block align-baseline font-bold text-sm text-theme hover:text-blue-600">
                Already registered?
            </a>
        </div>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>

<script>
    document.getElementById('email').addEventListener('input', function () {
        const emailField = this;
        const emailError = document.getElementById('emailError');
        const emailPattern = /^[a-zA-Z0-9._%+-]+@milele\.com$/;

        if (!emailPattern.test(emailField.value)) {
            emailError.classList.remove('hidden');
            emailField.classList.add('border-red-500');
        } else {
            emailError.classList.add('hidden');
            emailField.classList.remove('border-red-500');
        }
    });

    document.getElementById('password').addEventListener('input', function () {
        const passwordField = this;
        const passwordError = document.getElementById('passwordError');
        const passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/;

        if (!passwordPattern.test(passwordField.value)) {
            passwordError.classList.remove('hidden');
            passwordField.classList.add('border-red-500');
        } else {
            passwordError.classList.add('hidden');
            passwordField.classList.remove('border-red-500');
        }
    });

    // Password confirmation validation
    document.getElementById('password_confirmation').addEventListener('input', function () {
        const confirmField = this;
        const passwordField = document.getElementById('password');
        const confirmError = document.getElementById('confirmError');

        if (confirmField.value !== passwordField.value) {
            confirmError.classList.remove('hidden');
            confirmField.classList.add('border-red-500');
        } else {
            confirmError.classList.add('hidden');
            confirmField.classList.remove('border-red-500');
        }
    });
</script><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/auth/register.blade.php ENDPATH**/ ?>