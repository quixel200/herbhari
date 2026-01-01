<?php
session_start();
require 'db_conn.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$id = $_GET['id'];

// key product fetching
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found");
}

// Handle Gallery Deletion
if (isset($_GET['delete_image'])) {
    $imgId = $_GET['delete_image'];
    $stmt = $conn->prepare("SELECT image_url FROM product_images WHERE image_id = ? AND product_id = ?");
    $stmt->execute([$imgId, $id]);
    $img = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($img) {
        // Delete file if exists
        if (file_exists($img['image_url'])) {
            unlink($img['image_url']);
        }
        $conn->prepare("DELETE FROM product_images WHERE image_id = ?")->execute([$imgId]);
        // Redirect to clear query string
        header("Location: edit_product.php?id=" . $id);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];

    // Image Upload Handling
    $image_url = $product['image_url']; // Default to existing
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . "." . $filetype;
            $upload_dir = 'images/';
            if (!is_dir($upload_dir))
                mkdir($upload_dir, 0755, true);
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = $target_file;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type.";
        }
    }

    // Gallery Upload Handling
    if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
        $upload_dir = 'images/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0755, true);

        $total_files = count($_FILES['gallery_images']['name']);

        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['gallery_images']['error'][$i] === 0) {
                $filename = $_FILES['gallery_images']['name'][$i];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array(strtolower($filetype), $allowed)) {
                    $new_filename = uniqid() . "_gallery_" . $i . "." . $filetype;
                    $target_file = $upload_dir . $new_filename;

                    if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$i], $target_file)) {
                        // Insert into DB
                        $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
                        $stmt->execute([$id, $target_file]);
                    }
                }
            }
        }
    }

    if (empty($error)) {
        $category = $_POST['category'] ?? 'General';
        $detailed_description = $_POST['detailed_description'] ?? '';
        $benefits = $_POST['benefits'] ?? '';

        $sql = "UPDATE products SET name=?, description=?, detailed_description=?, benefits=?, price=?, stock_quantity=?, category=?, image_url=? WHERE product_id=?";
        try {
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$name, $description, $detailed_description, $benefits, $price, $stock, $category, $image_url, $id])) {
                $message = "Product updated successfully!";
                // Refresh product data
                $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
                $stmt->execute([$id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Failed to update product.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Product</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm">&larr; Back to
                                Dashboard</a>
                        </div>

                        <?php if ($message): ?>
                            <div class="alert alert-success">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="price" class="form-control"
                                            value="<?php echo $product['price']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stock Quantity</label>
                                    <input type="number" name="stock" class="form-control"
                                        value="<?php echo $product['stock_quantity']; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" required>
                                    <?php
                                    $categories = ['General', 'Hair Care', 'Skin Care', 'Pain Relief', 'Health Mix', 'Food Products'];
                                    foreach ($categories as $cat) {
                                        $selected = ($product['category'] === $cat) ? 'selected' : '';
                                        echo "<option value=\"$cat\" $selected>$cat</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Short Description</label>
                                <textarea name="description" class="form-control"
                                    rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Detailed Description</label>
                                <textarea name="detailed_description" class="form-control"
                                    rows="5"><?php echo htmlspecialchars($product['detailed_description'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Key Benefits</label>
                                <textarea name="benefits" class="form-control"
                                    rows="5"><?php echo htmlspecialchars($product['benefits'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Product Image (Main)</label>
                                <div class="mb-2">
                                    <?php if ($product['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" height="100"
                                            class="img-thumbnail">
                                    <?php else: ?>
                                        <span class="text-muted">No image set</span>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <small class="text-muted">Leave empty to keep current image</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Gallery Images</label>
                                <div class="mb-2 row">
                                    <?php
                                    $galleryIds = [];
                                    try {
                                        $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
                                        $stmt->execute([$id]);
                                        $galleryImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($galleryImages as $img) {
                                            $galleryIds[] = $img['image_id'];
                                            echo '<div class="col-md-3 mb-2 text-center">';
                                            echo '<img src="' . htmlspecialchars($img['image_url']) . '" class="img-thumbnail mb-1" style="height: 80px;">';
                                            echo '<br><a href="?id=' . $id . '&delete_image=' . $img['image_id'] . '" class="text-danger small" onclick="return confirm(\'Delete this image?\')">Delete</a>';
                                            echo '</div>';
                                        }
                                    } catch (PDOException $e) { /* Ignore if table doesn't exist yet */
                                    }
                                    ?>
                                </div>
                                <input type="file" name="gallery_images[]" class="form-control" multiple
                                    accept="image/*">
                                <small class="text-muted">Select multiple images to add to gallery</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Update Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>