# Receipt Printer Setup (per shop PC)

The POS prints receipts through Windows, so **any thermal receipt printer that comes with a
Windows driver works** (Epson TM-series, Xprinter, Rongta, Bixolon, HPRT and similar, USB or network).
Each shop prints its own receipts with its own branch name, address and phone.

## 1. Install the printer
1. Connect the printer (USB or network cable) and install the **Windows driver** from the maker's
   CD or website.
2. In **Windows Settings → Printers**, open the printer → *Printing preferences*:
   - Paper size: **80 mm × receipt** (or **58 mm** for small printers). Choose the "roll" or
     "receipt" size, not A4.
   - If a **cash drawer** is plugged into the printer (RJ11 cable), enable *Open cash drawer:
     before/after printing* in the driver settings. The drawer then opens on every receipt.
3. Right-click the printer → **Set as default printer**.
4. Print a Windows test page to check that it cuts and feeds correctly.

## 2. Tell the POS the paper width
Log in as the owner → **Settings → Business Info**:
- *Receipt Printer Paper Width*: 80 mm or 58 mm.
- *Print Receipt Automatically After Each Sale*: **Yes**.
- While there, set each shop's **address and phone** under **Settings → Branches**. They appear on
  that shop's receipts. Also set the branch **Code** (e.g. `MS`, `S35`): it begins every receipt number.

## 3. Print without the print dialog (recommended)
By default the browser shows a print dialog for every receipt. To print silently, start Chrome in
kiosk-printing mode:

1. Right-click the Chrome shortcut used for the POS → *Properties*.
2. In **Target**, add this at the end, after a space:
   ```
   --kiosk-printing http://127.0.0.1:8000/pos
   ```
3. Close **all** Chrome windows and open Chrome with that shortcut.

Receipts now go straight to the default printer. (Microsoft Edge accepts the same `--kiosk-printing` flag.)

## 4. Printing and reprinting
- **After each sale** the receipt prints automatically.
- **Reprint**: POS → *Receipts* → *Reprint*, or *Sales Log* → open the sale → *Reprint Receipt*.
  Reprints are marked **\*\*\* COPY / REPRINT \*\*\***, so a duplicate can't pass as a second sale.
- **Preview without printing**: open `/sales/<id>/print?preview=1`.

## Troubleshooting
| Problem | Fix |
|---|---|
| Prints on A4 / very small text | Driver paper size is not set to the receipt roll (step 1.2). |
| Wide receipt cut off on the right | Set the POS paper width to 58 mm (step 2). |
| Print dialog still appears | Chrome was already open when started with the shortcut; close every Chrome window and use the shortcut. |
| Prints to the wrong printer | Make the receipt printer the Windows default (step 1.3). |
| Cash drawer does not open | Enable the drawer kick in the driver preferences (step 1.2). |

---

# Barcode labels (for the garments you make)

Every size and colour of every product has its own permanent barcode, e.g. `BC-GWTS-24-NB`.
The POS prints it as a sticker or swing tag; scanning it at the till adds exactly that size and
colour to the sale. Everything works offline: the barcode is drawn by the POS itself.

## What to buy
- **A barcode scanner** (USB or wireless, "1D" or "2D", any brand). It acts like a keyboard: plug
  it in, click the POS search box, scan. No driver or setup needed.
- **A label printer** (direct-thermal, e.g. Xprinter XP-365B/XP-420B, Zebra, TSC, Godex) with a
  Windows driver, and **50 x 25 mm** labels (recommended). 40 x 30, 38 x 25 and 60 x 40 mm rolls
  and **A4 sticker sheets (3 x 8, 70 x 37 mm)** for an ordinary office printer also work.
  The receipt printer cannot print stickers; use a separate label printer.

## Install the label printer
1. Install its Windows driver. In *Printing preferences* set the **label size** to the roll you
   bought (e.g. 50 x 25 mm) and the media type to **labels with gaps**. Run the printer's
   calibration (usually: hold the feed button) so it stops at each label.
2. Leave the **receipt printer as the Windows default**. You choose the label printer in the print
   box when printing labels.

## Daily use
1. When the workshop delivers finished goods, record them under **Stock Intake → Workshop /
   Factory Receipts**. After saving, press **Print Barcode Labels**: it lists one label per piece.
2. Or go to **Products → Print Barcode Labels** (or a product's sizes page → *Print Barcode
   Labels*) and add the items yourself.
3. Choose the label size, then **Print Labels**. In the print box choose the **label printer**,
   margins **None**, scale **100%** (Chrome remembers this for next time).
4. **Test once:** print one label and scan it at the till before printing a big batch.

Notes:
- Labels open in a normal browser tab. A Chrome shortcut started with `--kiosk-printing` always
  prints to the default (receipt) printer, so print labels from a normal Chrome window.
- A barcode never changes once the item has been sold or stocked, so labels already on garments
  keep scanning even if you rename the product or change the price.
- Showing the price on the label is optional (Print Barcode Labels → *Show price on label*). The till
  always charges the current price in the system, not the price printed on the label.
- If a scanned code is not an exact match, the till shows the closest items instead of guessing.

| Problem | Fix |
|---|---|
| Labels drift / print across two stickers | Set the label size in the driver and calibrate the printer (step 1). |
| Scanner beeps but nothing appears | Click into the POS search box first; the scanner types into whatever is selected. |
| Scanner types wrong characters | Set the scanner to the **US keyboard** layout (scan the setup code in its manual). |
| Will not scan from a small label | Use 50 x 25 mm labels; long barcodes need the width. |
