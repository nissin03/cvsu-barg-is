<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Slide;

class SlidesSeeder extends Seeder
{
    public function run(): void
    {
        $sourcePath = public_path('uploads/slides');
        if (!File::exists($sourcePath)) {
            $this->command->error('Source folder does not exist: ' . $sourcePath);
            return;
        }

        $slides = [
            [
                'tagline' => 'Welcome to BaRG',
                'title' => 'Shop Now',
                'subtitle' => 'Discover the best products today',
                'link' => route('shop.index'),
                'image' => '1746751051.jpg',  
                'status' => true,
            ],
            [
                'tagline' => 'Learn About Us',
                'title' => 'Our Story',
                'subtitle' => 'Why BaRG is trusted by many',
                'link' => route('about.index'),
                'image' => '1746751045.jpg',  
                'status' => true,
            ],
            [
                'tagline' => 'Contact BaRG',
                'title' => 'Need Help?',
                'subtitle' => 'We’re here to answer your questions',
                'link' => route('home.contact'),
                'image' => '1746751038.jpg', 
                'status' => true,
            ],
        ];

        foreach ($slides as $slide) {
            Slide::create($slide);
        }
    }
}
