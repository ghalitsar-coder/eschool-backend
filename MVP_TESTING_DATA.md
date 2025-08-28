# MVP Testing Data

## Flow Kas Fisik (Cash Management)

**Proses Real World:**

1. **Member** datang ke **Bendahara** dengan **uang cash fisik**
2. **Bendahara** menerima uang cash dari member
3. **Bendahara** mencatat penerimaan uang di sistem melalui form/API
4. **Tidak ada** mekanisme pembayaran digital/online dari member

**Sistem hanya untuk pencatatan, bukan payment gateway!**

## Akun Bendahara untuk Testing

| Email                  | Password | Eschool         | Monthly Kas |
| ---------------------- | -------- | --------------- | ----------- |
| bendahara1@example.com | password | Kelas XII IPA 1 | Rp 25,000   |
| bendahara2@example.com | password | Kelas XI IPS 2  | Rp 20,000   |
| bendahara3@example.com | password | Kelas X MIPA 3  | Rp 15,000   |

## Fitur MVP yang Didukung Seeder

### 1. Login Bendahara

-   3 akun bendahara siap pakai
-   Password: `password` untuk semua akun
-   Setiap bendahara memiliki 1 eschool yang terkait

### 2. Fetch Members

-   Setiap eschool memiliki 5-8 member siswa
-   Member terkait dengan eschool bendahara (validasi otomatis)
-   Data member lengkap dengan student_id dan phone

### 3. Kas Records & Payments

-   Data kas_records sudah ada (income & expense)
-   Data kas_payments dengan status mixed (60% paid, 40% unpaid)
-   6 bulan data pembayaran untuk testing
-   Amount sesuai dengan monthly_kas_amount eschool

### 4. Otomatis Eschool ID

-   Seeder memastikan setiap bendahara memiliki eschool_id yang jelas
-   Relasi treasurer_id -> eschool_id sudah terdefinisi

## Cara Testing MVP

1. **Login sebagai bendahara:**

    ```
    POST /api/login
    {
      "email": "bendahara1@example.com",
      "password": "password"
    }
    ```

2. **Fetch members (otomatis berdasarkan eschool bendahara):**

    ```
    GET /api/members
    ```

3. **Catat kas pemasukan (setelah menerima cash dari member):**

    ```
    POST /api/kas/income
    {
      "description": "Penerimaan kas bulan Januari 2025",
      "date": "2025-01-15",
      "payments": [
        {"member_id": 1, "amount": 25000, "month": 1, "year": 2025},
        {"member_id": 2, "amount": 25000, "month": 1, "year": 2025}
      ]
    }
    ```

4. **Catat kas pengeluaran:**
    ```
    POST /api/kas/expense
    {
      "amount": 50000,
      "description": "Pembelian alat tulis untuk kelas",
      "date": "2025-01-15"
    }
    ```

## Data yang Tersedia

-   **5 Schools** dengan data lengkap
-   **3 Eschools** (1 per bendahara)
-   **15+ Members** tersebar di eschools
-   **Income & Expense Records** untuk setiap eschool
-   **Payment Records** dengan status mixed untuk testing form

## Validasi yang Didukung

-   Member_id hanya dari eschool yang sama dengan bendahara
-   Eschool_id otomatis dari treasurer_id
-   Token expiry (60 menit) - konfigurasi di Sanctum
