<?php

/**
 * Comments wrapper
 */

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

//@codingStandardsIgnoreStart
if (!function_exists('vcv_theme_builder_comments_list')) :
    /**
     * Comment
     *
     * @param string $comment Comment data.
     * @param array $args Args.
     * @param integer $depth Depth.
     */
    function vcv_theme_builder_comments_list($comment, $args, $depth)
    {
        if ('div' === $args['style']) {
            $tag = 'div';
            $add_below = 'comment';
        } else {
            $tag = 'li';
            $add_below = 'div-comment';
        }
        ?>
        <<?php echo esc_html($tag); ?><?php comment_class(
        empty($args['has_children']) ? '' : 'parent'
    ); ?> id="comment-<?php comment_ID(); ?>">
        <?php if ('div' !== $args['style']) : ?>
        <div id="div-comment-<?php comment_ID(); ?>" class="vcv-comment-body">
    <?php endif; ?>
        <div class="vcv-author-avatar">
            <div class="vcv-fade-in-image">
                <?php if (0 !== $args['avatar_size']) : ?>
                    <img src="<?php echo esc_url(
                        get_avatar_url(
                            $comment,
                            [
                                'size' => $args['avatar_size'],
                            ]
                        )
                    ); ?>"
                            data-src="<?php echo esc_url(
                                get_avatar_url(
                                    $comment,
                                    [
                                        'size' => $args['avatar_size'],
                                    ]
                                )
                            ); ?>">
                    <noscript>
                        <img src="<?php echo esc_url(
                            get_avatar_url(
                                $comment,
                                [
                                    'size' => $args['avatar_size'],
                                ]
                            )
                        ); ?>">
                    </noscript>
                <?php endif; ?>
            </div>
        </div>
        <div class="vcv-comment-wrapper">
            <footer class="vcv-comment-meta">
                <div class="vcv-comment-author">
                    <?php
                    /* translators: 1: comment author, 2: span opening tag, 3. span closing tag */
                    printf(
                        esc_html__('%1$s %2$s %3$s', 'visualcomposer'),
                        '<cite>' . get_comment_author_link() . '</cite>',
                        null,
                        null
                    ); ?>
                </div>
                <div class="vcv-comment-metadata">
                    <a href="<?php echo esc_url(get_comment_link($comment->comment_ID)); ?>">
                        <?php
                        /* translators: 1: date, 2: time */
                        printf(
                            esc_html__('On %1$s at %2$s', 'visualcomposer'),
                            get_comment_date(),
                            get_comment_time()
                        ); ?>
                    </a>
                    <?php edit_comment_link(esc_html__('(Edit)', 'visualcomposer'), '  ', ''); ?>
                    <?php if ('0' === $comment->comment_approved) : ?>
                        <em class="vcv-comment-awaiting-moderation"><?php esc_html_e(
                                'The comment is awaiting moderation.',
                                'visualcomposer'
                            ); ?></em>
                    <?php endif; ?>
                </div>
            </footer>
            <div class="vcv-comment-content">
                <?php comment_text(); ?>
            </div>
            <div class="vcv-comment-reply">
                <?php comment_reply_link(
                    array_merge(
                        $args,
                        [
                            'add_below' => $add_below,
                            'depth' => $depth,
                            'max_depth' => $args['max_depth'],
                        ]
                    )
                ); ?>
            </div>
        </div>

        <?php if ('div' !== $args['style']) : ?>
        </div>
    <?php endif; ?>
        </<?php echo esc_html($tag); ?>>
        <?php
    }
endif;

if (post_password_required()) {
    return;
}

ob_start();
?>
<?php if (get_post() && (comments_open() || get_comments_number()) && have_comments()) : ?>
    <h3 class="vcv-comments-title">
        <?php comments_number(
            esc_html__('No Comments', 'visualcomposer'),
            esc_html__('One Comment', 'visualcomposer'),
            esc_html__('% Comments', 'visualcomposer')
        ) ?>
    </h3>
    <p class="vcv-comments-subtitle"><?php echo esc_html__(
            'Join the discussion and tell us your opinion.',
            'visualcomposer'
        ); ?></p>

    <?php the_comments_navigation(); ?>

    <ol class="vcv-comments-list">
        <?php wp_list_comments(
            [
                'callback' => 'vcv_theme_builder_comments_list',
                'reply_text' => esc_html__('Reply', 'visualcomposer'),
                'avatar_size' => 80,
                'style' => 'ol',
            ]
        ); ?>
    </ol><!-- .comment-list -->

    <?php the_comments_navigation(); ?>

<?php endif; // Check for have_comments(). ?>

