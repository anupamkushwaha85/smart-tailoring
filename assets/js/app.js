/**
 * SMART TAILORING SERVICE - MAIN JAVASCRIPT
 * Handles all frontend interactions and AJAX calls
 */

// ============================================
// UTILITY FUNCTIONS
// ============================================

// Safe JSON parser for handling malformed API responses
function safeParseJSON(text) {
    const jsonStart = text.indexOf('{');
    if (jsonStart === -1) throw new Error('No JSON found');

    let braceCount = 0;
    let jsonEnd = jsonStart;

    for (let i = jsonStart; i < text.length; i++) {
        if (text[i] === '{') braceCount++;
        if (text[i] === '}') braceCount--;
        if (braceCount === 0) {
            jsonEnd = i + 1;
            break;
        }
    }

    return JSON.parse(text.substring(jsonStart, jsonEnd));
}

// ============================================
// GLOBAL VARIABLES
// ============================================
let currentUser = null;
let userType = null; // 'customer' or 'tailor'

// ============================================
// PAGE LOAD INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    // Initialize smooth scrolling
    initSmoothScroll();

    // Load tailors from database
    loadTailors();

    // Initialize search functionality
    initSearch();

    // Initialize filters
    initFilters();

    // Prevent autofill on login forms
    preventAutofill();

    // Check login status
    //  checkLoginStatus();

});

// ============================================
// PREVENT AUTOFILL
// ============================================
function preventAutofill() {
    // Get all password and email inputs in login/register forms
    const inputs = document.querySelectorAll('#loginForm input, #registerForm input');

    inputs.forEach(input => {
        // Remove readonly on focus to allow user input
        input.addEventListener('focus', function () {
            this.removeAttribute('readonly');
        });

        // Add readonly initially to prevent autofill
        input.setAttribute('readonly', 'readonly');

        // Remove readonly after a short delay to prevent autofill but allow normal typing
        setTimeout(() => {
            input.removeAttribute('readonly');
        }, 500);
    });
}

// ============================================
// SMOOTH SCROLLING
// ============================================
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');

            if (href !== '#' && href !== '') {
                e.preventDefault();
                const target = document.querySelector(href);

                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                    // Update active nav link
                    updateActiveNavLink(href);
                }
            }
        });
    });
}

// Update active navigation link
function updateActiveNavLink(href) {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });

    const activeLink = document.querySelector(`.nav-link[href="${href}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

// ============================================
// MOBILE MENU TOGGLE
// ============================================
function toggleMobileMenu() {
    const navMenu = document.getElementById('navMenu');

    if (navMenu.style.display === 'flex') {
        navMenu.style.display = 'none';
    } else {
        navMenu.style.display = 'flex';
        navMenu.style.flexDirection = 'column';
        navMenu.style.position = 'absolute';
        navMenu.style.top = '100%';
        navMenu.style.left = '0';
        navMenu.style.right = '0';
        navMenu.style.background = '#fff';
        navMenu.style.padding = '1rem';
        navMenu.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
    }
}

// ============================================
// SCROLL TO TAILORS SECTION
// ============================================
function scrollToTailors() {
    const tailorsSection = document.getElementById('tailors');
    if (tailorsSection) {
        tailorsSection.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// ============================================
// LOAD TAILORS FROM DATABASE (AJAX)
// ============================================
function loadTailors() {
    const tailorsGrid = document.getElementById('tailorsGrid');

    // Show loading state
    tailorsGrid.innerHTML = `
        <div class="loading">
            <i class="fas fa-spinner fa-spin"></i> Loading tailors from database...
        </div>
    `;

    // Fetch tailors from API
    fetch('api/get_tailors.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.tailors.length > 0) {
                displayTailors(data.tailors);
            } else {
                tailorsGrid.innerHTML = `
                    <div class="loading">
                        <i class="fas fa-exclamation-circle"></i> 
                        No tailors found. Be the first to register!
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading tailors:', error);
            tailorsGrid.innerHTML = `
                <div class="loading">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Unable to load tailors. Please try again later.
                </div>
            `;
        });
}

// ============================================
// DISPLAY TAILORS IN GRID
// ============================================
function displayTailors(tailors) {
    const tailorsGrid = document.getElementById('tailorsGrid');
    tailorsGrid.innerHTML = '';

    tailors.forEach(tailor => {
        const card = createTailorCard(tailor);
        tailorsGrid.appendChild(card);
    });
}

