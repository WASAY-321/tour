<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "tour"; // Change to your DB name

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = $conn->real_escape_string($_POST['name']);
    $country = $conn->real_escape_string($_POST['country']);
    $location = $conn->real_escape_string($_POST['location']);
    $best_time = $conn->real_escape_string($_POST['best_time']);
    $status = $conn->real_escape_string($_POST['status']);
    $description = $conn->real_escape_string($_POST['description']);

    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = basename($_FILES['image']['name']);
        $targetFile = $uploadDir . time() . '_' . $fileName;

        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;
            } else {
                die("Error uploading the image.");
            }
        } else {
            die("Only JPG, JPEG, PNG, and GIF files are allowed.");
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO destinations (name, country, location, best_time, status, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $country, $location, $best_time, $status, $description, $imagePath);

    if ($stmt->execute()) {
        echo "Destination added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
$pageTitle = "Manage Users";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Users']
];
include "admin-header.php";
?>
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Destinations - GreenTour Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* =========================
           Enhanced Variables & Base Styles
        ========================= */
        :root {
            --primary-color: #2E8B57;
            --primary-dark: #1F6B45;
            --primary-light: #4CAF50;
            --primary-lighter: #E8F5E9;
            --primary-gradient: linear-gradient(135deg, #2E8B57 0%, #4CAF50 100%);
            --secondary-color: #FF9800;
            --secondary-dark: #F57C00;
            --text-color: #2C3E50;
            --text-light: #7F8C8D;
            --text-lighter: #95A5A6;
            --white: #FFFFFF;
            --gray-light: #ECF0F1;
            --gray-medium: #BDC3C7;
            --gray-dark: #34495E;
            --shadow-light: 0 2px 10px rgba(0,0,0,0.08);
            --shadow-medium: 0 4px 20px rgba(0,0,0,0.12);
            --shadow-heavy: 0 8px 30px rgba(0,0,0,0.18);
            --border-radius: 16px;
            --border-radius-sm: 10px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --transition-fast: all 0.2s ease;
            --danger-color: #E74C3C;
            --danger-dark: #C0392B;
            --success-color: #27AE60;
            --warning-color: #F39C12;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f7f9fb 0%, #eef2f5 100%);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
            animation: fadeInBody 1s ease-out forwards;
        }

        @keyframes fadeInBody {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* =========================
           Main Container
        ========================= */
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-heavy);
            overflow: hidden;
            animation: slideInUp 0.6s ease-out forwards;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =========================
           Header Styles
        ========================= */
        .admin-header {
            background: var(--primary-gradient);
            color: var(--white);
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-medium);
        }

        .page-title {
            font-size: 2.8rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        /* =========================
           Enhanced Buttons
        ========================= */
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            box-shadow: var(--shadow-light);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transition: all 0.6s ease;
            transform: translate(-50%, -50%);
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn:active {
            transform: translateY(2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn i {
            font-size: 18px;
            transition: var(--transition-fast);
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: var(--white);
            animation: pulse 2s infinite;
        }

        .btn-primary:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-3px);
            animation: none;
        }

        .btn-primary:hover i {
            transform: translateX(3px);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--text-color);
            border: 2px solid rgba(255,255,255,0.3);
            font-weight: 500;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
            color: var(--white);
            transform: translateY(-2px);
        }

        /* =========================
           Main Content Area
        ========================= */
        .admin-main-content {
            padding: 40px;
            min-height: 80vh;
        }

        /* =========================
           Enhanced Form Styles
        ========================= */
        .form-container {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: var(--shadow-light);
            margin-bottom: 40px;
            animation: fadeIn 0.8s ease-out 0.2s forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .form-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }

        .form-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background: var(--primary-gradient);
            border-radius: 2px;
            animation: expandLine 0.8s ease-out 0.3s forwards;
            transform: scaleX(0);
            transform-origin: left;
        }

        @keyframes expandLine {
            to { transform: scaleX(1); }
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        .form-group {
            margin-bottom: 25px;
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .form-group:nth-child(5) { animation-delay: 0.5s; }
        .form-group:nth-child(6) { animation-delay: 0.6s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 16px;
            transition: var(--transition);
        }

        .form-group:focus-within .form-label {
            color: var(--primary-color);
        }

        .form-control {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius-sm);
            font-size: 16px;
            transition: var(--transition);
            background: var(--white);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(46, 139, 87, 0.1);
            transform: translateY(-2px);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 150px;
            transition: height 0.3s ease;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 18px;
            cursor: pointer;
            padding-right: 50px;
        }

        /* =========================
           Enhanced Image Upload Section
        ========================= */
        .image-upload-section {
            background: var(--primary-lighter);
            border-radius: var(--border-radius-sm);
            padding: 30px;
            margin-top: 20px;
            border: 2px dashed var(--primary-color);
            transition: var(--transition);
        }

        .image-upload-section:hover {
            border-color: var(--primary-dark);
            background: rgba(46, 139, 87, 0.08);
            transform: translateY(-2px);
        }

        .image-upload-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .image-preview-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .image-preview {
            width: 280px;
            height: 200px;
            object-fit: cover;
            border-radius: var(--border-radius-sm);
            border: 2px solid var(--primary-color);
            transition: var(--transition);
            box-shadow: var(--shadow-light);
        }

        .image-preview:hover {
            transform: scale(1.03);
            box-shadow: var(--shadow-medium);
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            margin-top: 10px;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-btn {
            padding: 16px 30px;
            border: 2px dashed var(--primary-color);
            color: var(--primary-color);
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: var(--transition);
            background: rgba(46, 139, 87, 0.05);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
        }

        .file-input-btn:hover {
            background: rgba(46, 139, 87, 0.1);
            border-style: solid;
            transform: translateY(-2px);
            animation: pulse 1s infinite;
        }

        /* =========================
           Form Actions
        ========================= */
        .form-actions {
            display: flex;
            gap: 20px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid var(--gray-light);
        }

        .btn-submit {
            background: var(--primary-gradient);
            color: var(--white);
            padding: 18px 40px;
            font-size: 18px;
            border-radius: var(--border-radius-sm);
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            box-shadow: var(--shadow-medium);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-heavy);
        }

        .btn-submit i {
            font-size: 20px;
            transition: var(--transition);
        }

        .btn-submit:hover i {
            transform: translateX(5px);
        }

        .btn-cancel {
            background: var(--gray-light);
            color: var(--text-color);
            padding: 18px 30px;
            font-size: 16px;
            border-radius: var(--border-radius-sm);
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .btn-cancel:hover {
            background: var(--gray-medium);
            transform: translateY(-2px);
        }

        /* =========================
           Enhanced Alerts
        ========================= */
        .alert {
            padding: 20px 25px;
            border-radius: var(--border-radius-sm);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 500;
            animation: slideInDown 0.5s ease forwards, bounceIn 0.8s ease;
            border-left: 5px solid;
            box-shadow: var(--shadow-light);
            transform-origin: top;
            font-size: 16px;
        }

        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
            border-left-color: var(--success-color);
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border-left-color: var(--danger-color);
        }

        .alert i {
            font-size: 24px;
            animation: pulse 2s infinite;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounceIn {
            0% {transform: scale(0.3); opacity: 0;}
            50% {transform: scale(1.05);}
            70% {transform: scale(0.9);}
            100% {transform: scale(1); opacity: 1;}
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* =========================
           Responsive Design
        ========================= */
        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .admin-main-content {
                padding: 30px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .admin-header {
                padding: 20px;
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .page-title {
                font-size: 2.2rem;
            }
            
            .admin-main-content {
                padding: 20px;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .form-title {
                font-size: 1.8rem;
            }
            
            .image-preview-container {
                flex-direction: column;
                align-items: center;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn-submit, .btn-cancel {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.8rem;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
            
            .form-control {
                padding: 14px 16px;
            }
            
            .file-input-btn {
                padding: 14px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <h1 class="page-title">Manage Destinations</h1>
            <div class="header-actions">
                <button class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="admin-main-content">
            <!-- Success/Error Messages -->
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Destination added successfully!
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <h2 class="form-title">Add New Destination</h2>
                
                <form id="destinationForm">
                    <div class="form-grid">
                        <!-- Destination Name -->
                        <div class="form-group">
                            <label for="name" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Destination Name
                            </label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Enter destination name" required>
                        </div>

                        <!-- Country -->
                        <div class="form-group">
                            <label for="country" class="form-label">
                                <i class="fas fa-globe"></i> Country
                            </label>
                            <input type="text" id="country" name="country" class="form-control" placeholder="Enter country" required>
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <label for="location" class="form-label">
                                <i class="fas fa-location-dot"></i> Location/Region
                            </label>
                            <input type="text" id="location" name="location" class="form-control" placeholder="Enter location or region" required>
                        </div>

                        <!-- Best Time to Visit -->
                        <div class="form-group">
                            <label for="best_time" class="form-label">
                                <i class="fas fa-calendar-alt"></i> Best Time to Visit
                            </label>
                            <input type="text" id="best_time" name="best_time" class="form-control" placeholder="e.g., March to May">
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label for="status" class="form-label">
                                <i class="fas fa-toggle-on"></i> Status
                            </label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="">Select Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="form-group full-width">
                            <label for="description" class="form-label">
                                <i class="fas fa-file-text"></i> Description
                            </label>
                            <textarea id="description" name="description" class="form-control" placeholder="Enter destination description" rows="5" required></textarea>
                        </div>
                    </div>

                    <!-- Image Upload Section -->
                    <div class="image-upload-section">
                        <h3 class="image-upload-title">
                            <i class="fas fa-image"></i> Destination Image
                        </h3>
                        
                        <div class="file-input-wrapper">
                            <button type="button" class="file-input-btn">
                                <i class="fas fa-cloud-upload-alt"></i> Choose Destination Image
                            </button>
                            <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                        </div>
                        
                        <div class="image-preview-container">
                            <img id="imagePreview" class="image-preview" style="display: none;">
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-plus-circle"></i> Add Destination
                        </button>
                        <button type="button" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Image Preview Function
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Form submission handling
        document.getElementById('destinationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simulate form submission
            const submitBtn = this.querySelector('.btn-submit');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Destination...';
            submitBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                alert('Destination added successfully!');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                this.reset();
                document.getElementById('imagePreview').style.display = 'none';
            }, 2000);
        });

        // Animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const formGroups = document.querySelectorAll('.form-group');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                    }
                });
            }, { threshold: 0.1 });
            
            formGroups.forEach(group => {
                observer.observe(group);
            });
        });
    </script>
</body>
</html>