<?php
session_start();
require 'db_conn.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found");
}

// Fetch gallery images
$stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
$stmt->execute([$product_id]);
$gallery_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($product['name']); ?> - Aswath Naturale
    </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .product-gallery-thumb {
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.3s;
        }

        .product-gallery-thumb:hover,
        .product-gallery-thumb.active {
            opacity: 1;
            border: 2px solid #2c5f2d;
        }
    </style>
</head>

<body>
    <!-- Navbar (Simplified) -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Aswath Naturale</a>
            <div class="ms-auto">
                <a class="nav-link text-white" href="cart.php">
                    <i class="fas fa-shopping-cart"></i> Cart
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5" style="margin-top: 80px;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php#products">
                        <?php echo htmlspecialchars($product['category']); ?>
                    </a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($product['name']); ?>
                </li>
            </ol>
        </nav>

        <div class="row">
            <!-- Image Gallery -->
            <div class="col-md-6 mb-4">
                <div class="card border-0">
                    <div class="main-image mb-3 text-center bg-light p-3"
                        style="height: 400px; display: flex; align-items: center; justify-content: center;">
                        <?php if ($product['image_url']): ?>
                            <img id="mainImage" src="<?php echo htmlspecialchars($product['image_url']); ?>"
                                class="img-fluid" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        <?php else: ?>
                            <span class="text-muted">No Image</span>
                        <?php endif; ?>
                    </div>
                    <?php if (count($gallery_images) > 0): ?>
                        <div class="d-flex gap-2 service-thumbnails overflow-auto">
                            <!-- Main Product Image Thumb -->
                            <?php if ($product['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
                                    class="product-gallery-thumb active" width="80" height="80" style="object-fit: cover;"
                                    onclick="changeImage(this.src, this)">
                            <?php endif; ?>

                            <!-- Gallery Thumbs -->
                            <?php foreach ($gallery_images as $img): ?>
                                <img src="<?php echo htmlspecialchars($img['image_url']); ?>" class="product-gallery-thumb"
                                    width="80" height="80" style="object-fit: cover;" onclick="changeImage(this.src, this)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-md-6">
                <h1 class="display-5 fw-bold mb-3">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>
                <div class="mb-3">
                    <span class="badge bg-success">
                        <?php echo htmlspecialchars($product['category']); ?>
                    </span>
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="badge bg-info text-dark">In Stock</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Out of Stock</span>
                    <?php endif; ?>
                </div>

                <h2 class="text-primary mb-4">₹
                    <?php echo number_format($product['price'], 2); ?>
                </h2>

                <p class="lead mb-4">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </p>

                <?php if (!empty($product['benefits'])): ?>
                    <div class="mb-4">
                        <h5 class="text-success"><i class="fas fa-check-circle me-2"></i>Key Benefits</h5>
                        <ul class="list-unstyled">
                            <?php
                            $benefits = explode("\n", $product['benefits']);
                            foreach ($benefits as $benefit) {
                                if (trim($benefit)) {
                                    echo '<li class="mb-2"><i class="fas fa-check text-success me-2"></i>' . htmlspecialchars(trim($benefit)) . '</li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($product['detailed_description'])): ?>
                    <div class="mb-4">
                        <h5>Description</h5>
                        <p class="text-muted">
                            <?php echo nl2br(htmlspecialchars($product['detailed_description'])); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-2 col-md-6">
                    <button class="btn btn-primary btn-lg"
                        onclick="addToCart(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>', <?php echo $product['price']; ?>)">
                        <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                    </button>
                    <a href="https://wa.me/91979166610?text=I'm interested in <?php echo urlencode($product['name']); ?>"
                        class="btn btn-success btn-lg" target="_blank">
                        <i class="fab fa-whatsapp me-2"></i> Order via WhatsApp
                    </a>
                </div>

                <div class="mt-5">
                    <h5><i class="fas fa-truck me-2"></i> Shipping Info</h5>
                    <ul class="list-unstyled text-muted">
                        <li>Standard Delivery: 3-5 Business Days</li>
                        <li>Free shipping on orders above ₹1000</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeImage(src, thumb) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.product-gallery-thumb').forEach(el => el.classList.remove('active'));
            thumb.classList.add('active');
        }

        function addToCart(id, name, price) {
            fetch('api/cart_actions.php?action=add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: id, name: name, price: price })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Added to cart!');
                    } else {
                        alert('Failed to add to cart: ' + (data.message || 'Unknown error'));
                    }
                });
        }
    </script>
</body>

</html>