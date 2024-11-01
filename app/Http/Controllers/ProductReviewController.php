<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Notification;
use Srmklive\PayPal\Services\PayPal;

use App\Notifications\StatusNotification;
use App\User;

use App\Models\ProductReview;

class ProductReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reviews = ProductReview::getAllReview();
        
        return view('backend.review.index')->with('reviews', $reviews);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // يمكنك إضافة المنطق هنا لإنشاء مراجعة جديدة إذا لزم الأمر
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'rate' => 'required|numeric|min:1'
        ]);

        $product_info = Product::getProductBySlug($request->slug);
        $data = $request->all();
        $data['product_id'] = $product_info->id;
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'active';

        $status = ProductReview::create($data);

        $admins = User::where('role', 'admin')->get();
        $details = [
            'title' => 'New Product Rating!',
            'actionURL' => route('product-detail', $product_info->slug),
            'fas' => 'fa-star'
        ];
        Notification::send($admins, new StatusNotification($details));

        if ($status) {
            return redirect()->back()->with('success', 'Thank you for your feedback');
        } else {
            return redirect()->back()->with('error', 'Something went wrong! Please try again!!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // يمكنك إضافة المنطق هنا لعرض تفاصيل مراجعة معينة إذا لزم الأمر
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $review = ProductReview::find($id);
        return view('backend.review.edit')->with('review', $review);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $review = ProductReview::find($id);
        
        if ($review) {
            $data = $request->all();
            $status = $review->fill($data)->save();

            if ($status) {
                return redirect()->route('review.index')->with('success', 'Review Successfully updated');
            } else {
                return redirect()->route('review.index')->with('error', 'Something went wrong! Please try again!!');
            }
        } else {
            return redirect()->route('review.index')->with('error', 'Review not found!!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $review = ProductReview::find($id);
        
        if ($review) {
            $status = $review->delete();
            if ($status) {
                return redirect()->route('review.index')->with('success', 'Successfully deleted review');
            } else {
                return redirect()->route('review.index')->with('error', 'Something went wrong! Try again');
            }
        } else {
            return redirect()->route('review.index')->with('error', 'Review not found!!');
        }
    }
}
