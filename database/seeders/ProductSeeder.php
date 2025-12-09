<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $imageMappings = [
            'Umbrella' => [
                'image'  => 'IMG_0109.jpg',
                'images' => ['IMG_0109.jpg', 'IMG_0109.jpg'],
            ],
            'Keychain' => [
                'image'  => 'IMG_0121.jpg',
                'images' => ['IMG_0121.jpg', 'IMG_0121.jpg'],
            ],
            'Keychain for Car' => [
                'image'  => 'IMG_0127.jpg',
                'images' => ['IMG_0127.jpg', 'IMG_0127.jpg'],
            ],
            'CVSU Mug' => [
                'image'  => 'IMG_0113.jpg',
                'images' => ['IMG_0113.jpg', 'IMG_0114.jpg', 'mug.jpg'],
            ],
            'Cap' => [
                'image'  => 'IMG_0125.jpg',
                'images' => ['IMG_0125.jpg', 'IMG_0125.jpg'],
            ],
            'Bonnet' => [
                'image'  => 'IMG_0123.jpg',
                'images' => ['IMG_0123.jpg', 'IMG_0123.jpg'],
            ],
            'T-Shirt' => [
                'image'  => 'IMG_0136.jpg',
                'images' => ['IMG_0136.jpg', 'IMG_0137.jpg'],
            ],
            'Polo Shirt' => [
                'image'  => 'IMG_0140.jpg',
                'images' => ['IMG_0142.jpg', 'IMG_0145.jpg'],
            ],
            'Case of Utensils' => [
                'image'  => 'IMG_0147.jpg',
                'images' => ['IMG_0147.jpg', 'IMG_0147.jpg'],
            ],
            'Notebook' => [
                'image'  => 'IMG_0148.jpg',
                'images' => ['IMG_0148.jpg', 'IMG_0148.jpg'],
            ],
            'Coffee Blend' => [
                'image'  => 'blend_coffee.jpg',
                'images' => ['IMG_0150.jpg', 'IMG_0152.jpg', 'IMG_0155.jpg', 'IMG_0156.jpg', 'original_blend.jpg', 'liberica_based.jpg', 'anti_oxidant.jpg', 'naturally_sweetened.jpg'],
            ],
            'CvSU Fans' => [
                'image'  => 'IMG_0159.jpg',
                'images' => ['IMG_0159.jpg', 'IMG_0159.jpg'],
            ],
            'Lace' => [
                'image'  => 'lace.jpg',
                'images' => ['lace_2.jpg', 'lace_3.jpg', 'lace_4.jpg', 'lace_5.jpg'],
            ],
            'Jacket' => [
                'image'  => 'IMG_0161.jpg',
                'images' => ['IMG_0161.jpg', 'IMG_0161.jpg', 'jacket.jpg'],
            ],
            'Ballpens' => [
                'image'  => 'IMG_0163.jpg',
                'images' => ['IMG_0163.jpg', 'IMG_0164.jpg'],
            ],
            'Blouse' => [
                'image'  => 'blouse.jpg',
                'images' => ['blouse.jpg'],
            ],
            'Slacks' => [
                'image'  => 'slacks.jpg',
                'images' => ['slacks.jpg'],
            ],
            'pe tshirt' => [
                'image'  => 'pe_t-shirt.jpg',
                'images' => ['pe_t-shirt.jpg'],
            ],
            'pe short' => [
                'image'  => 'pe_short.jpg',
                'images' => ['pe_short.jpg'],
            ],
            // 'polo' => [
            //     'image'  => 'polo.jpg',
            //     'images' => ['polo.jpg'],
            // ],
            'NSTP t-shirt' => [
                'image'  => 'NSTP_t-shirt.jpg',
                'images' => ['NSTP_t-shirt.jpg'],
            ],
            'cspear tshirt' => [
                'image'  => 'cspear_t-shirt.jpg',
                'images' => ['cspear_t-shirt.jpg'],
            ],
            'cspear short' => [
                'image'  => 'cspear_shorts.jpg',
                'images' => ['cspear_shorts.jpg'],
            ],
            'cspear jogging pants' => [
                'image'  => 'cspear_jogging-pants.jpg',
                'images' => ['cspear_jogging-pants.jpg'],
            ],
            'semi-golf umbrella manual' => [
                'image'  => 'semi_golf_umbrella_manual.jpg',
                'images' => ['semi_golf_umbrella_manual.jpg'],
            ],
            'semi-golf umbrella automatic' => [
                'image'  => 'semi_golf_umbrella_automatic.jpg',
                'images' => ['semi_golf_umbrella_automatic.jpg'],
            ],
            'cvsu pin' => [
                'image'  => 'cvsu_pin.jpg',
                'images' => ['cvsu_pin.jpg'],
            ],
            // 'tumbler' => [
            //     'image'  => 'tumbler.jpg',
            //     'images' => ['tumbler.jpg'],
            // ],
        ];

        $productDescriptions = [
            'Umbrella' => [
                'short' => 'Stay dry in style with our premium CvSU umbrella - perfect for rainy campus days!',
                'long' => 'Our high-quality CvSU umbrella features durable construction, wind-resistant design, and the official university logo. Perfect for protecting you from rain or sun while showing your school spirit across campus.

UMBRELLA SPECIFICATIONS:
Type: Foldable manual umbrella
Canopy Size: 42-inch diameter for maximum coverage
Material: Waterproof polyester fabric with steel ribs
Open/Close: Manual mechanism for reliable use
Wind Resistance: Reinforced frame for windy conditions
Features: Quick-dry fabric, compact folding design
Weight: Lightweight at only 450g for easy carrying
Handle: Ergonomic rubber grip for comfort',
            ],
            'Keychain' => [
                'short' => 'Carry your CvSU pride everywhere with our durable and stylish keychain!',
                'long' => 'This beautifully crafted CvSU keychain features the official university emblem in polished metal. Lightweight yet durable, it\'s the perfect accessory to keep your keys organized while displaying your campus loyalty.

KEYCHAIN SPECIFICATIONS:
Material: Premium metal alloy with enamel finish
Size: 3cm × 2cm compact design
Type: Standard key ring attachment
Design: Official CvSU emblem with school colors
Weight: Lightweight 15g for easy carrying
Durability: Rust-resistant and long-lasting
Use Case: Perfect for keys, bags, and backpacks
Included: 1 piece per order',
            ],
            'Keychain for Car' => [
                'short' => 'Show your CvSU spirit on the road with our special car keychain!',
                'long' => 'Designed specifically for car keys, this premium keychain combines functionality with school pride. The sturdy construction ensures your keys stay secure while the CvSU logo shows your university affiliation wherever you drive.

CAR KEYCHAIN SPECIFICATIONS:
Material: Durable acrylic with metal clasp
Size: 4cm × 3cm with clear visibility
Type: Secure clip attachment for car keys
Design: Vibrant CvSU colors and logo
Weight: 20g - lightweight yet sturdy
Durability: Scratch-resistant surface
Use Case: Specifically designed for car key organization
Included: Single piece with secure fastening',
            ],
            'CVSU Mug' => [
                'short' => 'Start your day with CvSU pride using our premium ceramic mug!',
                'long' => 'Enjoy your favorite beverages in style with our official CvSU mug. Made from high-quality ceramic that maintains temperature perfectly, featuring the university logo for your morning coffee or study session drinks.

MUG SPECIFICATIONS:
Material: High-quality ceramic construction
Capacity: 350ml perfect for coffee or tea
Dimensions: 9cm diameter × 10cm height
Weight: 400g with comfortable handle
Color: White ceramic with CvSU colored logo
Heat Resistance: Microwave and dishwasher safe
Features: Comfortable grip handle, printed design
Usage: Perfect for office, home, or dorm use',
            ],
            'Cap' => [
                'short' => 'Top off your look with our comfortable CvSU cap!',
                'long' => 'Our adjustable CvSU cap provides excellent sun protection while showcasing your university spirit. Made from breathable fabric with an embroidered logo, perfect for sunny campus days or athletic events.

CAP SPECIFICATIONS:
Type: Classic baseball cap style
Material: 100% cotton twill fabric
Size: Adjustable strap for one-size-fits-all
Head Circumference: Adjustable 54-62cm
Brim Length: 8cm for optimal sun protection
Features: Breathable fabric, UV protection
Embroidery: High-quality CvSU logo stitching
Design: Structured crown with curved brim',
            ],
            'Bonnet' => [
                'short' => 'Stay warm and stylish with our cozy CvSU bonnet!',
                'long' => 'Keep warm during cooler weather with our comfortable CvSU bonnet. Soft, stretchable material with the university logo, perfect for early morning classes or evening campus activities.

BONNET SPECIFICATIONS:
Type: Stretchable beanie/stocking cap
Material: Soft acrylic blend fabric
Size: One size fits most adults
Head Circumference: Stretches to fit 52-60cm
Features: Warm and breathable, machine washable
Design: Cuffed edge with embroidered CvSU logo
Usage: Perfect for cool weather and outdoor activities
Care: Easy maintenance and quick drying',
            ],
            'T-Shirt' => [
                'short' => 'Wear your CvSU pride with our comfortable and stylish t-shirts!',
                'long' => 'Made from 100% premium cotton, our CvSU t-shirts offer exceptional comfort and durability. Features the official university design, perfect for everyday wear, campus events, or representing your school pride.

T-SHIRT SPECIFICATIONS:
Fit Type: Regular fit for comfort
Fabric: 100% premium combed cotton
Fabric Weight: 180 GSM for optimal comfort
Sleeve Length: Short sleeve design
Neck Type: Ribbed round neck collar
Design: Screen printed CvSU design
Care Instructions: Machine washable
Features: Pre-shrunk fabric, colorfast printing',
            ],
            'Polo Shirt' => [
                'short' => 'Elevate your style with our premium CvSU polo shirts!',
                'long' => 'Our professional CvSU polo shirts combine comfort with sophistication. Made from high-quality pique cotton with embroidered university logo, perfect for semi-formal events, presentations, or looking sharp on campus.

POLO SHIRT SPECIFICATIONS:
Fit Type: Regular fit for professional look
Fabric: Premium pique cotton blend
Fabric Weight: 200 GSM for durability
Sleeve Length: Short sleeve with ribbed cuffs
Neck Type: Collared with 3-button placket
Design: Embroidered CvSU logo
Care Instructions: Machine wash, tumble dry low
Features: Side vents, quality stitching',
            ],
            'Case of Utensils' => [
                'short' => 'Eco-friendly utensil set for sustainable campus dining!',
                'long' => 'This reusable utensil case includes everything you need for meals on the go. Complete with CvSU branding, it\'s perfect for environmentally conscious students who want to reduce waste while enjoying campus dining.

UTENSIL CASE SPECIFICATIONS:
Material: BPA-free plastic utensils
Capacity: Complete set for one person
Dimensions: Case measures 18cm × 6cm × 3cm
Weight: Lightweight at 150g
Color: Clear case with CvSU logo
Features: Microwave safe, reusable design
Usage: Perfect for campus dining and picnics
Included: Fork, spoon, knife, and napkin',
            ],
            'Notebook' => [
                'short' => 'Capture your ideas in style with our CvSU branded notebooks!',
                'long' => 'High-quality CvSU notebooks perfect for lectures, research, and creative projects. Featuring durable covers and premium paper, these notebooks help you stay organized while showing your university spirit.

NOTEBOOK SPECIFICATIONS:
Type: Spiral-bound notebook
Material: 80 GSM premium paper
Size: A5 size (14.8cm × 21cm)
Page Count: 100 pages (50 sheets)
Paper Quality: 80 GSM for no bleed-through
Features: Perforated pages, sturdy cover
Design: CvSU branded cover in school colors
Usage: Ideal for notes, sketches, and planning',
            ],
            'Coffee Blend' => [
                'short' => 'Experience the rich taste of CvSU\'s exclusive coffee blends!',
                'long' => 'Savor the unique flavors of our specially curated CvSU coffee blends. Sourced from quality beans and roasted to perfection, each variety offers a distinct taste experience to fuel your study sessions and campus life.

COFFEE BLEND SPECIFICATIONS:
Type: Premium ground coffee blend
Material: 100% Arabica coffee beans
Capacity: 200g resealable packaging
Features: Freshly roasted, aromatic blend
Brewing: Suitable for all coffee makers
Packaging: Vacuum-sealed for freshness
Origin: Locally sourced quality beans
Usage: Perfect for morning routines and study breaks',
            ],
            'CvSU Fans' => [
                'short' => 'Stay cool with our handy CvSU hand fans!',
                'long' => 'Beat the heat with these portable CvSU hand fans. Lightweight and effective, featuring vibrant university designs - perfect for warm classrooms, outdoor events, or keeping comfortable during campus activities.

FAN SPECIFICATIONS:
Material: Durable paper and wooden frame
Size: 20cm diameter when open
Design: Traditional folding hand fan
Features: Lightweight and portable
Usage: Manual operation, no batteries needed
Design: Full-color CvSU graphics
Weight: Only 80g for easy carrying
Included: Single fan per order',
            ],
            'Lace' => [
                'short' => 'Add CvSU flair to your shoes with our decorative laces!',
                'long' => 'Custom CvSU shoelaces that let you personalize your footwear with university colors and logos. Durable construction with vibrant colors that maintain their brightness through daily campus wear.

LACE SPECIFICATIONS:
Width: 1cm standard shoelace width
Length: 120cm perfect for most shoes
Material: Durable nylon construction
Type: Standard aglet tips for easy threading
Design: CvSU colors and pattern
Features: Fade-resistant, durable stitching
Use Case: Athletic shoes, casual footwear
Included: 1 pair (2 laces)',
            ],
            'Jacket' => [
                'short' => 'Stay warm and represent CvSU in our premium jackets!',
                'long' => 'Our high-quality CvSU jackets provide excellent protection from cooler weather. Featuring water-resistant material, comfortable lining, and prominent university branding - perfect for chilly classrooms or outdoor campus activities.

JACKET SPECIFICATIONS:
Type: Hooded jacket with front zipper
Material: Polyester with water-resistant coating
Lining: Soft fleece interior for warmth
Features: Front pockets, adjustable hood
Sleeve Length: Full-length with ribbed cuffs
Design: Embroidered CvSU logo on chest
Care: Machine washable, quick drying
Usage: Perfect for cool weather and rain protection',
            ],
            'Ballpens' => [
                'short' => 'Write your success story with CvSU ballpens!',
                'long' => 'Smooth-writing CvSU ballpens designed for comfortable note-taking and exams. Reliable ink flow with the university logo, ensuring you\'re always prepared for lectures, tests, and academic work.

BALLPEN SPECIFICATIONS:
Type: Standard ballpoint pen
Material: Plastic barrel with metal tip
Ink Color: Blue and black options available
Features: Smooth writing, comfortable grip
Refill: Standard replaceable ink cartridge
Design: CvSU logo printed on barrel
Usage: Perfect for exams and note-taking
Included: Single pen with protective cap',
            ],
            'Blouse' => [
                'short' => 'Professional CvSU blouses for the modern student!',
                'long' => 'Elegant and comfortable CvSU blouses designed for female students. Made from premium breathable fabric with proper fit and university branding, perfect for academic requirements and professional appearances.

BLOUSE SPECIFICATIONS:
Fit Type: Regular fit for professional look
Fabric: Poly-cotton blend for comfort
Fabric Weight: 160 GSM lightweight material
Sleeve Length: Long sleeve with cuffs
Neck Type: Collared with front button placket
Design: Embroidered CvSU insignia
Care: Machine washable, iron safe
Features: Tuck-in style, professional appearance',
            ],
            'Slacks' => [
                'short' => 'Complete your uniform with our comfortable CvSU slacks!',
                'long' => 'Well-tailored CvSU slacks offering comfort and professional appearance. Durable fabric with perfect fit, designed to meet uniform requirements while ensuring all-day comfort during campus activities.

SLACKS SPECIFICATIONS:
Fit Type: Straight leg professional cut
Fabric: Polyester-wool blend for durability
Waist: Mid-rise with belt loops
Features: Front and back pockets, zip fly
Inseam: Standard length with hem options
Design: Professional trousers style
Care: Machine wash, easy iron
Usage: Perfect for uniform requirements',
            ],
            'pe tshirt' => [
                'short' => 'Comfortable PE t-shirts for active campus life!',
                'long' => 'Specially designed CvSU PE t-shirts made from breathable, moisture-wicking fabric. Perfect for physical education classes, sports activities, and comfortable casual wear around campus.

PE T-SHIRT SPECIFICATIONS:
Fit Type: Athletic cut for movement
Fabric: Moisture-wicking polyester blend
Fabric Weight: 150 GSM for active wear
Sleeve Length: Short sleeve raglan cut
Neck Type: Ribbed crew neck
Design: Screen printed PE designation
Care: Machine wash, quick dry
Features: Breathable, movement-friendly',
            ],
            'pe short' => [
                'short' => 'Comfortable PE shorts for maximum movement!',
                'long' => 'Designed for physical activities, these CvSU PE shorts offer excellent flexibility and comfort. Lightweight fabric with secure pockets, perfect for sports, exercises, and active campus life.

PE SHORTS SPECIFICATIONS:
Fit Type: Athletic loose fit
Fabric: Lightweight polyester blend
Waist: Elastic with drawstring
Features: Side pockets, gusseted crotch
Inseam: 5-inch standard athletic length
Design: Screen printed CvSU logo
Care: Machine wash, quick dry
Usage: Perfect for sports and PE classes',
            ],
            // 'polo' => [
            //     'short' => 'Classic CvSU polo shirts for male students!',
            //     'long' => 'Traditional polo shirts meeting CvSU uniform standards. Made from high-quality fabric with embroidered university logo, providing professional appearance with all-day comfort.
            //
            // POLO UNIFORM SPECIFICATIONS:
            // Fit Type: Regular uniform fit
            // Fabric: Premium pique cotton
            // Fabric Weight: 220 GSM for durability
            // Sleeve Length: Short sleeve with ribbing
            // Neck Type: Collared with 3-button placket
            // Design: Embroidered CvSU crest
            // Care: Professional care instructions
            // Features: Uniform compliance, quality stitching',
            // ],
            'NSTP t-shirt' => [
                'short' => 'Show your NSTP pride with official program t-shirts!',
                'long' => 'Official NSTP t-shirts featuring program-specific designs and CvSU branding. Comfortable cotton material perfect for community service activities and representing your NSTP involvement.

NSTP T-SHIRT SPECIFICATIONS:
Fit Type: Comfortable regular fit
Fabric: 100% soft cotton
Fabric Weight: 180 GSM for comfort
Sleeve Length: Short sleeve design
Neck Type: Crew neck collar
Design: NSTP program specific graphics
Care: Easy care washing
Features: Official program identification',
            ],
            'cspear tshirt' => [
                'short' => 'CSPEAR program t-shirts for aspiring engineers!',
                'long' => 'Specialty t-shirts for CSPEAR program participants. Features program-specific designs with comfortable fabric, perfect for laboratory work, classes, and engineering events.

CSPEAR T-SHIRT SPECIFICATIONS:
Fit Type: Engineering program fit
Fabric: Cotton-polyester blend
Fabric Weight: 170 GSM for daily wear
Sleeve Length: Short sleeve comfort
Neck Type: Standard crew neck
Design: CSPEAR program branding
Care: Machine washable
Features: Program-specific identification',
            ],
            'cspear short' => [
                'short' => 'CSPEAR shorts for practical engineering activities!',
                'long' => 'Designed for CSPEAR students, these shorts combine comfort with durability. Perfect for hands-on engineering activities, laboratory sessions, and casual program events.

CSPEAR SHORTS SPECIFICATIONS:
Fit Type: Practical athletic fit
Fabric: Durable twill blend
Waist: Elastic with drawstring
Features: Multiple pockets for tools
Inseam: Functional length for movement
Design: CSPEAR program logo
Care: Heavy-duty washing
Usage: Laboratory and fieldwork',
            ],
            'cspear jogging pants' => [
                'short' => 'Comfortable jogging pants for CSPEAR students!',
                'long' => 'Warm and comfortable jogging pants specifically for CSPEAR program. Ideal for early morning classes, laboratory work, or casual wear during engineering activities.

CSPEAR JOGGING PANTS SPECIFICATIONS:
Fit Type: Comfortable relaxed fit
Fabric: Brushed polyester blend
Waist: Elastic with adjustable drawstring
Features: Practical pockets, ribbed cuffs
Inseam: Full-length design
Design: Program-specific branding
Care: Easy maintenance washing
Usage: Campus and laboratory wear',
            ],
            'semi-golf umbrella manual' => [
                'short' => 'Large manual umbrella for maximum coverage!',
                'long' => 'Semi-golf manual umbrella providing extensive rain protection. Sturdy construction with automatic open feature, perfect for walking across campus during heavy rainfall.

SEMI-GOLF UMBRELLA SPECIFICATIONS:
Type: Semi-golf manual umbrella
Canopy Size: 48-inch large diameter
Fold Length: 35cm when closed
Material: Waterproof nylon with fiberglass ribs
Mechanism: Manual open/close
Wind Resistance: Reinforced frame
Features: Large coverage, comfortable handle
Weight: 550g for sturdy protection',
            ],
            'semi-golf umbrella automatic' => [
                'short' => 'Automatic semi-golf umbrella for convenience!',
                'long' => 'Premium automatic semi-golf umbrella with one-touch opening. Larger coverage area with wind-resistant design, featuring CvSU branding for stylish weather protection.

AUTOMATIC UMBRELLA SPECIFICATIONS:
Type: Automatic open semi-golf
Canopy Size: 48-inch maximum coverage
Fold Length: 38cm compact when closed
Material: Premium polyester with steel frame
Mechanism: One-touch automatic opening
Wind Resistance: Ventilated canopy design
Features: Ergonomic handle, quick dry
Weight: 600g with premium construction',
            ],
            'cvsu pin' => [
                'short' => 'Wear your CvSU pride with our official pins!',
                'long' => 'Official CvSU lapel pins made from quality materials. Perfect for attaching to bags, uniforms, or clothing to show your university spirit in a subtle, elegant way.

PIN SPECIFICATIONS:
Type: Enamel lapel pin
Size: 2.5cm diameter
Material: Metal with enamel fill
Backing: Rubber clutch safety pin
Design: Official CvSU emblem
Finish: Glossy enamel coating
Use Case: Bags, uniforms, clothing
Included: Single pin with secure backing',
            ],
            // 'tumbler' => [
            //     'short' => 'Stay hydrated with our insulated CvSU tumbler!',
            //     'long' => 'Double-walled insulated CvSU tumbler keeps drinks hot or cold for hours. Leak-proof design with university branding, perfect for carrying to classes, library sessions, or campus activities.
            //
            // TUMBLER SPECIFICATIONS:
            // Material: Stainless steel double-wall
            // Capacity: 500ml perfect for daily use
            // Dimensions: 7cm diameter × 21cm height
            // Weight: 300g lightweight design
            // Color: Metallic with CvSU logo
            // Insulation: Keeps hot 6hrs, cold 12hrs
            // Features: Leak-proof lid, carry handle
            // Usage: Perfect for beverages on campus',
            // ],
        ];

        $sizeDescriptions = [
            'tops' => [
                'xs' => 'Extra Small: Bust 32-34", Shoulder 15", Sleeve 23", Length 25"',
                's' => 'Small: Bust 34-36", Shoulder 16", Sleeve 24", Length 26"',
                'm' => 'Medium: Bust 38-40", Shoulder 17", Sleeve 25", Length 27"',
                'l' => 'Large: Bust 42-44", Shoulder 18", Sleeve 26", Length 28"',
                'xl' => 'Extra Large: Bust 46-48", Shoulder 19", Sleeve 27", Length 29"',
                '2xl' => '2X Large: Bust 50-52", Shoulder 20", Sleeve 28", Length 30"',
                '3xl' => '3X Large: Bust 54-56", Shoulder 21", Sleeve 29", Length 31"',
                '4xl' => '4X Large: Bust 58-60", Shoulder 22", Sleeve 30", Length 32"',
                '5xl' => '5X Large: Bust 62-64", Shoulder 23", Sleeve 31", Length 33"',
                '6xl' => '6X Large: Bust 66-68", Shoulder 24", Sleeve 32", Length 34"',
                '7xl' => '7X Large: Bust 70-72", Shoulder 25", Sleeve 33", Length 35"',
            ],
            'bottoms' => [
                'xs' => 'Extra Small: Waist 24-26", Hips 34-36", Inseam 30", Thigh 20"',
                's' => 'Small: Waist 26-28", Hips 36-38", Inseam 31", Thigh 21"',
                'm' => 'Medium: Waist 30-32", Hips 38-40", Inseam 32", Thigh 22"',
                'l' => 'Large: Waist 34-36", Hips 40-42", Inseam 33", Thigh 23"',
                'xl' => 'Extra Large: Waist 38-40", Hips 42-44", Inseam 34", Thigh 24"',
                '2xl' => '2X Large: Waist 42-44", Hips 44-46", Inseam 34", Thigh 25"',
                '3xl' => '3X Large: Waist 46-48", Hips 46-48", Inseam 35", Thigh 26"',
                '4xl' => '4X Large: Waist 50-52", Hips 48-50", Inseam 35", Thigh 27"',
                '5xl' => '5X Large: Waist 54-56", Hips 50-52", Inseam 36", Thigh 28"',
                '6xl' => '6X Large: Waist 58-60", Hips 52-54", Inseam 36", Thigh 29"',
            ],
            'jackets' => [
                'xs' => 'Extra Small: Chest 32-34", Shoulder 16", Sleeve 24", Length 26"',
                's' => 'Small: Chest 34-36", Shoulder 17", Sleeve 25", Length 27"',
                'm' => 'Medium: Chest 38-40", Shoulder 18", Sleeve 26", Length 28"',
                'l' => 'Large: Chest 42-44", Shoulder 19", Sleeve 27", Length 29"',
                'xl' => 'Extra Large: Chest 46-48", Shoulder 20", Sleeve 28", Length 30"',
                '2xl' => '2X Large: Chest 50-52", Shoulder 21", Sleeve 29", Length 31"',
                '3xl' => '3X Large: Chest 54-56", Shoulder 22", Sleeve 30", Length 32"',
                '4xl' => '4X Large: Chest 58-60", Shoulder 23", Sleeve 31", Length 33"',
                '5xl' => '5X Large: Chest 62-64", Shoulder 24", Sleeve 32", Length 34"',
                '6xl' => '6X Large: Chest 66-68", Shoulder 25", Sleeve 33", Length 35"',
            ]
        ];

        $colorDescriptions = [
            'Black' => 'Classic black color - professional and versatile',
            'Green' => 'CvSU green - official university color',
            'Blue' => 'Navy blue - timeless and professional',
            'Red' => 'Vibrant red - bold and energetic',
            'White' => 'Clean white - crisp and fresh look',
        ];

        $typeDescriptions = [
            'Parker' => 'Premium Parker style ballpen with smooth writing experience',
            'Naturally-Sweetened' => 'Naturally sweetened coffee blend with smooth finish',
            'Liberica-Based' => 'Liberica coffee beans with rich, full-bodied flavor',
            'Antioxidant-Enriched' => 'Antioxidant enriched blend for health benefits',
            'Original Blend' => 'Original CvSU coffee blend - our signature flavor',
            'CvSU Mug' => 'Standard CvSU ceramic mug',
            'CvSU Mug w/ Box' => 'CvSU ceramic mug with premium packaging box',
        ];

        $products = [
            [
                'name'       => 'Umbrella',
                'category'   => 'Accessories > Umbrella',
                'price'      => 220,
                'attributes' => [],
            ],
            [
                'name'       => 'Keychain',
                'category'   => 'Accessories > Keychain',
                'price'      => 100,
                'attributes' => [],
            ],
            [
                'name'       => 'Keychain for Car',
                'category'   => 'Accessories > Keychain',
                'price'      => 100,
                'attributes' => [],
            ],
            [
                'name'       => 'CVSU Mug',
                'category'   => 'Home & Kitchen > Mug',
                'price'      => 70,
                'attributes' => [
                    'Types' => [
                        'CvSU Mug'        => 70,
                        'CvSU Mug w/ Box' => 80,
                    ],
                ],
            ],
            [
                'name'       => 'Cap',
                'category'   => 'Apparel > Cap',
                'price'      => 150,
                'attributes' => [],
            ],
            [
                'name'       => 'Bonnet',
                'category'   => 'Apparel > Bonnet',
                'price'      => 150,
                'attributes' => [],
            ],
            [
                'name'       => 'T-Shirt',
                'category'   => 'Apparel > T-Shirt',
                'price'      => 220,
                'attributes' => [],
            ],
            [
                'name'       => 'Polo Shirt',
                'category'   => 'Apparel > Polo',
                'price'      => 240,
                'attributes' => [],
            ],
            [
                'name'       => 'Case of Utensils',
                'category'   => 'Home & Kitchen > Utensils',
                'price'      => 100,
                'attributes' => [],
            ],
            [
                'name'       => 'Notebook',
                'category'   => 'Stationery > Notebook',
                'price'      => 125,
                'attributes' => [],
            ],
            [
                'name'       => 'Coffee Blend',
                'category'   => 'Home & Kitchen > Coffee Blend',
                'price'      => 250,
                'attributes' => [
                    'Types' => [
                        'Naturally-Sweetened'  => 280,
                        'Liberica-Based'       => 260,
                        'Antioxidant-Enriched' => 290,
                        'Original Blend'       => 250,
                    ],
                ],
            ],
            [
                'name'       => 'CvSU Fans',
                'category'   => 'Home & Kitchen > Fans',
                'price'      => 50,
                'attributes' => [],
            ],
            [
                'name'       => 'Lace',
                'category'   => 'Accessories > Lace',
                'price'      => 70,
                'attributes' => [],
            ],
            [
                'name'       => 'Jacket',
                'category'   => 'Apparel > Jacket',
                'price'      => 660,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 660,
                        's'   => 660,
                        'm'   => 660,
                        'l'   => 660,
                        'xl'  => 680,
                        '2xl' => 700,
                        '3xl' => 720,
                        '4xl' => 740,
                    ],
                ],
            ],
            [
                'name'       => 'Ballpens',
                'category'   => 'Stationery > Ballpens',
                'price'      => 40,
                'attributes' => [
                    'Colors' => [
                        'Black' => 40,
                        'Green' => 40,
                    ],
                ],
            ],
            [
                'name'       => 'Blouse',
                'category'   => 'Apparel > Female Uniform',
                'price'      => 325,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 325,
                        's'   => 325,
                        'm'   => 325,
                        'l'   => 325,
                        'xl'  => 340,
                        '2xl' => 355,
                        '3xl' => 365,
                        '4xl' => 380,
                        '5xl' => 390,
                        '6xl' => 405,
                    ],
                ],
            ],
            [
                'name'       => 'Slacks',
                'category'   => 'Apparel > Female Uniform',
                'price'      => 375,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 375,
                        's'   => 390,
                        'm'   => 410,
                        'l'   => 430,
                        'xl'  => 450,
                        '2xl' => 470,
                        '3xl' => 490,
                        '4xl' => 510,
                        '5xl' => 530,
                        '6xl' => 550,
                    ],
                ],
            ],
            [
                'name'       => 'pe tshirt',
                'category'   => 'Apparel > PE',
                'price'      => 315,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 315,
                        's'   => 315,
                        'm'   => 315,
                        'l'   => 315,
                        'xl'  => 325,
                        '2xl' => 340,
                        '3xl' => 355,
                        '4xl' => 365,
                        '5xl' => 380,
                        '6xl' => 390,
                        '7xl' => 395,
                    ],
                ],
            ],
            [
                'name'       => 'pe short',
                'category'   => 'Apparel > PE',
                'price'      => 315,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 315,
                        's'   => 315,
                        'm'   => 315,
                        'l'   => 315,
                        'xl'  => 325,
                        '2xl' => 340,
                        '3xl' => 355,
                        '4xl' => 365,
                        '5xl' => 380,
                        '6xl' => 390,
                    ],
                ],
            ],
            // [
            //     'name'       => 'polo',
            //     'category'   => 'Apparel > Male Uniform',
            //     'price'      => 365,
            //     'attributes' => [
            //         'Sizes' => [
            //             'xs'  => 365,
            //             's'   => 365,
            //             'm'   => 365,
            //             'l'   => 365,
            //             'xl'  => 385,
            //             '2xl' => 405,
            //             '3xl' => 425,
            //             '4xl' => 445,
            //             '5xl' => 465,
            //             '6xl' => 485,
            //             '7xl' => 505,
            //         ],
            //     ],
            // ],
            [
                'name'       => 'NSTP t-shirt',
                'category'   => 'Apparel > NSTP',
                'price'      => 315,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 315,
                        's'   => 315,
                        'm'   => 315,
                        'l'   => 315,
                        'xl'  => 325,
                        '2xl' => 340,
                        '3xl' => 355,
                        '4xl' => 365,
                        '5xl' => 380,
                        '6xl' => 390,
                    ],
                ],
            ],
            [
                'name'       => 'cspear tshirt',
                'category'   => 'Apparel > Cspear',
                'price'      => 260,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 260,
                        's'   => 260,
                        'm'   => 260,
                        'l'   => 260,
                        'xl'  => 275,
                        '2xl' => 285,
                        '3xl' => 300,
                        '4xl' => 310,
                    ],
                ],
            ],
            [
                'name'       => 'cspear short',
                'category'   => 'Apparel > Cspear',
                'price'      => 310,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 310,
                        's'   => 310,
                        'm'   => 325,
                        'l'   => 325,
                        'xl'  => 335,
                        '2xl' => 350,
                        '3xl' => 360,
                        '4xl' => 375,
                    ],
                ],
            ],
            [
                'name'       => 'cspear jogging pants',
                'category'   => 'Apparel > Cspear',
                'price'      => 400,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 400,
                        's'   => 410,
                        'm'   => 425,
                        'l'   => 435,
                        'xl'  => 450,
                        '2xl' => 460,
                        '3xl' => 475,
                        '4xl' => 485,
                    ],
                ],
            ],
            [
                'name'       => 'semi-golf umbrella manual',
                'category'   => 'Accessories > Umbrella',
                'price'      => 280,
                'attributes' => [],
            ],
            [
                'name'       => 'semi-golf umbrella automatic',
                'category'   => 'Accessories > Umbrella',
                'price'      => 340,
                'attributes' => [],
            ],
            [
                'name'       => 'cvsu pin',
                'category'   => 'Accessories > Pin',
                'price'      => 100,
                'attributes' => [],
            ],
            // [
            //     'name'       => 'tumbler',
            //     'category'   => 'Home & Kitchen > Tumbler',
            //     'price'      => 220,
            //     'attributes' => [],
            // ],
        ];

        foreach ($products as $productData) {
            $category = $this->getCategoryByPath($productData['category']);
            if (!$category) {
                $this->command->error("Category not found for product: {$productData['name']}");
                continue;
            }

            $images = $imageMappings[$productData['name']] ?? null;
            if (!$images) {
                $this->command->error("Image mapping not found for product: {$productData['name']}");
                continue;
            }

            $descriptions = $productDescriptions[$productData['name']] ?? [
                'short' => "Quality {$productData['name']} from CvSU - perfect for campus life!",
                'long' => "This premium {$productData['name']} features official CvSU branding and high-quality materials. Designed to meet the needs of students and faculty, it combines functionality with school spirit for everyday campus use."
            ];

            $basePrice = isset($productData['price']) ? $productData['price'] : (rand(1000, 10000) / 100);

            $product = Product::create([
                'name'                  => $productData['name'],
                'slug'                  => Str::slug($productData['name']),
                'short_description'     => $descriptions['short'],
                'description'           => $descriptions['long'],
                'price'                 => $basePrice,
                'quantity'              => 50,
                'stock_status'          => 'instock',
                'reorder_quantity'      => 10,
                'outofstock_quantity'   => 0,
                'featured'              => true,
                'image'                 => $images['image'],
                'images'                => implode(',', $images['images']),
                'low_stock_notified'    => false,
                'archived'              => false,
                'archived_at'           => null,
                'category_id'           => $category->id,
                'product_attribute_id'  => null,
            ]);

            if (!empty($productData['attributes'])) {
                foreach ($productData['attributes'] as $attributeName => $values) {
                    $attribute = ProductAttribute::firstOrCreate(
                        ['name' => $attributeName],
                        ['name' => $attributeName]
                    );

                    if (is_null($product->product_attribute_id)) {
                        $product->product_attribute_id = $attribute->id;
                        $product->save();
                    }

                    $isAssociative = array_keys($values) !== range(0, count($values) - 1);

                    foreach ($values as $variantKey => $variantValue) {
                        $price = $isAssociative ? $variantValue : (rand(1000, 5000) / 100);

                        $description = $this->getAttributeDescription($attributeName, $variantKey, $productData['name'], $category->name);

                        ProductAttributeValue::create([
                            'product_attribute_id' => $attribute->id,
                            'product_id'           => $product->id,
                            'value'                => $variantKey,
                            'description'          => $description,
                            'price'                => $price,
                            'quantity'             => rand(10, 100),
                            'stock_status'         => 'instock',
                        ]);
                    }
                }
                $product->price = 0;
                $product->save();
            }
        }
    }

    private function getAttributeDescription($attributeName, $variantKey, $productName, $categoryName)
    {
        if ($attributeName === 'Sizes') {
            $categoryLower = strtolower($categoryName);

            if (str_contains($categoryLower, 'jacket') || $productName === 'Jacket') {
                return $this->sizeDescriptions['jackets'][$variantKey] ?? "Size {$variantKey} - standard jacket measurements";
            } elseif (str_contains($categoryLower, 'pant') || str_contains($categoryLower, 'short') || str_contains($categoryLower, 'slack') || str_contains($productName, 'pant') || str_contains($productName, 'short')) {
                return $this->sizeDescriptions['bottoms'][$variantKey] ?? "Size {$variantKey} - standard bottom measurements";
            } else {
                return $this->sizeDescriptions['tops'][$variantKey] ?? "Size {$variantKey} - standard top measurements";
            }
        }

        if ($attributeName === 'Colors') {
            return $this->colorDescriptions[$variantKey] ?? "{$variantKey} color option";
        }

        if ($attributeName === 'Types') {
            return $this->typeDescriptions[$variantKey] ?? "{$variantKey} type - premium quality";
        }

        return "{$variantKey} option for {$productName}";
    }

    private function getCategoryByPath(string $path): ?Category
    {
        $names = array_map('trim', explode('>', $path));
        $parent = null;
        foreach ($names as $name) {
            $query = Category::where('name', $name);
            if ($parent) {
                $query->where('parent_id', $parent->id);
            } else {
                $query->whereNull('parent_id');
            }
            $parent = $query->first();
            if (!$parent) {
                return null;
            }
        }
        return $parent;
    }

    private $sizeDescriptions = [
        'tops' => [
            'xs' => 'Extra Small: Bust 32-34", Shoulder 15", Sleeve 23", Length 25"',
            's' => 'Small: Bust 34-36", Shoulder 16", Sleeve 24", Length 26"',
            'm' => 'Medium: Bust 38-40", Shoulder 17", Sleeve 25", Length 27"',
            'l' => 'Large: Bust 42-44", Shoulder 18", Sleeve 26", Length 28"',
            'xl' => 'Extra Large: Bust 46-48", Shoulder 19", Sleeve 27", Length 29"',
            '2xl' => '2X Large: Bust 50-52", Shoulder 20", Sleeve 28", Length 30"',
            '3xl' => '3X Large: Bust 54-56", Shoulder 21", Sleeve 29", Length 31"',
            '4xl' => '4X Large: Bust 58-60", Shoulder 22", Sleeve 30", Length 32"',
            '5xl' => '5X Large: Bust 62-64", Shoulder 23", Sleeve 31", Length 33"',
            '6xl' => '6X Large: Bust 66-68", Shoulder 24", Sleeve 32", Length 34"',
            '7xl' => '7X Large: Bust 70-72", Shoulder 25", Sleeve 33", Length 35"',
        ],
        'bottoms' => [
            'xs' => 'Extra Small: Waist 24-26", Hips 34-36", Inseam 30", Thigh 20"',
            's' => 'Small: Waist 26-28", Hips 36-38", Inseam 31", Thigh 21"',
            'm' => 'Medium: Waist 30-32", Hips 38-40", Inseam 32", Thigh 22"',
            'l' => 'Large: Waist 34-36", Hips 40-42", Inseam 33", Thigh 23"',
            'xl' => 'Extra Large: Waist 38-40", Hips 42-44", Inseam 34", Thigh 24"',
            '2xl' => '2X Large: Waist 42-44", Hips 44-46", Inseam 34", Thigh 25"',
            '3xl' => '3X Large: Waist 46-48", Hips 46-48", Inseam 35", Thigh 26"',
            '4xl' => '4X Large: Waist 50-52", Hips 48-50", Inseam 35", Thigh 27"',
            '5xl' => '5X Large: Waist 54-56", Hips 50-52", Inseam 36", Thigh 28"',
            '6xl' => '6X Large: Waist 58-60", Hips 52-54", Inseam 36", Thigh 29"',
        ],
        'jackets' => [
            'xs' => 'Extra Small: Chest 32-34", Shoulder 16", Sleeve 24", Length 26"',
            's' => 'Small: Chest 34-36", Shoulder 17", Sleeve 25", Length 27"',
            'm' => 'Medium: Chest 38-40", Shoulder 18", Sleeve 26", Length 28"',
            'l' => 'Large: Chest 42-44", Shoulder 19", Sleeve 27", Length 29"',
            'xl' => 'Extra Large: Chest 46-48", Shoulder 20", Sleeve 28", Length 30"',
            '2xl' => '2X Large: Chest 50-52", Shoulder 21", Sleeve 29", Length 31"',
            '3xl' => '3X Large: Chest 54-56", Shoulder 22", Sleeve 30", Length 32"',
            '4xl' => '4X Large: Chest 58-60", Shoulder 23", Sleeve 31", Length 33"',
            '5xl' => '5X Large: Chest 62-64", Shoulder 24", Sleeve 32", Length 34"',
            '6xl' => '6X Large: Chest 66-68", Shoulder 25", Sleeve 33", Length 35"',
        ]
    ];

    private $colorDescriptions = [
        'Black' => 'Classic black color - professional and versatile',
        'Green' => 'CvSU green - official university color',
        'Blue' => 'Navy blue - timeless and professional',
        'Red' => 'Vibrant red - bold and energetic',
        'White' => 'Clean white - crisp and fresh look',
    ];

    private $typeDescriptions = [
        'Parker' => 'Premium Parker style ballpen with smooth writing experience',
        'Naturally-Sweetened' => 'Naturally sweetened coffee blend with smooth finish',
        'Liberica-Based' => 'Liberica coffee beans with rich, full-bodied flavor',
        'Antioxidant-Enriched' => 'Antioxidant enriched blend for health benefits',
        'Original Blend' => 'Original CvSU coffee blend - our signature flavor',
        'CvSU Mug' => 'Standard CvSU ceramic mug',
        'CvSU Mug w/ Box' => 'CvSU ceramic mug with premium packaging box',
    ];
}
