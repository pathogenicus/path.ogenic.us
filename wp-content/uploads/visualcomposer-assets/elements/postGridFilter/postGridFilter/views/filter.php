<?php
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
/**
 * @var $payload array
 * @var $id string
 * @var $filterTerms array
 * @var $filterAtts array
 * @var $activeTermId string
 * @var $controller \postGridFilter\postGridFilter\PostsGridFilter
 */

$shape = isset($filterAtts['filter_shape']) ? $filterAtts['filter_shape'] : 'none';
?>
<style>
  .vce-post-grid-filter-wrapper {
    --size: 17px 20px;
    --container-border-radius: 5px;
    --item-border-radius: 5px;
    --alignment: center;
    --gap: 20px;
    --border-color: #dcdcdc;
    --separator-color: #dcdcdc;
    --font-color: #515162;
    --font-active-color: #fff;
    --background-color: #fdfdfd;
    --background-active-color: #4c3ab3;
    display: flex;
    flex-wrap: wrap;
    justify-content: var(--alignment);
  }

  .vce-post-grid-filter-wrapper .vce-post-grid-filter-dropdown {
    width: 100%;
    -webkit-appearance: none;
    border: 2px solid rgba(125,125,125, .5);
    padding: 8px 33px 8px 15px;
    color: #515162;
    background: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyOTIuNCIgaGVpZ2h0PSIyOTIuNCI+PHBhdGggZmlsbD0iIzdkN2Q3ZCIgZD0iTTI4NyA2OS40YTE3LjYgMTcuNiAwIDAgMC0xMy01LjRIMTguNGMtNSAwLTkuMyAxLjgtMTIuOSA1LjRBMTcuNiAxNy42IDAgMCAwIDAgODIuMmMwIDUgMS44IDkuMyA1LjQgMTIuOWwxMjggMTI3LjljMy42IDMuNiA3LjggNS40IDEyLjggNS40czkuMi0xLjggMTIuOC01LjRMMjg3IDk1YzMuNS0zLjUgNS40LTcuOCA1LjQtMTIuOCAwLTUtMS45LTkuMi01LjUtMTIuOHoiLz48L3N2Zz4K") no-repeat right 15px center;
    background-size: 9px;
  }

  .vce-post-grid-filter-wrapper[data-vce-state="items"] .vce-post-grid-filter-dropdown {
    visibility: hidden;
    height: 0;
    position: absolute;
  }

  .vce-post-grid-filter-wrapper[data-vce-state="items"] .vce-post-grid-filter-container {
    visibility: visible;
    height: initial;
  }

  .vce-post-grid-filter-wrapper[data-vce-state="dropdown"] .vce-post-grid-filter-container {
    visibility: hidden;
    height: 0;
  }

  .vce-post-grid-filter-wrapper[data-vce-state="dropdown"] .vce-post-grid-filter-dropdown {
    visibility: visible;
    height: initial;
    position: initial;
  }

  .vce-post-grid-filter-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: var(--alignment);
    margin-bottom: var(--gap);
  }

  .vce-post-grid-filter-control {
    position: relative;
    padding: var(--size);
    border: none;
    border-radius: var(--item-border-radius);
    background: none;
    color: var(--font-color);
    font-size: 15px;
    line-height: 1;
    cursor: pointer;
    transition: background-color .2s ease-in-out,
    color .2s ease-in-out,
    border .2s ease-in-out,
    z-index .2s ease-in-out;
  }

  .vce-post-grid-filter-control:active,
  .vce-post-grid-filter-control:focus {
    color: var(--font-color);
    text-decoration: none;
  }

  .vce-post-grid-filter-control--active.vce-post-grid-filter-control,
  .vce-post-grid-filter-control--active.vce-post-grid-filter-control:focus,
  .vce-post-grid-filter-control--active.vce-post-grid-filter-control:active,
  .vce-post-grid-filter-control:hover {
    color: var(--font-active-color);
    text-decoration: none;
  }

  .vce-post-grid-filter-control::before,
  .vce-post-grid-filter-control::after {
    content: '';
    position: absolute;
    top: -1px;
    width: 1px;
    height: calc(100% + 2px);
    z-index: 1;
    background-color: transparent;
    transition: background-color .2s ease-in-out;
  }

  .vce-post-grid-filter-control::before {
    right: 100%;
    z-index: 2;
  }

  .vce-post-grid-filter-control::after {
    left: 100%;
  }

  .vce-post-grid-filter-container:not(.vce-post-grid-filter--shape-none) .vce-post-grid-filter-control {
    background-color: var(--background-color);
    border: 1px solid var(--border-color);
    border-right: none;
    border-radius: 0;
  }

  .vce-post-grid-filter-container:not(.vce-post-grid-filter--shape-none) .vce-post-grid-filter-control:last-of-type {
    border-right: 1px solid var(--border-color);
  }

  .vce-post-grid-filter-container .vce-post-grid-filter-control--active.vce-post-grid-filter-control,
  .vce-post-grid-filter-container .vce-post-grid-filter-control.vce-post-grid-filter-control:hover {
    background-color: var(--background-active-color) !important;
    border-color: var(--background-active-color) !important;
  }

  .vce-post-grid-filter-container:not(.vce-post-grid-filter--shape-none) .vce-post-grid-filter-control:not(:last-of-type)::after {
    background-color: var(--separator-color);
  }

  .vce-post-grid-filter-container:not(.vce-post-grid-filter--shape-none) .vce-post-grid-filter-control--active.vce-post-grid-filter-control:not(:last-of-type)::after,
  .vce-post-grid-filter-container:not(.vce-post-grid-filter--shape-none) .vce-post-grid-filter-control:hover:not(:last-of-type)::after,
  .vce-post-grid-filter-container:not(.vce-post-grid-filter--shape-none) .vce-post-grid-filter-control--active.vce-post-grid-filter-control:not(:first-of-type)::before,
  .vce-post-grid-filter-container:not(.vce-post-grid-filter--shape-none) .vce-post-grid-filter-control:hover:not(:first-of-type)::before {
    background-color: var(--background-active-color);
  }

  .vce-post-grid-filter-container:not(.vce-post-grid-filter--shape-none) .vce-post-grid-filter-control:first-of-type {
    border-top-left-radius: var(--container-border-radius);
    border-bottom-left-radius: var(--container-border-radius);
  }

  .vce-post-grid-filter-container:not(.vce-post-grid-filter--shape-none) .vce-post-grid-filter-control:last-of-type {
    border-top-right-radius: var(--container-border-radius);
    border-bottom-right-radius: var(--container-border-radius);
  }

  .vce-post-grid-filter-helper {
    scroll-margin-top: 100px;
  }