// Create individual tailor card - HYBRID LAYOUT WITH CARD CLICK
function createTailorCard(tailor) {
    const card = document.createElement('div');
    card.className = 'tailor-card-compact';
    card.dataset.area = tailor.area;
    card.dataset.verified = tailor.is_verified;
    card.dataset.priceRange = tailor.price_range;
    card.dataset.rating = tailor.rating || 0;

    // Prepare services array
    const services = tailor.services_offered ? tailor.services_offered.split(',').slice(0, 3) : [];

    // Make entire card clickable
    card.style.cursor = 'pointer';
    card.onclick = () => {
        window.location.href = `/smart/smart-tailoring/view_tailor.php?id=${tailor.id}`;
    };

    card.innerHTML = `
        <!-- TOP SECTION: Image (left) + Title/Rating (right) -->
        <div class="tailor-header-section">
            <div class="tailor-image-left">
                ${tailor.shop_image
            ? `<img src="/smart/smart-tailoring/uploads/shops/${tailor.shop_image}" alt="${tailor.shop_name}">`
            : `<div class="tailor-avatar-left">${tailor.shop_name.charAt(0).toUpperCase()}</div>`
        }
            </div>
            
            <div class="tailor-title-right">
                <div class="title-verified-row">
                    <h3 class="shop-name-title">${tailor.shop_name}</h3>
                    ${tailor.is_verified == 1 ? '<span class="verified-badge-small"><i class="fas fa-check-circle"></i> Verified</span>' : ''}
                </div>
                <p class="owner-subtitle">by ${tailor.owner_name}</p>
                <div class="rating-exp-row">
                    <div class="rating-group">
                        <span class="stars">${generateStars(tailor.rating || 0)}</span>
                        <span class="rating-num">${tailor.rating ? Number(tailor.rating).toFixed(1) : '0.0'}</span>
                        <span class="review-count">(${tailor.total_reviews || 0} reviews)</span>
                    </div>
                    <span class="exp-badge">${tailor.experience_years}+ years</span>
                </div>
            </div>
        </div>
        
        <!-- MIDDLE SECTION: Details (full width from left) -->
        <div class="tailor-details-full">
            <div class="detail-line">
                <i class="fas fa-map-marker-alt"></i>
                <span>${tailor.shop_address}</span>
            </div>
            <div class="detail-line">
                <i class="fas fa-phone"></i>
                <span>+91 ${maskPhone(tailor.phone)}</span>
            </div>
            <div class="detail-line">
                <i class="fas fa-rupee-sign"></i>
                <span>${formatPriceRange(tailor.price_range)}</span>
            </div>
            
            <div class="services-row">
                <span class="services-label">Services:</span>
                ${services.map(service =>
            `<span class="service-badge">${service.trim()}</span>`
        ).join('')}
            </div>
        </div>
        
        <!-- BOTTOM SECTION: Buttons (equally distributed) -->
        <div class="tailor-actions-footer" onclick="event.stopPropagation()">
            <button class="action-btn btn-call-action" onclick="callTailor('${tailor.phone}')">
                <i class="fas fa-phone"></i>
            </button>
            <button class="action-btn btn-location-action" onclick="showLocation(${tailor.id})">
                <i class="fas fa-map-marker-alt"></i>
            </button>
            <button class="action-btn btn-order-action" onclick="placeOrder(${tailor.id})">
                <i class="fas fa-shopping-cart"></i>
            </button>
        </div>
    `;

    return card;
}




// Mask phone number for privacy
function maskPhone(phone) {
    if (phone && phone.length === 10) {
        return phone.substring(0, 4) + 'XXXXXX';
    }
    return phone;
}

// Format price range with proper labels
function formatPriceRange(priceRange) {
    const ranges = {
        'budget': '₹500-2000',
        'medium': '₹400-1800',
        'premium': '₹600-2500'
    };
    return ranges[priceRange] || '₹400-1800';
}

// Action button handlers
function callTailor(phone) {
    window.location.href = `tel:+91${phone}`;
}

function showLocation(tailorId) {
    // Fetch tailor data to get location coordinates
    fetch(`api/get_tailors.php`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.tailors) {
                const tailor = data.tailors.find(t => t.id == tailorId);
                if (tailor) {
                    if (tailor.latitude && tailor.longitude) {
                        // Show location on map
                        showLocationModal(tailor.id, tailor.shop_name, parseFloat(tailor.latitude), parseFloat(tailor.longitude));
                    } else {
                        alert('📍 Location not set for this tailor shop yet.');
                    }
                } else {
                    alert('Tailor not found');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load tailor location');
        });
}

