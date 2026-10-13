<?php
/**
 * JYSTORE Product Catalog (Pharmaceutical & Healthcare)
 */

declare(strict_types=1);

function getProductCatalog(): array
{
    return [
        'fishoil' => [
            'id' => 'fishoil',
            'name' => 'Omega-3 Triple Strength Fish Oil 1200mg',
            'category' => 'Heart & Cognitive',
            'category_slug' => 'heart',
            'price' => 78.00,
            'price_cents' => 7800,
            'original_price' => 110.00,
            'image' => 'assets/fishoil.jpg',
            'badge' => 'Best Seller',
            'badge_color' => 'badge-blue',
            'rating' => 4.9,
            'reviews' => 642,
            'tagline' => 'Molecularly distilled deep-sea wild fish oil with 720mg active EPA & DHA for heart, joint, and brain health.',
            'specs' => ['120 Softgels', '720mg EPA/DHA', 'Heavy Metal Tested', 'Zero Fishy Aftertaste'],
            'stock' => 38,
        ],
        'vitaminc' => [
            'id' => 'vitaminc',
            'name' => 'Pure Liposomal Vitamin C 1000mg + Zinc',
            'category' => 'Immunity & Daily Health',
            'category_slug' => 'immunity',
            'price' => 45.00,
            'price_cents' => 4500,
            'original_price' => 65.00,
            'image' => 'assets/vitaminc.jpg',
            'badge' => 'Pharmacist Choice',
            'badge_color' => 'badge-teal',
            'rating' => 4.8,
            'reviews' => 489,
            'tagline' => 'High-absorption liposomal delivery buffered with Zinc and bioflavonoids for maximum immunity and cellular defense.',
            'specs' => ['60 Veggie Caps', '1000mg Vit C', '15mg Zinc Citrate', 'Gentle on Stomach'],
            'stock' => 52,
        ],
        'probiotic' => [
            'id' => 'probiotic',
            'name' => 'Multi-Strain Probiotic Complex 50B CFU',
            'category' => 'Digestive & Gut Health',
            'category_slug' => 'gut',
            'price' => 89.00,
            'price_cents' => 8900,
            'original_price' => 135.00,
            'image' => 'assets/probiotic.jpg',
            'badge' => 'High Potency',
            'badge_color' => 'badge-emerald',
            'rating' => 4.9,
            'reviews' => 812,
            'tagline' => '14 clinically researched live probiotic strains with prebiotic FOS in delayed-release acid-resistant capsules.',
            'specs' => ['50 Billion CFU', '14 Live Strains', 'Prebiotic FOS Blend', 'Shelf-Stable Tech'],
            'stock' => 24,
        ],
        'melatonin' => [
            'id' => 'melatonin',
            'name' => 'Dual-Release Melatonin 5mg Sleep Aid',
            'category' => 'Sleep & Relaxation',
            'category_slug' => 'sleep',
            'price' => 52.00,
            'price_cents' => 5200,
            'original_price' => 75.00,
            'image' => 'assets/melatonin.jpg',
            'badge' => 'Restful Sleep',
            'badge_color' => 'badge-indigo',
            'rating' => 4.7,
            'reviews' => 395,
            'tagline' => 'Fast-acting dissolution layer for quick sleep onset paired with a sustained release layer for uninterrupted rest.',
            'specs' => ['90 Chewables', '5mg Active Melatonin', 'L-Theanine Infused', 'Non-Habit Forming'],
            'stock' => 41,
        ],
        'musclegel' => [
            'id' => 'musclegel',
            'name' => 'Deep Relief Thermal Muscle & Joint Gel',
            'category' => 'Pain Relief & Mobility',
            'category_slug' => 'pain',
            'price' => 36.00,
            'price_cents' => 3600,
            'original_price' => 50.00,
            'image' => 'assets/musclegel.jpg',
            'badge' => 'Fast Relief',
            'badge_color' => 'badge-amber',
            'rating' => 4.9,
            'reviews' => 530,
            'tagline' => 'Targeted pain relief gel with Arnica and Glucosamine. Provides refreshing cooling followed by therapeutic deep warmth.',
            'specs' => ['100g Tube', 'Dual Cool/Warm', 'Arnica & Menthol', 'Non-Greasy & Clean'],
            'stock' => 65,
        ],
        'woundcare' => [
            'id' => 'woundcare',
            'name' => 'Clinical Antiseptic Wound Care Spray',
            'category' => 'First Aid & Antiseptic',
            'category_slug' => 'firstaid',
            'price' => 28.00,
            'price_cents' => 2800,
            'original_price' => 42.00,
            'image' => 'assets/woundcare.jpg',
            'badge' => 'Hospital Grade',
            'badge_color' => 'badge-rose',
            'rating' => 4.8,
            'reviews' => 294,
            'tagline' => 'Sting-free, alcohol-free antimicrobial barrier spray that purifies minor cuts, abrasions, and burns while promoting recovery.',
            'specs' => ['120ml Spray', 'Sting-Free Formula', 'Kills 99.9% Germs', 'Hypoallergenic'],
            'stock' => 88,
        ],
    ];
}

function getProductById(string $id): ?array
{
    $catalog = getProductCatalog();
    return $catalog[$id] ?? null;
}
