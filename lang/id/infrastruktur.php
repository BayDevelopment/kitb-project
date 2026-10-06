<?php

return [
  'title' => 'Infrastruktur',
  'subtitle' => 'Kelola informasi infrastruktur kawasan perusahaan.',
  'add' => 'Tambah Infrastruktur',

  'search_placeholder' => 'Cari nama atau deskripsi infrastruktur (ID / EN / 中文)...',
  'clear_search' => 'Hapus pencarian',

  'data_title' => 'Data Infrastruktur',
  'data_subtitle' => 'Informasi infrastruktur yang tersedia di kawasan.',
  'data_count' => 'data',

  'table' => [
    'number' => 'No.',
    'infrastructure' => 'Infrastruktur',
    'description' => 'Deskripsi',
    'order' => 'Urutan',
    'status' => 'Status',
    'actions' => 'Aksi',
  ],

  'status' => [
    'active' => 'Aktif',
    'inactive' => 'Nonaktif',
    'infrastructure_active' => 'Infrastruktur Aktif',
    'infrastructure_inactive' => 'Infrastruktur Nonaktif',
    'click_to_change' => 'Klik untuk ubah',
  ],

  'actions' => [
    'view_detail' => 'Lihat detail',
    'edit' => 'Edit infrastruktur',
    'delete' => 'Hapus infrastruktur',
    'move_up' => 'Pindah ke atas',
    'move_down' => 'Pindah ke bawah',
  ],

  'pagination' => [
    'first' => 'Halaman pertama',
    'previous' => 'Halaman sebelumnya',
    'next' => 'Halaman berikutnya',
    'last' => 'Halaman terakhir',
    'showing' => 'Menampilkan',
    'of' => 'dari',
    'infrastructure' => 'infrastruktur',
  ],

  'empty' => [
    'not_found' => 'Infrastruktur tidak ditemukan',
    'no_data' => 'Belum ada infrastruktur',
    'search_description' => 'Tidak ditemukan data yang sesuai dengan pencarian.',
    'add_description' => 'Tambahkan data infrastruktur untuk mulai mengelola informasi kawasan.',
    'clear_search' => 'Bersihkan Pencarian',
  ],

  'modal' => [
    'create_title' => 'Tambah Infrastruktur',
    'edit_title' => 'Edit Infrastruktur',
    'create_description' => 'Tambahkan informasi infrastruktur baru.',
    'edit_description' => 'Perbarui informasi infrastruktur.',
    'detail_title' => 'Detail Infrastruktur',
    'detail_description' => 'Informasi lengkap infrastruktur kawasan.',
    'delete_title' => 'Hapus Infrastruktur?',
    'delete_description' => 'Yakin ingin menghapus',
    'delete_warning' => 'Seluruh terjemahan (ID, EN, 中文) akan ikut terhapus dan tidak dapat dikembalikan.',
    'close' => 'Tutup',
    'cancel' => 'Batal',
    'save' => 'Simpan Infrastruktur',
    'save_changes' => 'Simpan Perubahan',
    'saving' => 'Menyimpan...',
    'deleting' => 'Menghapus...',
    'confirm_delete' => 'Ya, Hapus',
  ],

  'form' => [
    'name' => 'Nama Infrastruktur',
    'name_required' => 'Nama infrastruktur (Indonesia) wajib diisi.',
    'name_placeholder' => 'Contoh: Jalan Utama Kawasan',
    'name_en' => 'Infrastructure Name',
    'name_en_placeholder' => 'Example: Main Estate Road',
    'name_zh' => '基础设施名称',
    'name_zh_placeholder' => '例如：园区主干道',

    'slug' => 'Slug',
    'slug_placeholder' => 'slug-otomatis',
    'slug_help' => 'Slug dibuat otomatis oleh sistem dari nama Bahasa Indonesia dan tidak dapat diubah secara manual. Slug final dapat berbeda jika sudah digunakan.',

    'status' => 'Status',

    'order' => 'Urutan',
    'order_placeholder' => '0',
    'order_help' => 'Kosongkan atau gunakan 0 untuk mengikuti urutan otomatis.',

    'description' => 'Deskripsi',
    'description_languages' => 'Indonesia / English / 中文',
    'description_placeholder' => 'Tuliskan deskripsi lengkap mengenai infrastruktur...',
    'description_en_placeholder' => 'Write the English description of the infrastructure...',
    'description_zh_placeholder' => '请输入基础设施的中文描述...',
    'language_notice' => 'Isi nama dan deskripsi untuk bahasa yang sedang dipilih.',
    'translation_fallback' => 'Jika kosong, halaman publik otomatis menggunakan nama dan deskripsi Bahasa Indonesia.',

    'image' => 'Gambar Infrastruktur',
    'image_preview' => 'Preview gambar',
    'select_image' => 'Pilih gambar',
    'image_help' => 'JPG, JPEG, PNG, WEBP — maksimal 2 MB',
    'remove_new_image' => 'Hapus gambar baru',
    'remove_image' => 'Hapus gambar',
    'image_will_be_deleted' => 'Gambar lama akan dihapus saat disimpan.',
    'no_image' => 'Tidak ada gambar',
  ],

  'languages' => [
    'indonesia' => '🇮🇩 Indonesia',
    'english' => '🇬🇧 English',
    'chinese' => '🇨🇳 中文',
  ],

  'detail' => [
    'slug' => 'Slug',
    'order' => 'Urutan',
    'description' => 'Deskripsi',
  ],

  'loading' => 'Memuat...',

  'errors' => [
    'image_format' => 'Gambar harus berformat JPG, JPEG, PNG, atau WEBP.',
    'image_size' => 'Ukuran gambar maksimal 2 MB.',
    'name_slug' => 'Nama harus mengandung huruf atau angka agar slug dapat dibuat.',
  ],

  'public' => [
    'breadcrumb_home' => 'Beranda',
    'breadcrumb_area' => 'Kawasan',
    'breadcrumb_current' => 'Infrastruktur',

    'hero_badge' => 'Infrastruktur Kawasan',
    'hero_title' => 'Infrastruktur Pendukung',
    'hero_title_highlight' => 'Kawasan Industri',
    'hero_description' => 'Infrastruktur terpadu yang mendukung operasional industri, distribusi logistik, dan kebutuhan tenant di kawasan.',

    'section_label' => 'Infrastruktur',
    'available' => 'infrastruktur tersedia',
    'carousel_label' => 'Carousel infrastruktur kawasan',
    'navigation' => 'Navigasi infrastruktur',
    'previous' => 'Infrastruktur sebelumnya',
    'next' => 'Infrastruktur berikutnya',
    'show_slide' => 'Tampilkan :name',

    'image_label' => 'Infrastruktur Kawasan',
    'image_alt_fallback' => 'Infrastruktur kawasan',
    'image_preview' => 'Pratinjau gambar infrastruktur',
    'view_larger_image' => 'Lihat gambar lebih besar',
    'close_preview' => 'Tutup pratinjau',
    'supporting_facility' => 'Fasilitas Pendukung',

    'description_fallback' => 'Deskripsi infrastruktur belum tersedia.',

    'area' => 'Lokasi',
    'area_name' => 'Kawasan Industri Tanjung Buton (KITB)',
    'facility' => 'Fasilitas Kawasan',
    'area_map' => 'Peta Kawasan',
    'view_area_profile' => 'Lihat Profil Kawasan',

    'empty_title' => 'Belum ada infrastruktur',
    'empty_description' => 'Informasi infrastruktur kawasan akan segera ditampilkan di halaman ini.',

    'integrated_title' => 'Terintegrasi',
    'integrated_description' => 'Jaringan jalan, listrik, air, dan logistik dirancang saling terhubung untuk menunjang aktivitas industri.',
    'industry_title' => 'Siap untuk Industri',
    'industry_description' => 'Infrastruktur disiapkan untuk memenuhi kebutuhan operasional industri dan tenant secara andal.',
    'connected_title' => 'Terhubung Strategis',
    'connected_description' => 'Akses kawasan mendukung kelancaran mobilitas kendaraan dan distribusi barang.',

    'explore_label' => 'Jelajahi Kawasan',
    'explore_title' => 'Kenali lebih jauh kawasan kami',
    'explore_description' => 'Lihat profil kawasan, fasilitas, dan peta untuk memahami keunggulan lokasi dan infrastruktur yang tersedia.',
  ],
];
