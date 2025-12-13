// Measurement field configurations for different garment types
const measurementConfigs = {
    'Shirt': [
        { name: 'chest', label: 'Chest', placeholder: 'e.g., 38 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 32 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 16 inches', unit: 'inches' },
        { name: 'sleeve_length', label: 'Sleeve Length', placeholder: 'e.g., 24 inches', unit: 'inches' },
        { name: 'shirt_length', label: 'Shirt Length', placeholder: 'e.g., 28 inches', unit: 'inches' },
        { name: 'collar', label: 'Collar Size', placeholder: 'e.g., 15 inches', unit: 'inches' }
    ],
    'Pants': [
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 32 inches', unit: 'inches' },
        { name: 'hip', label: 'Hip', placeholder: 'e.g., 38 inches', unit: 'inches' },
        { name: 'inseam', label: 'Inseam/Length', placeholder: 'e.g., 30 inches', unit: 'inches' },
        { name: 'thigh', label: 'Thigh', placeholder: 'e.g., 22 inches', unit: 'inches' },
        { name: 'knee', label: 'Knee', placeholder: 'e.g., 16 inches', unit: 'inches' },
        { name: 'bottom', label: 'Bottom/Ankle', placeholder: 'e.g., 14 inches', unit: 'inches' },
        { name: 'rise', label: 'Rise (Front)', placeholder: 'e.g., 11 inches', unit: 'inches' }
    ],
    'Suit': [
        { name: 'chest', label: 'Chest', placeholder: 'e.g., 40 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 34 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 17 inches', unit: 'inches' },
        { name: 'sleeve_length', label: 'Sleeve Length', placeholder: 'e.g., 25 inches', unit: 'inches' },
        { name: 'jacket_length', label: 'Jacket Length', placeholder: 'e.g., 30 inches', unit: 'inches' },
        { name: 'pant_waist', label: 'Pant Waist', placeholder: 'e.g., 34 inches', unit: 'inches' },
        { name: 'inseam', label: 'Pant Inseam', placeholder: 'e.g., 30 inches', unit: 'inches' }
    ],
    'Kurta': [
        { name: 'chest', label: 'Chest', placeholder: 'e.g., 38 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 32 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 16 inches', unit: 'inches' },
        { name: 'sleeve_length', label: 'Sleeve Length', placeholder: 'e.g., 24 inches', unit: 'inches' },
        { name: 'kurta_length', label: 'Kurta Length', placeholder: 'e.g., 38 inches', unit: 'inches' },
        { name: 'neck', label: 'Neck', placeholder: 'e.g., 15 inches', unit: 'inches' }
    ],
    'Sherwani': [
        { name: 'chest', label: 'Chest', placeholder: 'e.g., 40 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 34 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 17 inches', unit: 'inches' },
        { name: 'sleeve_length', label: 'Sleeve Length', placeholder: 'e.g., 25 inches', unit: 'inches' },
        { name: 'sherwani_length', label: 'Sherwani Length', placeholder: 'e.g., 42 inches', unit: 'inches' },
        { name: 'neck', label: 'Neck', placeholder: 'e.g., 15.5 inches', unit: 'inches' }
    ],
    'Blazer': [
        { name: 'chest', label: 'Chest', placeholder: 'e.g., 40 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 34 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 17 inches', unit: 'inches' },
        { name: 'sleeve_length', label: 'Sleeve Length', placeholder: 'e.g., 25 inches', unit: 'inches' },
        { name: 'blazer_length', label: 'Blazer Length', placeholder: 'e.g., 28 inches', unit: 'inches' }
    ],
    'Waistcoat': [
        { name: 'chest', label: 'Chest', placeholder: 'e.g., 38 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 32 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 16 inches', unit: 'inches' },
        { name: 'waistcoat_length', label: 'Waistcoat Length', placeholder: 'e.g., 22 inches', unit: 'inches' }
    ],
    'Blouse': [
        { name: 'bust', label: 'Bust', placeholder: 'e.g., 34 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 28 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 14 inches', unit: 'inches' },
        { name: 'sleeve_length', label: 'Sleeve Length', placeholder: 'e.g., 12 inches', unit: 'inches' },
        { name: 'blouse_length', label: 'Blouse Length', placeholder: 'e.g., 15 inches', unit: 'inches' },
        { name: 'armhole', label: 'Armhole', placeholder: 'e.g., 16 inches', unit: 'inches' }
    ],
    'Saree': [
        { name: 'blouse_bust', label: 'Blouse Bust', placeholder: 'e.g., 34 inches', unit: 'inches' },
        { name: 'blouse_waist', label: 'Blouse Waist', placeholder: 'e.g., 28 inches', unit: 'inches' },
        { name: 'blouse_shoulder', label: 'Shoulder Width', placeholder: 'e.g., 14 inches', unit: 'inches' },
        { name: 'blouse_length', label: 'Blouse Length', placeholder: 'e.g., 15 inches', unit: 'inches' },
        { name: 'petticoat_waist', label: 'Petticoat Waist', placeholder: 'e.g., 28 inches', unit: 'inches' },
        { name: 'petticoat_length', label: 'Petticoat Length', placeholder: 'e.g., 38 inches', unit: 'inches' }
    ],
    'Salwar Kameez': [
        { name: 'bust', label: 'Bust', placeholder: 'e.g., 36 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 30 inches', unit: 'inches' },
        { name: 'hip', label: 'Hip', placeholder: 'e.g., 38 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 14 inches', unit: 'inches' },
        { name: 'sleeve_length', label: 'Sleeve Length', placeholder: 'e.g., 18 inches', unit: 'inches' },
        { name: 'kameez_length', label: 'Kameez Length', placeholder: 'e.g., 42 inches', unit: 'inches' },
        { name: 'salwar_length', label: 'Salwar Length', placeholder: 'e.g., 38 inches', unit: 'inches' }
    ],
    'Lehenga': [
        { name: 'bust', label: 'Bust', placeholder: 'e.g., 34 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 28 inches', unit: 'inches' },
        { name: 'hip', label: 'Hip', placeholder: 'e.g., 36 inches', unit: 'inches' },
        { name: 'choli_length', label: 'Choli Length', placeholder: 'e.g., 14 inches', unit: 'inches' },
        { name: 'lehenga_length', label: 'Lehenga Length', placeholder: 'e.g., 40 inches', unit: 'inches' },
        { name: 'dupatta_length', label: 'Dupatta Length', placeholder: 'e.g., 2.5 meters', unit: 'meters' }
    ],
    'Dress': [
        { name: 'bust', label: 'Bust', placeholder: 'e.g., 34 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 28 inches', unit: 'inches' },
        { name: 'hip', label: 'Hip', placeholder: 'e.g., 36 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 14 inches', unit: 'inches' },
        { name: 'sleeve_length', label: 'Sleeve Length', placeholder: 'e.g., 20 inches', unit: 'inches' },
        { name: 'dress_length', label: 'Dress Length', placeholder: 'e.g., 38 inches', unit: 'inches' }
    ],
    'Gown': [
        { name: 'bust', label: 'Bust', placeholder: 'e.g., 34 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 28 inches', unit: 'inches' },
        { name: 'hip', label: 'Hip', placeholder: 'e.g., 36 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 14 inches', unit: 'inches' },
        { name: 'sleeve_length', label: 'Sleeve Length', placeholder: 'e.g., 22 inches', unit: 'inches' },
        { name: 'gown_length', label: 'Gown Length', placeholder: 'e.g., 56 inches', unit: 'inches' }
    ],
    'Kurti': [
        { name: 'bust', label: 'Bust', placeholder: 'e.g., 36 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 30 inches', unit: 'inches' },
        { name: 'hip', label: 'Hip', placeholder: 'e.g., 38 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 14 inches', unit: 'inches' },
        { name: 'sleeve_length', label: 'Sleeve Length', placeholder: 'e.g., 18 inches', unit: 'inches' },
        { name: 'kurti_length', label: 'Kurti Length', placeholder: 'e.g., 36 inches', unit: 'inches' }
    ],
    'Kids Shirt': [
        { name: 'chest', label: 'Chest', placeholder: 'e.g., 26 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 24 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 11 inches', unit: 'inches' },
        { name: 'sleeve_length', label: 'Sleeve Length', placeholder: 'e.g., 16 inches', unit: 'inches' },
        { name: 'shirt_length', label: 'Shirt Length', placeholder: 'e.g., 20 inches', unit: 'inches' }
    ],
    'Kids Dress': [
        { name: 'chest', label: 'Chest', placeholder: 'e.g., 24 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 22 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 10 inches', unit: 'inches' },
        { name: 'dress_length', label: 'Dress Length', placeholder: 'e.g., 24 inches', unit: 'inches' }
    ],
    'Kids Frock': [
        { name: 'chest', label: 'Chest', placeholder: 'e.g., 24 inches', unit: 'inches' },
        { name: 'waist', label: 'Waist', placeholder: 'e.g., 22 inches', unit: 'inches' },
        { name: 'shoulder', label: 'Shoulder Width', placeholder: 'e.g., 10 inches', unit: 'inches' },
        { name: 'frock_length', label: 'Frock Length', placeholder: 'e.g., 26 inches', unit: 'inches' }
    ]
};