function placeOrder(tailorId) {
    // Check if user is logged in
    if (!checkIfLoggedIn()) {
        showAuthMessage('Please login to place an order', 'error');
        openLoginModal();
        return;
    }

    // Open order modal
    openOrderModal(tailorId);
}

// Check if user is logged in (simple check)
function checkIfLoggedIn() {
    // This will be true if session exists (we'll enhance this)
    return document.body.classList.contains('logged-in');
}



// Generate star rating HTML
function generateStars(rating) {
    let stars = '';
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;

    for (let i = 0; i < fullStars; i++) {
        stars += '<i class="fas fa-star"></i>';
    }

    if (hasHalfStar) {
        stars += '<i class="fas fa-star-half-alt"></i>';
    }

    const emptyStars = 5 - Math.ceil(rating);
    for (let i = 0; i < emptyStars; i++) {
        stars += '<i class="far fa-star"></i>';
    }

    return stars;
}

// Get price range label
function getPriceRangeLabel(priceRange) {
    const labels = {
        'budget': '₹ Budget Friendly',
        'medium': '₹₹ Moderate',
        'premium': '₹₹₹ Premium'
    };
    return labels[priceRange] || '₹₹ Moderate';
}

// ============================================
// SEARCH FUNCTIONALITY
// ============================================
function initSearch() {
    const searchInput = document.getElementById('searchInput');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            filterTailors(searchTerm);
        });
    }
}

// Activate search from navigation icon
function activateSearch() {
    // Scroll to tailors section
    const tailorsSection = document.getElementById('tailors');
    if (tailorsSection) {
        tailorsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

        // Focus on search input after scrolling
        setTimeout(() => {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }, 800);
    }
}

function filterTailors(searchTerm) {
    const tailorCards = document.querySelectorAll('.tailor-card-compact');

    tailorCards.forEach(card => {
        const text = card.textContent.toLowerCase();

        if (text.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// ============================================
// FILTER FUNCTIONALITY
// ============================================
function initFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');

    filterButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Apply filter
            const filter = this.dataset.filter;
            applyFilter(filter);
        });
    });
}

function applyFilter(filter) {
    const tailorCards = document.querySelectorAll('.tailor-card-compact');

    tailorCards.forEach(card => {
        if (filter === 'all') {
            card.style.display = 'block';
        } else if (filter === 'verified') {
            // Show only verified tailors
            card.style.display = card.dataset.verified === '1' ? 'block' : 'none';
        } else if (filter === 'premium') {
            // Show verified tailors with 5-star rating
            const isVerified = card.dataset.verified === '1';
            const rating = parseFloat(card.dataset.rating || 0);
            const isPremium = isVerified && rating >= 5.0;
            card.style.display = isPremium ? 'block' : 'none';
        }
    });
}

// ============================================
// VIEW TAILOR DETAILS
// ============================================
function viewTailorDetails(tailorId) {
    // TODO: Implement tailor details modal or page
    alert('Tailor details feature coming soon! Tailor ID: ' + tailorId);
}


// ============================================
// CHECK LOGIN STATUS
// ============================================
// function checkLoginStatus() {
//     fetch('auth/check_session.php')
//         .then(response => response.json())
//         .then(data => {
//             if (data.loggedIn) {
//                 currentUser = data.user;
//                 userType = data.userType;
//                 updateUIForLoggedInUser();
//             }
//         })
//         .catch(error => {
//             console.log('Not logged in');
//         });
// }

function updateUIForLoggedInUser() {
    // TODO: Update UI when user is logged in
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

// Show notification/toast message
function showNotification(message, type = 'info') {
    // Simple alert for now, can be replaced with better toast library
    alert(message);
}

// Format currency
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toFixed(2);
}

// Validate email
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Validate phone number (Indian format)
function isValidPhone(phone) {
    const regex = /^[6-9]\d{9}$/;
    return regex.test(phone);
}

// ============================================
// AUTHENTICATION MODAL FUNCTIONS
// ============================================

let currentAuthUserType = 'customer'; // Track current user type selection
let currentAuthMode = 'login'; // Track login vs register

