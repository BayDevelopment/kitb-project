<?php

/*
 * Keys match the `ui` object in:
 *   resources/js/pages/Berita/Index.vue
 *   resources/js/pages/Berita/Show.vue
 *
 * readAria & detailAria use the :title placeholder
 * (in Vue: ui.readAria(title) / ui.detailAria(title)).
 */

return [
  // General
  'home' => 'Home',
  'informationCenter' => 'Information Center',
  'news' => 'News',
  'description' => 'Latest information about the activities, developments, investments, and operations of PT Kawasan Industri Tanjung Buton.',

  // Search & filter
  'searchLabel' => 'Search news',
  'searchPlaceholder' => 'Search title, category, or news content...',
  'categoryFilter' => 'Filter news category',
  'allCategories' => 'All Categories',
  'reset' => 'Reset',

  // Language
  'language' => 'Language',
  'chooseLanguage' => 'Choose news language',
  'newsCount' => 'news',

  // Status
  'loading' => 'Loading news...',

  'emptyTitle' => 'No News Available',
  'emptyDescription' => 'There is no news available at the moment. Please come back for the latest information from KITB.',

  'notFoundTitle' => 'News Not Found',
  'notFoundDescription' => 'No news matches the selected search or category.',

  'showAll' => 'Show All News',

  // Cards & modal
  'featured' => 'Featured News',
  'readMore' => 'Read More',
  'close' => 'Close',
  'fullArticle' => 'Read Full Article',
  'summary' => 'Summary',
  'content' => 'News Content',
  'views' => 'views',

  // Pagination
  'previous' => 'Previous',
  'next' => 'Next',
  'previousAria' => 'Previous page',
  'nextAria' => 'Next page',
  'paginationAria' => 'News pagination',

  // Aria
  'newsListAria' => 'News list',
  'searchSectionAria' => 'News search',
  'loadingAria' => 'Loading news',
  'readAria' => 'Read :title',
  'detailAria' => 'View news details :title',
  'modalCloseAria' => 'Close news details',

  // Detail page (Show.vue)
  'featuredDetail' => 'Featured News',
  'contentUnavailable' => 'News content is not available.',
  'share' => 'Share this news',
  'copy' => 'Copy link',
  'copied' => 'Link copied',
  'whatsapp' => 'WhatsApp',
  'back' => 'Back to news list',
  'related' => 'Other News',
];