</style>
<div id="grid-<?php echo esc_attr($id); ?>" class="vce-post-grid-filter-helper"></div>
<div class="vce-post-grid-filter-wrapper" id="el-<?php echo esc_attr($id); ?>-filter" data-vce-collapsible="<?php echo $filterAtts['filter_toggle_convert_to_dropdown'] ? 'true' : 'false'; ?>" style="
        --size: <?php echo $filterAtts['filter_size'] === 'small' ? '10px 16px' : '17px 20px'; ?>;
        --container-border-radius: <?php echo $filterAtts['filter_shape']; ?>px;
        --item-border-radius: <?php echo $filterAtts['filter_active_shape']; ?>px;
        --alignment: <?php echo $filterAtts['filter_position']; ?>;
        --gap: <?php echo $filterAtts['filter_gap']; ?>px;
        --border-color: <?php echo $filterAtts['filter_border_color']; ?>;
        --separator-color: <?php echo $filterAtts['filter_separator_color']; ?>;
        --font-color: <?php echo $filterAtts['filter_font_color']; ?>;
        --font-active-color: <?php echo $filterAtts['filter_font_active_color']; ?>;
        --background-color: <?php echo $filterAtts['filter_shape_color']; ?>;
        --background-active-color: <?php echo $filterAtts['filter_active_color']; ?>;" >
    <?php
    if ($filterAtts['filter_toggle_convert_to_dropdown']) {
        ?>
        <select class="vce-post-grid-filter-dropdown" onchange="if(window.location.href.indexOf('vcv-editable') < 0){window.location.href = window.location.origin + '/' + this.value}">
            <?php
            foreach ($filterTerms as $term) {
                echo sprintf(
                    '<option value="%s" %s>%s</option>',
                    $controller->getFilterUrl($id, esc_attr($term['term_id'])),
                    $activeTermId === (int)$term['term_id'] ? 'selected' : '',
                    esc_html($term['name'])
                );
            }
            ?>
        </select>
    <?php } ?>
    <div class="vce-post-grid-filter-container vce-post-grid-filter--shape-<?php echo esc_attr($shape); ?>">
        <?php
        foreach ($filterTerms as $term) {
            $activeClass = (int)$term['term_id'] === $activeTermId ? '  vce-post-grid-filter-control--active' : '';
            echo sprintf(
                '<a href="%s"  class="vce-post-grid-filter-control%s">%s</a>',
                $controller->getFilterUrl($id, esc_attr($term['term_id'])),
                $activeClass,
                esc_html($term['name'])
            );
        }
        ?>
    </div>
</div>
