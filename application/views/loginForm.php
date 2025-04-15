<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Patrick Hand', cursive;
        }
    </style>
</head>

<body class="bg-white h-screen flex items-center justify-center">
    <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-8 flex flex-col border-2 border-black ">
        <div class="flex flex-col items-center mb-8">
            <div class="w-24 h-24 border-2 border-black rounded-full mb-4 flex items-center justify-center">
                <i class="fas fa-user text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold">Login</h1>
        </div>

        <form action="<?php echo base_url('auth/aksiLogin'); ?>" id="login-form" class="flex flex-col space-y-4" method="POST">
            <div class="flex flex-col">
                <label for="email" class="mb-1 font-bold">email</label>
                <input type="text" id="email" name="email" placeholder="Enter your email"
                    class="p-3 border-2 border-black rounded-lg">
            </div>
            <div class="flex flex-col">
                <label for="password" class="mb-1 font-bold">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" placeholder="Enter your password"
                        class="p-3 border-2 border-black rounded-lg w-full">
                    <button type="button" id="toggle-password" class="absolute right-3 top-3">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <!--        
            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" class="mr-2 h-4 w-4 border-2 border-black">
                <label for="remember">Remember me</label>
            </div>
            -->
            <button type="submit" class="bg-white border-2 border-black p-3 rounded-lg font-bold hover:bg-gray-100 transition duration-300">
                Login
            </button>
        </form>

        <div class="mt-6 text-center">
            <p>Don't have an account? <a href="<?php echo base_url('auth/signup') ?>" class="font-bold underline">Sign up</a></p>
            <a href="#" class="block mt-2 text-sm">Forgot password?</a>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#toggle-password').click(function() {
                const passwordInput = $('#password');
                const icon = $(this).find('i');
                const isHidden = passwordInput.attr('type') === 'password';
                passwordInput.attr('type', isHidden ? 'text' : 'password');
                icon.toggleClass('fa-eye fa-eye-slash');
            });            

            // Form validation
            $("#signup-form").validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    },                    
                    password: {
                        required: true,
                        minlength: 6
                    }
                },
                messages: {
                    email: {
                        required: "Please enter your email.",
                        email: "Please enter a valid email address."
                    },                    
                    password: {
                        required: "Please provide a password.",
                        minlength: "Password must be at least 6 characters long."
                    }
                },
                errorElement: "div",
                errorClass: "text-red-600 text-sm mt-1",
                highlight: function(element) {
                    $(element).addClass("border-red-500");
                },
                unhighlight: function(element) {
                    $(element).removeClass("border-red-500");
                }
            });
            /*
            // Form submission (you would add AJAX here to handle actual login)
            $('#login-form').submit(function(e) {
                e.preventDefault();
                // Add your login logic here
                console.log('Login submitted');
            });
            */
        });
    </script>
</body>

</html>