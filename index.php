<?php
session_start();
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM properties WHERE status = 'approved' ORDER BY created_at DESC LIMIT 6");
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zillow Clone - Home</title>
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
        .featured {
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
            .featured {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Zillow Clone</h1>
        <p>Find Your Dream Home</p>
    </header>
    <nav>
        <div>
            <a href="index.php">Home</a>
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
        <h2>Featured Properties</h2>
        <div class="featured">
            <?php foreach ($properties as $property): ?>
                <div class="property-card">
                    <img src="<?php echo htmlspecialchars($property['image'] ?: 'https://via.placeholder.com/300'); ?>" alt="Property">
                    <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                    <p><?php echo htmlspecialchars($property['location']); ?> - $<?php echo number_format($property['price']); ?></p>
                    <a href="#" onclick="redirectTo('property_details.php?id=<?php echo $property['id']; ?>')" class="btn">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Zillow Clone. All rights reserved.</p>
    </footer>
    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
