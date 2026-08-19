<?php

/**
 * UIKit Theme Builder AddOn
 */

$addon = rex_addon::get('uikit_theme_builder');

echo rex_view::title($addon->i18n('uikit_theme_builder_title'));

rex_be_controller::includeCurrentPageSubPath();