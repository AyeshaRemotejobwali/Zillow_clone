<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user is an agent (optional restriction)
$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    // Uncomment the following to restrict to agents only
    /*
    if ($user['role'] !== 'agent') {
        header('Location: index.php');
        exit;
    }
    */
} catch (PDOException $e) {
    die("Error fetching user: " . $e->getMessage());
}

// Handle form submission
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $property_type = $_POST['property_type'] ?? '';
    $beds = trim($_POST['beds'] ?? '');
    $baths = trim($_POST['baths'] ?? '');
    $amenities = trim($_POST['amenities'] ?? '');
    $image = trim($_POST['image'] ?? '');

    // Basic validation
    if (empty($title) || empty($description) || empty($price) || empty($location) || empty($city) || empty($state) || empty($property_type) || empty($beds) || empty($baths)) {
        $error = "All required fields must be filled.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Price must be a valid number.";
    } elseif (!is_numeric($beds) || $beds < 0) {
        $error = "Number of bedrooms must be a valid number.";
    } elseif (!is_numeric($baths) || $baths < 0) {
        $error = "Number of bathrooms must be a valid number.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO properties (user_id, title, description, price, location, city, state, property_type, bedrooms, bathrooms, amenities, image, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$user_id, $title, $description, $price, $location, $city, $state, $property_type, $beds, $baths, $amenities, $image]);
            $success = "Property listed successfully! Awaiting approval.";
            echo "<script>setTimeout(() => { window.location.href = 'dashboard.php'; }, 1000);</script>";
        } catch (PDOException $e) {
            $error = "Error listing property: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Property - Zillow Clone</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }
        header {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            text-align: center;
        }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        nav a {
            color: #007bff;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
        }
        nav a:hover {
            color: #0056b3;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .form-container {
                margin: 10px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Zillow Clone</h1>
        <p>List Your Property</p>
    </header>
    <nav>
        <div>
            <a href="#" onclick="redirectTo('index.php')">Home</a>
            <a href="#" onclick="redirectTo('search.php')">Search</a>
            <a href="#" onclick="redirectTo('dashboard.php')">Dashboard</a>
            <a href="#" onclick="redirectTo('logout.php')">Logout</a>
        </div>
    </nav>
    <div class="container">
        <div class="form-container">
            <h2>List a Property</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="title">Property Title</label>
                    <input type="text" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="location">Location (Address)</label>
                    <input type="text" id="location" name="location" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="property_type">Property Type</label>
                    <select id="property_type" name="property_type" required>
                        <option value="">Select Type</option>
                        <option value="house" <?php echo isset($_POST['property_type']) && $_POST['property_type'] === 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="apartment" <?php echo isset($_POST['property_type']) && $_POST['property_type'] === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                        <option value="commercial" <?php echo isset($_POST['property_type']) && $_POST['property_type'] === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="beds">Bedrooms</label>
                    <input type="number" id="beds" name="beds" value="<?php echo isset($_POST['beds']) ? htmlspecialchars($_POST['beds']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="baths">Bathrooms</label>
                    <input type="number" id="baths" name="baths" value="<?php echo isset($_POST['baths']) ? htmlspecialchars($_POST['baths']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="amenities">Amenities (comma-separated, e.g., pool, garage)</label>
                    <input type="text" id="amenities" name="amenities" value="<?php echo isset($_POST['amenities']) ? htmlspecialchars($_POST['amenities']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="image">Image URL</label>
                    <input type="url" id="image" name="image" value="<?php echo isset($_POST['image']) ? htmlspecialchars($_POST['image']) : ''; ?>">
                </div>
                <button type="submit">List Property</button>
            </form>
            <a href="#" onclick="redirectTo('dashboard.php')">Back to Dashboard</a>
        </div>
    </div>
    <footer>
        <p>© 2025 Zillow Clone. All rights reserved.</p>
    </footer>
    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
