<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use App\Models\Category;
use App\Models\PostTag;
use App\Models\PostCategory;
use App\Models\Post;
use App\Models\Cart;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route($request->user()->role);
    }

    public function home()
    {
        $featured = Product::where('status', 'active')->where('is_featured', 1)->orderBy('price', 'DESC')->limit(2)->get();
        $posts = Post::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        $banners = Banner::where('status', 'active')->limit(3)->orderBy('id', 'DESC')->get();
        $products = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(8)->get();
        $categories = Category::where('status', 'active')->where('is_parent', 1)->orderBy('title', 'ASC')->get();

        return view('frontend.index', compact('featured', 'posts', 'banners', 'products', 'categories'));
    }

    public function aboutUs()
    {
        return view('frontend.pages.about-us');
    }

    public function contact()
    {
        return view('frontend.pages.contact');
    }

    public function productDetail($slug)
    {
        $productDetail = Product::getProductBySlug($slug);
        return view('frontend.pages.product_detail', compact('productDetail'));
    }

    public function productGrids(Request $request)
    {
        $products = Product::where('status', 'active');

        if ($request->filled('category')) {
            $catIds = Category::whereIn('slug', explode(',', $request->category))->pluck('id');
            $products->whereIn('cat_id', $catIds);
        }

        if ($request->filled('brand')) {
            $brandIds = Brand::whereIn('slug', explode(',', $request->brand))->pluck('id');
            $products->whereIn('brand_id', $brandIds);
        }

        if ($request->filled('sortBy')) {
            $products->orderBy($request->sortBy === 'title' ? 'title' : 'price', 'ASC');
        }

        if ($request->filled('price')) {
            $priceRange = explode('-', $request->price);
            $products->whereBetween('price', $priceRange);
        }

        $recentProducts = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        $products = $products->paginate($request->get('show', 9));

        return view('frontend.pages.product-grids', compact('products', 'recentProducts'));
    }

    public function productLists(Request $request)
    {
        $products = Product::where('status', 'active');

        if ($request->filled('category')) {
            $catIds = Category::whereIn('slug', explode(',', $request->category))->pluck('id');
            $products->whereIn('cat_id', $catIds);
        }

        if ($request->filled('brand')) {
            $brandIds = Brand::whereIn('slug', explode(',', $request->brand))->pluck('id');
            $products->whereIn('brand_id', $brandIds);
        }

        if ($request->filled('sortBy')) {
            $products->orderBy($request->sortBy === 'title' ? 'title' : 'price', 'ASC');
        }

        if ($request->filled('price')) {
            $priceRange = explode('-', $request->price);
            $products->whereBetween('price', $priceRange);
        }

        $recentProducts = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        $products = $products->paginate($request->get('show', 6));

        return view('frontend.pages.product-lists', compact('products', 'recentProducts'));
    }

    public function productFilter(Request $request)
    {
        $data = $request->all();
        $urlParameters = [];

        if ($request->filled('show')) {
            $urlParameters[] = 'show=' . $data['show'];
        }

        if ($request->filled('sortBy')) {
            $urlParameters[] = 'sortBy=' . $data['sortBy'];
        }

        if ($request->filled('category')) {
            $urlParameters[] = 'category=' . implode(',', $data['category']);
        }

        if ($request->filled('brand')) {
            $urlParameters[] = 'brand=' . implode(',', $data['brand']);
        }

        if ($request->filled('price_range')) {
            $urlParameters[] = 'price=' . $data['price_range'];
        }

        $route = request()->is('e-shop.loc/product-grids') ? 'product-grids' : 'product-lists';

        return redirect()->route($route, implode('&', $urlParameters));
    }

    public function productSearch(Request $request)
    {
        $recentProducts = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        $products = Product::where('status', 'active')->where(function ($query) use ($request) {
            $query->orWhere('title', 'like', '%' . $request->search . '%')
                  ->orWhere('slug', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('summary', 'like', '%' . $request->search . '%')
                  ->orWhere('price', 'like', '%' . $request->search . '%');
        })->orderBy('id', 'DESC')->paginate(9);

        return view('frontend.pages.product-grids', compact('products', 'recentProducts'));
    }

    public function productBrand(Request $request)
    {
        $products = Brand::getProductByBrand($request->slug);
        $recentProducts = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();

        return view(request()->is('e-shop.loc/product-grids') ? 'frontend.pages.product-grids' : 'frontend.pages.product-lists', compact('products', 'recentProducts'));
    }

    public function productCat(Request $request)
    {
        $products = Category::getProductByCat($request->slug);
        $recentProducts = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();

        return view(request()->is('e-shop.loc/product-grids') ? 'frontend.pages.product-grids' : 'frontend.pages.product-lists', compact('products', 'recentProducts'));
    }

    public function productSubCat(Request $request)
    {
        $products = Category::getProductBySubCat($request->sub_slug);
        $recentProducts = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();

        return view(request()->is('e-shop.loc/product-grids') ? 'frontend.pages.product-grids' : 'frontend.pages.product-lists', compact('products', 'recentProducts'));
    }

    public function blog(Request $request)
    {
        $posts = Post::where('status', 'active');

        if ($request->filled('category')) {
            $catIds = PostCategory::whereIn('slug', explode(',', $request->category))->pluck('id');
            $posts->whereIn('post_cat_id', $catIds);
        }

        if ($request->filled('tag')) {
            $tagIds = PostTag::whereIn('slug', explode(',', $request->tag))->pluck('id');
            $posts->whereIn('post_tag_id', $tagIds);
        }

        $posts = $posts->orderBy('id', 'DESC')->paginate($request->get('show', 9));
        $recentPosts = Post::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();

        return view('frontend.pages.blog', compact('posts', 'recentPosts'));
    }

    public function blogDetail($slug)
    {
        $post = Post::getPostBySlug($slug);
        $recentPosts = Post::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();

        return view('frontend.pages.blog-detail', compact('post', 'recentPosts'));
    }

    public function blogSearch(Request $request)
    {
        $recentPosts = Post::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        $posts = Post::where('status', 'active')->where(function ($query) use ($request) {
            $query->orWhere('title', 'like', '%' . $request->search . '%')
                  ->orWhere('quote', 'like', '%' . $request->search . '%')
                  ->orWhere('summary', 'like', '%' . $request->search . '%')
                  ->orWhere('slug', 'like', '%' . $request->search . '%');
        })->orderBy('id', 'DESC')->paginate(9);

        return view('frontend.pages.blog', compact('posts', 'recentPosts'));
    }

    public function addToCart(Request $request)
    {
        $cart = Cart::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => 0,
            ]
        );

        $cart->increment('quantity');
        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function cart()
    {
        $cartItems = Cart::where('user_id', Auth::id())->get();
        return view('frontend.pages.cart', compact('cartItems'));
    }

    public function removeCart($id)
    {
        Cart::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Product removed from cart successfully!');
    }
}
