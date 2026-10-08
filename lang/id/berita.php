<?php

/*
 * Keys sama persis dengan objek `ui` di:
 *   resources/js/pages/Berita/Index.vue
 *   resources/js/pages/Berita/Show.vue
 *
 * readAria & detailAria memakai placeholder :title
 * (di Vue: ui.readAria(title) / ui.detailAria(title)).
 */

return [
  // Umum
  'home' => 'Beranda',
  'informationCenter' => 'Pusat Informasi',
  'news' => 'Berita',
  'description' => 'Informasi terbaru mengenai kegiatan, perkembangan, investasi, dan aktivitas PT Kawasan Industri Tanjung Buton.',

  // Pencarian & filter
  'searchLabel' => 'Cari berita',
  'searchPlaceholder' => 'Cari judul, kategori, atau isi berita...',
  'categoryFilter' => 'Filter kategori berita',
  'allCategories' => 'Semua Kategori',
  'reset' => 'Reset',

  // Bahasa
  'language' => 'Bahasa',
  'chooseLanguage' => 'Pilih bahasa berita',
  'newsCount' => 'berita',

  // Status
  'loading' => 'Memuat berita...',

  'emptyTitle' => 'Belum Ada Berita',
  'emptyDescription' => 'Belum ada berita yang dapat ditampilkan saat ini. Silakan kembali lagi untuk mendapatkan informasi terbaru dari KITB.',

  'notFoundTitle' => 'Berita Tidak Ditemukan',
  'notFoundDescription' => 'Tidak ada berita yang sesuai dengan pencarian atau kategori yang dipilih.',

  'showAll' => 'Tampilkan Semua Berita',

  // Kartu & modal
  'featured' => 'Berita Unggulan',
  'readMore' => 'Baca Selengkapnya',
  'close' => 'Tutup',
  'fullArticle' => 'Baca Berita Selengkapnya',
  'summary' => 'Ringkasan',
  'content' => 'Isi Berita',
  'views' => 'dilihat',

  // Pagination
  'previous' => 'Sebelumnya',
  'next' => 'Berikutnya',
  'previousAria' => 'Halaman sebelumnya',
  'nextAria' => 'Halaman berikutnya',
  'paginationAria' => 'Pagination berita',

  // Aria
  'newsListAria' => 'Daftar berita',
  'searchSectionAria' => 'Pencarian berita',
  'loadingAria' => 'Memuat berita',
  'readAria' => 'Baca :title',
  'detailAria' => 'Lihat detail berita :title',
  'modalCloseAria' => 'Tutup detail berita',

  // Halaman detail (Show.vue)
  'featuredDetail' => 'Berita Pilihan',
  'contentUnavailable' => 'Isi berita belum tersedia.',
  'share' => 'Bagikan berita ini',
  'copy' => 'Salin tautan',
  'copied' => 'Tautan disalin',
  'whatsapp' => 'WhatsApp',
  'back' => 'Kembali ke daftar berita',
  'related' => 'Berita lainnya',
];
