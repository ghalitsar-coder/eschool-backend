# Kas API Documentation

## Authentication

Semua endpoint kas memerlukan authentication dengan Sanctum dan role 'bendahara'.

```
Headers:
Authorization: Bearer {token}
Content-Type: application/json
```

## Endpoints

### 1. Get Members

**GET** `/api/members`

Mengambil daftar member yang terkait dengan eschool bendahara yang login.

**Response:**

```json
{
    "eschool": {
        "id": 1,
        "name": "Kelas XII IPA 1",
        "monthly_kas_amount": 25000
    },
    "members": [
        {
            "id": 1,
            "student_id": "STD0001",
            "name": "Andi Pratama",
            "email": "siswa1@example.com",
            "phone": "081234567890"
        }
    ]
}
```

### 2. Get Kas Summary

**GET** `/api/kas/summary`

Mengambil ringkasan kas untuk dashboard.

**Response:**

```json
{
    "eschool": {
        "name": "Kelas XII IPA 1",
        "monthly_kas_amount": 25000
    },
    "summary": {
        "total_income": 500000,
        "total_expense": 150000,
        "balance": 350000,
        "total_members": 8
    },
    "current_month": {
        "month": 8,
        "year": 2025,
        "paid_count": 5,
        "unpaid_count": 3,
        "payment_percentage": 62.5
    }
}
```

### 3. Get Kas Records

**GET** `/api/kas/records`

Mengambil history kas records dengan pagination.

**Query Parameters:**

-   `type` (optional): 'income' atau 'expense'
-   `month` (optional): 1-12
-   `year` (optional): tahun
-   `page` (optional): halaman pagination

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "type": "income",
            "amount": 100000,
            "description": "Pembayaran kas bulan Januari",
            "date": "2025-01-15",
            "created_at": "2025-01-15 10:30:00",
            "payments": [
                {
                    "member_name": "Andi Pratama",
                    "amount": 25000,
                    "month": 1,
                    "year": 2025
                }
            ]
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 20,
        "total": 100
    }
}
```

### 4. Store Income (Pemasukan)

**POST** `/api/kas/income`

Mencatat pemasukan kas setelah bendahara menerima uang cash dari member.

**Flow Proses:**

1. Member memberikan uang cash fisik ke bendahara
2. Bendahara mencatat penerimaan uang tersebut melalui endpoint ini
3. Sistem mencatat sebagai kas_record (income) + kas_payment per member

**Request Body:**

```json
{
    "description": "Penerimaan kas bulan Januari 2025",
    "date": "2025-01-15",
    "payments": [
        {
            "member_id": 1,
            "amount": 25000,
            "month": 1,
            "year": 2025
        },
        {
            "member_id": 2,
            "amount": 25000,
            "month": 1,
            "year": 2025
        }
    ]
}
```

**Response:**

```json
{
    "message": "Pemasukan berhasil dicatat",
    "kas_record_id": 15
}
```

### 5. Store Expense (Pengeluaran)

**POST** `/api/kas/expense`

Mencatat pengeluaran kas (tidak perlu detail member).

**Request Body:**

```json
{
    "amount": 50000,
    "description": "Pembelian alat tulis untuk kelas",
    "date": "2025-01-15"
}
```

**Response:**

```json
{
    "message": "Pengeluaran berhasil dicatat",
    "kas_record_id": 16
}
```

## Logic Explanation

### Flow Kas Fisik (Cash Flow)

**Real World Process:**

1. Member datang ke bendahara dengan uang cash
2. Bendahara menerima uang fisik dari member
3. Bendahara mencatat penerimaan di sistem

**Tidak ada pembayaran digital/online dari member!**

### Pemasukan (Income)

-   **1 kas_record** dapat memiliki **1 atau lebih kas_payment**
-   Setiap kas_payment = record penerimaan cash dari member spesifik
-   Total amount di kas_record = sum dari semua kas_payment yang diterima
-   Eschool_id otomatis diambil dari treasurer_id yang login
-   Validasi: semua member_id harus dari eschool yang sama

### Pengeluaran (Expense)

-   **1 kas_record** untuk pengeluaran
-   **Tidak ada kas_payment** terkait (karena bukan penerimaan dari member)
-   Langsung catat amount, description, dan date
-   Eschool_id otomatis diambil dari treasurer_id yang login

### Validasi

-   User harus role 'bendahara'
-   Member_id harus terkait dengan eschool bendahara
-   Token expires dalam 60 menit (konfigurasi Sanctum)

## Testing Data

Gunakan akun bendahara dari seeder:

-   Email: `bendahara1@example.com`
-   Password: `password`
-   Eschool: "Kelas XII IPA 1"
