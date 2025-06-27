<?php
session_start();
include_once 'config/database.php';
include_once 'classes/Book.php';
include_once 'classes/Author.php';

$database = new Database();
$db = $database->getConnection();
$book = new Book($db);
$author = new Author($db);

$message = "";
$message_type = "";


$book_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Book ID not found.');


$query = "SELECT b.*, a.name as author_name, a.bio as author_bio, a.nationality as author_nationality, a.birth_date as author_birth_date
          FROM books b 
          LEFT JOIN authors a ON b.author_id = a.id 
          WHERE b.id = ? LIMIT 0,1";

$stmt = $db->prepare($query);
$stmt->bindParam(1, $book_id);
$stmt->execute();

$num = $stmt->rowCount();
if($num == 0) {
    die('ERROR: Book not found.');
}

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if($_POST) {
    if(!isset($_SESSION['user_id'])) {
        $message = "Please login to borrow or purchase books.";
        $message_type = "error";
    } else {
        if(isset($_POST['action'])) {
            $book->id = $book_id;
            $book->price = $row['price'];
            $book->available_copies = $row['available_copies'];
            
            if($_POST['action'] == 'borrow') {
                if($book->borrowBook($_SESSION['user_id'])) {
                    $message = "Book borrowed successfully! Due date: " . date('Y-m-d', strtotime('+14 days'));
                    $message_type = "success";
                    $row['available_copies']--;
                } else {
                    $message = "Failed to borrow book. It may not be available.";
                    $message_type = "error";
                }
            } elseif($_POST['action'] == 'purchase') {
                if($book->purchaseBook($_SESSION['user_id'])) {
                    $message = "Book purchased successfully for $" . $row['price'] . "!";
                    $message_type = "success";
                } else {
                    $message = "Failed to purchase book. Please try again.";
                    $message_type = "error";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row['title']); ?> - LibraryHub</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">

    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="flex items-center space-x-2 text-blue-600 hover:text-blue-800">
                    <span>← Back to Library</span>
                </a>
                <div class="flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <span class="text-sm text-gray-600">Welcome, <?php echo $_SESSION['user_name']; ?></span>
                        <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if($message): ?>
            <div class="mb-6 p-4 rounded-md <?php echo $message_type == 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
  
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <img src="<?php echo $row['cover_image'] ?: 'https://via.placeholder.com/300x400'; ?>" 
                         alt="<?php echo htmlspecialchars($row['title']); ?>" 
                         class="w-full h-96 object-cover rounded-lg mb-6">

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl font-bold text-green-600">$<?php echo $row['price']; ?></span>
                            <div class="flex items-center">
                                <span class="text-yellow-500">★</span>
                                <span class="ml-1 text-lg font-semibold"><?php echo $row['rating']; ?></span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="<?php echo $row['available_copies'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> px-2 py-1 rounded text-sm">
                                <?php echo $row['available_copies'] > 0 ? $row['available_copies'] . ' Available' : 'Out of Stock'; ?>
                            </span>
                            <span class="text-sm text-gray-500"><?php echo $row['total_copies']; ?> total copies</span>
                        </div>

                        <?php if(isset($_SESSION['user_id'])): ?>
                            <div class="space-y-3">
                                <form method="POST" class="w-full">
                                    <input type="hidden" name="action" value="borrow">
                                    <button type="submit" 
                                            <?php echo $row['available_copies'] <= 0 ? 'disabled' : ''; ?>
                                            class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                        📚 Borrow (Free)
                                    </button>
                                </form>
                                <form method="POST" class="w-full">
                                    <input type="hidden" name="action" value="purchase">
                                    <button type="submit" 
                                            class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                                        🛒 Buy $<?php echo $row['price']; ?>
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <a href="login.php" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-center block">
                                    Login to Borrow
                                </a>
                                <a href="login.php" class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 text-center block">
                                    Login to Purchase
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        
            <div class="lg:col-span-2 space-y-6">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($row['title']); ?></h1>
                    <p class="text-xl text-gray-600 mb-4">by <?php echo htmlspecialchars($row['author_name']); ?></p>
                    <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm">
                        <?php echo htmlspecialchars($row['genre']); ?>
                    </span>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-4">Description</h2>
                    <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                </div>

        
                <?php if($row['author_name']): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold mb-4">👤 About the Author</h2>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($row['author_name']); ?></h3>
                                <?php if($row['author_nationality']): ?>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($row['author_nationality']); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if($row['author_bio']): ?>
                                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($row['author_bio'])); ?></p>
                            <?php endif; ?>
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <?php if($row['author_birth_date']): ?>
                                    <div class="flex items-center">
                                        <span>📅 Born: <?php echo date('F j, Y', strtotime($row['author_birth_date'])); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>


                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-4">Book Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-semibold text-gray-900">Genre</h4>
                            <p class="text-gray-600"><?php echo htmlspecialchars($row['genre']); ?></p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Price</h4>
                            <p class="text-gray-600">$<?php echo $row['price']; ?></p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Availability</h4>
                            <p class="text-gray-600"><?php echo $row['available_copies']; ?> of <?php echo $row['total_copies']; ?> copies</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Rating</h4>
                            <div class="flex items-center">
                                <span class="text-yellow-500">★</span>
                                <span class="ml-1 text-gray-600"><?php echo $row['rating']; ?> out of 5</span>
                            </div>
                        </div>
                        <?php if($row['sales']): ?>
                            <div>
                                <h4 class="font-semibold text-gray-900">Sales</h4>
                                <p class="text-gray-600"><?php echo $row['sales']; ?> copies sold</p>
                            </div>
                        <?php endif; ?>
                        <?php if($row['views']): ?>
                            <div>
                                <h4 class="font-semibold text-gray-900">Views</h4>
                                <p class="text-gray-600"><?php echo $row['views']; ?> views</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-4">Borrowing Information</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Loan Period:</span>
                            <span class="font-semibold">14 days</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Late Fee:</span>
                            <span class="font-semibold">$0.50 per day</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Renewals:</span>
                            <span class="font-semibold">Up to 2 times</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php

    $update_views = "UPDATE books SET views = views + 1 WHERE id = ?";
    $update_stmt = $db->prepare($update_views);
    $update_stmt->bindParam(1, $book_id);
    $update_stmt->execute();
    ?>
</body>
</html>