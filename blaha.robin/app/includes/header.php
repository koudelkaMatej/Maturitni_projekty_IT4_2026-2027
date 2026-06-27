<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SPŠ HelpDesk</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">
    <script src="assets/tailwind.js"></script>
    <script src="assets/lucide.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        /* ===== Page entry animations ===== */
        .anim-fade-in {
            animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .anim-slide-up {
            animation: slideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(100%); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .anim-slide-right {
            animation: slideRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes slideRight {
            from { opacity: 0; transform: translateX(-100%); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .anim-scale-in {
            animation: scaleIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to   { opacity: 1; transform: scale(1); }
        }

        /* ===== Micro-interactions ===== */
        .hover-lift {
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(0,0,0,0.08);
        }

        .hover-glow {
            transition: box-shadow 0.2s ease;
        }
        .hover-glow:hover {
            box-shadow: 0 0 20px rgba(37,99,235,0.15);
        }

        /* ===== Skeleton shimmer ===== */
        .shimmer {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s ease-in-out infinite;
        }
        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* ===== Spinner ===== */
        .spinner {
            width: 20px; height: 20px;
            border: 2.5px solid #e2e8f0;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ===== Pulse glow (for new badge etc.) ===== */
        .pulse-glow {
            animation: pulseGlow 2s ease-in-out infinite;
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
            50%      { box-shadow: 0 0 0 6px rgba(16,185,129,0); }
        }

        /* ===== Scrollbar ===== */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ===== Mobile bottom safe area ===== */
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }

        /* ===== Smooth backdrop ===== */
        .backdrop-blur {
            -webkit-backdrop-filter: blur(8px);
            backdrop-filter: blur(8px);
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {primary: '#2563eb', secondary: '#475569'},
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
                        'glow': '0 0 15px rgba(37, 99, 235, 0.2)',
                        'card': '0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.06)',
                        'card-hover': '0 10px 30px -5px rgba(0,0,0,0.08), 0 4px 10px -2px rgba(0,0,0,0.04)',
                        'modal': '0 25px 60px -15px rgba(0,0,0,0.25)',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-50 text-slate-800 h-dvh flex overflow-hidden selection:bg-blue-100 selection:text-blue-700">
