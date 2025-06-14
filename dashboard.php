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

// Fetch user details
$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching user: " . $e->getMessage());
}

// Fetch saved listings
try {
    $stmt = $pdo->prepare("SELECT p.* FROM properties p JOIN saved_listings sl ON p.id = sl.property_id WHERE sl.user_id = ?");
    $stmt->execute([$user_id]);
    $saved_listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $saved_listings = [];
    $error = "Error fetching saved listings: " . $e->getMessage();
}

// Fetch agent listings (if user is an agent)
$agent_listings = [];
if ($user['role'] === 'agent') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $agent_listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error fetching agent listings: " . $e->getMessage();
    }
}

// Handle profile update
$update_error = '';
$update_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($username) || empty($email)) {
        $update_error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $update_error = "Invalid email format.";
    } else {
        try {
            // Check for duplicate username/email (excluding current user)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $user_id]);
            if ($stmt->fetchColumn() > 0) {
                $update_error = "Username or email already exists.";
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->execute([$username, $email, $user_id]);
                $update_success = "Profile updated successfully!";
                $user['username'] = $username;
                $user['email'] = $email;
            }
        } catch (PDOException $e) {
            $update_error = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Handle delete listing (for agents)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_property'])) {
    $property_id = $_POST['property_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ? AND user_id = ?");
        $stmt->execute([$property_id, $user_id]);
        header('Location: dashboard.php');
        exit;
    } catch (PDOException $e) {
        $error = "Error deleting property: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Zillow Clone</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
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
        .section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h2 {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 20px;
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
        .listings {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .property-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .property-card:hover {
            transform: scale(1.05);
        }
        .property-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .property-card h3 {
            margin: 10px;
            color: #333;
        }
        .property-card p {
            margin: 0 10px 10px;
            color: #666;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .delete-btn {
            background: #dc3545;
        }
        .delete-btn:hover {
            background: #c82333;
        }
        @media (max-width: 768px) {
            .listings {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Zillow Clone</h1>
        <p>Your Dashboard</p>
    </header>
    <nav>
        <div>
            <a href="#" onclick="redirectTo('index.php')">Home</a>
            <a href="#" onclick="redirectTo('search.php')">Search</a>
            <a href="#" onclick="redirectTo('list_property.php')">List Property</a>
            <a href="#" onclick="redirectTo('logout.php')">Logout</a>
        </div>
    </nav>
    <div class="container">
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Profile Section -->
        <div class="section">
            <h2>User Profile</h2>
            <?php if ($update_error): ?>
                <p class="error"><?php echo htmlspecialchars($update_error); ?></p>
            <?php endif; ?>
            <?php if ($update_success): ?>
                <p class="success"><?php echo htmlspecialchars($update_success); ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <button type="submit">Update Profile</button>
            </form>
        </div>

        <!-- Saved Listings Section -->
        <div class="section">
            <h2>Saved Listings</h2>
            <div class="listings">
                <?php if (count($saved_listings) > 0): ?>
                    <?php foreach ($saved_listings as $property): ?>
                        <div class="property-card">
                            <img src="<?php echo htmlspecialchars($property['image'] ?: 'https://via.placeholder.com/300'); ?>" alt="Property">
                            <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                            <p><?php echo htmlspecialchars($property['location']); ?> - $<?php echo number_format($property['price']); ?></p>
                            <p><?php echo $property['bedrooms']; ?> Beds | <?php echo $property['bathrooms']; ?> Baths</p>
                            <a href="#" onclick="redirectTo('property_details.php?id=<?php echo $property['id']; ?>')" class="btn">View Details</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No saved listings found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Agent Listings Section -->
        <?php if ($user['role'] === 'agent'): ?>
            <div class="section">
                <h2>Your Listings</h2>
                <div class="listings">
                    <?php if (count($agent_listings) > 0): ?>
                        <?php foreach ($agent_listings as $property): ?>
                            <div class="property-card">
                                <img src="<?php echo htmlspecialchars($property['image'] ?: 'https://via.placeholder.com/300'); ?>" alt="Property">
                                <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                                <p><?php echo htmlspecialchars($property['location']); ?> - $<?php echo number_format($property['price']); ?></p>
                                <p><?php echo $property['bedrooms']; ?> Beds | <?php echo $property['bathrooms']; ?> Baths</p>
                                <a href="#" onclick="redirectTo('property_details.php?id=<?php echo $property['id']; ?>')" class="btn">View Details</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                    <input type="hidden" name="delete_property" value="1">
                                    <button type="submit" class="btn delete-btn">Delete</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No listings found. <a href="#" onclick="redirectTo('list_property.php')">List a property</a>.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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
