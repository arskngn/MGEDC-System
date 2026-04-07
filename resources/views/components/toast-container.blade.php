<div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

<style>
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    @keyframes progress {
        from {
            width: 100%;
        }
        to {
            width: 0;
        }
    }

    .toast {
        animation: slideIn 0.3s ease-out;
    }

    .toast.exit {
        animation: slideOut 0.3s ease-out;
    }

    .toast-progress {
        animation: progress 5s linear forwards;
    }

    .welcome-sparkle {
        position: absolute;
        pointer-events: none;
        z-index: 10;
        animation: sparkle 1.5s ease-out infinite;
    }

    @keyframes sparkle {
        0%, 100% { transform: scale(0) rotate(0deg); opacity: 0; }
        50% { transform: scale(1) rotate(180deg); opacity: 1; }
    }
</style>

<script>
    function showToast(message, type = 'info', duration = 5000) {
        const container = document.getElementById('toast-container');
        
        const icons = {
            'success': '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>',
            'error': '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>',
            'warning': '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.487 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>',
            'info': '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>',
            'welcome': '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd" /></svg>',
        };

        const colors = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'warning': 'bg-amber-500',
            'info': 'bg-blue-500',
            'welcome': 'bg-gradient-to-r from-purple-500 via-indigo-500 to-purple-600',
        };

        const toastElement = document.createElement('div');
        toastElement.className = `toast relative bg-white rounded-lg shadow-xl overflow-hidden border-l-4 ${
            type === 'success' ? 'border-green-500' :
            type === 'error' ? 'border-red-500' :
            type === 'warning' ? 'border-amber-500' :
            type === 'welcome' ? 'border-purple-500 ring-2 ring-purple-200' :
            'border-blue-500'
        }`;
        
        toastElement.innerHTML = `
            <div class="p-4 ${type === 'welcome' ? 'bg-gradient-to-br from-purple-50 to-indigo-50' : ''}">
                <div class="flex items-start gap-3 relative z-20">
                    <div class="${colors[type] || 'bg-blue-500'} text-white rounded-full p-1.5 flex-shrink-0 shadow-sm">
                        ${icons[type] || icons['info']}
                    </div>
                    <div class="flex-1">
                        <p class="${type === 'welcome' ? 'text-indigo-950 font-extrabold text-base' : 'text-gray-800 font-medium text-sm'}">${message}</p>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-600 flex-shrink-0 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="mt-3 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="toast-progress ${colors[type] || 'bg-blue-500'} h-full rounded-full"></div>
                </div>
            </div>
        `;

        // Add sparkles for welcome toast
        if (type === 'welcome') {
            for (let i = 0; i < 6; i++) {
                const sparkle = document.createElement('div');
                sparkle.className = 'welcome-sparkle text-purple-400';
                sparkle.innerHTML = '✨';
                sparkle.style.left = Math.random() * 90 + '%';
                sparkle.style.top = Math.random() * 90 + '%';
                sparkle.style.animationDelay = (Math.random() * 2) + 's';
                toastElement.appendChild(sparkle);
            }
        }

        // Close button functionality
        const closeButton = toastElement.querySelector('button');
        closeButton.addEventListener('click', () => {
            toastElement.classList.add('exit');
            setTimeout(() => toastElement.remove(), 300);
        });

        container.appendChild(toastElement);

        // Auto-remove after duration
        setTimeout(() => {
            if (toastElement.parentElement) {
                toastElement.classList.add('exit');
                setTimeout(() => {
                    if (toastElement.parentElement) {
                        toastElement.remove();
                    }
                }, 300);
            }
        }, duration);

        return toastElement;
    }

    // Listen for expiration event
    window.addEventListener('expiration-notification', (e) => {
        showToast(e.detail.message, 'warning', 5000);
    });
</script>
