<?php
session_start();

// Get tailor ID from URL
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$tailor_id = (int)$_GET['id'];

define('DB_ACCESS', true);
require_once 'config/db.php';
require_once 'repositories/TailorRepository.php';

// Get tailor details
$tailorRepo = new TailorRepository($conn);
$tailor = $tailorRepo->findById($tailor_id);

if (!$tailor) {
    header('Location: index.php');
    exit;
}

$page_title = $tailor['shop_name'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tailor['shop_name']); ?> - Smart Tailoring Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            border: none;
            border-radius: 8px;
            color: #667eea;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .back-button:hover {
            transform: translateX(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .profile-header {
            display: flex;
            gap: 30px;
            align-items: start;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .shop-avatar {
            width: 150px;
            height: 150px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 3rem;
            font-weight: bold;
            overflow: hidden;
            flex-shrink: 0;
        }

        .shop-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .shop-info h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 15px;
        }

        .rating-summary {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .rating-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }

        .stars {
            color: #ffd700;
            font-size: 1.5rem;
        }

        .rating-count {
            color: #666;
            font-size: 0.9rem;
        }

        .shop-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
        }

        .detail-item i {
            color: #667eea;
            font-size: 1.2rem;
        }

        .reviews-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .reviews-header h2 {
            font-size: 1.8rem;
            color: #333;
        }

        .write-review-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .write-review-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .review-form {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: none;
        }

        .review-form.active {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .star-rating {
            display: flex;
            gap: 10px;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .star-rating i {
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s;
        }

        .star-rating i:hover,
        .star-rating i.active {
            color: #ffd700;
            transform: scale(1.2);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-family: inherit;
            resize: vertical;
            min-height: 120px;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #666;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .review-card {
            background: #fff;
            border: 2px solid #f0f0f0;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .review-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .reviewer-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .reviewer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .reviewer-details h4 {
            font-size: 1rem;
            color: #333;
            margin-bottom: 5px;
        }

        .review-meta {
            display: flex;
            gap: 15px;
            align-items: center;
            font-size: 0.85rem;
            color: #666;
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .review-rating {
            color: #ffd700;
            font-size: 1.2rem;
        }

        .review-text {
            color: #555;
            line-height: 1.6;
            margin-top: 15px;
        }

        .no-reviews {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .no-reviews i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ddd;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .alert.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .alert.active {
            display: block;
            animation: slideDown 0.3s ease-out;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <!-- Tailor Profile Card -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="shop-avatar">
                    <?php if (!empty($tailor['shop_image'])): ?>
                        <?php
                        $imagePath = $tailor['shop_image'];
                        if (!preg_match('/^http/', $imagePath)) {
                            $imagePath = '/smart/smart-tailoring/uploads/shops/' . $imagePath;
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($tailor['shop_name']); ?>">
                    <?php else: ?>
                        <?php echo strtoupper(substr($tailor['shop_name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="shop-info">
                    <h1><?php echo htmlspecialchars($tailor['shop_name']); ?></h1>
                    <div class="rating-summary">
                        <div class="rating-number"><?php echo number_format($tailor['rating'], 1); ?></div>
                        <div>
                            <div class="stars" id="avgStars">
                                <?php
                                $rating = floor($tailor['rating']);
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                }
                                ?>
                            </div>
                            <div class="rating-count"><?php echo $tailor['total_reviews']; ?> reviews</div>
                        </div>
                    </div>
                    <div class="shop-details">
                        <div class="detail-item">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($tailor['owner_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($tailor['city'] ?? 'Location'); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-briefcase"></i>
                            <span><?php echo htmlspecialchars($tailor['experience_years'] ?? '0'); ?>+ years experience</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <div class="reviews-header">
                <h2><i class="fas fa-star" style="color: #ffd700;"></i> Customer Reviews</h2>
                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer'): ?>
                    <button class="write-review-btn" onclick="toggleReviewForm()">
                        <i class="fas fa-pen"></i> Write a Review
                    </button>
                <?php endif; ?>
            </div>

            <!-- Alert Messages -->
            <div class="alert success" id="successAlert"></div>
            <div class="alert error" id="errorAlert"></div>

            <!-- Review Form (Only for logged-in customers) -->
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer'): ?>
                <div class="review-form" id="reviewForm">
                    <h3 style="margin-bottom: 20px;">Share Your Experience</h3>
                    <form id="submitReviewForm">
                        <div class="form-group">
                            <label>Your Rating *</label>
                            <div class="star-rating" id="starRating">
                                <i class="far fa-star" data-rating="1"></i>
                                <i class="far fa-star" data-rating="2"></i>
                                <i class="far fa-star" data-rating="3"></i>
                                <i class="far fa-star" data-rating="4"></i>
                                <i class="far fa-star" data-rating="5"></i>
                            </div>
                            <input type="hidden" id="ratingValue" name="rating" value="0">
                        </div>
                        <div class="form-group">
                            <label>Your Review</label>
                            <textarea id="reviewText" name="review_text" placeholder="Tell us about your experience with this tailor..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Select Order *</label>
                            <select id="orderSelect" name="order_id" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px;">
                                <option value="">Loading orders...</option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Review
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleReviewForm()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Reviews List -->
            <div id="reviewsList">
                <div style="text-align: center; padding: 40px; color: #999;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i>
                    <p style="margin-top: 15px;">Loading reviews...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const tailorId = <?php echo $tailor_id; ?>;
        let selectedRating = 0;

        // Load reviews on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadReviews();
            loadCustomerOrders();
            initializeStarRating();
        });

        // Star rating functionality
        function initializeStarRating() {
            const stars = document.querySelectorAll('#starRating i');
            if (stars.length === 0) return; // Exit if no star rating (not logged in)

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    selectedRating = parseInt(this.dataset.rating);
                    document.getElementById('ratingValue').value = selectedRating;
                    updateStarDisplay(selectedRating);
                });

                star.addEventListener('mouseenter', function() {
                    const rating = parseInt(this.dataset.rating);
                    updateStarDisplay(rating);
                });
            });

            const starRating = document.getElementById('starRating');
            if (starRating) {
                starRating.addEventListener('mouseleave', function() {
                    updateStarDisplay(selectedRating);
                });
            }
        }

        function updateStarDisplay(rating) {
            const stars = document.querySelectorAll('#starRating i');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('far');
                    star.classList.add('fas', 'active');
                } else {
                    star.classList.remove('fas', 'active');
                    star.classList.add('far');
                }
            });
        }

        // Toggle review form
        function toggleReviewForm() {
            const form = document.getElementById('reviewForm');
            if (form) {
                form.classList.toggle('active');
            }
        }

        // Load customer's orders with this tailor
        function loadCustomerOrders() {
            const select = document.getElementById('orderSelect');
            if (!select) return; // Exit if not logged in as customer

            fetch(`/smart/smart-tailoring/api/orders/get_customer_orders.php?tailor_id=${tailorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.orders && data.orders.length > 0) {
                        select.innerHTML = '<option value="">Select an order</option>';
                        data.orders.forEach(order => {
                            select.innerHTML += `<option value="${order.id}">${order.order_number} - ${order.service_type || 'Order'}</option>`;
                        });
                    } else {
                        select.innerHTML = '<option value="">No orders found with this tailor</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading orders:', error);
                    if (select) {
                        select.innerHTML = '<option value="">Error loading orders</option>';
                    }
                });
        }

        // Load and display reviews
        function loadReviews() {
            fetch(`/smart/smart-tailoring/api/reviews/get_reviews.php?tailor_id=${tailorId}`)
                .then(response => response.json())
                .then(data => {
                    const reviewsList = document.getElementById('reviewsList');

                    if (data.success && data.reviews && data.reviews.length > 0) {
                        reviewsList.innerHTML = data.reviews.map(review => `
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        ${review.customer_name.charAt(0).toUpperCase()}
                                    </div>
                                    <div class="reviewer-details">
                                        <h4>${escapeHtml(review.customer_name)}</h4>
                                        <div class="review-meta">
                                            <div class="review-rating">
                                                ${generateStars(review.rating)}
                                            </div>
                                            <span>${formatDate(review.created_at)}</span>
                                            ${review.is_verified ? '<span class="verified-badge"><i class="fas fa-check-circle"></i> Verified Purchase</span>' : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ${review.review_text ? `<div class="review-text">${escapeHtml(review.review_text)}</div>` : ''}
                        </div>
                    `).join('');
                    } else {
                        reviewsList.innerHTML = `
                        <div class="no-reviews">
                            <i class="fas fa-comments"></i>
                            <h3>No reviews yet</h3>
                            <p>Be the first to review this tailor!</p>
                        </div>
                    `;
                    }
                })
                .catch(error => {
                    console.error('Error loading reviews:', error);
                    document.getElementById('reviewsList').innerHTML = `
                    <div class="no-reviews">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Error loading reviews. Please try again later.</p>
                    </div>
                `;
                });
        }

        // Submit review
        const reviewForm = document.getElementById('submitReviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const rating = parseInt(document.getElementById('ratingValue').value);
                const reviewText = document.getElementById('reviewText').value.trim();
                const orderId = parseInt(document.getElementById('orderSelect').value);

                // Validation
                if (rating === 0) {
                    showAlert('Please select a rating', 'error');
                    return;
                }

                if (!orderId) {
                    showAlert('Please select an order', 'error');
                    return;
                }

                // Submit review
                fetch('/smart/smart-tailoring/api/reviews/submit_review.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            tailor_id: tailorId,
                            order_id: orderId,
                            rating: rating,
                            review_text: reviewText
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('Review submitted successfully!', 'success');
                            toggleReviewForm();
                            reviewForm.reset();
                            selectedRating = 0;
                            updateStarDisplay(0);
                            loadReviews();
                            // Reload page to update rating in header
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert(data.message || 'Failed to submit review', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('An error occurred. Please try again.', 'error');
                    });
            });
        }

        // Helper functions
        function generateStars(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                stars += i <= rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
            }
            return stars;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            return date.toLocaleDateString('en-US', options);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showAlert(message, type) {
            const alert = document.getElementById(type === 'success' ? 'successAlert' : 'errorAlert');
            if (alert) {
                alert.textContent = message;
                alert.classList.add('active');
                setTimeout(() => {
                    alert.classList.remove('active');
                }, 5000);
            }
        }
    </script>

</body>

</html>
<?php
db_close();
?>