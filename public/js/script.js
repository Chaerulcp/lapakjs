// Form validation functions
function validatePassword(password) {
    return password.length >= 6;
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhoneNumber(phone) {
    const re = /^[0-9]{10,13}$/;
    return re.test(phone);
}

// Registration form validation
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('no_hp').value;

        let isValid = true;
        let errorMessage = '';

        if (!validatePassword(password)) {
            errorMessage += 'Password minimal 6 karakter\n';
            isValid = false;
        }

        if (password !== confirmPassword) {
            errorMessage += 'Password tidak cocok\n';
            isValid = false;
        }

        if (!validateEmail(email)) {
            errorMessage += 'Format email tidak valid\n';
            isValid = false;
        }

        if (!validatePhoneNumber(phone)) {
            errorMessage += 'Format nomor telepon tidak valid\n';
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert(errorMessage);
        }
    });
}

// Product quantity validation
const quantityInputs = document.querySelectorAll('.quantity-input');
quantityInputs.forEach(input => {
    input.addEventListener('change', function(e) {
        const min = parseInt(this.getAttribute('min')) || 1;
        const max = parseInt(this.getAttribute('max')) || 99;
        let value = parseInt(this.value) || 0;

        if (value < min) {
            this.value = min;
        } else if (value > max) {
            this.value = max;
            alert('Jumlah melebihi stok yang tersedia');
        }
    });
});

// Payment method selection
const paymentMethods = document.querySelectorAll('input[name="metode_pembayaran"]');
if (paymentMethods) {
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            const methodId = this.id;
            const instructions = document.querySelectorAll('.payment-instruction');
            
            instructions.forEach(instruction => {
                instruction.style.display = 'none';
            });

            const selectedInstruction = document.getElementById(`instruction-${methodId}`);
            if (selectedInstruction) {
                selectedInstruction.style.display = 'block';
            }
        });
    });
}

// File upload preview
const fileInput = document.getElementById('bukti_transfer');
if (fileInput) {
    fileInput.addEventListener('change', function(e) {
        const file = this.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (file) {
            if (file.size > maxSize) {
                alert('Ukuran file maksimal 5MB');
                this.value = '';
                return;
            }

            if (!allowedTypes.includes(file.type)) {
                alert('File harus berupa gambar (JPG, JPEG, atau PNG)');
                this.value = '';
                return;
            }

            // Show preview if needed
            const preview = document.getElementById('file-preview');
            if (preview) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
    });
}

// Cart quantity update
const cartQuantityForms = document.querySelectorAll('.quantity-form');
if (cartQuantityForms) {
    cartQuantityForms.forEach(form => {
        const quantityInput = form.querySelector('.quantity-input');
        const originalValue = quantityInput.value;

        quantityInput.addEventListener('change', function() {
            const newValue = this.value;
            if (newValue !== originalValue) {
                form.submit();
            }
        });
    });
}

// Alert auto-dismiss
const alerts = document.querySelectorAll('.alert');
if (alerts) {
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 3000);
    });
}

// Responsive navigation
const navToggle = document.querySelector('.nav-toggle');
const navLinks = document.querySelector('.nav-links');

if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });
}

// Date input validation for checkout
const dateInput = document.getElementById('tanggal_kirim');
if (dateInput) {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    dateInput.setAttribute('min', minDate);

    dateInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const today = new Date();
        
        if (selectedDate <= today) {
            alert('Tanggal pengiriman minimal H+1 dari hari ini');
            this.value = minDate;
        }
    });
}

// Product search functionality
const searchInput = document.getElementById('search');
const productGrid = document.querySelector('.product-grid');

if (searchInput && productGrid) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const products = productGrid.querySelectorAll('.product-card');

        products.forEach(product => {
            const title = product.querySelector('.product-title').textContent.toLowerCase();
            const description = product.querySelector('.product-description').textContent.toLowerCase();

            if (title.includes(searchTerm) || description.includes(searchTerm)) {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        });
    });
}

// Initialize tooltips
const tooltips = document.querySelectorAll('[data-tooltip]');
if (tooltips) {
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function(e) {
            const tip = document.createElement('div');
            tip.className = 'tooltip';
            tip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tip);

            const rect = this.getBoundingClientRect();
            tip.style.top = rect.top - tip.offsetHeight - 10 + 'px';
            tip.style.left = rect.left + (rect.width - tip.offsetWidth) / 2 + 'px';
        });

        tooltip.addEventListener('mouseleave', function() {
            const tooltips = document.querySelectorAll('.tooltip');
            tooltips.forEach(t => t.remove());
        });
    });
}

// Newsletter subscription form handling
const newsletterForm = document.querySelector('.newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = this.querySelector('input[type="email"]').value;
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Mengirim...';

        fetch('subscribe.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `email=${encodeURIComponent(email)}`
        })
        .then(response => response.json())
        .then(data => {
            showNotification(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                this.reset();
            }
        })
        .catch(error => {
            showNotification('Terjadi kesalahan. Silakan coba lagi.', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });
}

// Notification system
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = message;
    
    document.body.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => notification.classList.add('show'), 10);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add styles for notifications
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 5px;
        color: white;
        font-weight: 500;
        transform: translateX(120%);
        transition: transform 0.3s ease;
        z-index: 1000;
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification-success {
        background-color: #28a745;
    }

    .notification-error {
        background-color: #dc3545;
    }
`;
document.head.appendChild(notificationStyles);

// Handle URL parameters for notifications
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('subscribe')) {
    const status = urlParams.get('subscribe');
    if (status === 'success') {
        showNotification('Terima kasih telah berlangganan newsletter kami!');
    } else if (status === 'error') {
        showNotification(urlParams.get('message') || 'Terjadi kesalahan. Silakan coba lagi.', 'error');
    }
}
