

<?php $__env->startSection('title', 'Login - CollaborInbox'); ?>

<?php $__env->startSection('body-class', 'login-page'); ?>

<?php $__env->startSection('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/auth.css')); ?>">
    <style>
        .emergency-logout {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            font-size: 12px;
            cursor: pointer;
            z-index: 1000;
        }
        .emergency-logout:hover {
            background-color: #e9ecef;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <?php if(session('error')): ?>
            <div class="alert alert-danger">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="login-container">
            <div class="login-form">
                <div class="login-header">
                    <h1>CollaborInbox</h1>
                    <p>Please log in to your account</p>
                </div>
                
                <form method="POST" action="<?php echo e(route('login')); ?>">
                    <?php echo csrf_field(); ?>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo e(old('email')); ?>" required autocomplete="email" autofocus />
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password" />
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    
                    <div class="form-group remember-me">
                        <input type="checkbox" id="remember" name="remember" <?php echo e(old('remember') ? 'checked' : ''); ?> />
                        <label for="remember">Remember me</label>
                    </div>
                    
                    <button type="submit" class="primary-button">
                        Log In
                    </button>
                </form>
                
                <div class="login-footer">
                    <p class="help-text">Having trouble logging in? Contact your administrator.</p>
                    <p class="signup-link">Don't have an account? <a href="<?php echo e(route('signup')); ?>">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="emergency-logout" id="emergency-logout">
        Emergency Logout (Stuck in login loop?)
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        // Emergency logout function
        document.getElementById('emergency-logout').addEventListener('click', function() {
            // Clear any session cookies
            document.cookie.split(";").forEach(function(c) {
                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
            });
            
            // Force reload from server, not cache
            window.location.href = '/login?nocache=' + new Date().getTime();
            
            alert('Authentication data cleared! Page will reload.');
        });
    </script>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CollaborInbox\resources\views/auth/login.blade.php ENDPATH**/ ?>