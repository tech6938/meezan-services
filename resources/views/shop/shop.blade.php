@extends('layout.dashboard-layout')

@section('css')
<style>
    .shop-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }

    .shop-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .shop-image {
        height: 200px;
        width: 100%;
        object-fit: cover;
    }

    .shop-location {
        font-size: 14px;
        color: #6c757d;
    }

    .shopkeeper-badge {
        background: linear-gradient(135deg, #28a745, #218838);
        color: #fff;
        font-size: 13px;
        padding: 6px 12px;
        border-radius: 20px;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Shops</h1>
        </div>

        <div class="section-body">
            <div class="row">

                @forelse($data as $item)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card shop-card">

                            <img src="{{ $item->shop_image }}"
                                 class="shop-image"
                                 alt="Shop Image">

                            <div class="card-body">
                                <h5 class="mb-1">{{ $item->shop_name }}</h5>

                                <p class="shop-location mb-3">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $item->lang }} -- {{ $item->lat }}
                                </p>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="shopkeeper-badge">
                                        {{ $item->shopkeeper->name }}
                                    </span>

                                    <!-- <span class="text-muted small">
                                        ID: {{ $item->shopkeeper_id }}
                                    </span> -->
                                </div>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-light text-center">
                            <i class="fas fa-store-alt"></i><br>
                            No Shops Found
                        </div>
                    </div>
                @endforelse

            </div>
        </div>
    </section>
</div>
@endsection
