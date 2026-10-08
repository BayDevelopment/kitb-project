<?php

// Halaman Karier: Index, Detail, dan Lamaran.

return [
  'index' => [
    'seo' => [
      'title' => 'Careers | KITB',
      'description' => 'Discover career opportunities and join PT Kawasan Industri Tanjung Buton.',
    ],

    'hero' => [
      'badge' => 'Career Opportunities at KITB',
      'title_prefix' => 'Build Your Future',
      'title_highlight' => 'With KITB',
      'description' => 'Find opportunities to grow, collaborate, and make a real contribution to building a sustainable industrial estate.',
    ],

    'stats' => [
      'positions' => ':count Open Positions',
      'environment' => 'Professional Environment',
    ],

    'filter' => [
      'region_label' => 'Job search and filters',
      'search_label' => 'Search jobs',
      'search_placeholder' => 'Search position, department, or location...',
      'department_label' => 'Filter by department',
      'all_departments' => 'All Departments',
      'type_label' => 'Filter by job type',
      'all_types' => 'All Types',
      'reset' => 'Reset',
      'reset_sr' => 'search and filters',
      'found' => ':count position(s) found.',
    ],

    'list' => [
      'label' => 'Job listings',
      'deadline_soon' => 'Closing Soon',
      'deadline' => 'Application deadline: :date',
      'view_detail' => 'View Position Details',
      'default_description' => 'Find opportunities to grow and contribute together with KITB.',
    ],

    'empty' => [
      'not_found_title' => 'No positions found',
      'not_found_description' => 'No positions match your search or the filters you selected.',
      'title' => 'No openings yet',
      'description' => 'There are no open positions at the moment. Please check back later.',
      'reset_filter' => 'Reset Filters',
    ],

    'pagination' => [
      'showing' => 'Showing :from-:to of :total positions',
      'label' => 'Job listing pagination',
      'previous' => 'Previous page',
      'next' => 'Next page',
      'page' => 'Page :n',
      'page_current' => 'Page :n, current',
    ],

    'cta' => [
      'title' => 'Can\'t find a suitable position?',
      'description' => 'Check the KITB careers page regularly for the latest opportunities and openings.',
      'button' => 'View All Jobs',
    ],

  ],

  'types' => [
    'full_time' => 'Full Time',
    'part_time' => 'Part Time',
    'contract' => 'Contract',
    'internship' => 'Internship',
    'freelance' => 'Freelance',
    'remote' => 'Remote',
    'default' => 'Job',
  ],

  'detail' => [
    'seo' => [
      'title' => ':title - KITB Careers',
      'description' => 'Job information for :title at PT Kawasan Industri Tanjung Buton (KITB).',
      'og_description' => 'View the details of the :title position and career opportunities at KITB.',
    ],

    'navigation' => [
      'back' => 'Back to Careers',
    ],

    'badges' => [
      'featured' => 'Featured Position',
    ],

    'status' => [
      'unavailable' => 'Unavailable',
      'unavailable_description' => 'This position is not yet available to applicants.',
      'closed' => 'Applications closed',
      'closed_description' => 'The application period for this position has ended.',
      'open' => 'Applications open',
      'open_description' => 'This position is currently accepting applications.',
    ],

    'hero' => [
      'prefix' => 'Join',
      'company' => 'PT Kawasan Industri Tanjung Buton (KITB)',
      'suffix' => 'to grow, collaborate, and make a real contribution to developing a sustainable industrial estate.',
    ],

    'meta' => [
      'department' => 'Department',
      'location' => 'Location',
      'start' => 'Start',
      'deadline' => 'Application Deadline',
      'job_type_fallback' => 'Job type',
    ],

    'summary' => [
      'start_registration' => 'Registration Opens',
      'deadline_registration' => 'Registration Deadline',
      'job_type' => 'Job Type',
      'eyebrow' => 'Job Overview',
      'title' => 'Job Summary',
      'description' => 'Key information about the position you are viewing.',
    ],

    'sections' => [
      'about' => [
        'eyebrow' => 'About the Role',
        'title' => 'Job Description',
      ],

      'responsibilities' => [
        'eyebrow' => 'Role & Responsibilities',
        'title' => 'Responsibilities',
      ],

      'qualifications' => [
        'eyebrow' => 'Requirements',
        'title' => 'Qualifications',
      ],

      'benefits' => [
        'eyebrow' => 'What You Get',
        'title' => 'Benefits',
      ],

    ],

    'empty' => [
      'title' => 'Information not available yet',
      'description' => 'Details for this position have not been completed. Please return to the careers page to see other positions.',
    ],

    'apply' => [
      'now' => 'Apply Now',
      'closed' => 'Applications Closed',
      'note' => 'Make sure your CV and any required supporting documents are ready before applying.',
    ],

    'explore' => [
      'eyebrow' => 'Explore',
      'label' => 'View other positions',
    ],

    'cta' => [
      'eyebrow' => 'Grow With Us',
      'title' => 'Ready to grow with KITB?',
      'description' => 'Discover more career opportunities and become part of the development of the Tanjung Buton industrial estate.',
      'button' => 'View All Jobs',
    ],

  ],

  'apply' => [
    'seo' => [
      'title' => 'Apply for :title | KITB Careers',
      'description' => 'Apply for the :title position at PT Kawasan Industri Tanjung Buton (KITB).',
    ],

    'navigation' => [
      'back' => 'Back to Job Details',
    ],

    'hero' => [
      'badge' => 'Job Application',
      'description' => 'Complete your personal information and documents to apply for this position.',
    ],

    'form' => [
      'label' => 'Candidate Form',
      'title' => 'Application Details',
      'description' => 'Please provide accurate and up-to-date information.',
      'required' => 'Required',
      'optional' => 'Optional',
    ],

    'personal' => [
      'title' => 'Personal Information',
      'description' => 'Main information for recruitment communication.',
      'full_name' => 'Full Name',
      'full_name_placeholder' => 'Enter your full name',
      'email' => 'Email',
      'email_placeholder' => 'name@email.com',
      'phone' => 'Phone Number',
      'phone_placeholder' => '08xxxxxxxxxx',
    ],

    'documents' => [
      'title' => 'Application Documents',
      'description' => 'Upload documents in the specified format.',
      'cv' => 'CV / Curriculum Vitae',
      'choose_cv' => 'Choose CV file',
      'choose_letter' => 'Choose cover letter',
      'letter' => 'Cover Letter',
      'max_file' => 'PDF, DOC, or DOCX · Max. 1 MB',
      'remove_cv' => 'Remove CV',
      'remove_letter' => 'Remove cover letter',
      'tip' => 'Make sure the uploaded documents are the latest version, readable, and within the maximum file size.',
    ],

    'profile' => [
      'title' => 'Professional Profile',
      'description' => 'Add professional links if available.',
      'linkedin' => 'LinkedIn',
      'linkedin_placeholder' => 'https://linkedin.com/in/name',
      'portfolio' => 'Portfolio',
      'portfolio_placeholder' => 'https://website.com',
    ],

    'message' => [
      'title' => 'Message for Recruitment Team',
      'max' => 'Max. 2,000 characters / language',
      'placeholder_id' => 'Tuliskan pesan singkat atau informasi tambahan yang relevan dengan lamaran Anda...',
      'placeholder_en' => 'Write a brief message or additional information relevant to your application...',
      'placeholder_zh' => '请填写与您的申请相关的简短留言或补充信息...',
    ],

    'submit' => [
      'review_title' => 'Review your information',
      'review_description' => 'Make sure your email, phone number, and documents are correct before submitting.',
      'sending' => 'Sending...',
      'button' => 'Submit Application',
    ],

    'sidebar' => [
      'position' => 'Position Applied For',
      'department' => 'Department',
      'location' => 'Location',
      'job_type' => 'Job Type',
      'start' => 'Start',
      'deadline' => 'Application Deadline',
      'view_detail' => 'View job details',
    ],

    'tips' => [
      'title' => 'Before You Submit',
      'item_1' => 'Make sure the personal information provided is correct.',
      'item_2' => 'Use an up-to-date CV with easy-to-read information.',
      'item_3' => 'Make sure your email and phone number can be reached.',
      'item_4' => 'Review your documents before clicking submit.',
    ],

    'privacy' => [
      'title' => 'Data Information',
      'description' => 'The data and documents you submit are used for the recruitment process for this position.',
    ],

    'success' => [
      'title' => 'Application Submitted Successfully',
      'description' => 'Thank you for applying for the :title position. Your data and documents have been received successfully.',
      'close' => 'Close',
      'other_jobs' => 'View Other Jobs',
    ],

    'validation' => [
      'cv_required' => 'CV is required.',
      'file_type' => 'File must be PDF, DOC, or DOCX.',
      'file_size' => 'Maximum file size is 1 MB.',
    ],

  ],
];