// Open Login Modal
function openLoginModal(userType = 'customer') {
    currentAuthUserType = userType;
    currentAuthMode = 'login';

    // Update modal
    document.getElementById('authModal').classList.add('active');
    document.getElementById('modalTitle').textContent = 'Welcome Back!';
    document.getElementById('modalSubtitle').textContent = 'Login to access your account';

    // Show login form, hide register form
    showLoginForm();

    // Set active user type tab
    switchUserType(userType);

    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

// Open Register Modal
function openRegisterModal(userType = 'customer') {
    currentAuthUserType = userType;
    currentAuthMode = 'register';

    // Update modal
    document.getElementById('authModal').classList.add('active');
    document.getElementById('modalTitle').textContent = 'Join Us Today!';
    document.getElementById('modalSubtitle').textContent = 'Create your account';

    // Show register form, hide login form
    showRegisterForm();

    // Set active user type tab
    switchUserType(userType);

    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

// Close Auth Modal
function closeAuthModal() {
    document.getElementById('authModal').classList.remove('active');
    document.body.style.overflow = 'auto';

    // Clear forms
    document.getElementById('loginForm').reset();
    document.getElementById('registerForm').reset();

    // Hide error messages
    hideAuthMessage();
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('authModal');

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeAuthModal();
            }
        });
    }
});

// Switch between Customer and Tailor
function switchUserType(userType) {
    currentAuthUserType = userType;

    // Update tab styling
    document.querySelectorAll('.modal-tab[data-user-type]').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`.modal-tab[data-user-type="${userType}"]`).classList.add('active');

    // Update hidden inputs
    document.getElementById('loginUserType').value = userType;
    document.getElementById('registerUserType').value = userType;

    // Show/hide tailor-specific fields in register form
    if (userType === 'tailor') {
        document.getElementById('tailorFields').style.display = 'block';
        document.getElementById('customerFields').style.display = 'none';

        // Make tailor fields required
        document.querySelectorAll('#tailorFields input[required], #tailorFields textarea[required]').forEach(field => {
            field.required = true;
        });
    } else {
        document.getElementById('tailorFields').style.display = 'none';
        document.getElementById('customerFields').style.display = 'block';

        // Make tailor fields not required
        document.querySelectorAll('#tailorFields input, #tailorFields textarea').forEach(field => {
            field.required = false;
        });
    }
}

// Show Login Form
function showLoginForm() {
    currentAuthMode = 'login';

    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('registerForm').style.display = 'none';

    // Update tabs
    document.getElementById('loginTab').classList.add('active');
    document.getElementById('registerTab').classList.remove('active');

    // Update modal title
    document.getElementById('modalTitle').textContent = 'Welcome Back!';
    document.getElementById('modalSubtitle').textContent = 'Login to access your account';

    // Update switch text
    document.getElementById('switchText').innerHTML =
        `Don't have an account? <a onclick="showRegisterForm()">Register Now</a>`;

    hideAuthMessage();
}

// Show Register Form
function showRegisterForm() {
    currentAuthMode = 'register';

    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';

    // Update tabs
    document.getElementById('loginTab').classList.remove('active');
    document.getElementById('registerTab').classList.add('active');

    // Update modal title
    document.getElementById('modalTitle').textContent = 'Join Us Today!';
    document.getElementById('modalSubtitle').textContent = 'Create your account';

    // Update switch text
    document.getElementById('switchText').innerHTML =
        `Already have an account? <a onclick="showLoginForm()">Login</a>`;

    hideAuthMessage();
}

// Toggle Password Visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Show Auth Message (Error/Success)
function showAuthMessage(message, type = 'error') {
    const messageEl = document.getElementById('authMessage');
    messageEl.textContent = message;
    messageEl.className = `form-message show ${type}`;
}

// Hide Auth Message
function hideAuthMessage() {
    const messageEl = document.getElementById('authMessage');
    messageEl.className = 'form-message';
}

// ============================================
// FORM SUBMISSION HANDLERS
// ============================================

// Handle Login Form Submission
document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');

    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();
            handleLogin();
        });
    }
});

// Handle Register Form Submission
document.addEventListener('DOMContentLoaded', function () {
    const registerForm = document.getElementById('registerForm');

    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            e.preventDefault();
            handleRegister();
        });
    }
});

