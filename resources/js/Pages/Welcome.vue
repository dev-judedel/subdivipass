<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center p-4">
        <div class="max-w-2xl w-full bg-white rounded-2xl shadow-2xl p-8 md:p-12">
            <div class="text-center mb-8">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    🏘️ SubdiPass
                </h1>
                <p class="text-xl text-gray-600 mb-2">
                    Digital Pass Management System
                </p>
                <div class="inline-block px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                    ✓ Setup Complete
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                        Technology Stack
                    </h2>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="font-semibold text-gray-700">Backend</p>
                            <ul class="text-gray-600 space-y-1 mt-2">
                                <li>✓ Laravel 10.49.1</li>
                                <li>✓ PHP 8.1.10</li>
                                <li>✓ MySQL 8.0.43</li>
                                <li>✓ Redis 5.0.14.1</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-700">Frontend</p>
                            <ul class="text-gray-600 space-y-1 mt-2">
                                <li>✓ Vue.js 3.4</li>
                                <li>✓ Inertia.js 2.0</li>
                                <li>✓ Tailwind CSS 3.x</li>
                                <li>✓ Vite</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 rounded-lg p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                        Core Packages Installed
                    </h2>
                    <ul class="text-gray-600 space-y-2 text-sm">
                        <li>✓ <span class="font-semibold">Laravel Sanctum</span> - API Authentication</li>
                        <li>✓ <span class="font-semibold">Spatie Permission</span> - Role & Permission Management</li>
                        <li>✓ <span class="font-semibold">Spatie Activity Log</span> - Audit Logging</li>
                        <li>✓ <span class="font-semibold">Endroid QR Code</span> - QR Generation</li>
                        <li>✓ <span class="font-semibold">ZXing</span> - QR Scanning</li>
                        <li>✓ <span class="font-semibold">Predis</span> - Redis Client</li>
                    </ul>
                </div>

                <div class="bg-purple-50 rounded-lg p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                        Next Steps
                    </h2>
                    <ol class="text-gray-600 space-y-2 text-sm list-decimal list-inside">
                        <li>Design and create database migrations</li>
                        <li>Implement authentication system</li>
                        <li>Set up role & permission structure</li>
                        <li>Create pass management system</li>
                        <li>Build QR code generation & scanning</li>
                        <li>Develop guard mobile interface (PWA)</li>
                    </ol>
                </div>

                <div class="text-center pt-4">
                    <p class="text-gray-500 text-sm">
                        Generated with Claude Code • November 2024
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
</script>