// Initialize measurement fields functionality
document.addEventListener('DOMContentLoaded', function () {
    const garmentTypeSelect = document.querySelector('select[name="garment_type"]');
    const measurementOptions = document.querySelectorAll('input[name="measurement_option"]');
    const defaultMeasurementMsg = document.getElementById('defaultMeasurementMsg');
    const customMeasurementFields = document.getElementById('customMeasurementFields');
    const dynamicMeasurementFields = document.getElementById('dynamicMeasurementFields');
    const noDefaultError = document.getElementById('noDefaultError');
    const measurementDefaultRadio = document.getElementById('measurementDefault');
    const measurementCustomRadio = document.getElementById('measurementCustom');

    let hasDefaultMeasurement = false;
    let defaultMeasurementData = null;
    let currentGarmentType = '';

    // Check if user is logged in (based on body class)
    const isLoggedIn = document.body.classList.contains('logged-in');
    const userType = document.body.getAttribute('data-user-type');

    // Handle garment type change
    if (garmentTypeSelect) {
        garmentTypeSelect.addEventListener('change', function () {
            const selectedGarment = this.value;
            currentGarmentType = selectedGarment;

            // Generate fields for custom measurements
            generateMeasurementFields(selectedGarment);

            // Check if customer has default measurement for this garment type
            if (isLoggedIn && userType === 'customer' && selectedGarment) {
                checkDefaultMeasurement(selectedGarment);
            } else {
                // Not logged in or not a customer - hide error
                if (noDefaultError) {
                    noDefaultError.style.display = 'none';
                }
            }
        });
    }

    // Handle measurement option change (default vs custom)
    measurementOptions.forEach(option => {
        option.addEventListener('change', function () {
            if (this.value === 'default') {
                handleDefaultSelection();
            } else {
                handleCustomSelection();
            }
        });
    });

    // Function to check if customer has default measurement
    function checkDefaultMeasurement(garmentType) {
        fetch(`api/measurements/check_default.php?garment_type=${encodeURIComponent(garmentType)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.has_default) {
                    hasDefaultMeasurement = true;
                    defaultMeasurementData = data.measurement;

                    // If default option is selected, populate hidden fields
                    if (measurementDefaultRadio && measurementDefaultRadio.checked) {
                        populateDefaultMeasurementFields();
                        defaultMeasurementMsg.style.display = 'block';
                        noDefaultError.style.display = 'none';
                    }
                } else {
                    hasDefaultMeasurement = false;
                    defaultMeasurementData = null;

                    // Clear hidden fields
                    clearDefaultMeasurementFields();

                    // If default option is selected, show error and force custom
                    if (measurementDefaultRadio && measurementDefaultRadio.checked) {
                        showNoDefaultError();
                    }
                }
            })
            .catch(error => {
                console.error('Error checking default measurement:', error);
                hasDefaultMeasurement = false;
                defaultMeasurementData = null;
            });
    }

    function populateDefaultMeasurementFields() {
        const measurementIdInput = document.getElementById('measurementId');
        const measurementsSnapshotInput = document.getElementById('measurementsSnapshot');

        if (measurementIdInput && defaultMeasurementData) {
            measurementIdInput.value = defaultMeasurementData.id;
        }

        if (measurementsSnapshotInput && defaultMeasurementData) {
            measurementsSnapshotInput.value = JSON.stringify(defaultMeasurementData.measurements);
        }
    }

    function clearDefaultMeasurementFields() {
        const measurementIdInput = document.getElementById('measurementId');
        const measurementsSnapshotInput = document.getElementById('measurementsSnapshot');

        if (measurementIdInput) measurementIdInput.value = '';
        if (measurementsSnapshotInput) measurementsSnapshotInput.value = '';
    }

    // Function to show no default measurement error
    function showNoDefaultError() {
        if (noDefaultError) {
            noDefaultError.style.display = 'block';
            defaultMeasurementMsg.style.display = 'none';
            customMeasurementFields.style.display = 'none';

            // Disable the default option
            if (measurementDefaultRadio) {
                measurementDefaultRadio.disabled = true;
            }

            // Auto-select custom measurements
            if (measurementCustomRadio) {
                measurementCustomRadio.checked = true;
                measurementCustomRadio.disabled = false;
                handleCustomSelection();
            }
        }
    }

    // Function to handle default selection
    function handleDefaultSelection() {
        if (!hasDefaultMeasurement && currentGarmentType && isLoggedIn && userType === 'customer') {
            // No default measurement - show error and prevent selection
            showNoDefaultError();
        } else {
            // Has default measurement or not applicable
            defaultMeasurementMsg.style.display = 'block';
            customMeasurementFields.style.display = 'none';
            if (noDefaultError) {
                noDefaultError.style.display = 'none';
            }
            populateDefaultMeasurementFields();
        }
    }

    // Function to handle custom selection
    function handleCustomSelection() {
        defaultMeasurementMsg.style.display = 'none';
        customMeasurementFields.style.display = 'block';
        if (noDefaultError) {
            noDefaultError.style.display = 'none';
        }

        clearDefaultMeasurementFields();

        // Re-enable default option
        if (measurementDefaultRadio) {
            measurementDefaultRadio.disabled = false;
        }

        // Trigger field generation if garment type is selected
        const selectedGarment = garmentTypeSelect.value;
        if (selectedGarment && measurementConfigs[selectedGarment]) {
            generateMeasurementFields(selectedGarment);
        }
    }

    // Function to generate measurement fields based on garment type
    function generateMeasurementFields(garmentType) {
        const config = measurementConfigs[garmentType];

        if (!config) {
            dynamicMeasurementFields.innerHTML = `
                <p style="color: var(--text-light); font-style: italic;">
                    <i class="fas fa-info-circle"></i> Please specify measurements in the special instructions field
                </p>
            `;
            return;
        }

        let fieldsHTML = '<div class="measurement-grid">';

        config.forEach(field => {
            fieldsHTML += `
                <div class="measurement-field-group">
                    <label class="measurement-label">
                        ${field.label} <span style="color: var(--text-light); font-size: 0.875rem;">(${field.unit})</span>
                    </label>
                    <input 
                        type="text" 
                        name="measurement_${field.name}" 
                        class="form-input measurement-input"
                        placeholder="${field.placeholder}"
                        pattern="[0-9.]+"
                        title="Enter numeric value only"
                    >
                </div>
            `;
        });

        fieldsHTML += '</div>';
        dynamicMeasurementFields.innerHTML = fieldsHTML;
    }
});
