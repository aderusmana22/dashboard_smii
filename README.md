# Export Data MPS vs Forecast Import

Dokumentasi ini menjelaskan alur proses untuk menyiapkan data **MPS** dan **Forecast Import** menjadi tabel yang siap diekspor ke Excel.

---

## 1. Struktur Data

### a. Data MPS
Data MPS berisi informasi produksi per item:

| Field            | Keterangan                                     | Contoh Data |
|-----------------|-----------------------------------------------|------------|
| Item Number      | Kode unik item (`pt_part`)                     | `ITEM001`  |
| Description      | Nama/Deskripsi item (`pt_desc1`)              | `Widget A` |
| UOM              | Unit of Measure (`pt_um`)                      | `PCS`      |
| Net Weight       | Berat bersih item (`pt_net_wt`)               | `1.2`      |
| Month            | Bulan data (`bulan`)                           | `10`       |
| Year             | Tahun data (`tahun`)                           | `2025`     |
| Inventory Qty    | Jumlah persediaan saat ini (`inventory_qty`)   | `500`      |
| Dispatch Qty     | Jumlah yang dikirim (`dispatch_qty`)           | `200`      |
| Allocated Qty    | Jumlah yang dialokasikan (`allocated_qty`)     | `50`       |
| SO Outstanding   | Sales Order yang belum terpenuhi (`so_outstanding`) | `30` |
| MPS Qty          | Jumlah produksi yang dijadwalkan (`mps_qty`)   | `220`      |

### b. Data Forecast Import
Data Forecast Import biasanya diambil dari file (CSV/Excel) dan memiliki field:

| Field         | Keterangan          | Contoh Data |
|---------------|-------------------|------------|
| Item Number   | Kode item          | `ITEM001`  |
| Description   | Deskripsi item     | `Widget A` |
| Month         | Bulan forecast     | `10`       |
| Year          | Tahun forecast     | `2025`     |
| Unit          | Jumlah unit        | `210`      |
| Tonage        | Berat total (ton)  | `1.26`     |

---

## 2. Proses Pengolahan

1. **Import Data**
   - Ambil data MPS dari sistem internal.
   - Ambil data Forecast Import dari file eksternal (CSV/Excel).

2. **Normalisasi**
   - Pastikan format `Item Number`, `Month`, dan `Year` sama pada kedua dataset.
   - Ambil field yang relevan untuk export.

3. **Grouping**
   - Group data MPS berdasarkan `Item Number`, `Month`, `Year`.
   - Group data Forecast Import berdasarkan `Item Number`, `Month`, `Year`.

4. **Export**
   - Satukan data MPS dan Forecast Import menjadi satu tabel.
   - Export ke **Excel** menggunakan library yang tersedia (misal `Laravel Excel`).
   - Tidak ada perhitungan selisih; data ditampilkan sebagaimana adanya.

---

## 3. Output Contoh

| Item Number | Description | Month | Year | Inventory Qty | Dispatch Qty | Allocated Qty | SO Outstanding | MPS Qty | Forecast Unit | Forecast Ton |
|------------|------------|-------|------|---------------|-------------|---------------|----------------|---------|---------------|--------------|
| ITEM001    | Widget A   | 10    | 2025 | 500           | 200         | 50            | 30             | 220     | 210           | 1.26         |
| ITEM002    | Widget B   | 10    | 2025 | 300           | 150         | 20            | 10             | 180     | 170           | 0.95         |

> Tabel ini siap langsung diekspor ke Excel untuk laporan atau analisis lebih lanjut.
