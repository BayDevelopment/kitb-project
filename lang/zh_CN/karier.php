<?php

// Halaman Karier: Index, Detail, dan Lamaran.

return [
  'index' => [
    'seo' => [
      'title' => '招聘 | KITB',
      'description' => '探索职业机会，加入 PT Kawasan Industri Tanjung Buton。',
    ],

    'hero' => [
      'badge' => 'KITB 职业机会',
      'title_prefix' => '共创未来',
      'title_highlight' => '加入 KITB',
      'description' => '在这里寻找成长、协作的机会，为建设可持续发展的工业园区做出真正的贡献。',
    ],

    'stats' => [
      'positions' => ':count 个在招职位',
      'environment' => '专业的工作环境',
    ],

    'filter' => [
      'region_label' => '职位搜索和筛选',
      'search_label' => '搜索职位',
      'search_placeholder' => '搜索职位、部门或地点...',
      'department_label' => '按部门筛选',
      'all_departments' => '所有部门',
      'type_label' => '按工作类型筛选',
      'all_types' => '所有类型',
      'reset' => '重置',
      'reset_sr' => '搜索和筛选',
      'found' => '共找到 :count 个职位。',
    ],

    'list' => [
      'label' => '职位列表',
      'deadline_soon' => '即将截止',
      'deadline' => '申请截止日期：:date',
      'view_detail' => '查看职位详情',
      'default_description' => '在 KITB 寻找成长与贡献的机会。',
    ],

    'empty' => [
      'not_found_title' => '未找到职位',
      'not_found_description' => '没有符合您搜索或筛选条件的职位。',
      'title' => '暂无职位',
      'description' => '目前没有开放的职位，请稍后再来查看。',
      'reset_filter' => '重置筛选',
    ],

    'pagination' => [
      'showing' => '显示第 :from-:to 个，共 :total 个职位',
      'label' => '职位列表分页',
      'previous' => '上一页',
      'next' => '下一页',
      'page' => '第 :n 页',
      'page_current' => '第 :n 页（当前）',
    ],

    'cta' => [
      'title' => '没有找到合适的职位？',
      'description' => '请定期关注 KITB 招聘页面，获取最新的机会和职位信息。',
      'button' => '查看所有职位',
    ],

  ],

  'types' => [
    'full_time' => '全职',
    'part_time' => '兼职',
    'contract' => '合同制',
    'internship' => '实习',
    'freelance' => '自由职业',
    'remote' => '远程',
    'default' => '职位',
  ],

  'detail' => [
    'seo' => [
      'title' => ':title - KITB 招聘',
      'description' => 'PT Kawasan Industri Tanjung Buton (KITB) 的 :title 职位信息。',
      'og_description' => '查看 :title 职位详情及 KITB 的职业发展机会。',
    ],

    'navigation' => [
      'back' => '返回招聘',
    ],

    'badges' => [
      'featured' => '重点职位',
    ],

    'status' => [
      'unavailable' => '暂不可用',
      'unavailable_description' => '该职位目前尚未向申请人开放。',
      'closed' => '申请已截止',
      'closed_description' => '该职位的申请期限已结束。',
      'open' => '接受申请中',
      'open_description' => '该职位目前正在接受申请。',
    ],

    'hero' => [
      'prefix' => '加入',
      'company' => 'PT Kawasan Industri Tanjung Buton (KITB)',
      'suffix' => '，在成长与协作中，为可持续工业园区的发展做出真正的贡献。',
    ],

    'meta' => [
      'department' => '部门',
      'location' => '地点',
      'start' => '开始',
      'deadline' => '申请截止日期',
      'job_type_fallback' => '工作类型',
    ],

    'summary' => [
      'start_registration' => '开始报名',
      'deadline_registration' => '报名截止',
      'job_type' => '工作类型',
      'eyebrow' => '职位概览',
      'title' => '职位摘要',
      'description' => '您正在查看的职位的重要信息。',
    ],

    'sections' => [
      'about' => [
        'eyebrow' => '关于职位',
        'title' => '职位描述',
      ],

      'responsibilities' => [
        'eyebrow' => '角色与职责',
        'title' => '岗位职责',
      ],

      'qualifications' => [
        'eyebrow' => '任职要求',
        'title' => '资格条件',
      ],

      'benefits' => [
        'eyebrow' => '您将获得',
        'title' => '福利待遇',
      ],

    ],

    'empty' => [
      'title' => '暂无信息',
      'description' => '该职位的详细信息尚未完善。请返回招聘页面查看其他职位。',
    ],

    'apply' => [
      'now' => '立即申请',
      'closed' => '申请已截止',
      'note' => '申请前请准备好简历及所需的支持文件。',
    ],

    'explore' => [
      'eyebrow' => '探索',
      'label' => '查看其他职位',
    ],

    'cta' => [
      'eyebrow' => '与我们共同成长',
      'title' => '准备好与 KITB 一起成长了吗？',
      'description' => '探索更多职业机会，成为 Tanjung Buton 工业园区发展历程的一部分。',
      'button' => '查看所有职位',
    ],

  ],

  'apply' => [
    'seo' => [
      'title' => '申请 :title | KITB 招聘',
      'description' => '申请 PT Kawasan Industri Tanjung Buton (KITB) 的 :title 职位。',
    ],

    'navigation' => [
      'back' => '返回职位详情',
    ],

    'hero' => [
      'badge' => '职位申请',
      'description' => '请填写您的个人信息和申请文件，以申请该职位。',
    ],

    'form' => [
      'label' => '候选人表单',
      'title' => '申请信息',
      'description' => '请提供准确且最新的信息。',
      'required' => '必填',
      'optional' => '可选',
    ],

    'personal' => [
      'title' => '个人信息',
      'description' => '用于招聘沟通的主要信息。',
      'full_name' => '姓名',
      'full_name_placeholder' => '请输入您的姓名',
      'email' => '电子邮箱',
      'email_placeholder' => 'name@email.com',
      'phone' => '电话号码',
      'phone_placeholder' => '08xxxxxxxxxx',
    ],

    'documents' => [
      'title' => '申请文件',
      'description' => '请按照指定格式上传文件。',
      'cv' => '简历 / CV',
      'choose_cv' => '选择简历文件',
      'choose_letter' => '选择求职信',
      'letter' => '求职信',
      'max_file' => 'PDF、DOC 或 DOCX · 最大 1 MB',
      'remove_cv' => '删除简历',
      'remove_letter' => '删除求职信',
      'tip' => '请确保上传的文件为最新版本、清晰可读，并且不超过规定的最大文件大小。',
    ],

    'profile' => [
      'title' => '职业资料',
      'description' => '如有专业链接，请添加。',
      'linkedin' => 'LinkedIn',
      'linkedin_placeholder' => 'https://linkedin.com/in/name',
      'portfolio' => '作品集',
      'portfolio_placeholder' => 'https://website.com',
    ],

    'message' => [
      'title' => '给招聘团队的留言',
      'max' => '每种语言最多 2,000 个字符',
      'placeholder_id' => 'Tuliskan pesan singkat atau informasi tambahan yang relevan dengan lamaran Anda...',
      'placeholder_en' => 'Write a brief message or additional information relevant to your application...',
      'placeholder_zh' => '请填写与您的申请相关的简短留言或补充信息...',
    ],

    'submit' => [
      'review_title' => '请再次检查您的信息',
      'review_description' => '提交前请确认电子邮箱、电话号码和文件信息正确无误。',
      'sending' => '正在发送...',
      'button' => '提交申请',
    ],

    'sidebar' => [
      'position' => '申请职位',
      'department' => '部门',
      'location' => '地点',
      'job_type' => '工作类型',
      'start' => '开始',
      'deadline' => '申请截止日期',
      'view_detail' => '查看职位详情',
    ],

    'tips' => [
      'title' => '提交前须知',
      'item_1' => '请确认填写的个人信息准确无误。',
      'item_2' => '请使用最新且信息清晰易读的简历。',
      'item_3' => '请确保电子邮箱和电话号码可以联系到您。',
      'item_4' => '点击提交前请再次检查申请文件。',
    ],

    'privacy' => [
      'title' => '数据说明',
      'description' => '您提交的数据和文件仅用于该职位的招聘流程。',
    ],

    'success' => [
      'title' => '申请提交成功',
      'description' => '感谢您申请 :title 职位。您的信息和文件已成功收到。',
      'close' => '关闭',
      'other_jobs' => '查看其他职位',
    ],

    'validation' => [
      'cv_required' => '必须上传简历。',
      'file_type' => '文件必须为 PDF、DOC 或 DOCX 格式。',
      'file_size' => '文件大小最大为 1 MB。',
    ],

  ],
];
