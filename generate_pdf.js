import fs from 'fs';
import path from 'path';

class SimplePDF {
    constructor() {
        this.objects = [];
        this.pages = [];
        this.currentPageStream = [];
        this.fontSize = 12;
        this.fontName = 'F1';
        this.cursorY = 740; // Starts near top of 792 pt page
        this.marginLeft = 54;
        this.pageWidth = 612;
        this.pageHeight = 792;
        this.usableWidth = 504;
    }

    addObject(content) {
        this.objects.push(content);
        return this.objects.length; // 1-based object id
    }

    newPage() {
        if (this.currentPageStream.length > 0) {
            this.pages.push(this.currentPageStream.join('\n'));
        }
        this.currentPageStream = [];
        this.cursorY = 740;

        // Add page header / accent bar
        this.currentPageStream.push('0.06 0.09 0.16 rg'); // Slate dark
        this.currentPageStream.push('54 755 504 3 f');   // Header top accent line
        this.currentPageStream.push('0.4 0.4 0.4 rg');
        this.currentPageStream.push('BT /F1 8 Tf 54 762 Td (Uniform Shop ERP - Technical Architecture & Developer Guide) Tj ET');
        this.currentPageStream.push('BT /F1 8 Tf 480 762 Td (Confidential) Tj ET');
    }

    addText(text, options = {}) {
        const font = options.font || 'F1'; // F1 = Helvetica, F2 = Helvetica-Bold, F3 = Courier
        const size = options.size || 11;
        const color = options.color || [0.1, 0.1, 0.1]; // [r, g, b] 0 to 1
        const leading = options.leading || (size * 1.35);

        if (this.cursorY - leading < 54) {
            this.newPage();
        }

        // Color setting
        const [r, g, b] = color;
        this.currentPageStream.push(`${r.toFixed(3)} ${g.toFixed(3)} ${b.toFixed(3)} rg`);

        // Text positioning and rendering
        const escapePDF = (str) => str.replace(/\\/g, '\\\\').replace(/\(/g, '\\(').replace(/\)/g, '\\)');
        this.currentPageStream.push(`BT /${font} ${size} Tf ${this.marginLeft} ${this.cursorY.toFixed(2)} Td (${escapePDF(text)}) Tj ET`);

        this.cursorY -= leading;
    }

    addHeading(text, level = 1) {
        if (level === 1) {
            if (this.cursorY < 120) this.newPage();
            this.cursorY -= 10;
            // Draw section background pill / accent line
            this.currentPageStream.push('0.31 0.27 0.90 rg'); // Indigo color
            this.currentPageStream.push(`54 ${(this.cursorY - 2).toFixed(2)} 4 ${(22).toFixed(2)} f`);
            this.currentPageStream.push('0.06 0.09 0.16 rg');
            this.addText(`  ${text}`, { font: 'F2', size: 16, color: [0.06, 0.09, 0.16], leading: 26 });
            this.cursorY -= 4;
        } else if (level === 2) {
            if (this.cursorY < 90) this.newPage();
            this.cursorY -= 6;
            this.addText(text, { font: 'F2', size: 13, color: [0.2, 0.2, 0.4], leading: 20 });
        } else if (level === 3) {
            if (this.cursorY < 70) this.newPage();
            this.addText(text, { font: 'F2', size: 11, color: [0.25, 0.25, 0.25], leading: 16 });
        }
    }

    addCodeBlock(lines) {
        if (this.cursorY - (lines.length * 14) < 60) {
            this.newPage();
        }

        const blockHeight = lines.length * 14 + 10;
        const startY = this.cursorY - blockHeight + 10;

        // Background rectangle
        this.currentPageStream.push('0.95 0.96 0.98 rg'); // Light gray bg
        this.currentPageStream.push(`54 ${startY.toFixed(2)} 504 ${blockHeight.toFixed(2)} f`);
        // Left border accent
        this.currentPageStream.push('0.31 0.27 0.90 rg');
        this.currentPageStream.push(`54 ${startY.toFixed(2)} 3 ${blockHeight.toFixed(2)} f`);

        this.cursorY -= 6;

        for (const line of lines) {
            this.currentPageStream.push('0.15 0.15 0.2 rg');
            const escapePDF = (str) => str.replace(/\\/g, '\\\\').replace(/\(/g, '\\(').replace(/\)/g, '\\)');
            this.currentPageStream.push(`BT /F3 9.5 Tf ${this.marginLeft + 10} ${this.cursorY.toFixed(2)} Td (${escapePDF(line)}) Tj ET`);
            this.cursorY -= 14;
        }

        this.cursorY -= 10;
    }

