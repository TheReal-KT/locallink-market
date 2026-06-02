<?php
$categories = [
    ['name' => 'Phones', 'count' => 18],
    ['name' => 'Fashion', 'count' => 24],
    ['name' => 'Homeware', 'count' => 11],
    ['name' => 'Study', 'count' => 9],
];

$products = [
    [
        'id' => 1,
        'title' => 'Refurbished smartphone',
        'price' => 'R 2 450',
        'category' => 'Phones',
        'location' => 'Mamelodi',
        'seller' => 'Thabo N.',
        'rating' => '4.8',
        'status' => 'Popular item',
        'description' => 'Unlocked Android phone with a clean screen, charger, and seven-day return window.',
        'image' => 'assets/images/product-phone.svg',
    ],
    [
        'id' => 2,
        'title' => 'Canvas street backpack',
        'price' => 'R 380',
        'category' => 'Fashion',
        'location' => 'Soweto',
        'seller' => 'Lebo M.',
        'rating' => '4.6',
        'status' => 'Fast replies',
        'description' => 'Durable everyday bag with laptop sleeve, side pockets, and reinforced straps.',
        'image' => 'assets/images/product-bag.svg',
    ],
    [
        'id' => 3,
        'title' => 'Minimal desk lamp',
        'price' => 'R 220',
        'category' => 'Homeware',
        'location' => 'Hatfield',
        'seller' => 'Aisha K.',
        'rating' => '4.9',
        'status' => 'Recently listed',
        'description' => 'Adjustable desk lamp for study rooms, small offices, and bedside tables.',
        'image' => 'assets/images/product-lamp.svg',
    ],
    [
        'id' => 4,
        'title' => 'Accounting textbook set',
        'price' => 'R 640',
        'category' => 'Study',
        'location' => 'Midrand',
        'seller' => 'Nandi P.',
        'rating' => '4.7',
        'status' => 'Student favourite',
        'description' => 'Second-year accounting books with neat notes and protective covers.',
        'image' => 'assets/images/product-books.svg',
    ],
];

$orders = [
    ['code' => '#LLM-1038', 'item' => 'Canvas street backpack', 'seller' => 'Lebo M.', 'status' => 'Ready for delivery', 'total' => 'R 380'],
    ['code' => '#LLM-1031', 'item' => 'Minimal desk lamp', 'seller' => 'Aisha K.', 'status' => 'Completed', 'total' => 'R 220'],
    ['code' => '#LLM-1024', 'item' => 'Accounting textbook set', 'seller' => 'Nandi P.', 'status' => 'Pending seller', 'total' => 'R 640'],
];

$sellerStats = [
    ['label' => 'Active listings', 'value' => '12'],
    ['label' => 'Pending orders', 'value' => '5'],
    ['label' => 'Monthly sales', 'value' => 'R 8 920'],
];
?>
