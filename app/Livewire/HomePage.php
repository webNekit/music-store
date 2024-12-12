<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Главная')]

class HomePage extends Component
{

    use LivewireAlert;
    public function addToCart($product_id)
    {
        $total_count = CartManagement::addItemToCart($product_id);

        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);

        $this->alert('success', 'Товар добавлен в корзину', [
            'position' => 'bottom-end',
            'timer' => 3000,
            'toast' => true
        ]);
    }

    public function render()
    {
        $brands = Brand::where('is_active', 1)->get();
        $categories = Category::where('is_active', 1)->get();
        $products = Product::where('is_active', 1)->where("is_featured", 1)->orderBy("updated_at", "desc")->get();

        return view('livewire.home-page', [
            'brands' => $brands,
            'categories' => $categories,
            'products' => $products
        ]);
    }
}
