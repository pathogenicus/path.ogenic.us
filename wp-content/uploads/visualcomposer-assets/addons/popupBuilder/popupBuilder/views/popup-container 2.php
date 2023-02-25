<?php

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
/**
 * @var array $popups
 * @var array $renderedPopups
 */
// @codingStandardsIgnoreStart
?>

<?php if (!empty($popups)) : ?>
    <?php foreach ($popups as $id => $popup) : ?>
        <?php if (!in_array($id, $renderedPopups, true)) : ?>
            <div id='vcv-popup-<?php echo (int)$id; ?>' class='vcv-popup-container'><?php echo $popup; ?></div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

