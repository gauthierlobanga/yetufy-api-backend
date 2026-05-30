<?php

// config/comments.php

return [
    /*
     * Auto-approve comments (no moderation)
     */
    'auto_approve' => env('COMMENTS_AUTO_APPROVE', false),

    /*
     * Maximum nesting level for replies
     */
    'max_nesting_level' => env('COMMENTS_MAX_NESTING_LEVEL', 3),

    /*
     * Comment editing time limit in minutes (null = unlimited)
     */
    'edit_time_limit' => env('COMMENTS_EDIT_TIME_LIMIT', 30),

    /*
     * Spam detection
     */
    'spam' => [
        'enabled' => env('COMMENTS_SPAM_DETECTION', true),
        'keywords' => [
            'viagra', 'casino', 'porn', 'xxx', 'spam',
        ],
        'max_links' => 2,
    ],

    /*
     * Profanity filter
     */
    'profanity' => [
        'enabled' => env('COMMENTS_PROFANITY_FILTER', true),
        'words' => [
            // Liste de mots interdits
        ],
        'replacement' => '***',
    ],

    /*
     * Notifications
     */
    'notifications' => [
        'new_comment' => true,
        'comment_approved' => true,
        'user_mentioned' => true,
    ],
];
