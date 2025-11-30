<?php

/**
 * Customer Measurements Page
 * Manage body measurements for tailoring orders
 */

session_start();

// Check if user is logged in and is a customer
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../index.php');
    exit;
}

$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Measurements - Smart Tailoring Service</title>

    <link rel="icon" type="image/jpg" href="../assets/images/STP-favicon.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .measurements-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .measurements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .measurement-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .measurement-card:hover {
            border-color: #667eea;
            box-shadow: 0 6px 12px rgba(102, 126, 234, 0.2);
        }

        .measurement-card.default {
            border-color: #10b981;
            background: linear-gradient(to bottom, #ecfdf5 0%, white 20%);
        }

        .measurement-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .measurement-label {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
        }

        .default-badge {
            background: #10b981;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .measurement-context {
            display: inline-block;
            background: #e0e7ff;
            color: #4338ca;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .measurements-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin: 1rem 0;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 8px;
        }

        .measurement-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem;
            background: white;
            border-radius: 6px;
        }

        .measurement-item label {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .measurement-item value {
            font-weight: 600;
            color: #1f2937;
        }

        .measurement-notes {
            font-size: 0.9rem;
            color: #6b7280;
            font-style: italic;
            margin: 0.5rem 0;
        }

        .measurement-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .btn-set-default {
            background: #10b981;
            color: white;
        }

        .btn-set-default:hover {
            background: #059669;
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
        }

        .btn-edit:hover {
            background: #2563eb;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .btn-add-new {
            background: white;
            color: #667eea;
            border: 2px solid white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-add-new:hover {
            background: #764ba2;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content {
            background: white;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 12px;
            max-width: 800px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .modal-header h2 {
            margin: 0;
            color: #1f2937;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #6b7280;
        }

        .close-modal:hover {
            color: #ef4444;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .measurements-grid-form {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
        }

        .btn-save {
            background: #667eea;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            font-size: 1.1rem;
            transition: all 0.3s;
        }

        .btn-save:hover {
            background: #764ba2;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .empty-state i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {

            /* Hide welcome text in navbar on mobile */
            .welcome-text {
                display: none !important;
            }

            /* Hide navigation menu on mobile */
            .nav-menu {
                display: none;
            }

            /* Make dashboard and logout buttons icon-only on mobile */
            .btn-dashboard .btn-text,
            .btn-logout .btn-text {
                display: none;
            }

            .btn-dashboard,
            .btn-logout {
                width: 40px;
                height: 40px;
                padding: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                margin-left: 0.5rem;
            }

            .btn-dashboard i,
            .btn-logout i {
                margin: 0;
                font-size: 1.1rem;
            }

            .measurements-container {
                padding: 0 1rem;
                margin: 1rem auto;
            }

            .measurements-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1.5rem 1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .measurement-card {
                padding: 1rem;
            }

            .measurement-header h3 {
                font-size: 1.1rem;
            }

            .measurement-details {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .measurement-item {
                padding: 0.75rem;
            }

            .item-value {
                font-size: 1.25rem;
            }

            .measurement-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
                padding: 0.75rem;
                font-size: 0.9rem;
            }

            /* Responsive measurement form grid - 2 columns on tablet */
            .measurements-grid-form {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.875rem;
            }

            .modal-content {
                margin: 1rem;
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .measurements-container {
                padding: 0 0.75rem;
            }

            .page-header {
                padding: 1.25rem 0.85rem;
            }

            .page-header h1 {
                font-size: 1.25rem;
            }

            .measurement-card {
                padding: 0.85rem;
            }

            .measurement-header h3 {
                font-size: 1rem;
            }

            .item-value {
                font-size: 1.1rem;
            }

            .btn-action {
                padding: 0.65rem;
                font-size: 0.85rem;
            }

            /* Single column for mobile */
            .measurements-grid-form {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .modal-content {
                margin: 0.5rem;
                padding: 1rem;
            }

            .modal-header h2 {
                font-size: 1.25rem;
            }

            .form-group label {
                font-size: 0.9rem;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 0.65rem;
                font-size: 0.95rem;
                min-height: 44px;
                /* Touch-friendly */
            }

            .btn-save {
                padding: 0.85rem;
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="../assets/images/logo.png" alt="Logo">
                <span class="logo-text">Smart Tailoring Service</span>
            </div>

            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link">Home</a></li>
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="orders.php" class="nav-link">My Orders</a></li>
                <li><a href="measurements.php" class="nav-link active">Measurements</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
            </ul>

            <div class="nav-auth">
                <span class="welcome-text" style="margin-right: 1rem;">Welcome, <?php echo htmlspecialchars($customer_name); ?>!</span>
                <a href="dashboard.php" class="btn-dashboard" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i> <span class="btn-text">Dashboard</span>
                </a>
                <button class="btn-logout" onclick="window.location.href='../auth/logout.php'">
                    <i class="fas fa-sign-out-alt"></i> <span class="btn-text">Logout</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Measurements Container -->
    <div class="measurements-container">

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1><i class="fas fa-ruler"></i> My Measurements</h1>
                <p>Save your body measurements for quick and accurate orders</p>
            </div>
            <button class="btn-add-new" onclick="openMeasurementModal()">
                <i class="fas fa-plus"></i> Add New Measurement
            </button>
        </div>

        <!-- Measurements Grid -->
        <div class="measurements-grid" id="measurementsGrid">
            <div class="loading" style="text-align: center; padding: 3rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #667eea;"></i>
                <p>Loading your measurements...</p>
            </div>
        </div>

    </div>

    <!-- Add/Edit Measurement Modal -->
    <div id="measurementModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Measurement</h2>
                <button class="close-modal" onclick="closeMeasurementModal()">&times;</button>
            </div>

            <form id="measurementForm" onsubmit="saveMeasurement(event)">
                <input type="hidden" id="measurement_id" name="measurement_id">

                <div class="form-group">
                    <label for="label">Label / Name *</label>
                    <input type="text" id="label" name="label" placeholder="e.g., Formal Shirts, Party Wear" required>
                </div>

                <div class="form-group">
                    <label for="garment_context">Garment Type *</label>
                    <select id="garment_context" name="garment_context" required onchange="updateMeasurementFields()">
                        <option value="">Select garment type</option>
                        <optgroup label="Men's Wear">
                            <option value="Shirt">Shirt</option>
                            <option value="Pants">Pants/Trousers</option>
                            <option value="Suit">Suit</option>
                            <option value="Kurta">Kurta</option>
                            <option value="Sherwani">Sherwani</option>
                            <option value="Blazer">Blazer</option>
                            <option value="Waistcoat">Waistcoat</option>
                        </optgroup>
                        <optgroup label="Women's Wear">
                            <option value="Blouse">Blouse</option>
                            <option value="Saree">Saree</option>
                            <option value="Salwar Kameez">Salwar Kameez</option>
                            <option value="Lehenga">Lehenga</option>
                            <option value="Dress">Dress</option>
                            <option value="Gown">Gown</option>
                            <option value="Kurti">Kurti</option>
                        </optgroup>
                        <optgroup label="Kids Wear">
                            <option value="Kids Shirt">Kids Shirt</option>
                            <option value="Kids Dress">Kids Dress</option>
                            <option value="Kids Frock">Kids Frock</option>
                        </optgroup>
                    </select>
                </div>

                <div class="form-group">
                    <label>Measurements (in inches) *</label>
                    <div class="measurements-grid-form" id="measurementFields">
                        <!-- Dynamically populated based on garment type -->
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Any special notes or preferences"></textarea>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="is_default" name="is_default" value="1">
                    <label for="is_default" style="margin: 0;">Set as default measurement</label>
                </div>

                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Save Measurement
                </button>
            </form>
        </div>
    </div>

    <script>
        // Measurement fields templates for all garment types
        const measurementFields = {
            'Shirt': [{
                    name: 'chest',
                    label: 'Chest'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'sleeve_length',
                    label: 'Sleeve Length'
                },
                {
                    name: 'shirt_length',
                    label: 'Shirt Length'
                },
                {
                    name: 'collar',
                    label: 'Collar Size'
                },
                {
                    name: 'neck',
                    label: 'Neck'
                },
                {
                    name: 'bicep',
                    label: 'Bicep'
                },
                {
                    name: 'wrist',
                    label: 'Wrist'
                }
            ],
            'Pants': [{
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'hip',
                    label: 'Hip'
                },
                {
                    name: 'inseam',
                    label: 'Inseam/Length'
                },
                {
                    name: 'outseam',
                    label: 'Outseam'
                },
                {
                    name: 'thigh',
                    label: 'Thigh'
                },
                {
                    name: 'knee',
                    label: 'Knee'
                },
                {
                    name: 'ankle',
                    label: 'Bottom/Ankle'
                },
                {
                    name: 'rise',
                    label: 'Rise (Front)'
                }
            ],
            'Suit': [{
                    name: 'chest',
                    label: 'Chest'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'sleeve_length',
                    label: 'Sleeve Length'
                },
                {
                    name: 'jacket_length',
                    label: 'Jacket Length'
                },
                {
                    name: 'pant_waist',
                    label: 'Pant Waist'
                },
                {
                    name: 'inseam',
                    label: 'Pant Inseam'
                },
                {
                    name: 'neck',
                    label: 'Neck'
                }
            ],
            'Kurta': [{
                    name: 'chest',
                    label: 'Chest'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'sleeve_length',
                    label: 'Sleeve Length'
                },
                {
                    name: 'kurta_length',
                    label: 'Kurta Length'
                },
                {
                    name: 'neck',
                    label: 'Neck'
                }
            ],
            'Sherwani': [{
                    name: 'chest',
                    label: 'Chest'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'sleeve_length',
                    label: 'Sleeve Length'
                },
                {
                    name: 'sherwani_length',
                    label: 'Sherwani Length'
                },
                {
                    name: 'neck',
                    label: 'Neck'
                }
            ],
            'Blazer': [{
                    name: 'chest',
                    label: 'Chest'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'sleeve_length',
                    label: 'Sleeve Length'
                },
                {
                    name: 'blazer_length',
                    label: 'Blazer Length'
                }
            ],
            'Waistcoat': [{
                    name: 'chest',
                    label: 'Chest'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'waistcoat_length',
                    label: 'Waistcoat Length'
                }
            ],
            'Blouse': [{
                    name: 'bust',
                    label: 'Bust'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'sleeve_length',
                    label: 'Sleeve Length'
                },
                {
                    name: 'blouse_length',
                    label: 'Blouse Length'
                },
                {
                    name: 'armhole',
                    label: 'Armhole'
                }
            ],
            'Saree': [{
                    name: 'blouse_bust',
                    label: 'Blouse Bust'
                },
                {
                    name: 'blouse_waist',
                    label: 'Blouse Waist'
                },
                {
                    name: 'blouse_shoulder',
                    label: 'Shoulder Width'
                },
                {
                    name: 'blouse_length',
                    label: 'Blouse Length'
                },
                {
                    name: 'sleeve_length',
                    label: 'Sleeve Length'
                },
                {
                    name: 'petticoat_waist',
                    label: 'Petticoat Waist'
                },
                {
                    name: 'petticoat_length',
                    label: 'Petticoat Length'
                }
            ],
            'Salwar Kameez': [{
                    name: 'bust',
                    label: 'Bust'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'hip',
                    label: 'Hip'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'sleeve_length',
                    label: 'Sleeve Length'
                },
                {
                    name: 'kameez_length',
                    label: 'Kameez Length'
                },
                {
                    name: 'salwar_length',
                    label: 'Salwar Length'
                }
            ],
            'Lehenga': [{
                    name: 'bust',
                    label: 'Bust'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'hip',
                    label: 'Hip'
                },
                {
                    name: 'choli_length',
                    label: 'Choli Length'
                },
                {
                    name: 'lehenga_length',
                    label: 'Lehenga Length'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                }
            ],
            'Dress': [{
                    name: 'bust',
                    label: 'Bust'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'hip',
                    label: 'Hip'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'sleeve_length',
                    label: 'Sleeve Length'
                },
                {
                    name: 'dress_length',
                    label: 'Dress Length'
                }
            ],
            'Gown': [{
                    name: 'bust',
                    label: 'Bust'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'hip',
                    label: 'Hip'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'sleeve_length',
                    label: 'Sleeve Length'
                },
                {
                    name: 'gown_length',
                    label: 'Gown Length'
                }
            ],
            'Kurti': [{
                    name: 'bust',
                    label: 'Bust'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'hip',
                    label: 'Hip'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'sleeve_length',
                    label: 'Sleeve Length'
                },
                {
                    name: 'kurti_length',
                    label: 'Kurti Length'
                }
            ],
            'Kids Shirt': [{
                    name: 'chest',
                    label: 'Chest'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'sleeve_length',
                    label: 'Sleeve Length'
                },
                {
                    name: 'shirt_length',
                    label: 'Shirt Length'
                }
            ],
            'Kids Dress': [{
                    name: 'chest',
                    label: 'Chest'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'dress_length',
                    label: 'Dress Length'
                }
            ],
            'Kids Frock': [{
                    name: 'chest',
                    label: 'Chest'
                },
                {
                    name: 'waist',
                    label: 'Waist'
                },
                {
                    name: 'shoulder_width',
                    label: 'Shoulder Width'
                },
                {
                    name: 'frock_length',
                    label: 'Frock Length'
                }
            ]
        };

        // Load measurements on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadMeasurements();
            updateMeasurementFields(); // Initialize with default fields
        });

        // Load all measurements
        function loadMeasurements() {
            fetch('../api/measurements/get_measurements.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.measurements.length > 0) {
                        displayMeasurements(data.measurements);
                    } else {
                        document.getElementById('measurementsGrid').innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-ruler-combined"></i>
                                <h2>No measurements saved yet</h2>
                                <p>Add your first measurement to get started with quick orders</p>
                                <button class="btn-add-new" onclick="openMeasurementModal()" style="margin-top: 1.5rem;">
                                    <i class="fas fa-plus"></i> Add Your First Measurement
                                </button>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading measurements:', error);
                    alert('Failed to load measurements');
                });
        }

        // Display measurements
        function displayMeasurements(measurements) {
            const grid = document.getElementById('measurementsGrid');
            grid.innerHTML = measurements.map(m => createMeasurementCard(m)).join('');
        }

        // Create measurement card HTML
        function createMeasurementCard(measurement) {
            const isDefault = measurement.is_default == 1;
            const context = measurement.garment_context; // Already has proper casing from database

            let measurementsHTML = '';
            if (measurement.measurements) {
                measurementsHTML = Object.entries(measurement.measurements)
                    .map(([key, value]) => `
                        <div class="measurement-item">
                            <label>${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</label>
                            <value>${value}"</value>
                        </div>
                    `).join('');
            }

            return `
                <div class="measurement-card ${isDefault ? 'default' : ''}">
                    <div class="measurement-header">
                        <div class="measurement-label">${measurement.label || 'Untitled'}</div>
                        ${isDefault ? '<span class="default-badge"><i class="fas fa-star"></i> Default</span>' : ''}
                    </div>
                    <span class="measurement-context"><i class="fas fa-tag"></i> ${context}</span>
                    
                    <div class="measurements-list">
                        ${measurementsHTML}
                    </div>
                    
                    ${measurement.notes ? `<div class="measurement-notes"><i class="fas fa-sticky-note"></i> ${measurement.notes}</div>` : ''}
                    
                    <div class="measurement-actions">
                        ${!isDefault ? `
                            <button class="btn-sm btn-set-default" onclick="setAsDefault(${measurement.id})">
                                <i class="fas fa-star"></i> Set Default
                            </button>
                        ` : ''}
                        <button class="btn-sm btn-edit" onclick="editMeasurement(${measurement.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn-sm btn-delete" onclick="deleteMeasurement(${measurement.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `;
        }

        // Update measurement fields based on garment type
        function updateMeasurementFields() {
            const garmentType = document.getElementById('garment_context').value;
            const fields = measurementFields[garmentType];
            const container = document.getElementById('measurementFields');

            if (!garmentType) {
                container.innerHTML = '<p style="color: var(--text-light); font-style: italic; grid-column: 1 / -1;"><i class="fas fa-arrow-up"></i> Please select a garment type first</p>';
                return;
            }

            if (!fields) {
                container.innerHTML = '<p style="color: var(--text-light); font-style: italic; grid-column: 1 / -1;"><i class="fas fa-info-circle"></i> No specific fields configured for this garment type</p>';
                return;
            }

            container.innerHTML = fields.map(field => `
                <div class="form-group">
                    <label for="${field.name}">${field.label} <span style="color: var(--text-light); font-size: 0.875rem;">(inches)</span></label>
                    <input type="number" step="0.1" id="${field.name}" name="${field.name}" placeholder="0.0" min="0">
                </div>
            `).join('');
        }

        // Open measurement modal
        function openMeasurementModal(measurementId = null) {
            document.getElementById('measurementModal').style.display = 'block';
            document.getElementById('measurementForm').reset();
            document.getElementById('measurement_id').value = '';
            document.getElementById('modalTitle').textContent = 'Add New Measurement';
            updateMeasurementFields();
        }

        // Close measurement modal
        function closeMeasurementModal() {
            document.getElementById('measurementModal').style.display = 'none';
        }

        // Edit measurement
        function editMeasurement(id) {
            fetch(`../api/measurements/get_measurement.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const m = data.measurement;
                        document.getElementById('measurement_id').value = m.id;
                        document.getElementById('label').value = m.label;
                        document.getElementById('garment_context').value = m.garment_context;
                        document.getElementById('notes').value = m.notes || '';
                        document.getElementById('is_default').checked = m.is_default == 1;

                        updateMeasurementFields();

                        // Fill in measurement values
                        if (m.measurements) {
                            Object.entries(m.measurements).forEach(([key, value]) => {
                                const input = document.getElementById(key);
                                if (input) input.value = value;
                            });
                        }

                        document.getElementById('modalTitle').textContent = 'Edit Measurement';
                        document.getElementById('measurementModal').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load measurement');
                });
        }

        // Save measurement
        function saveMeasurement(event) {
            event.preventDefault();

            const formData = new FormData(event.target);

            fetch('../api/measurements/save_measurement.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        closeMeasurementModal();
                        loadMeasurements();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to save measurement');
                });
        }

        // Set as default
        function setAsDefault(id) {
            const formData = new FormData();
            formData.append('measurement_id', id);

            fetch('../api/measurements/set_default.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        loadMeasurements();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to set default');
                });
        }

        // Delete measurement
        function deleteMeasurement(id) {
            if (!confirm('Are you sure you want to delete this measurement?')) {
                return;
            }

            const formData = new FormData();
            formData.append('measurement_id', id);

            fetch('../api/measurements/delete_measurement.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        loadMeasurements();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete measurement');
                });
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('measurementModal');
            if (event.target === modal) {
                closeMeasurementModal();
            }
        }
    </script>

</body>

</html>