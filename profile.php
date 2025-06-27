<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();


$borrowed_query = "SELECT bb.*, b.title, b.price, a.name as author_name 
                   FROM borrowed_books bb 
                   JOIN books b ON bb.book_id = b.id 
                   JOIN authors a ON b.author_id = a.id 
                   WHERE bb.user_id = ? AND bb.status = 'borrowed' 
                   ORDER BY bb.borrow_date DESC";
$borrowed_stmt = $db->prepare($borrowed_query);
$borrowed_stmt->bindParam(1, $_SESSION['user_id']);
$borrowed_stmt->execute();
$borrowed_books = $borrowed_stmt->fetchAll(PDO::FETCH_ASSOC);

$purchased_query = "SELECT pb.*, b.title, a.name as author_name 
                    FROM purchased_books pb 
                    JOIN books b ON pb.book_id = b.id 
                    JOIN authors a ON b.author_id = a.id 
                    WHERE pb.user_id = ? 
                    ORDER BY pb.purchase_date DESC";
$purchased_stmt = $db->prepare($purchased_query);
$purchased_stmt->bindParam(1, $_SESSION['user_id']);
$purchased_stmt->execute();
$purchased_books = $purchased_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - LibraryHub</title>
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
                    <span class="text-sm text-gray-600">Welcome, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex items-center space-x-4">
                <div class="bg-blue-100 rounded-full p-4">
                    <span class="text-2xl">👤</span>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo $_SESSION['user_name']; ?></h1>
                    <p class="text-gray-600"><?php echo $_SESSION['user_email']; ?></p>
                    <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-sm mt-2 inline-block">
                        <?php echo ucfirst($_SESSION['user_role']); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-500">Books Borrowed</h3>
                <p class="text-2xl font-bold text-blue-600"><?php echo count($borrowed_books); ?></p>
                <p class="text-xs text-gray-500">Currently borrowed</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-500">Books Purchased</h3>
                <p class="text-2xl font-bold text-green-600"><?php echo count($purchased_books); ?></p>
                <p class="text-xs text-gray-500">Total owned</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-500">Total Spent</h3>
                <p class="text-2xl font-bold text-purple-600">
                    $<?php echo array_sum(array_column($purchased_books, 'purchase_price')); ?>
                </p>
                <p class="text-xs text-gray-500">On book purchases</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-2xl font-bold mb-6">Borrowed Books</h2>
            <?php if(empty($borrowed_books)): ?>
                <p class="text-gray-500 text-center py-8">No borrowed books yet.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($borrowed_books as $book): ?>
                        <div class="border rounded-lg p-4">
                            <h3 class="font-semibold"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="text-gray-600">by <?php echo htmlspecialchars($book['author_name']); ?></p>
                            <p class="text-sm text-gray-500 mt-2">
                                Borrowed: <?php echo date('M j, Y', strtotime($book['borrow_date'])); ?>
                            </p>
                            <p class="text-sm <?php echo strtotime($book['due_date']) < time() ? 'text-red-600' : 'text-gray-500'; ?>">
                                Due: <?php echo date('M j, Y', strtotime($book['due_date'])); ?>
                                <?php if(strtotime($book['due_date']) < time()): ?>
                                    (Overdue)
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>


        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-2xl font-bold mb-6">Purchased Books</h2>
            <?php if(empty($purchased_books)): ?>
                <p class="text-gray-500 text-center py-8">No purchased books yet.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($purchased_books as $book): ?>
                        <div class="border rounded-lg p-4">
                            <h3 class="font-semibold"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="text-gray-600">by <?php echo htmlspecialchars($book['author_name']); ?></p>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-lg font-bold text-green-600">$<?php echo $book['purchase_price']; ?></span>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Owned</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">
                                Purchased: <?php echo date('M j, Y', strtotime($book['purchase_date'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>