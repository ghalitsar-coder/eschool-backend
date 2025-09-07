# Analisis Sistem Eschool Management

## 1. Gambaran Umum

Berdasarkan dokumen QWEN.md dan PRD.md serta struktur migrasi database yang telah dibuat, sistem Eschool Management adalah platform terpusat untuk mengelola kegiatan ekstrakurikuler di multiple sekolah. Sistem ini dirancang untuk mendukung berbagai role pengguna dengan tanggung jawab yang berbeda serta menyediakan fitur absensi dan manajemen keuangan untuk setiap eschool.

## 2. Struktur Database

### 2.1 Tabel Utama

#### a. schools
Tabel ini menyimpan informasi dasar tentang setiap sekolah:
- id (primary key)
- name
- address
- phone
- email (unik)
- timestamps

#### b. profiles
Tabel ini menyimpan data profil individu (baik guru maupun siswa):
- id (primary key)
- name
- date_of_birth
- gender (enum: M/F)
- address (nullable)
- status (enum: active/inactive, default: active)
- timestamps

#### c. users
Tabel ini menyimpan data autentikasi pengguna:
- id (primary key)
- profile_id (foreign key ke profiles)
- name
- email (unik)
- email_verified_at
- password
- remember_token
- timestamps

#### d. teachers
Tabel ini menyimpan data khusus untuk guru/licensed teacher:
- id (primary key)
- profile_id (foreign key ke profiles, unik)
- license_number (unik)
- school_id (foreign key ke schools)
- timestamps

#### e. students
Tabel ini menyimpan data khusus untuk siswa:
- id (primary key)
- profile_id (foreign key ke profiles, unik)
- school_id (foreign key ke schools)
- student_id (unik, nullable)
- grade_level (nullable)
- timestamps

#### f. eschools
Tabel ini menyimpan informasi tentang setiap kegiatan ekstrakurikuler:
- id (primary key)
- school_id (foreign key ke schools)
- name
- schedule_days
- description (nullable)
- is_active (boolean, default: true)
- monthly_fee_amount (decimal 8,2, default: 0.00)
- timestamps

#### g. user_eschool_roles
Tabel ini menyimpan hubungan antara pengguna dan eschool dengan role masing-masing:
- id (primary key)
- user_id (foreign key ke users)
- eschool_id (foreign key ke eschools, nullable untuk supervisor)
- role (enum: supervisor, coordinator, treasurer, member)
- timestamps

### 2.2 Tabel Pendukung

#### a. attendance_record
Tabel ini menyimpan data absensi peserta eschool:
- id (primary key)
- user_eschool_role_id (foreign key ke user_eschool_roles)
- status (enum: present, absent, late)
- notes (nullable)
- timestamps

#### b. kas_record
Tabel ini menyimpan transaksi keuangan eschool:
- id (primary key)
- eschool_id (foreign key ke eschools)
- description
- category
- amount (decimal 8,2)
- date
- recorder_id (foreign key ke user_eschool_roles)
- timestamps

#### c. kas_payment
Tabel ini menyimpan data pembayaran iuran bulanan anggota:
- id (primary key)
- kas_record_id (foreign key ke kas_record)
- member_id (foreign key ke user_eschool_roles)
- amount (decimal 8,2)
- month
- year
- is_paid (boolean, default: false)
- paid_date (nullable)
- timestamps

## 3. Kesesuaian dengan Requirement

### 3.1 Multi-School Architecture
✅ Sesuai dengan requirement. Tabel `schools` mendukung multiple sekolah yang beroperasi secara independen. Setiap eschool terkait dengan satu sekolah melalui foreign key `school_id`.

### 3.2 Role Definitions
✅ Sesuai dengan requirement:
- **Staff (Supervisor)**: Diimplementasikan sebagai role "supervisor" dalam tabel `user_eschool_roles`. Staff dapat mengelola semua eschool dalam satu sekolah.
- **Coordinator**: Diimplementasikan sebagai role "coordinator" dalam tabel `user_eschool_roles`. Setiap koordinator hanya mengelola satu eschool.
- **Treasurer**: Diimplementasikan sebagai role "treasurer" dalam tabel `user_eschool_roles`. Setiap bendahara hanya mengelola kas satu eschool.
- **Member**: Diimplementasikan sebagai role "member" dalam tabel `user_eschool_roles`. Anggota dapat bergabung dengan multiple eschool dalam sekolah yang sama.

### 3.3 Core Features
✅ Sesuai dengan requirement:
- **Attendance System**: Diimplementasikan melalui tabel `attendance_record` yang terhubung dengan `user_eschool_roles` untuk tracking kehadiran anggota.
- **Kas Management**: Diimplementasikan melalui tabel `kas_record` untuk transaksi dan `kas_payment` untuk tracking pembayaran iuran bulanan.

### 3.4 Business Rules
✅ Sebagian besar sesuai dengan requirement:
- ✅ Siswa tidak bisa join eschool di sekolah lain: Diimplementasikan melalui foreign key `school_id` di tabel `students`.
- ⚠️ Koordinator dan bendahara terbatas satu eschool: Struktur tabel memungkinkan ini, tetapi enforcement tergantung pada logika aplikasi.
- ✅ Staff oversight semua eschool di sekolah mereka: Diimplementasikan melalui role "supervisor" yang tidak memerlukan `eschool_id`.
- ✅ Member bisa join multiple eschool dalam sekolah yang sama: Diimplementasikan melalui multiple entry dalam tabel `user_eschool_roles`.

## 4. Observasi dan Rekomendasi

### 4.1 Observasi
1. **Struktur Tabel**: Struktur tabel sudah sesuai dengan requirement sistem multi-sekolah dengan role-based access control.
2. **Relasi Tabel**: Relasi antar tabel sudah terdefinisi dengan baik menggunakan foreign key constraints.
3. **Fitur Absensi**: Tabel `attendance_record` sudah mendukung tracking status kehadiran (present, absent, late).
4. **Manajemen Keuangan**: Tabel `kas_record` dan `kas_payment` sudah mendukung pencatatan transaksi dan tracking pembayaran iuran.

### 4.2 Rekomendasi
1. **Enforcement Role Constraints**: Untuk memastikan bahwa koordinator dan bendahara hanya bisa terdaftar di satu eschool, perlu ditambahkan unique constraint pada kombinasi `(user_id, role)` untuk role coordinator dan treasurer dalam tabel `user_eschool_roles`.
2. **Validasi Staff per Sekolah**: Untuk membatasi maksimal 2 staff per sekolah, perlu ditambahkan logika validasi di aplikasi atau trigger di database.
3. **Kategori Kas**: Pertimbangkan untuk membuat tabel terpisah untuk kategori kas agar lebih mudah dikelola dan dikelompokkan.
4. **Jadwal Eschool**: Kolom `schedule_days` dalam tabel `eschools` saat ini bertipe string. Pertimbangkan untuk membuat struktur yang lebih terstruktur untuk menyimpan jadwal.

## 5. Kesimpulan

Struktur database yang telah dibuat sudah sesuai dengan requirement yang tercantum dalam dokumen QWEN.md dan PRD.md. Sistem telah mendukung multi-sekolah, role-based access control, serta fitur utama absensi dan manajemen keuangan. Beberapa penyesuaian kecil diperlukan untuk memastikan enforcement business rules secara penuh, terutama terkait constraint untuk role koordinator dan bendahara.