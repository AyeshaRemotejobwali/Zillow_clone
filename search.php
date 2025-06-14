<?php
session_start();
require_once 'db.php';

// Initialize variables for search and filters
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : PHP_INT_MAX;
$property_type = isset($_GET['property_type']) ? $_GET['property_type'] : '';
$bedrooms = isset($_GET['bedrooms']) ? (int)$_GET['bedrooms'] : 0;
$amenities = isset($_GET['amenities']) ? $_GET['amenities'] : '';

// Build the SQL query
$sql = "SELECT * FROM properties WHERE status = 'approved'";
$params = [];

if ($search_query) {
    $sql .= " AND (city LIKE ? OR state LIKE ? OR location LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

if ($min_price > 0) {
    $sql .= " AND price >= ?";
    $params[] = $min_price;
}

if ($max_price < PHP_INT_MAX) {
    $sql .= " AND price <= ?";
    $params[] = $max_price;
}

if ($property_type) {
    $sql .= " AND property_type = ?";
    $params[] = $property_type;
}

if ($bedrooms > 0) {
    $sql .= " AND bedrooms >= ?";
    $params[] = $bedrooms;
}

if ($amenities) {
    $sql .= " AND amenities LIKE ?";
    $params[] = "%$amenities%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Properties - Zillow Clone</title>
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
        .search-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .search-form form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        .search-form input, .search-form select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }
        .search-form button {
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-form button:hover {
            background: #0056b3;
        }
        .results {
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
        footer {
            text-align: center;
            padding: 20px;
            background: #333;
            color: white;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .results {
                grid-template-columns: 1fr;
            }
            .search-form form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Zillow Clone</h1>
        <p>Search for Your Dream Home</p>
    </header>
    <nav>
        <div>
            <a href="#" onclick="redirectTo('index.php')">Home</a>
            <a href="#" onclick="redirectTo('search.php')">Search</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" onclick="redirectTo('dashboard.php')">Dashboard</a>
                <a href="#" onclick="redirectTo('list_property.php')">List Property</a>
                <a href="#" onclick="redirectTo('logout.php')">Logout</a>
            <?php else: ?>
                <a href="#" onclick="redirectTo('signup.php')">Sign Up</a>
                <a href="#" onclick="redirectTo('login.php')">Login</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container">
        <div class="search-form">
            <form method="GET">
                <input type="text" name="search" placeholder="City, State, or Neighborhood" value="<?php echo htmlspecialchars($search_query); ?>">
                <input type="number" name="min_price" placeholder="Min Price" value="<?php echo $min_price ?: ''; ?>">
                <input type="number" name="max_price" placeholder="Max Price" value="<?php echo $max_price < PHP_INT_MAX ? $max_price : ''; ?>">
                <select name="property_type">
                    <option value="">Property Type</option>
                    <option value="house" <?php echo $property_type === 'house' ? 'selected' : ''; ?>>House</option>
                    <option value="apartment" <?php echo $property_type === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                    <option value="commercial" <?php echo $property_type === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                </select>
                <input type="number" name="bedrooms" placeholder="Min Bedrooms" value="<?php echo $bedrooms ?: ''; ?>">
                <input type="text" name="amenities" placeholder="Amenities (e.g., pool, garage)" value="<?php echo htmlspecialchars($amenities); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        <h2>Search Results</h2>
        <div class="results">
            <?php if (count($properties) > 0): ?>
                <?php foreach ($properties as $property): ?>
                    <div class="property-card">
                        <img src="<?php echo htmlspecialchars($property['image'] ?: 'https://via.placeholder.com/300'); ?>" alt="Property">
                        <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                        <p><?php echo htmlspecialchars($property['location']); ?> - $<?php echo number_format($property['price']); ?></p>
                        <p><?php echo $property['bedrooms']; ?> Beds | <?php echo $property['bathrooms']; ?> Baths</p>
                        <a href="#" onclick="redirectTo('property_details.php?id=<?php echo $property['id']; ?>')" class="btn">View Details</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No properties found matching your criteria.</p>
            <?php endif; ?>
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
