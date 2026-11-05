# Project Go (sub-proyek Yunassist)

Web app pencatatan kerja pribadi: work log harian + to-do per proyek + scratchpad + kalender. Jadi fondasi data (logbook engine) untuk Yuna, asisten AI.
Tujuan utama: log kerja selengkap mungkin dengan effort input serendah mungkin. Menandai To-Do Done otomatis membuat baris log berstempel waktu.

Single-user, private/local. TANPA auth, RBAC, multi-tenant, native mobile app.

## Tech stack
- Frontend: Next.js (App Router) + Tailwind CSS. Optimistic UI untuk aksi instan.
- Backend: Laravel (REST API). DB: MySQL/PostgreSQL.
- Env: Windows + Laragon (`C:\laragon\www\Yuna-Assistant`). Asumsi struktur monorepo: `backend/` dan `frontend/`.
- Waktu: simpan UTC/ISO 8601, tampilkan Asia/Jakarta (WIB).

## Menjalankan (Windows/Laragon)
- Backend (Laravel 10, PHP 8.1 karena Laragon): tambah `C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64` ke PATH, start MySQL Laragon, lalu `cd backend; php artisan migrate; php artisan serve --port=8000`. DB: `yuna_go`.
- Frontend (Next 16, baca `frontend/AGENTS.md` sebelum ubah API Next): `cd frontend; npm run dev` (API URL via `NEXT_PUBLIC_API_URL`, default `http://127.0.0.1:8000/api`).
- Fase 1 sudah dibuat: tabel projects/todos/work_logs/daily_notes, endpoint `/api/{projects,todos,work-logs,daily-note}`, dashboard di `frontend/app/page.tsx`. Todo Done menutup sesi aktif `--.--` menjadi baris todo itu; kalau tidak ada sesi, start = end baris sebelumnya.

- Fase 2 sudah dibuat: folders (projects.folder_id), prioritas todo, tags polymorphic (`taggables`, dipakai Todo + ScratchNote; API menerima `tags: ["nama"]`, find-or-create by slug), scratch_notes (archive, tidak ada auto-delete). Endpoint tambahan: `/api/{folders,tags,scratch-notes}`. UI: `frontend/app/components/{TodoWidget,ScratchpadWidget,TagChip}.tsx`; dialog/guard di `lib/ui.tsx`. Input cepat: `#tag` di judul todo / scratchpad.
- Fase 3 (sebagian): Calendar & event tracker sudah dibuat. Tabel `calendar_events`, endpoint `/api/calendar-events?from=&to=`, halaman `frontend/app/calendar/page.tsx` (tampilan Bulan/Minggu/Hari, klik hari = event baru), helper tanggal `lib/dates.ts` (string YYYY-MM-DD, hitung UTC), form `components/EventModal.tsx`. Integrasi API Yuna (token admin) BELUM dibuat.
- UI jangan pakai `alert/confirm/prompt` browser; pakai `useDialog()` (card tengah) dan bungkus aksi API dengan `useGuard()` (error -> banner).

## Fase (kerjakan berurutan, jangan lompat)
1. MVP: Work log (tabel + sesi live `--.--`), Daily Focus Note, Project + To-Do (auto-sync ke log), manual log entry.
2. Structure: Folder proyek, prioritas to-do, custom tag + filter, Scratchpad (tagging, archive).
3. Integrasi: Calendar/event tracker, API Yuna (full CRUD admin).

## Model data
- Folder: id, name, timestamps
- Project: id, folder_id (null), name, description (null), color_code, timestamps
- Todo: id, project_id, title, status (pending|done), priority (primary|secondary|tertiary), completed_at (null), timestamps
- Tag: id, name, slug, color
- WorkLog: id, todo_id (null), date, start_time, end_time (null = sesi aktif), activity, type (WORK|ACTIVITY), notes (null), is_manually_edited (default false), timestamps
- DailyNote: id, date (unique), focus_note, timestamps
- ScratchNote: id, content, is_archived (default false), timestamps
- CalendarEvent: id, project_id (null), event_date, title, event_type (deadline|event|target), timestamps

## Perilaku Work Log
Kolom: Waktu Mulai (HH:mm), Aktivitas, Waktu Selesai (HH:mm atau `--.--` jika aktif), Jenis, Catatan.
- Tombol "Mulai Kerja" membuat baris dengan start=now, end=`--.--`.
- Todo -> Done: buat baris WORK, activity=judul todo, end=now, tanpa input jam manual.
- BREAK (Istirahat/Solat/Makan) dan aktivitas manual = ACTIVITY.

## Aturan bisnis (wajib)
1. Todo di-uncheck setelah Done: hapus baris log terkait setelah konfirmasi user.
2. start_time baris auto-sync = end_time baris log sebelumnya; jika log kosong, pakai timestamp tombol Mulai Kerja.
3. Todo Done tanpa sesi Mulai Kerja: baris tetap dibuat, start_time kosong (bisa diedit manual).
4. Dua todo Done di menit sama: dua baris berurutan berdasar timestamp milidetik.
5. Semua kolom log boleh diedit manual; baris yang diedit diberi `is_manually_edited=true` dan tidak boleh ditimpa auto-sync.
6. Overlap rentang waktu: tampilkan peringatan visual, JANGAN blokir simpan.
7. Sesi `--.--` belum ditutup sampai hari berikutnya: tampilkan banner minta tutup/edit.
8. Baris dari todo Done selalu WORK; manual/istirahat selalu ACTIVITY.
- Scratchpad: tidak pernah dihapus otomatis, hanya archived/history.

## UI
- Dashboard: Top bar (tanggal, status sesi aktif, quick actions); kiri 65% (Daily Focus Note + Work Log Table); sidebar 35% (To-Do widget + Scratchpad widget).
- Dark mode native. Badge: WORK indigo/emerald, ACTIVITY slate/cyan, BREAK amber/orange, sesi aktif = pulsing dot animasi.
- Shortcut: Space/klik = centang todo; Ctrl+Enter = submit log manual/scratchpad; double-click sel tabel = inline edit.
- Harus cepat dan tidak mengganggu flow; desktop-first, responsif di mobile browser.

## Integrasi Yuna (Fase 3)
API Bearer Token dengan scope Admin. Yuna boleh: baca focus note & rekap durasi log; tambah todo dari percakapan; perbaiki/hapus log dan tutup sesi `--.--`.

## Open questions (belum ada di PRD, tanyakan sebelum asumsi)
- (Terputuskan) enum DB `tertiary`, label UI "Tersier"; tag pakai satu tabel polymorphic.
- Struktur repo final (monorepo vs terpisah).

## Cara kerja
- PRD sudah diringkas di file ini; jangan minta/baca ulang PRD penuh.
- Fase 1, 2, dan kalender selesai; API Yuna (Bearer token admin) belum, jangan dibangun sebelum diminta.
- Jika keputusan tidak tercakup di sini, tanya singkat dulu.
