@extends('backend.layouts.master')

@section('main-content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
            @include('backend.layouts.notification')
        </div>
    </div>
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary float-left">Product Lists</h6>
        <a href="{{route('product.create')}}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Add Product"><i class="fas fa-plus"></i> Add Product</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            @if(count($products)>0)
            <table class="table table-bordered" id="product-dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>S.N.</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Is Featured</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Size</th>
                        <th>Condition</th>
                        <th>Brand</th>
                        <th>Stock</th>
                        <th>Photo</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    @php
                        $sub_cat_info = DB::table('categories')->select('title')->where('id', $product->child_cat_id)->first();
                        $brands = DB::table('brands')->select('title')->where('id', $product->brand_id)->first();
                    @endphp
                    <tr>
                        <td>{{$product->id}}</td>
                        <td>{{$product->title}}</td>
                        <td>
                            {{ optional($product->cat_info)['title'] ?? 'Default Category' }}
                            <sub>{{ optional($sub_cat_info)['title'] ?? '' }}</sub>
                        </td>
                        <td>{{ ($product->is_featured == 1) ? 'Yes' : 'No' }}</td>
                        <td>Rs. {{$product->price}} /-</td>
                        <td>{{$product->discount}}% OFF</td>
                        <td>{{$product->size}}</td>
                        <td>{{$product->condition}}</td>
                        <td>{{ ucfirst(optional($brands)['title'] ?? '') }}</td>
                        <td>
                            @if($product->stock > 0)
                            <span class="badge badge-primary">{{$product->stock}}</span>
                            @else
                            <span class="badge badge-danger">{{$product->stock}}</span>
                            @endif
                        </td>
                        <td>
                            @if($product->photo)
                            @php $photo = explode(',', $product->photo); @endphp
                            <img src="{{$photo[0]}}" class="img-fluid zoom" style="max-width:80px" alt="{{$product->title}}">
                            @else
                            <img src="{{asset('backend/img/thumbnail-default.jpg')}}" class="img-fluid" style="max-width:80px" alt="Default Image">
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $product->status == 'active' ? 'badge-success' : 'badge-warning' }}">{{ $product->status }}</span>
                        </td>
                        <td>
                            <a href="{{route('product.edit', $product->id)}}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px; border-radius:50%" data-toggle="tooltip" title="edit"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-danger btn-sm dltBtn" data-id="{{$product->id}}" style="height:30px; width:30px; border-radius:50%" data-toggle="tooltip" title="Delete"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <span style="float:right">{{$products->links()}}</span>
            @else
            <h6 class="text-center">No Products found! Please create a Product</h6>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<style>
    .zoom { transition: transform .2s; }
    .zoom:hover { transform: scale(1.5); }
</style>
@endpush

@push('scripts')
<script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script>
    $(document).ready(function() {
        $('#product-dataTable').DataTable({
            "columnDefs": [{
                "orderable": false,
                "targets": [10, 11, 12]
            }]
        });

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $('.dltBtn').click(function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            let dataID = $(this).data('id');
            
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this data!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        type: 'POST',
                        url: '{{ url("product/destroy") }}/' + dataID,
                        data: { _method: 'DELETE' },
                        success: function(response) {
                            swal("Deleted!", "Your data has been deleted!", "success")
                                .then((value) => {
                                    location.reload();
                                });
                        },
                        error: function(error) {
                            swal("Error!", "There was an error deleting your data.", "error");
                        }
                    });
                } else {
                    swal("Your data is safe!");
                }
            });
        });
    });
</script>
@endpush
