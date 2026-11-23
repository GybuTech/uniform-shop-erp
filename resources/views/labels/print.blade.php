<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barcode Labels ({{ count($labels) }})</title>
    @php
        $isSheet = $sizeKey === 'a4';
        $w = $size['w'];
        $h = $size['h'];
        $pad = $h >= 35 ? 2.5 : 1.5;
    @endphp
    <style>
        @page {
            @if($isSheet)
            size: A4 portrait;
            margin: 0;
            @else
            size: {{ $w }}mm {{ $h }}mm;
            margin: 0;
            @endif
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; }

        .label {
            width: {{ $w }}mm;
            height: {{ $h }}mm;
            padding: {{ $pad }}mm {{ $pad + 0.5 }}mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        @if(!$isSheet)
        .label { page-break-after: always; break-after: page; }
        .label:last-child { page-break-after: auto; break-after: auto; }
        @else
        .sheet {
            width: 210mm;
            display: grid;
            grid-template-columns: repeat(3, 70mm);
            grid-auto-rows: 37mm;
            padding-top: 0.5mm;
        }
        .sheet .label { page-break-inside: avoid; break-inside: avoid; }
        .sheet .label:nth-child(24n) { page-break-after: always; break-after: page; }
        @endif

        .biz { font-size: {{ $h >= 35 ? 7 : 5.5 }}pt; text-transform: uppercase; letter-spacing: .3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.1; }
        .name { font-size: {{ $h >= 35 ? 10 : 7.5 }}pt; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.15; }
        .row { display: flex; justify-content: space-between; align-items: baseline; gap: 1mm; line-height: 1.1; }
        .details { font-size: {{ $h >= 35 ? 9 : 7 }}pt; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .price { font-size: {{ $h >= 35 ? 11 : 8 }}pt; font-weight: bold; white-space: nowrap; }
        .bars { flex: 1 1 auto; min-height: 7mm; margin-top: 0.6mm; }
        .bars svg { display: block; width: 100%; height: 100%; }
        .code { font-family: "Courier New", monospace; font-size: {{ $h >= 35 ? 8 : 6.5 }}pt; text-align: center; letter-spacing: .5px; line-height: 1.1; }

        .toolbar { font-family: Arial, sans-serif; font-size: 13px; padding: 10px 14px; background: #f1f5f9; border-bottom: 1px solid #cbd5e1; }
        .toolbar button { font-size: 13px; font-weight: bold; padding: 6px 14px; margin-right: 8px; cursor: pointer; }
        .toolbar .warn { color: #b45309; margin-top: 6px; }
        @media screen {
            body { background: #e2e8f0; }
            .stack { padding: 16px; display: flex; flex-wrap: wrap; gap: 8px; }
            .stack .label, .sheet .label { background: #fff; outline: 1px dashed #94a3b8; }
            .sheet { background: #fff; margin: 16px; }
        }
        @media print { .toolbar { display: none; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print {{ count($labels) }} label(s)</button>
        <button type="button" onclick="window.close()">Close</button>
        <span>{{ $size['label'] }}. In the print box choose your <strong>label printer</strong>, margins <strong>None</strong>, scale <strong>100%</strong>.</span>
        @if(!$isSheet && $w < 50)
            <div class="warn">Small labels: if a long barcode won't scan, use 50 x 25 mm labels instead.</div>
        @endif
    </div>

    <div class="{{ $isSheet ? 'sheet' : 'stack' }}">
        @for($i = 0; $i < $skip; $i++)
            <div class="label"></div>
        @endfor
        @foreach($labels as $label)
            <div class="label">
                @if($showBusiness)
                    <div class="biz">{{ $businessName }}</div>
                @endif
                <div class="name">{{ $label['name'] }}</div>
                <div class="row">
                    <span class="details">{{ $label['details'] }}</span>
                    @if($showPrice)
                        <span class="price">KES {{ number_format($label['price'], fmod($label['price'], 1) == 0 ? 0 : 2) }}</span>
                    @endif
                </div>
                <div class="bars">{!! $label['svg'] !!}</div>
                <div class="code">{{ $label['code'] }}</div>
            </div>
        @endforeach
    </div>

    @unless($preview)
    <script>
        window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });
    </script>
    @endunless
</body>
</html>
