<?php
session_start();
include_once 'config/database.php';
include_once 'classes/Book.php';

$database = new Database();
$db = $database->getConnection();
$book = new Book($db);

$stmt = $book->read();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibraryHub - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100">

    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <h1 class="text-2xl font-bold text-gray-900">LibraryHub</h1>
                </div>
                <nav class="flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <span class="text-sm text-gray-600">Welcome, <?php echo $_SESSION['user_name']; ?></span>
                        <?php if($_SESSION['user_role'] == 'admin'): ?>
                            <a href="admin/dashboard.php" class="text-blue-600 hover:text-blue-800">Admin</a>
                        <?php endif; ?>
                        <?php if($_SESSION['user_role'] == 'author'): ?>
                            <a href="author/dashboard.php" class="text-blue-600 hover:text-blue-800">Author Dashboard</a>
                        <?php endif; ?>
                        <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="text-blue-600 hover:text-blue-800">Login</a>
                        <a href="register.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>


    <section class="py-20 px-4 text-center">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-5xl font-bold text-gray-900 mb-6">Discover Your Next Great Read</h2>
            <p class="text-xl text-gray-600 mb-8">Browse thousands of books, borrow for free, or purchase to own forever</p>
        </div>
    </section>

    <!-- Books Grid -->
    <section class="py-16 px-4">
        <div class="max-w-7xl mx-auto">
            <h3 class="text-3xl font-bold text-gray-900 mb-8 text-center">Featured Books</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach($books as $book_item): ?>
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                        <img src="<?php echo $book_item['cover_image'] ?: 'https://via.placeholder.com/200x300'; ?>" 
                             alt="<?php echo htmlspecialchars($book_item['title']); ?>" 
                             class="w-full h-48 object-cover rounded-t-lg">
                        <div class="p-4">
                            <h4 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($book_item['title']); ?></h4>
                            <p class="text-gray-600 mb-2">by <?php echo htmlspecialchars($book_item['author_name']); ?></p>
                            <div class="flex items-center justify-between mb-2">
                                <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-sm"><?php echo htmlspecialchars($book_item['genre']); ?></span>
                                <span class="text-yellow-500">★ <?php echo $book_item['rating']; ?></span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4"><?php echo substr(htmlspecialchars($book_item['description']), 0, 100) . '...'; ?></p>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-lg font-bold text-green-600">$<?php echo $book_item['price']; ?></span>
                                <span class="text-sm text-gray-500"><?php echo $book_item['available_copies']; ?>/<?php echo $book_item['total_copies']; ?> available</span>
                            </div>
                            <a href="book_details.php?id=<?php echo $book_item['id']; ?>" 
                               class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-center block">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</body>
</html>