    addBullet(text, boldPrefix = '') {
        if (this.cursorY < 60) this.newPage();
        this.currentPageStream.push('0.31 0.27 0.90 rg');
        this.currentPageStream.push(`BT /F2 12 Tf ${this.marginLeft + 5} ${this.cursorY.toFixed(2)} Td (\u2022) Tj ET`);

        this.currentPageStream.push('0.1 0.1 0.1 rg');
        const escapePDF = (str) => str.replace(/\\/g, '\\\\').replace(/\(/g, '\\(').replace(/\)/g, '\\)');
        
        let streamText = `BT `;
        if (boldPrefix) {
            streamText += `/F2 10.5 Tf ${this.marginLeft + 20} ${this.cursorY.toFixed(2)} Td (${escapePDF(boldPrefix)}) Tj `;
            streamText += `/F1 10.5 Tf (${escapePDF(text)}) Tj ET`;
        } else {
            streamText += `/F1 10.5 Tf ${this.marginLeft + 20} ${this.cursorY.toFixed(2)} Td (${escapePDF(text)}) Tj ET`;
        }
        this.currentPageStream.push(streamText);

        this.cursorY -= 16;
    }

    addDivider() {
        if (this.cursorY < 60) this.newPage();
        this.cursorY -= 5;
        this.currentPageStream.push('0.85 0.87 0.90 rg');
        this.currentPageStream.push(`54 ${this.cursorY.toFixed(2)} 504 1 f`);
        this.cursorY -= 15;
    }

    buildPDF() {
        if (this.currentPageStream.length > 0) {
            this.pages.push(this.currentPageStream.join('\n'));
        }

        let pdfOutput = '%PDF-1.4\n';
        const objectOffsets = [];

        // Obj 1: Catalog
        objectOffsets.push(pdfOutput.length);
        pdfOutput += `1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n`;

        // We will create Font objs: 3 (Helvetica), 4 (Helvetica-Bold), 5 (Courier)
        const font1Id = 3;
        const font2Id = 4;
        const font3Id = 5;

        // Page objects will start at ID 6
        const totalPages = this.pages.length;
        const pageObjIds = [];
        for (let i = 0; i < totalPages; i++) {
            pageObjIds.push(6 + (i * 2)); // Page obj, Stream obj
        }

        // Obj 2: Pages Tree
        objectOffsets.push(pdfOutput.length);
        const pageKidsStr = pageObjIds.map(id => `${id} 0 R`).join(' ');
        pdfOutput += `2 0 obj\n<< /Type /Pages /Kids [${pageKidsStr}] /Count ${totalPages} >>\nendobj\n`;

        // Obj 3: Font 1
        objectOffsets.push(pdfOutput.length);
        pdfOutput += `3 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n`;

        // Obj 4: Font 2 (Bold)
        objectOffsets.push(pdfOutput.length);
        pdfOutput += `4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>\nendobj\n`;

        // Obj 5: Font 3 (Courier)
        objectOffsets.push(pdfOutput.length);
        pdfOutput += `5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Courier /Encoding /WinAnsiEncoding >>\nendobj\n`;

        // Create Page and Stream Objects
        let currentObjId = 6;
        for (let i = 0; i < totalPages; i++) {
            const pageObjId = currentObjId;
            const streamObjId = currentObjId + 1;
            currentObjId += 2;

            const pageStream = this.pages[i];

            // Add footer to page stream
            const pageFooter = `\n0.5 0.5 0.5 rg\nBT /F1 8 Tf 260 30 Td (Page ${i + 1} of ${totalPages}) Tj ET\n`;
            const fullStream = pageStream + pageFooter;

            // Page Obj
            objectOffsets.push(pdfOutput.length);
            pdfOutput += `${pageObjId} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents ${streamObjId} 0 R /Resources << /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R >> >> >>\nendobj\n`;

            // Stream Obj
            objectOffsets.push(pdfOutput.length);
            pdfOutput += `${streamObjId} 0 obj\n<< /Length ${Buffer.byteLength(fullStream)} >>\nstream\n${fullStream}\nendstream\nendobj\n`;
        }

        // Cross-Reference Table
        const startXref = pdfOutput.length;
        pdfOutput += `xref\n0 ${currentObjId}\n0000000000 65535 f \n`;
        for (const offset of objectOffsets) {
            pdfOutput += `${offset.toString().padStart(10, '0')} 00000 n \n`;
        }

        // Trailer
        pdfOutput += `trailer\n<< /Size ${currentObjId} /Root 1 0 R >>\nstartxref\n${startXref}\n%%EOF\n`;

        return Buffer.from(pdfOutput, 'binary');
    }
}

