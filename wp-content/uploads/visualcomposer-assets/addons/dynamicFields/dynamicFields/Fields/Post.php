<?php

namespace dynamicFields\dynamicFields\Fields;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once 'FieldResponse.php';

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Frontend;
use VisualComposer\Helpers\PostData;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class Post
 * @package dynamicFields\dynamicFields\Fields
 */
class Post extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;
    use FieldResponse;

    /**
     * Post constructor.
     */
    public function __construct()
    {
        $this->addFilter('vcv:editor:data:postFields', 'addPostTitle');
        $this->addFilter('vcv:editor:data:postFields', 'addPostId');
        $this->addFilter('vcv:editor:data:postFields', 'addPostAuthor');
        $this->addFilter('vcv:editor:data:postFields', 'addPostAuthorLink');
        $this->addFilter('vcv:editor:data:postFields', 'addPostAuthorBio');
        $this->addFilter('vcv:editor:data:postFields', 'addPostExcerpt');
        $this->addFilter('vcv:editor:data:postFields', 'addPostType');
        $this->addFilter('vcv:editor:data:postFields', 'addPostCommentCount');
        $this->addFilter('vcv:editor:data:postFields', 'addPostDate');
        $this->addFilter('vcv:editor:data:postFields', 'addPostModifyDate');
        $this->addFilter('vcv:editor:data:postFields', 'addPostParentName');
        $this->addFilter('vcv:editor:data:postFields', 'addPostCategories');
        $this->addFilter('vcv:editor:data:postFields', 'addPostTags');
        $this->addFilter('vcv:editor:data:postFields', 'addPostUrl');
        $this->addFilter('vcv:editor:data:postFields', 'addCommentUrl');

        $this->addFilter('vcv:dynamic:value:post_author', 'postAuthor');
        $this->addFilter('vcv:dynamic:value:post_author_link', 'postAuthorLink');
        $this->addFilter('vcv:dynamic:value:post_author_bio', 'postAuthorBio');
        $this->addFilter('vcv:dynamic:value:post_type', 'postType');
        $this->addFilter('vcv:dynamic:value:post_excerpt', 'postExcerpt');
        $this->addFilter('vcv:dynamic:value:post_id', 'postId');
        $this->addFilter('vcv:dynamic:value:post_title', 'postTitle');
        $this->addFilter('vcv:dynamic:value:post_comment_count', 'postCommentCount');
        $this->addFilter('vcv:dynamic:value:post_date', 'postDate');
        $this->addFilter('vcv:dynamic:value:post_modify_date', 'postModifyDate');
        $this->addFilter('vcv:dynamic:value:post_parent_name', 'postParentName');
        $this->addFilter('vcv:dynamic:value:post_categories', 'postCategories');
        $this->addFilter('vcv:dynamic:value:post_tags', 'postTags');
        $this->addFilter('vcv:dynamic:value:post_url', 'postUrl');
        $this->addFilter('vcv:dynamic:value:comment_url', 'commentUrl');
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostAuthor($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            || (
                //@codingStandardsIgnoreLine
                isset($post) && post_type_supports($post->post_type, 'author')
                //@codingStandardsIgnoreLine
                && $post->post_status !== 'trash'
            )
        ) {
            $response['string']['author']['group']['values'][] = [
                'value' => 'post_author',
                'label' => str_replace('&#039;', '\'', esc_html__('Author\'s Name', 'visualcomposer')),
            ];

            $response['htmleditor']['author']['group']['values'][] = [
                'value' => 'post_author',
                'label' => str_replace('&#039;', '\'', esc_html__('Author\'s Name', 'visualcomposer')),
            ];

            $response['inputSelect']['author']['group']['values'][] = [
                'value' => 'post_author',
                'label' => str_replace('&#039;', '\'', esc_html__('Author\'s Name', 'visualcomposer')),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostAuthorLink($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            || (
                //@codingStandardsIgnoreLine
                isset($post) && post_type_supports($post->post_type, 'author')
                //@codingStandardsIgnoreLine
                && $post->post_status !== 'trash'
            )
        ) {
            $response['string']['author']['group']['values'][] = [
                'value' => 'post_author_link',
                'label' => str_replace('&#039;', '\'', esc_html__('Author\'s Link', 'visualcomposer')),
            ];

            $response['htmleditor']['author']['group']['values'][] = [
                'value' => 'post_author_link',
                'label' => str_replace('&#039;', '\'', esc_html__('Author\'s Link', 'visualcomposer')),
            ];

            $response['inputSelect']['author']['group']['values'][] = [
                'value' => 'post_author_link',
                'label' => str_replace('&#039;', '\'', esc_html__('Author\'s Link', 'visualcomposer')),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostAuthorBio($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            || (
                //@codingStandardsIgnoreLine
                isset($post) && post_type_supports($post->post_type, 'author')
                //@codingStandardsIgnoreLine
                && $post->post_status !== 'trash'
            )
        ) {
            $response['string']['author']['group']['values'][] = [
                'value' => 'post_author_bio',
                'label' => str_replace('&#039;', '\'', esc_html__('Author\'s Bio', 'visualcomposer')),
            ];
            $response['htmleditor']['author']['group']['values'][] = [
                'value' => 'post_author_bio',
                'label' => str_replace('&#039;', '\'', esc_html__('Author\'s Bio', 'visualcomposer')),
            ];
            $response['inputSelect']['author']['group']['values'][] = [
                'value' => 'post_author_bio',
                'label' => str_replace('&#039;', '\'', esc_html__('Author\'s Bio', 'visualcomposer')),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostCommentCount($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            || (
                //@codingStandardsIgnoreLine
                isset($post) && post_type_supports($post->post_type, 'comments')
                //@codingStandardsIgnoreLine
                && $post->post_status !== 'trash'
            )
        ) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'post_comment_count',
                'label' => esc_html__('Post Comment Count', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'post_comment_count',
                'label' => esc_html__('Post Comment Count', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'post_comment_count',
                'label' => esc_html__('Post Comment Count', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostTitle($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            || (
                //@codingStandardsIgnoreLine
                isset($post) && post_type_supports($post->post_type, 'title')
                //@codingStandardsIgnoreLine
                && $post->post_status !== 'trash'
            )
        ) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'post_title',
                'label' => esc_html__('Post Title', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'post_title',
                'label' => esc_html__('Post Title', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'post_title',
                'label' => esc_html__('Post Title', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostType($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            || (
                //@codingStandardsIgnoreLine
                isset($post) && $post->post_status !== 'trash'
            )
        ) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'post_type',
                'label' => esc_html__('Post Type', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'post_type',
                'label' => esc_html__('Post Type', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'post_type',
                'label' => esc_html__('Post Type', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostExcerpt($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            || (
                //@codingStandardsIgnoreLine
                isset($post) && post_type_supports($post->post_type, 'excerpt')
                //@codingStandardsIgnoreLine
                && $post->post_status !== 'trash'
            )
        ) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'post_excerpt',
                'label' => esc_html__('Post Excerpt', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'post_excerpt',
                'label' => esc_html__('Post Excerpt', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'post_excerpt',
                'label' => esc_html__('Post Excerpt', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostId($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            //@codingStandardsIgnoreLine
            || (isset($post) && $post->post_status !== 'trash')
        ) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'post_id',
                'label' => esc_html__('Post ID', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'post_id',
                'label' => esc_html__('Post ID', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'post_id',
                'label' => esc_html__('Post ID', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostDate($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            //@codingStandardsIgnoreLine
            || (isset($post) && $post->post_status !== 'trash')
        ) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'post_date',
                'label' => esc_html__('Post Date', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'post_date',
                'label' => esc_html__('Post Date', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'post_date',
                'label' => esc_html__('Post Date', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostModifyDate($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            //@codingStandardsIgnoreLine
            || (isset($post) && $post->post_status !== 'trash')
        ) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'post_modify_date',
                'label' => esc_html__('Post Modify Date', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'post_modify_date',
                'label' => esc_html__('Post Modify Date', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'post_modify_date',
                'label' => esc_html__('Post Modify Date', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostParentName($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            //@codingStandardsIgnoreLine
            || (isset($post) && $post->post_parent && $post->post_status !== 'trash')
        ) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'post_parent_name',
                'label' => esc_html__('Post Parent\'s Name', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'post_parent_name',
                'label' => esc_html__('Post Parent\'s Name', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'post_parent_name',
                'label' => esc_html__('Post Parent\'s Name', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostCategories($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            //@codingStandardsIgnoreLine
            || (isset($post) && $post->post_type === 'post' && $post->post_status !== 'trash')
        ) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'post_categories',
                'label' => esc_html__('Post Categories', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'post_categories',
                'label' => esc_html__('Post Categories', 'visualcomposer'),
            ];
            $response['inputSelect']['post']['group']['values'][] = [
                'value' => 'post_categories',
                'label' => esc_html__('Post Categories', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostTags($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            //@codingStandardsIgnoreLine
            || (isset($post) && $post->post_type === 'post' && $post->post_status !== 'trash')
        ) {
            $response['string']['post']['group']['values'][] = [
                'value' => 'post_tags',
                'label' => esc_html__('Post Tags', 'visualcomposer'),
            ];
            $response['htmleditor']['post']['group']['values'][] = [
                'value' => 'post_tags',
                'label' => esc_html__('Post Tags', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addPostUrl($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            //@codingStandardsIgnoreLine
            || (isset($post) && $post->post_status !== 'trash')
        ) {
            $response['url']['post']['group']['values'][] = [
                'value' => 'post_url',
                'label' => esc_html__('Post/Page', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     *
     * @param $payload
     *
     * @return mixed
     */
    protected function addCommentUrl($response, $payload)
    {
        $sourceId = $payload['sourceId'];
        $post = get_post($sourceId);
        if ((isset($payload['forceAddField']) && $payload['forceAddField'])
            //@codingStandardsIgnoreLine
            || (isset($post) && comments_open($sourceId) && $post->post_status !== 'trash')
        ) {
            $response['url']['post']['group']['values'][] = [
                'value' => 'comment_url',
                'label' => esc_html__('Comment', 'visualcomposer'),
            ];
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postAuthor($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostAuthor($sourceId);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postAuthorLink($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostAuthorLink($sourceId);

            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     *
     * @return mixed
     */
    protected function postTitle($response, $payload, PostData $postDataHelper, Frontend $frontendHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $disableMeta = get_post_meta($sourceId, '_' . VCV_PREFIX . 'pageTitleDisabled', true);
            if ($disableMeta && !$frontendHelper->isPageEditable()) {
                return '';
            }
            $actualValue = $postDataHelper->getPostTitle($sourceId);

            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postId($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostId($sourceId);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postType($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostType($sourceId);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postExcerpt($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostExcerpt($sourceId);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postCategories($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostCategories($sourceId, $payload);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postTags($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostTags($sourceId, $payload);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postCommentCount($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostCommentCount($sourceId);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postDate($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostDate($sourceId);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postModifyDate($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostModifyDate($sourceId);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postParentName($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostParentName($sourceId);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postAuthorBio($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostAuthorBio($sourceId);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function postUrl($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getPostUrl($sourceId);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }

    /**
     * @param $response
     * @param $payload
     * @param \VisualComposer\Helpers\PostData $postDataHelper
     *
     * @return mixed
     */
    protected function commentUrl($response, $payload, PostData $postDataHelper)
    {
        $atts = $payload['atts'];
        $sourceId = false;
        if (isset($atts['sourceId'])) {
            $sourceId = $atts['sourceId'];
        }

        if (isset($atts['currentValue'])) {
            $actualValue = $postDataHelper->getCommentUrl($sourceId);
            if (empty($response)) {
                return $actualValue;
            }
            $response = $this->parseResponse($atts['currentValue'], $actualValue, $response);
        }

        return $response;
    }
}