// Handle Login (CONNECTED TO BACKEND)
function handleLogin() {
    const form = document.getElementById('loginForm');
    const formData = new FormData(form);

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';

    // Send AJAX request
    fetch('auth/login_handler.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.requires_otp) {
                    showAuthMessage(data.message, 'success');
                    setTimeout(() => {
                        closeAuthModal();
                        showOTPModal(data.email, data.user_type);
                    }, 1500);
                } else {
                    showAuthMessage(data.message, 'success');

                    // Redirect after 1 second
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                }
            } else {
                showAuthMessage(data.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            showAuthMessage('Something went wrong. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
}

// Handle Register (CONNECTED TO BACKEND)
function handleRegister() {
    const form = document.getElementById('registerForm');
    const formData = new FormData(form);

    // Validate password length
    const password = form.querySelector('input[name="password"]').value;
    if (password.length < 6) {
        showAuthMessage('Password must be at least 6 characters long', 'error');
        return;
    }

    // Validate phone number
    const phone = form.querySelector('input[name="phone"]').value;
    if (!/^[6-9][0-9]{9}$/.test(phone)) {
        showAuthMessage('Please enter a valid 10-digit mobile number', 'error');
        return;
    }

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';

    // Send AJAX request
    fetch('auth/register_handler.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(text => {
            // Find the complete JSON object with proper brace matching
            const jsonStart = text.indexOf('{');
            if (jsonStart === -1) throw new Error('No JSON found');

            let braceCount = 0;
            let jsonEnd = jsonStart;

            for (let i = jsonStart; i < text.length; i++) {
                if (text[i] === '{') braceCount++;
                if (text[i] === '}') braceCount--;
                if (braceCount === 0) {
                    jsonEnd = i + 1;
                    break;
                }
            }

            const jsonStr = text.substring(jsonStart, jsonEnd);
            return JSON.parse(jsonStr);
        })
        .then(data => {
            console.log('Registration response:', data);
            if (data.success) {
                if (data.requires_otp) {
                    showAuthMessage(data.message, 'success');
                    setTimeout(() => {
                        closeAuthModal();
                        showOTPModal(data.email, data.user_type);
                    }, 1500);
                } else {
                    // No OTP required, proceed to login
                    showAuthMessage(data.message, 'success');
                    setTimeout(() => {
                        showLoginForm();
                        form.reset();
                    }, 2000);
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            } else {
                showAuthMessage(data.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Registration error:', error);
            showAuthMessage('Something went wrong. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
}




// Helper function to generate star icons
// generateStars function is defined earlier in the file (line 342)
// Removed duplicate definition to eliminate code duplication

// ============================================
// ORDER MODAL FUNCTIONS
// ============================================

let currentTailorForOrder = null;

// Open Order Modal
function openOrderModal(tailorId) {
    currentTailorForOrder = tailorId;

    // Set tailor ID in hidden field
    document.getElementById('orderTailorId').value = tailorId;

    // Fetch and display tailor info
    fetchTailorInfoForOrder(tailorId);

    // Open modal
    document.getElementById('orderModal').classList.add('active');
    document.body.style.overflow = 'hidden';

    // Hide any previous messages
    hideOrderMessage();
}

// Close Order Modal
function closeOrderModal() {
    document.getElementById('orderModal').classList.remove('active');
    document.body.style.overflow = 'auto';

    // Reset form
    document.getElementById('orderForm').reset();
    hideOrderMessage();
}

// Fetch tailor info to display in order modal
function fetchTailorInfoForOrder(tailorId) {
    fetch(`api/get_tailors.php`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tailor = data.tailors.find(t => t.id === tailorId);
                if (tailor) {
                    document.getElementById('orderTailorName').textContent = tailor.shop_name;
                    document.getElementById('orderTailorArea').textContent = tailor.area + ', Satna';
                }
            }
        })
        .catch(error => {
            console.error('Error fetching tailor info:', error);
        });
}

// Show Order Message
function showOrderMessage(message, type = 'error') {
    const messageEl = document.getElementById('orderMessage');
    messageEl.textContent = message;
    messageEl.className = `form-message show ${type}`;
}

// Hide Order Message
function hideOrderMessage() {
    const messageEl = document.getElementById('orderMessage');
    messageEl.className = 'form-message';
}

// Handle Order Form Submission
document.addEventListener('DOMContentLoaded', function () {
    const orderForm = document.getElementById('orderForm');

    if (orderForm) {
        orderForm.addEventListener('submit', function (e) {
            e.preventDefault();
            handleOrderSubmission();
        });
    }
});

// Submit Order
function handleOrderSubmission() {
    const form = document.getElementById('orderForm');
    const formData = new FormData(form);

    // Validate
    const serviceType = formData.get('service_type');
    const garmentType = formData.get('garment_type');

    if (!serviceType || !garmentType) {
        showOrderMessage('Please fill in all required fields', 'error');
        return;
    }

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing order...';

    // Send order request
    fetch('api/orders/create_order.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showOrderMessage('Order placed successfully! The tailor will contact you soon.', 'success');

                // Close modal after 2 seconds
                setTimeout(() => {
                    closeOrderModal();
                    // Refresh page or redirect to orders page
                    window.location.href = 'customer/dashboard.php';
                }, 2000);
            } else {
                showOrderMessage(data.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Order submission error:', error);
            showOrderMessage('Failed to place order. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('orderModal');

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeOrderModal();
            }
        });
    }
});

// ==================== FORGOT PASSWORD FUNCTIONALITY ====================

// Show forgot password modal
function showForgotPasswordModal() {
    const authModal = document.getElementById('authModal');
    const forgotModal = document.getElementById('forgotPasswordModal');

    if (authModal) authModal.style.display = 'none';
    if (forgotModal) {
        forgotModal.style.display = 'flex';
        // Clear previous messages and form
        document.getElementById('forgotMessage').style.display = 'none';
        document.getElementById('forgotPasswordForm').reset();
    }
}

// Close forgot password modal
function closeForgotPasswordModal() {
    const forgotModal = document.getElementById('forgotPasswordModal');
    if (forgotModal) {
        forgotModal.style.display = 'none';
        document.getElementById('forgotMessage').style.display = 'none';
        document.getElementById('forgotPasswordForm').reset();
    }
}

// Back to login modal
function backToLogin() {
    const authModal = document.getElementById('authModal');
    const forgotModal = document.getElementById('forgotPasswordModal');

    if (forgotModal) forgotModal.style.display = 'none';
    if (authModal) {
        authModal.style.display = 'flex';
        showLogin(); // Show login tab
    }
}

// Handle forgot password form submission
document.addEventListener('DOMContentLoaded', function () {
    const forgotForm = document.getElementById('forgotPasswordForm');

    if (forgotForm) {
        forgotForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const emailInput = forgotForm.querySelector('input[name="email"]');
            const email = emailInput ? emailInput.value.trim() : '';
            const userType = forgotForm.querySelector('input[name="user_type"]:checked')?.value;
            const messageDiv = document.getElementById('forgotMessage');
            const submitBtn = forgotForm.querySelector('button[type="submit"]');

            // Validate inputs
            if (!email || !userType) {
                if (messageDiv) {
                    messageDiv.textContent = 'Please fill in all fields';
                    messageDiv.className = 'forgot-message error';
                    messageDiv.style.display = 'block';
                }
                return;
            }

            // Show loading state
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending OTP...';

            try {
                // Send OTP for password reset
                const formData = new FormData();
                formData.append('email', email);
                formData.append('purpose', 'password_reset');
                formData.append('user_type', userType);

                const response = await fetch('api/otp/send_otp.php', {
                    method: 'POST',
                    body: formData
                });

                const text = await response.text();
                const data = safeParseJSON(text);

                if (data.success) {
                    // Close forgot password modal and show OTP modal
                    closeForgotPasswordModal();
                    showOTPModal(email, userType, 'password_reset');
                } else {
                    if (messageDiv) {
                        messageDiv.textContent = data.message || 'Failed to send OTP';
                        messageDiv.className = 'forgot-message error';
                        messageDiv.style.display = 'block';
                    }
                }

                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

            } catch (error) {
                console.error('Forgot password error:', error);
                if (messageDiv) {
                    messageDiv.textContent = 'An error occurred. Please try again.';
                    messageDiv.className = 'forgot-message error';
                    messageDiv.style.display = 'block';
                }

                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // Close forgot password modal when clicking outside
    const forgotModal = document.getElementById('forgotPasswordModal');
    if (forgotModal) {
        forgotModal.addEventListener('click', function (e) {
            if (e.target === forgotModal) {
                closeForgotPasswordModal();
            }
        });
    }
});

// ============================================
// OTP VERIFICATION FUNCTIONS
// ============================================

let otpTimerInterval = null;
let resendTimerInterval = null;
let otpUserData = {};

// Show OTP Modal
function showOTPModal(email, userType = 'customer', purpose = 'registration') {
    const otpModal = document.getElementById('otpModal');
    const otpEmailDisplay = document.getElementById('otpEmail');
    const otpEmailHidden = document.getElementById('otpEmailHidden');
    const otpPurpose = document.getElementById('otpPurpose');

    if (otpModal && otpEmailDisplay && otpEmailHidden) {
        otpEmailDisplay.textContent = email;
        otpEmailHidden.value = email;
        otpPurpose.value = purpose;
        otpUserData = { email, userType, purpose };

        otpModal.style.display = 'flex';
        document.getElementById('otpCode').focus();

        // Start 10-minute timer
        startOTPTimer(600); // 10 minutes = 600 seconds

        // Start resend timer (2 minutes)
        startResendTimer(120); // 2 minutes = 120 seconds
    }
}

// Close OTP Modal
function closeOTPModal() {
    const otpModal = document.getElementById('otpModal');
    if (otpModal) {
        otpModal.style.display = 'none';
        document.getElementById('otpVerificationForm').reset();
        clearInterval(otpTimerInterval);
        clearInterval(resendTimerInterval);
    }
}

// Start OTP expiry timer
function startOTPTimer(seconds) {
    clearInterval(otpTimerInterval);
    const timerDisplay = document.getElementById('otpTimer');

    let timeLeft = seconds;

    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const secs = timeLeft % 60;
        timerDisplay.textContent = `${minutes}:${secs.toString().padStart(2, '0')}`;

        if (timeLeft <= 0) {
            clearInterval(otpTimerInterval);
            timerDisplay.textContent = 'Expired';
            timerDisplay.style.color = '#ef4444';
            showOTPMessage('OTP has expired. Please request a new one.', 'error');
        }
        timeLeft--;
    }

    updateTimer();
    otpTimerInterval = setInterval(updateTimer, 1000);
}

// Start resend countdown timer
function startResendTimer(seconds) {
    clearInterval(resendTimerInterval);
    const resendBtn = document.getElementById('resendOtpBtn');
    const resendTimerDiv = document.getElementById('resendTimer');
    const countdownSpan = document.getElementById('resendCountdown');

    resendBtn.disabled = true;
    resendBtn.style.opacity = '0.5';
    resendTimerDiv.style.display = 'block';

    let timeLeft = seconds;

    function updateResendTimer() {
        countdownSpan.textContent = timeLeft;

        if (timeLeft <= 0) {
            clearInterval(resendTimerInterval);
            resendBtn.disabled = false;
            resendBtn.style.opacity = '1';
            resendTimerDiv.style.display = 'none';
        }
        timeLeft--;
    }

    updateResendTimer();
    resendTimerInterval = setInterval(updateResendTimer, 1000);
}

// Handle OTP Verification Form Submit
document.addEventListener('DOMContentLoaded', function () {
    const otpForm = document.getElementById('otpVerificationForm');
    if (otpForm) {
        otpForm.addEventListener('submit', function (e) {
            e.preventDefault();
            verifyOTP();
        });
    }

    // Auto-submit when 6 digits entered
    const otpCodeInput = document.getElementById('otpCode');
    if (otpCodeInput) {
        otpCodeInput.addEventListener('input', function (e) {
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, '');

            // Auto-submit when 6 digits entered
            if (this.value.length === 6) {
                setTimeout(() => verifyOTP(), 300);
            }
        });
    }
});

// Verify OTP
function verifyOTP() {
    const form = document.getElementById('otpVerificationForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    const purpose = document.getElementById('otpPurpose').value;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';

    fetch('api/otp/verify_otp.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showOTPMessage(data.message, 'success');

                setTimeout(() => {
                    closeOTPModal();

                    if (purpose === 'password_reset') {
                        // Show password reset form
                        showPasswordResetForm(otpUserData.email, otpUserData.userType);
                    } else {
                        // Registration verification - show success and redirect
                        alert('✅ Email verified successfully! Please login to continue.');
                        window.location.reload();
                    }
                }, 1500);
            } else {
                showOTPMessage(data.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                // Clear OTP input for retry
                document.getElementById('otpCode').value = '';
                document.getElementById('otpCode').focus();
            }
        })
        .catch(error => {
            console.error('OTP verification error:', error);
            showOTPMessage('Verification failed. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
}

// Show Password Reset Form (after OTP verification)
function showPasswordResetForm(email, userType) {
    const content = `
        <div style="max-width: 400px; margin: 2rem auto; padding: 2rem; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="text-align: center; color: #667eea; margin-bottom: 1.5rem;">
                🔒 Reset Password
            </h2>
            <form id="resetPasswordForm" style="display: flex; flex-direction: column; gap: 1rem;">
                <input type="hidden" name="email" value="${email}">
                <input type="hidden" name="user_type" value="${userType}">
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">New Password</label>
                    <input type="password" name="password" required minlength="6" 
                        placeholder="Enter new password (min 6 characters)"
                        style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="6"
                        placeholder="Confirm new password"
                        style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem;">
                </div>
                
                <div id="resetMessage" style="display: none; padding: 0.75rem; border-radius: 8px; text-align: center;"></div>
                
                <button type="submit" style="padding: 0.75rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                    <i class="fas fa-check"></i> Reset Password
                </button>
            </form>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', `
        <div id="resetPasswordOverlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 10000;">
            ${content}
        </div>
    `);

    // Handle form submission
    document.getElementById('resetPasswordForm').addEventListener('submit', function (e) {
        e.preventDefault();
        handlePasswordReset(this);
    });
}

// Handle Password Reset
function handlePasswordReset(form) {
    const password = form.password.value;
    const confirmPassword = form.confirm_password.value;
    const messageDiv = document.getElementById('resetMessage');
    const submitBtn = form.querySelector('button[type="submit"]');

    if (password !== confirmPassword) {
        messageDiv.textContent = 'Passwords do not match!';
        messageDiv.style.display = 'block';
        messageDiv.style.background = '#fee2e2';
        messageDiv.style.color = '#dc2626';
        return;
    }

    if (password.length < 6) {
        messageDiv.textContent = 'Password must be at least 6 characters!';
        messageDiv.style.display = 'block';
        messageDiv.style.background = '#fee2e2';
        messageDiv.style.color = '#dc2626';
        return;
    }

    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';

    const formData = new FormData(form);

    fetch('api/auth/reset_password.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.textContent = data.message;
                messageDiv.style.display = 'block';
                messageDiv.style.background = '#d1fae5';
                messageDiv.style.color = '#065f46';

                setTimeout(() => {
                    document.getElementById('resetPasswordOverlay').remove();
                    alert('✅ Password reset successfully! Please login with your new password.');
                    window.location.reload();
                }, 2000);
            } else {
                messageDiv.textContent = data.message;
                messageDiv.style.display = 'block';
                messageDiv.style.background = '#fee2e2';
                messageDiv.style.color = '#dc2626';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Password reset error:', error);
            messageDiv.textContent = 'An error occurred. Please try again.';
            messageDiv.style.display = 'block';
            messageDiv.style.background = '#fee2e2';
            messageDiv.style.color = '#dc2626';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
}

// Resend OTP
function resendOTP() {
    const resendBtn = document.getElementById('resendOtpBtn');
    const originalText = resendBtn.innerHTML;

    resendBtn.disabled = true;
    resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    const formData = new FormData();
    formData.append('email', otpUserData.email);
    formData.append('purpose', otpUserData.purpose);
    formData.append('user_type', otpUserData.userType);

    fetch('api/otp/resend_otp.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showOTPMessage('New OTP sent successfully!', 'success');

                // Restart timers
                startOTPTimer(600);
                startResendTimer(120);

                // Clear OTP input
                document.getElementById('otpCode').value = '';
                document.getElementById('otpCode').focus();
            } else {
                showOTPMessage(data.message, 'error');
                resendBtn.disabled = false;
                resendBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Resend OTP error:', error);
            showOTPMessage('Failed to resend OTP. Please try again.', 'error');
            resendBtn.disabled = false;
            resendBtn.innerHTML = originalText;
        });
}

// Skip OTP Verification
function skipOTPVerification() {
    if (confirm('Are you sure you want to skip email verification? You can verify your email later from your profile.')) {
        closeOTPModal();
        alert('Registration completed! Please login to continue.');
        window.location.reload();
    }
}

// Show OTP Message
function showOTPMessage(message, type) {
    const messageDiv = document.getElementById('otpMessage');
    if (messageDiv) {
        messageDiv.textContent = message;
        messageDiv.className = `form-message ${type}`;
        messageDiv.style.display = 'block';

        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    }
}