<?php
// If comments are closed and there are comments, let's leave a little note, shall we?
$disabledCommentsPlaceholder = false;
if (!comments_open()) :
    if (get_comments_number() && post_type_supports(get_post_type(), 'comments')) :
        ?>
        <p class="vcv-no-comments"><?php esc_html_e('Comments are closed.', 'visualcomposer'); ?></p>
    <?php elseif (vchelper('Frontend')->isPageEditable()) : ?>
        <?php
        $disabledCommentsPlaceholder = true;
        ?>
        <div class="vcv-no-comments">
            <div class='vce-layouts-wp-comments-area-placeholder'>
                <svg class='vcv-placeholder-wp-logo' xmlns='http://www.w3.org/2000/svg' width='100px' height='100px' viewBox='0 0 100 100' version='1.1'>
                    <g id='Page-1' stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'>
                        <g id='content-area-placeholder' transform='translate(-273.000000, -163.000000)' fill='#363636' fill-rule='nonzero'>
                            <g id='wordpress' transform='translate(273.000000, 163.000000)'>
                                <path d='M29.6941966,30.6088117 L24.2181507,30.6088117 L39.1473641,73 L47.5051895,49.0087871 L41.0252832,30.6088117 L35.2475476,30.6088117 L35.2475476,26.5695214 L60.8331435,26.5695214 L60.8331435,30.6088117 L54.8416556,30.6088117 L69.7706474,73 L75.1370483,57.5953384 C82.1766977,37.8590197 69.3672869,31.7110837 69.3672869,27.306446 C69.3672869,22.9018084 72.9219986,19.3313285 77.3066895,19.3313285 C77.5439213,19.3313285 77.7742864,19.3442336 78,19.3653713 C70.5705019,12.3203096 60.5524967,8 49.5290804,8 C35.1252769,8 22.4376938,15.3750312 15,26.5695214 L29.6937536,26.5695214 L29.6937536,30.6088117 L29.6941966,30.6088117 L29.6941966,30.6088117 Z' id='Path' />
                                <path d='M8,50.2960376 C8,66.2023154 16.9125177,80.0184874 30,87 L11.3089674,34 C9.17979956,39.003654 8,44.5115332 8,50.2960376 Z' id='Path' />
                                <path d='M86.6159225,29 C87.2097203,32.5512272 87.0299629,36.5425868 86.1187636,40.5494805 L86.2838922,40.5494805 L85.656182,42.371246 L85.656182,42.371246 C85.2842549,43.6866904 84.8159104,45.0323025 84.2839526,46.3545009 L70,87 C83.0899291,79.9061007 92,65.899533 92,49.7771779 C92,42.222434 90.0419521,35.1328122 86.6159225,29 Z' id='Path' />
                                <path d='M37,89.9939809 C41.0254069,91.2945503 45.3191742,92 49.7790905,92 C54.0330351,92 58.1367599,91.359645 62,90.1718328 L49.8797533,56 L37,89.9939809 Z' id='Path' />
                                <path d='M85.3551915,14.6446178 C75.9114892,5.2008944 63.355254,0 49.9997766,0 C36.6442992,0 24.0882875,5.2008944 14.6445851,14.6446178 C5.20088279,24.0883413 0,36.6443811 0,50.0001117 C0,63.3556189 5.20088279,75.9116587 14.6445851,85.3553822 C24.0882875,94.7991056 36.6445226,100 50,100 C63.3554774,100 75.9114892,94.7991056 85.3554149,85.3553822 C94.7991172,75.9116587 100,63.3553956 100,50.0001117 C100,36.6443811 94.7988938,24.0883413 85.3551915,14.6446178 Z M49.9997766,96.4728719 C24.3746566,96.4728719 3.52712018,75.625289 3.52712018,50.0001117 C3.52712018,24.3749344 24.3746566,3.52712806 49.9997766,3.52712806 C75.6248967,3.52712806 96.4724331,24.3749344 96.4724331,50.0001117 C96.4724331,75.625289 75.6251201,96.4728719 49.9997766,96.4728719 Z' id='Shape' />
                            </g>
                        </g>
                    </g>
                </svg>
                <p class='vcv-placeholder-description'> <?php esc_html_e(
                        'Comment area will not be displayed as comments are disabled for this post. Make sure to enable comments.',
                        'visualcomposer'
                    ); ?></p>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
if (get_comments_number()) {
    comment_form(
        [
            'title_reply_before' => '<h2 id="reply-title" class="vcv-comment-reply-title">',
            'title_reply_after' => '</h2>',
            'title_reply' => esc_html__('Leave A Comment', 'visualcomposer'),
        ]
    );
} else {
    comment_form(
        [
            'title_reply_before' => '<h2 id="reply-title" class="vcv-comment-reply-title">',
            'title_reply_after' => '</h2>',
            'title_reply' => esc_html__('Share Your Thoughts', 'visualcomposer'),
        ]
    );
}

$content = trim(ob_get_clean());
if (!empty($content)) :
    if($disabledCommentsPlaceholder) : echo $content;
    else :
    ?>
    <div id="vcv-comments" class="vcv-comments-area">
        <div class="vcv-comments-container">
            <?php echo $content; ?>
        </div><!-- .container -->
    </div><!-- .vcv-comments-area#comments .vcv-comments-container -->
    <?php endif; ?>
<?php
endif;
