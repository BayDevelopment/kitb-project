<?php

return [
  'title' => '基础设施',
  'subtitle' => '管理公司工业园区基础设施信息。',
  'add' => '添加基础设施',

  'search_placeholder' => '搜索基础设施名称或描述（ID / EN / 中文）...',
  'clear_search' => '清除搜索',

  'data_title' => '基础设施数据',
  'data_subtitle' => '园区现有基础设施信息。',
  'data_count' => '条数据',

  'table' => [
    'number' => '序号',
    'infrastructure' => '基础设施',
    'description' => '描述',
    'order' => '排序',
    'status' => '状态',
    'actions' => '操作',
  ],

  'status' => [
    'active' => '启用',
    'inactive' => '停用',
    'infrastructure_active' => '基础设施已启用',
    'infrastructure_inactive' => '基础设施已停用',
    'click_to_change' => '点击更改',
  ],

  'actions' => [
    'view_detail' => '查看详情',
    'edit' => '编辑基础设施',
    'delete' => '删除基础设施',
    'move_up' => '上移',
    'move_down' => '下移',
  ],

  'pagination' => [
    'first' => '第一页',
    'previous' => '上一页',
    'next' => '下一页',
    'last' => '最后一页',
    'showing' => '显示',
    'of' => '共',
    'infrastructure' => '条基础设施',
  ],

  'empty' => [
    'not_found' => '未找到基础设施',
    'no_data' => '暂无基础设施',
    'search_description' => '没有找到符合搜索条件的数据。',
    'add_description' => '添加基础设施数据以开始管理园区信息。',
    'clear_search' => '清除搜索',
  ],

  'modal' => [
    'create_title' => '添加基础设施',
    'edit_title' => '编辑基础设施',
    'create_description' => '添加新的基础设施信息。',
    'edit_description' => '更新基础设施信息。',
    'detail_title' => '基础设施详情',
    'detail_description' => '园区基础设施的完整信息。',
    'delete_title' => '删除基础设施？',
    'delete_description' => '确定要删除',
    'delete_warning' => '所有翻译（ID、EN、中文）将一并删除，且无法恢复。',
    'close' => '关闭',
    'cancel' => '取消',
    'save' => '保存基础设施',
    'save_changes' => '保存更改',
    'saving' => '正在保存...',
    'deleting' => '正在删除...',
    'confirm_delete' => '确认删除',
  ],

  'form' => [
    'name' => '基础设施名称',
    'name_required' => '请填写基础设施名称（印度尼西亚语）。',
    'name_placeholder' => '例如：园区主干道',
    'name_en' => 'Infrastructure Name',
    'name_en_placeholder' => 'Example: Main Estate Road',
    'name_zh' => '基础设施名称',
    'name_zh_placeholder' => '例如：园区主干道',

    'slug' => 'Slug',
    'slug_placeholder' => '自动生成的 slug',
    'slug_help' => 'Slug 由系统根据印度尼西亚语名称自动生成，无法手动修改。如果该 slug 已被使用，最终 slug 可能会有所不同。',

    'status' => '状态',

    'order' => '排序',
    'order_placeholder' => '0',
    'order_help' => '留空或使用 0 以按照系统自动排序。',

    'description' => '描述',
    'description_languages' => 'Indonesia / English / 中文',
    'description_placeholder' => '请输入基础设施的完整描述...',
    'description_en_placeholder' => 'Write the English description of the infrastructure...',
    'description_zh_placeholder' => '请输入基础设施的中文描述...',
    'language_notice' => '请填写当前所选语言的名称和描述。',
    'translation_fallback' => '如果为空，公共页面将自动使用印度尼西亚语的名称和描述。',

    'image' => '基础设施图片',
    'image_preview' => '图片预览',
    'select_image' => '选择图片',
    'image_help' => 'JPG、JPEG、PNG、WEBP — 最大 2 MB',
    'remove_new_image' => '删除新图片',
    'remove_image' => '删除图片',
    'image_will_be_deleted' => '保存时将删除原有图片。',
    'no_image' => '暂无图片',
  ],

  'languages' => [
    'indonesia' => '🇮🇩 印度尼西亚语',
    'english' => '🇬🇧 英语',
    'chinese' => '🇨🇳 中文',
  ],

  'detail' => [
    'slug' => 'Slug',
    'order' => '排序',
    'description' => '描述',
  ],

  'loading' => '正在加载...',

  'errors' => [
    'image_format' => '图片必须为 JPG、JPEG、PNG 或 WEBP 格式。',
    'image_size' => '图片大小不能超过 2 MB。',
    'name_slug' => '名称必须包含字母或数字，以便生成 slug。',
  ],

  'public' => [
    'breadcrumb_home' => '首页',
    'breadcrumb_area' => '园区',
    'breadcrumb_current' => '基础设施',

    'hero_badge' => '园区基础设施',
    'hero_title' => '完善的配套基础设施',
    'hero_title_highlight' => '服务工业园区',
    'hero_description' => '一体化的基础设施，为园区内的工业运营、物流配送及入驻企业需求提供有力支持。',

    'section_label' => '基础设施',
    'available' => '项基础设施',
    'carousel_label' => '园区基础设施轮播',
    'navigation' => '基础设施导航',
    'previous' => '上一项基础设施',
    'next' => '下一项基础设施',
    'show_slide' => '查看：:name',

    'image_label' => '园区基础设施',
    'image_alt_fallback' => '园区基础设施',
    'image_preview' => '基础设施图片预览',
    'view_larger_image' => '查看大图',
    'close_preview' => '关闭预览',
    'supporting_facility' => '配套设施',

    'description_fallback' => '暂无基础设施描述。',

    'area' => '位置',
    'area_name' => '丹戎布顿工业园区 (KITB)',
    'facility' => '园区设施',
    'area_map' => '园区地图',
    'view_area_profile' => '查看园区简介',

    'empty_title' => '暂无基础设施',
    'empty_description' => '园区基础设施信息即将在此页面展示。',

    'integrated_title' => '一体化布局',
    'integrated_description' => '道路、电力、供水与物流系统相互衔接，共同保障工业活动。',
    'industry_title' => '工业就绪',
    'industry_description' => '基础设施可靠满足工业企业及入驻企业的运营需求。',
    'connected_title' => '战略互联',
    'connected_description' => '园区交通便利，保障车辆通行与货物配送顺畅。',

    'explore_label' => '探索园区',
    'explore_title' => '进一步了解我们的园区',
    'explore_description' => '浏览园区简介、设施与地图，了解园区的区位优势及现有基础设施。',
  ],
];
