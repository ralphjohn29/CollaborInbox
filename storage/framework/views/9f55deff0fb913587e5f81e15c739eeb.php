<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'CollaborInbox'); ?></title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>?v=<?php echo e(time()); ?>">
    <?php echo $__env->yieldContent('styles'); ?>
    
    <!-- Babel Helpers (must be loaded before other scripts) -->
    <script src="<?php echo e(asset('js/babel-helpers.js')); ?>"></script>
    
    <script>
        // Global emergency logout function
        window.emergencyLogout = function() {
            // Clear localStorage data
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            localStorage.removeItem('tenant');
            
            // Reset fetch if it was overridden
            if (window.originalFetch) {
                window.fetch = window.originalFetch;
            }
            
            // Clear cookies
            document.cookie.split(";").forEach(function(c) {
                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
            });
            
            // Redirect to login with special parameter
            window.location.href = '/login?force_logout=1&t=' + new Date().getTime();
            
            return false; // Prevent default anchor behavior if used in href
        };
    </script>
</head>
<body class="<?php echo $__env->yieldContent('body-class', ''); ?>">
    <div id="app">
        <!-- Flash Messages -->
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(session('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?php echo e(session('warning')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(session('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo e(session('info')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php echo $__env->yieldContent('content'); ?>
    </div>
    
    <!-- Scripts -->
    <script src="<?php echo e(asset('js/app.js')); ?>?v=<?php echo e(time()); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html> <?php /**PATH D:\CollaborInbox\resources\views/layouts/app.blade.php ENDPATH**/ ?>