<?php

return [
    'categories' => [
        'Categories',
        'Organize the categories in your GameCore catalog.',
        'tags',
        'category',
        [['name', 'Name'], ['description', 'Description']],
        [],
    ],
    'products' => [
        'Products',
        'Manage catalog details, prices, and stock.',
        'box-seam',
        'product',
        [['name', 'Product'], ['sku', 'SKU'], ['category.name', 'Category'], ['price', 'Price (JOD)'], ['stock', 'Stock'], ['is_active', 'Active']],
        [['category_id', 'Category', 'categories'], ['name', 'Name'], ['sku', 'SKU'], ['description', 'Description', 'textarea'], ['brand', 'Brand', 'optional'], ['platform', 'Platform', 'optional'], ['price', 'Price', 'money'], ['sale_price', 'Sale price', 'optional-money'], ['stock', 'Stock', 'number'], ['is_active', 'Active', 'boolean']],
        ['category_id', 'All categories', 'categories'],
    ],
    'users' => [
        'Users',
        'Manage customer accounts and account access.',
        'people',
        'user',
        [['name', 'Name'], ['email', 'Email'], ['phone', 'Phone'], ['is_active', 'Active']],
        [['name', 'Name'], ['email', 'Email', 'email'], ['phone', 'Phone', 'optional'], ['password', 'Password', 'password'], ['is_active', 'Active', 'boolean']],
    ],
    'orders' => [
        'Orders',
        'Manage customer orders, delivery details, and status.',
        'receipt',
        'order',
        [['id', 'Order'], ['recipient_name', 'Recipient'], ['city', 'City'], ['total', 'Total (JOD)'], ['status', 'Status']],
        [['user_id', 'Customer', 'users', 'create'], ['recipient_name', 'Recipient'], ['phone', 'Phone'], ['city', 'City'], ['shipping_address', 'Shipping address', 'textarea'], ['shipping_fee', 'Shipping fee', 'money', 'create'], ['payment_method', 'Payment method', ['cod', 'card']], ['payment_status', 'Payment status', ['pending', 'paid', 'failed', 'refunded'], 'edit'], ['status', 'Order status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'], 'edit'], ['notes', 'Notes', 'optional-textarea']],
        ['status', 'All statuses', ['pending', 'processing', 'shipped', 'delivered', 'cancelled']],
    ],
    'reviews' => [
        'Reviews',
        'Manage product ratings, comments, and visibility.',
        'star',
        'review',
        [['product.name', 'Product'], ['user.name', 'Customer'], ['rating', 'Rating'], ['comment', 'Comment'], ['is_visible', 'Visible']],
        [['user_id', 'Customer', 'users'], ['product_id', 'Product', 'products'], ['rating', 'Rating (1–5)', 'number'], ['comment', 'Comment', 'optional-textarea'], ['is_visible', 'Visible', 'boolean']],
        ['is_visible', 'All visibility', [['1', 'Visible'], ['0', 'Hidden']]],
    ],
];
