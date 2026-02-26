@extends('admin.layout')

@section('admin-title')
    Products
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Products' => 'admin/data/products', 'Invoices' => 'admin/data/products/invoices']) !!}

    <h1>
        Product Invoices
    </h1>

    <p>
        Invoices from products purchased on the shop.
    </p>

    <div class="text-right">
        <a href="{{ url('admin/data/products') }}" class="btn btn-primary text-right mb-3"><i class="fas fa-arrow-left fa-fw"></i> Back to Products</a>
    </div>

    @if (!count($invoices))
        <p>No invoices found.</p>
    @else
        <table class="table table-sm shop-table">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Products</th>
                    <th>Discount</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr>
                        <td>
                            Invoice #{{ $invoice->id }}
                        </td>
                        <td>
                            {!! $invoice->user->displayName !!}
                        </td>
                        <td>
                            {{ $invoice->status }}
                        </td>
                        <td>
                            <i class="{!! $invoice->paymentMethodIcon !!}"></i>
                            {{ $invoice->currency . ' ' . $invoice->total }}
                        </td>
                        <td>
                            {!! $invoice->displayProducts !!}
                        </td>
                        <td>
                            @if (isset($invoice->data['discount']) && $invoice->data['discount'] != [])
                                {{ $invoice->currency . ' ' . $invoice->data['discount']['discount'] }}
                                ({!! $invoice->displayDiscountedProducts !!})
                            @else
                                None
                            @endif
                        </td>
                        <td>
                            {!! format_date($invoice->created_at) !!}
                        </td>
                        <td>
                            <a href="{{ url('admin/data/products/invoices/' . $invoice->id) }}" class="btn btn-primary invoice" data-id="{{ $invoice->id }}">
                                <i class="fas fa-file-invoice fa-fw"></i> View Invoice
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.invoice').on('click', function(e) {
                e.preventDefault();
                console.log($(this).data('id'));
                loadModal("{{ url('shops/invoice') }}/" + $(this).data('id'), 'Viewing Invoice');
            });
        });
    </script>
@endsection