// Generate the PDF Document
const doc = new SimplePDF();
doc.newPage();

// Title & Subtitle Header Banner
doc.addHeading('Uniform Shop ERP', 1);
doc.addText('System Architecture, Database Connections & Developer Guide', { font: 'F2', size: 14, color: [0.31, 0.27, 0.90], leading: 22 });
doc.addText('Comprehensive Technical Documentation for Uniform Shop ERP System', { font: 'F1', size: 10, color: [0.5, 0.5, 0.5], leading: 16 });
doc.addDivider();

// Section 1: System Architecture & Request Flow
doc.addHeading('1. System Architecture & Request Lifecycle (MVC)', 1);
doc.addText('Uniform Shop ERP is built on the Laravel framework following the Model-View-Controller (MVC) pattern.', { size: 10.5 });
doc.addText('Here is how HTTP requests travel through the application stack:', { size: 10.5 });

doc.addCodeBlock([
    '  [ User / Browser ]',
    '          │  (HTTP Request e.g. GET /pos or POST /variants)',
    '          ▼',
    '  [ Router: routes/web.php ] ──► (Authentication & Permission Middleware)',
    '          │',
    '          ▼',
    '  [ Controller: PosController ]',
    '       ┌──┴─────────────────────────┐',
    '       ▼                            ▼',
    '  [ Model: ProductVariant ]    [ View: pos/index.blade.php ]',
    '       │                            │',
    '  [ MySQL Database ]           [ Layout: layouts/app.blade.php ]',
    '                                    │',
    '                                    ▼',
    '                           [ HTML Page in Browser ]'
]);

// Section 2: Master Layout System
doc.addHeading('2. Layouts System (resources/views/layouts/)', 1);
doc.addText('The layout wrapper eliminates redundant HTML code by encapsulating the HTML head, fonts, scripts, top navigation bar, and sidebar navigation.', { size: 10.5 });

doc.addBullet('Located at resources/views/layouts/app.blade.php', 'Master Layout: ');
doc.addBullet('Integrated via @include(\'layouts.sidebar\') in dark slate styling.', 'Sidebar Component: ');
doc.addBullet('Dynamic slots $header (page title) and $slot (page body content).', 'Blade Slots: ');

doc.addCodeBlock([
    '<!-- Example Usage in any View -->',
    '<x-app-layout>',
    '    <x-slot name="header">',
    '        <h2 class="font-bold text-xl">Module Title</h2>',
    '    </x-slot>',
    '    <div class="bg-white p-6 rounded-xl">',
    '        <!-- Page Content Injected Here -->',
    '    </div>',
    '</x-app-layout>'
]);

// Section 3: Model & Migration
doc.addHeading('3. Creating Models & Database Migrations', 1);
doc.addText('Models represent database tables in Eloquent ORM. Migrations manage database schema changes.', { size: 10.5 });

doc.addBullet('Generate Model & Migration: php artisan make:model Supplier -m', 'Command: ');
doc.addBullet('Defines table schema in database/migrations/', 'Migration File: ');
doc.addBullet('Defines $fillable attributes, soft deletes, and relationships in app/Models/', 'Model File: ');

doc.addCodeBlock([
    '// Example Model: app/Models/ProductVariant.php',
    'class ProductVariant extends Model {',
    '    use HasFactory, SoftDeletes;',
    '    protected $fillable = [\'product_id\', \'sku\', \'barcode\', \'size\', \'colour\', \'selling_price\', \'stock_quantity\'];',
    '    public function product(): BelongsTo { return $this->belongsTo(Product::class); }',
    '}'
]);

// Section 4: Controller Implementation
doc.addHeading('4. Creating Controllers & Business Logic', 1);
doc.addText('Controllers process user requests, handle data validation, execute transactions, and return Blade views.', { size: 10.5 });

