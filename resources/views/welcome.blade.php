<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Laravel Website Monitoring Tool - Real-time uptime monitoring with instant Telegram alerts. Keep your websites online 24/7.">

        <title>{{ config('app.name', 'Laravel') }} - Website Monitoring Tool</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <!-- Header -->
        <header class="fixed top-0 left-0 right-0 bg-white dark:bg-gray-800 shadow-sm z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Website Monitor</h1>
                    </div>

                    <!-- Navigation -->
                    <nav class="flex items-center space-x-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Dashboard
                            </a>
                        @else
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" 
                                   class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Log in
                                </a>
                            @endif
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="pt-16">
            <!-- Hero Section -->
            <section class="bg-gradient-to-br from-blue-600 to-indigo-700 py-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
                            Laravel Website Monitoring Tool
                        </h1>
                        <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
                            Real-time uptime monitoring with instant Telegram alerts. Keep your websites online 24/7. 🚀📊🔔
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            @auth
                                <a href="{{ url('/dashboard') }}" 
                                   class="inline-flex items-center px-8 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors duration-200 text-lg">
                                    Go to Dashboard
                                </a>
                            @else
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" 
                                       class="inline-flex items-center px-8 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors duration-200 text-lg">
                                        Get Started
                                    </a>
                                @endif
                            @endif
                            <a href="#features" 
                               class="inline-flex items-center px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-indigo-600 transition-colors duration-200 text-lg">
                                View Features
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features" class="py-20 bg-white dark:bg-gray-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                            Powerful Monitoring Features
                        </h2>
                        <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                            Comprehensive website monitoring with advanced error detection and instant notifications
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <!-- Feature 1 -->
                        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                            <div class="text-4xl mb-4">🔄</div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                Real-time Monitoring
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Continuous uptime monitoring with configurable check intervals to ensure your websites are always accessible.
                            </p>
                        </div>

                        <!-- Feature 2 -->
                        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                            <div class="text-4xl mb-4">📱</div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                Instant Telegram Alerts
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Immediate notifications via Telegram Bot when issues are detected, keeping you informed instantly.
                            </p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                            <div class="text-4xl mb-4">🔒</div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                SSL Certificate Detection
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Monitor SSL certificates, detect expiration and invalid certificates to maintain security.
                            </p>
                        </div>

                        <!-- Feature 4 -->
                        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                            <div class="text-4xl mb-4">🌐</div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                DNS Error Detection
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Detect DNS resolution failures and domain issues that could affect your website's accessibility.
                            </p>
                        </div>

                        <!-- Feature 5 -->
                        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                            <div class="text-4xl mb-4">📋</div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                Bulk Website Import
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Add multiple websites at once via CSV upload for efficient management of large portfolios.
                            </p>
                        </div>

                        <!-- Feature 6 -->
                        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                            <div class="text-4xl mb-4">⏰</div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                Scheduled Monitoring
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Automated background monitoring using Laravel's task scheduler for reliable 24/7 monitoring.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- How It Works Section -->
            <section class="py-20 bg-gray-50 dark:bg-gray-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                            Get Started in 4 Simple Steps
                        </h2>
                        <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                            Start monitoring your websites in minutes with our easy setup process
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <!-- Step 1 -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                                1
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                Create Account
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Sign up and configure your Telegram bot for instant notifications
                            </p>
                        </div>

                        <!-- Step 2 -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                                2
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                Add Websites
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Add websites individually or import in bulk via CSV for efficient management
                            </p>
                        </div>

                        <!-- Step 3 -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                                3
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                Configure Monitoring
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Set check intervals and alert preferences to match your monitoring needs
                            </p>
                        </div>

                        <!-- Step 4 -->
                        <div class="text-center">
                            <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                                4
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                Receive Alerts
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Get instant Telegram notifications when issues occur with your websites
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Error Detection Section -->
            <section class="py-20 bg-white dark:bg-gray-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                            Comprehensive Error Detection
                        </h2>
                        <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                            Our monitoring system detects a wide range of issues that could affect your website's performance
                        </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-4 py-2 rounded-full text-center font-medium">
                            SSL Certificate Issues
                        </div>
                        <div class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-4 py-2 rounded-full text-center font-medium">
                            DNS Resolution Errors
                        </div>
                        <div class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-4 py-2 rounded-full text-center font-medium">
                            Connection Timeouts
                        </div>
                        <div class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-4 py-2 rounded-full text-center font-medium">
                            HTTP 4xx/5xx Errors
                        </div>
                        <div class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-4 py-2 rounded-full text-center font-medium">
                            Redirect Loops
                        </div>
                        <div class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-4 py-2 rounded-full text-center font-medium">
                            Empty Responses
                        </div>
                    </div>
                </div>
            </section>

            <!-- Final CTA Section -->
            <section class="py-20 bg-gradient-to-r from-indigo-600 to-purple-600">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                        Ready to Monitor Your Websites?
                    </h2>
                    <p class="text-xl text-indigo-100 mb-8 max-w-2xl mx-auto">
                        Start monitoring your websites in minutes with instant Telegram alerts
                    </p>
                    @auth
                        <a href="{{ url('/dashboard') }}" 
                           class="inline-flex items-center px-8 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors duration-200 text-lg">
                            Go to Dashboard
                        </a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" 
                               class="inline-flex items-center px-8 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors duration-200 text-lg">
                                Start Free
                            </a>
                        @endif
                    @endif
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 dark:bg-gray-900 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-gray-400">
                    Made with ❤️ for reliable website monitoring
                </p>
            </div>
        </footer>
    </body>
</html>
