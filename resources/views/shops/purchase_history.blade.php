@extends('shops.layout')

@section('shops-title')
    My Purchase History
@endsection

@section('shops-content')
    {!! breadcrumbs(['Shops' => 'shops', 'My Purchase History' => 'history']) !!}

    <h1>
        My Purchase History
    </h1>

    {!! $logs->render() !!}

    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-12 col-md-2">
                    <div class="logs-table-cell">Item</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Quantity</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Shop</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Character</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Cost</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Date</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($logs as $log)
                <div class="logs-table-row">
                    @include('shops._purchase_history_row', ['log' => $log])
                </div>
            @endforeach
        </div>
    </div>
    {!! $logs->render() !!}

    @if (count($invoices))
        <h1 class="mt-3">
            My Product Purchase History
        </h1>
        <div class="alert alert-info">
            <i class="fas fa-info-circle fa-fw"></i> This is a list of all the products you have purchased. Only completed purchases are shown.
        </div>

        {!! $invoices->render() !!}

        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-12 col-md-2">
                        <div class="logs-table-cell">Status</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Payment Method</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Total</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">Products</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Date</div>
                    </div>
                    <div class="col-6 col-md-1">
                        <div class="logs-table-cell">View</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($invoices as $invoice)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-12 col-md-2">
                                <div class="logs-table-cell">{{ ucfirst($invoice->status) }}</div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="logs-table-cell">
                                    <i class="{!! $invoice->paymentMethodIcon !!}"></i>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="logs-table-cell">{{ $invoice->currency . ' ' . $invoice->total }}</div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="logs-table-cell">{!! $invoice->displayProducts !!}</div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="logs-table-cell">{!! pretty_date($invoice->created_at) !!}</div>
                            </div>
                            <div class="col-6 col-md-1">
                                <div class="logs-table-cell">
                                    <a href="#" class="btn btn-primary invoice" data-id="{{ $invoice->id }}">
                                        <i class="fas fa-file-invoice fa-fw"></i> View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {!! $invoices->render() !!}
    @endif

@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('.invoice').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('shops/invoice') }}/" + $(this).data('id'), 'Viewing Invoice');
            });
        });
    </script>
@endsection
