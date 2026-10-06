<?php

return [
  'title' => 'Infrastructure',
  'subtitle' => 'Manage information about the company\'s industrial estate infrastructure.',
  'add' => 'Add Infrastructure',

  'search_placeholder' => 'Search infrastructure name or description (ID / EN / 中文)...',
  'clear_search' => 'Clear search',

  'data_title' => 'Infrastructure Data',
  'data_subtitle' => 'Infrastructure information available in the industrial estate.',
  'data_count' => 'data',

  'table' => [
    'number' => 'No.',
    'infrastructure' => 'Infrastructure',
    'description' => 'Description',
    'order' => 'Order',
    'status' => 'Status',
    'actions' => 'Actions',
  ],

  'status' => [
    'active' => 'Active',
    'inactive' => 'Inactive',
    'infrastructure_active' => 'Active Infrastructure',
    'infrastructure_inactive' => 'Inactive Infrastructure',
    'click_to_change' => 'Click to change',
  ],

  'actions' => [
    'view_detail' => 'View details',
    'edit' => 'Edit infrastructure',
    'delete' => 'Delete infrastructure',
    'move_up' => 'Move up',
    'move_down' => 'Move down',
  ],

  'pagination' => [
    'first' => 'First page',
    'previous' => 'Previous page',
    'next' => 'Next page',
    'last' => 'Last page',
    'showing' => 'Showing',
    'of' => 'of',
    'infrastructure' => 'infrastructure',
  ],

  'empty' => [
    'not_found' => 'Infrastructure not found',
    'no_data' => 'No infrastructure available',
    'search_description' => 'No data matching your search was found.',
    'add_description' => 'Add infrastructure data to start managing industrial estate information.',
    'clear_search' => 'Clear Search',
  ],

  'modal' => [
    'create_title' => 'Add Infrastructure',
    'edit_title' => 'Edit Infrastructure',
    'create_description' => 'Add new infrastructure information.',
    'edit_description' => 'Update infrastructure information.',
    'detail_title' => 'Infrastructure Details',
    'detail_description' => 'Complete information about the industrial estate infrastructure.',
    'delete_title' => 'Delete Infrastructure?',
    'delete_description' => 'Are you sure you want to delete',
    'delete_warning' => 'All translations (ID, EN, 中文) will be deleted and cannot be recovered.',
    'close' => 'Close',
    'cancel' => 'Cancel',
    'save' => 'Save Infrastructure',
    'save_changes' => 'Save Changes',
    'saving' => 'Saving...',
    'deleting' => 'Deleting...',
    'confirm_delete' => 'Yes, Delete',
  ],

  'form' => [
    'name' => 'Infrastructure Name',
    'name_required' => 'Infrastructure name (Indonesian) is required.',
    'name_placeholder' => 'Example: Main Estate Road',
    'name_en' => 'Infrastructure Name',
    'name_en_placeholder' => 'Example: Main Estate Road',
    'name_zh' => '基础设施名称',
    'name_zh_placeholder' => '例如：园区主干道',

    'slug' => 'Slug',
    'slug_placeholder' => 'auto-generated-slug',
    'slug_help' => 'The slug is automatically generated from the Indonesian name and cannot be changed manually. The final slug may differ if it is already in use.',

    'status' => 'Status',

    'order' => 'Order',
    'order_placeholder' => '0',
    'order_help' => 'Leave empty or use 0 to follow the automatic order.',

    'description' => 'Description',
    'description_languages' => 'Indonesia / English / 中文',
    'description_placeholder' => 'Write a complete description of the infrastructure...',
    'description_en_placeholder' => 'Write the English description of the infrastructure...',
    'description_zh_placeholder' => '请输入基础设施的中文描述...',
    'language_notice' => 'Fill in the name and description for the selected language.',
    'translation_fallback' => 'If left empty, the public page will automatically use the Indonesian name and description.',

    'image' => 'Infrastructure Image',
    'image_preview' => 'Image preview',
    'select_image' => 'Choose image',
    'image_help' => 'JPG, JPEG, PNG, WEBP — maximum 2 MB',
    'remove_new_image' => 'Remove new image',
    'remove_image' => 'Remove image',
    'image_will_be_deleted' => 'The existing image will be deleted when saved.',
    'no_image' => 'No image',
  ],

  'languages' => [
    'indonesia' => '🇮🇩 Indonesia',
    'english' => '🇬🇧 English',
    'chinese' => '🇨🇳 中文',
  ],

  'detail' => [
    'slug' => 'Slug',
    'order' => 'Order',
    'description' => 'Description',
  ],

  'loading' => 'Loading...',

  'errors' => [
    'image_format' => 'Image must be in JPG, JPEG, PNG, or WEBP format.',
    'image_size' => 'Image size must not exceed 2 MB.',
    'name_slug' => 'The name must contain letters or numbers so that a slug can be generated.',
  ],

  'public' => [
    'breadcrumb_home' => 'Home',
    'breadcrumb_area' => 'Industrial Estate',
    'breadcrumb_current' => 'Infrastructure',

    'hero_badge' => 'Estate Infrastructure',
    'hero_title' => 'Supporting Infrastructure for the',
    'hero_title_highlight' => 'Industrial Estate',
    'hero_description' => 'Integrated infrastructure that supports industrial operations, logistics distribution, and tenant needs within the estate.',

    'section_label' => 'Infrastructure',
    'available' => 'infrastructure items available',
    'carousel_label' => 'Estate infrastructure carousel',
    'navigation' => 'Infrastructure navigation',
    'previous' => 'Previous infrastructure',
    'next' => 'Next infrastructure',
    'show_slide' => 'Show :name',

    'image_label' => 'Estate Infrastructure',
    'image_alt_fallback' => 'Estate infrastructure',
    'image_preview' => 'Infrastructure image preview',
    'view_larger_image' => 'View larger image',
    'close_preview' => 'Close preview',
    'supporting_facility' => 'Supporting Facility',

    'description_fallback' => 'The infrastructure description is not available yet.',

    'area' => 'Location',
    'area_name' => 'Tanjung Buton Industrial Estate (KITB)',
    'facility' => 'Estate Facilities',
    'area_map' => 'Estate Map',
    'view_area_profile' => 'View Estate Profile',

    'empty_title' => 'No infrastructure available',
    'empty_description' => 'Estate infrastructure information will be displayed on this page soon.',

    'integrated_title' => 'Integrated',
    'integrated_description' => 'Roads, power, water, and logistics are designed to work together to support industrial activities.',
    'industry_title' => 'Industry Ready',
    'industry_description' => 'Infrastructure is prepared to reliably meet the operational needs of industries and tenants.',
    'connected_title' => 'Strategically Connected',
    'connected_description' => 'Estate access supports smooth vehicle mobility and goods distribution.',

    'explore_label' => 'Explore the Estate',
    'explore_title' => 'Get to know our estate better',
    'explore_description' => 'Browse the estate profile, facilities, and map to understand the advantages of the location and the infrastructure available.',
  ],
];