doc.addBullet('php artisan make:controller SupplierController', 'Command: ');
doc.addBullet('index(), create(), store(), edit(), update(), destroy()', 'Standard Methods: ');
doc.addBullet('Use DB::transaction() for atomicity when updating stock & sale items.', 'DB Transactions: ');

doc.addCodeBlock([
    '// Example Controller Action in PosController.php',
    'public function store(Request $request) {',
    '    $validated = $request->validate([ \'items\' => \'required|array\', \'payment_method\' => \'required\' ]);',
    '    return DB::transaction(function() use ($validated) {',
    '        $sale = Sale::create([...]);',
    '        // Deduct stock_quantity from ProductVariant...',
    '        return redirect()->route(\'sales.show\', $sale);',
    '    });',
    '}'
]);

// Section 5: Routing System
doc.addHeading('5. Routing System (routes/web.php)', 1);
doc.addText('Routes connect URL endpoints directly to Controller methods.', { size: 10.5 });

doc.addCodeBlock([
    '// Resource Routes (automatically maps all 7 CRUD actions)',
    'Route::resource(\'variants\', ProductVariantController::class)->except([\'show\']);',
    'Route::resource(\'stock-entries\', StockEntryController::class)->only([\'index\', \'create\', \'store\']);',
    '',
    '// Custom POS Named Routes',
    'Route::get(\'/pos\', [PosController::class, \'index\'])->name(\'pos.index\');',
    'Route::post(\'/pos\', [PosController::class, \'store\'])->name(\'pos.store\');'
]);

// Section 6: Blade Views & Alpine.js
doc.addHeading('6. Creating Blade Views & Interactive POS', 1);
doc.addText('Blade views render dynamic HTML output using simple directives:', { size: 10.5 });

doc.addBullet('Loop through dataset with empty fallback.', '@forelse($items as $item) ... @empty ... @endforelse: ');
doc.addBullet('Generate dynamic named URLs.', '{{ route(\'pos.index\') }}: ');
doc.addBullet('Used in POS terminal for real-time barcode filtering and shopping cart calculations.', 'AlpineJS Interactivity: ');

// Section 7: Connecting Navigation Sidebar Links
doc.addHeading('7. Adding New Links to Navigation Sidebar', 1);
doc.addText('To add a new module link, update resources/views/layouts/sidebar.blade.php:', { size: 10.5 });

doc.addCodeBlock([
    '<a href="{{ route(\'suppliers.index\') }}"',
    '   class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs(\'suppliers.*\') ? \'bg-indigo-600 text-white\' : \'text-slate-300\' }}">',
    '    <svg class="w-5 h-5 mr-3" ...></svg>',
    '    Suppliers',
    '</a>'
]);

// Section 8: ERP Modules Reference Cheat Sheet
doc.addHeading('8. Uniform Shop ERP Modules Summary', 1);
doc.addBullet('Today\'s revenue, sales count, low stock alerts, recent transactions.', '1. Dashboard: ');
doc.addBullet('Category management with soft deletes.', '2. Categories: ');
doc.addBullet('Products catalog belonging to categories.', '3. Products: ');
doc.addBullet('Product size, colour, SKU, barcode, price, stock.', '4. Variants: ');
doc.addBullet('Production intake logging with auto-incrementing stock.', '5. Stock Intake: ');
doc.addBullet('Customer profile & complete order history.', '6. Customers: ');
doc.addBullet('Real-time barcode search, cart, Cash/M-Pesa/Card, receipt print.', '7. POS / Sales: ');
doc.addBullet('Daily/monthly sales analytics, best sellers, cashier report.', '8. Reports: ');
doc.addBullet('Staff accounts creation, roles, and status activation.', '9. Users: ');

doc.addDivider();
doc.addText('Generated for Uniform Shop ERP System | Location: c:\\Development\\Laravel\\uniform-shop-erp\\documentation.pdf', { font: 'F1', size: 9, color: [0.5, 0.5, 0.5] });

// Build the PDF buffer
const pdfBuffer = doc.buildPDF();

// Save directly to project root as documentation.pdf
const targetPdfPath = path.resolve('c:/Development/Laravel/uniform-shop-erp/documentation.pdf');
fs.writeFileSync(targetPdfPath, pdfBuffer);

console.log(`Successfully generated documentation.pdf at ${targetPdfPath}`);
