@php
    use App\Models\BusinessSetting as BS;

    $width     = BS::get('receipt_paper_width', '80') === '58' ? 58 : 80;
    $currency  = BS::get('business_currency', 'KES');
    $branch    = \App\Models\Branch::where('name', $sale->branch_name)->first();
    $vatNumber = BS::get('vat_number');
    $vatAmount = round((float) $sale->total_amount * 16 / 116, 2);
    $returned  = (float) $sale->returns()->sum('refund_total');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $sale->receipt_number }}</title>
    <style>
        @page { size: {{ $width }}mm auto; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #fff; color: #000; }
        body {
            width: {{ $width }}mm;
            padding: 3mm {{ $width === 58 ? '2mm' : '4mm' }};
            font-family: 'Courier New', Courier, monospace;
            font-size: {{ $width === 58 ? '10px' : '12px' }};
            line-height: 1.35;
        }
        .c { text-align: center; }
        .r { text-align: right; }
        .b { font-weight: bold; }
        .big { font-size: 1.35em; }
        .rule { border-top: 1px dashed #000; margin: 4px 0; }
        .copy { border: 2px solid #000; text-align: center; font-weight: bold; padding: 2px; margin: 4px 0; letter-spacing: 2px; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 1px 0; }
        .item-name { word-break: break-word; }
        .no-print { margin-top: 8px; text-align: center; }
        .no-print button { font: inherit; padding: 6px 10px; margin: 2px; cursor: pointer; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="c">
        <div class="b big">{{ strtoupper(BS::get('business_name', 'TSHAPRINT SOLUTIONS')) }}</div>
        <div class="b">{{ $sale->branch_name }}</div>
        @if($branch?->address)<div>{{ $branch->address }}</div>@endif
        <div>Tel: {{ $branch?->phone ?: BS::get('business_phone', '') }}</div>
        @if($kra = BS::get('kra_pin'))<div>KRA PIN: {{ $kra }}</div>@endif
        @if($till = BS::get('mpesa_till_number'))<div>M-PESA TILL: {{ $till }}</div>@endif
        @if($paybill = BS::get('mpesa_paybill_number'))<div>PAYBILL: {{ $paybill }}</div>@endif
        @if($header = BS::get('receipt_header_note'))<div>{{ $header }}</div>@endif
    </div>

    @if($isCopy)
        <div class="copy">*** COPY / REPRINT ***</div>
    @endif

    <div class="rule"></div>
    <table>
        <tr><td>Receipt:</td><td class="r b">{{ $sale->receipt_number }}</td></tr>
        <tr><td>Date:</td><td class="r">{{ $sale->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Served by:</td><td class="r">{{ $sale->user->name ?? 'Staff' }}</td></tr>
        @if($sale->customer)
        <tr><td>Customer:</td><td class="r">{{ $sale->customer->name }}</td></tr>
        @endif
    </table>
    <div class="rule"></div>

    <table>
        @foreach($sale->saleItems as $item)
            <tr>
                <td colspan="2" class="item-name">{{ $item->productVariant->formatted_title ?? 'Item' }}</td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }} x {{ number_format($item->unit_price, 2) }}</td>
                <td class="r">{{ number_format($item->line_total, 2) }}</td>
            </tr>
        @endforeach
    </table>
    <div class="rule"></div>

    <table>
        <tr class="b big"><td>TOTAL</td><td class="r">{{ $currency }} {{ number_format($sale->total_amount, 2) }}</td></tr>
        @if($vatNumber)
        <tr><td>Incl. VAT 16%</td><td class="r">{{ number_format($vatAmount, 2) }}</td></tr>
        @endif
        <tr><td>Paid by:</td><td class="r">{{ $sale->payment_method }}</td></tr>
        @if($sale->payment_reference)
        <tr><td>Ref:</td><td class="r">{{ $sale->payment_reference }}</td></tr>
        @endif
        @if($sale->payment_status === 'credit')
        <tr class="b"><td colspan="2">ON ACCOUNT - PAYMENT DUE</td></tr>
        @endif
        @if($returned > 0)
        <tr><td>Returned:</td><td class="r">-{{ number_format($returned, 2) }}</td></tr>
        @endif
    </table>
    <div class="rule"></div>

    <div class="c">
        <div class="b">Thank you for shopping with us!</div>
        <div>{{ BS::get('receipt_footer_note', 'Goods once sold are returnable within 7 days with valid receipt.') }}</div>
        <div>Returns accepted at {{ $sale->branch_name }} only.</div>
    </div>
    <br>

    <div class="no-print">
        <button type="button" onclick="window.print()">Print</button>
        <button type="button" onclick="window.close()">Close</button>
    </div>

    @if($autoPrint)
    <script>
        // Print as soon as the receipt is ready. With Chrome started with --kiosk-printing,
        // this goes straight to the default (receipt) printer with no dialog.
        window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 150); });
    </script>
    @endif
</body>
</html>
