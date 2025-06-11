</div>
        </main>
    </div>

    <!-- Mobile sidebar overlay -->
    <div class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden hidden" id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <script>
    // Toggle sidebar on mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }

    // Close sidebar on mobile when clicking outside
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        
        // Initially hide sidebar on mobile
        if (window.innerWidth < 1024) {
            sidebar.classList.add('-translate-x-full');
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                document.getElementById('sidebar-overlay').classList.add('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        });
    });

    // DataTables initialization with Tailwind styling
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            pageLength: 10,
            responsive: true,
            dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4"<"mb-2 sm:mb-0"l><"mb-2 sm:mb-0"f>>rtip',
            initComplete: function() {
                // Style the DataTable elements with Tailwind classes
                $('.dataTables_length select').addClass('rounded-md border-gray-300 text-sm');
                $('.dataTables_filter input').addClass('rounded-md border-gray-300 text-sm px-3 py-2');
                $('.dataTables_paginate .paginate_button').addClass('px-3 py-2 text-sm');
            }
        });
    }

    // Initialize tooltips with better positioning
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function(e) {
            const tip = document.createElement('div');
            tip.className = 'absolute z-50 px-3 py-2 text-sm text-white bg-gray-900 rounded-lg shadow-lg pointer-events-none';
            tip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tip);

            const rect = this.getBoundingClientRect();
            const tipRect = tip.getBoundingClientRect();
            
            // Position tooltip above element
            tip.style.top = (rect.top - tipRect.height - 8) + 'px';
            tip.style.left = (rect.left + (rect.width - tipRect.width) / 2) + 'px';
            
            // Add arrow
            const arrow = document.createElement('div');
            arrow.className = 'absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900';
            tip.appendChild(arrow);
        });

        tooltip.addEventListener('mouseleave', function() {
            document.querySelectorAll('.absolute.z-50').forEach(t => {
                if (t.textContent === this.getAttribute('data-tooltip')) {
                    t.remove();
                }
            });
        });
    });

    // Enhanced form validation with Tailwind styling
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let hasErrors = false;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    e.preventDefault();
                    hasErrors = true;
                    field.classList.add('border-red-500', 'focus:border-red-500');
                    field.classList.remove('border-gray-300');
                } else {
                    field.classList.remove('border-red-500', 'focus:border-red-500');
                    field.classList.add('border-gray-300');
                }
            });
            
            if (hasErrors) {
                showNotification('Harap isi semua field yang wajib diisi', 'error');
            }
        });
    });

    // Enhanced notification system with Tailwind styling
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        const baseClasses = 'fixed top-4 right-4 z-50 max-w-sm p-4 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out translate-x-full opacity-0';
        
        let typeClasses = '';
        let icon = '';
        
        switch(type) {
            case 'success':
                typeClasses = 'bg-green-500 text-white';
                icon = '<i class="fas fa-check-circle mr-2"></i>';
                break;
            case 'error':
                typeClasses = 'bg-red-500 text-white';
                icon = '<i class="fas fa-exclamation-circle mr-2"></i>';
                break;
            case 'warning':
                typeClasses = 'bg-yellow-500 text-white';
                icon = '<i class="fas fa-exclamation-triangle mr-2"></i>';
                break;
            case 'info':
                typeClasses = 'bg-blue-500 text-white';
                icon = '<i class="fas fa-info-circle mr-2"></i>';
                break;
        }
        
        notification.className = `${baseClasses} ${typeClasses}`;
        notification.innerHTML = `
            <div class="flex items-center">
                ${icon}
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Trigger animation
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
        }, 10);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    // Add smooth scrolling for better UX
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    </script>
</body>
</html>
