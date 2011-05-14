<?php

class Unl_Core_Block_Catalog_Navigation extends Mage_Catalog_Block_Navigation
{
    /* Overrides
     * @see Mage_Catalog_Block_Navigation::renderCategoriesMenuHtml()
     * by adding ACL checks
     */
    public function renderCategoriesMenuHtml($level = 0, $outermostItemClass = '', $childrenWrapClass = '')
    {
        $activeCategories = array();
        foreach ($this->getStoreCategories() as $child) {
            if ($child->getIsActive() && Mage::helper('unl_core')->isCustomerAllowedCategory($child)) {
                $activeCategories[] = $child;
            }
        }
        $activeCategoriesCount = count($activeCategories);
        $hasActiveCategoriesCount = ($activeCategoriesCount > 0);

        if (!$hasActiveCategoriesCount) {
            return '';
        }

        $html = '';
        $j = 0;
        foreach ($activeCategories as $category) {
            $html .= $this->_renderCategoryMenuItemHtml(
                $category,
                $level,
                ($j == $activeCategoriesCount - 1),
                ($j == 0),
                true,
                $outermostItemClass,
                $childrenWrapClass,
                true
            );
            $j++;
        }

        return $html;
    }

    /* Overrides
     * @see Mage_Catalog_Block_Navigation::_renderCategoryMenuItemHtml()
     * by adding ACL checks
     */
    protected function _renderCategoryMenuItemHtml($category, $level = 0, $isLast = false, $isFirst = false,
        $isOutermost = false, $outermostItemClass = '', $childrenWrapClass = '', $noEventAttributes = false)
    {
        if (!$category->getIsActive()) {
            return '';
        }
        $html = array();

        // get all children
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $children = (array)$category->getChildrenNodes();
            $childrenCount = count($children);
        } else {
            $children = $category->getChildren();
            $childrenCount = $children->count();
        }
        $hasChildren = ($children && $childrenCount);

        // select active children
        $activeChildren = array();
        foreach ($children as $child) {
            if ($child->getIsActive() && Mage::helper('unl_core')->isCustomerAllowedCategory($child)) {
                $activeChildren[] = $child;
            }
        }
        $activeChildrenCount = count($activeChildren);
        $hasActiveChildren = ($activeChildrenCount > 0);

        // prepare list item html classes
        $classes = array();
        $classes[] = 'level' . $level;
        $classes[] = 'nav-' . $this->_getItemPosition($level);
        if ($this->isCategoryActive($category)) {
            $classes[] = 'active';
        }
        $linkClass = '';
        if ($isOutermost && $outermostItemClass) {
            $classes[] = $outermostItemClass;
            $linkClass = ' class="'.$outermostItemClass.'"';
        }
        if ($isFirst) {
            $classes[] = 'first';
        }
        if ($isLast) {
            $classes[] = 'last';
        }
        if ($hasActiveChildren) {
            $classes[] = 'parent';
        }

        // prepare list item attributes
        $attributes = array();
        if (count($classes) > 0) {
            $attributes['class'] = implode(' ', $classes);
        }
        if ($hasActiveChildren && !$noEventAttributes) {
             $attributes['onmouseover'] = 'toggleMenu(this,1)';
             $attributes['onmouseout'] = 'toggleMenu(this,0)';
        }

        // assemble list item with attributes
        $htmlLi = '<li';
        foreach ($attributes as $attrName => $attrValue) {
            $htmlLi .= ' ' . $attrName . '="' . str_replace('"', '\"', $attrValue) . '"';
        }
        $htmlLi .= '>';
        $html[] = $htmlLi;

        $html[] = '<a href="'.$this->getCategoryUrl($category).'"'.$linkClass.'>';
        $html[] = '<span>' . $this->escapeHtml($category->getName()) . '</span>';
        $html[] = '</a>';

        // render children
        $htmlChildren = '';
        $j = 0;
        foreach ($activeChildren as $child) {
            $htmlChildren .= $this->_renderCategoryMenuItemHtml(
                $child,
                ($level + 1),
                ($j == $activeChildrenCount - 1),
                ($j == 0),
                false,
                $outermostItemClass,
                $childrenWrapClass,
                $noEventAttributes
            );
            $j++;
        }
        if (!empty($htmlChildren)) {
            if ($childrenWrapClass) {
                $html[] = '<div class="' . $childrenWrapClass . '">';
            }
            $html[] = '<ul class="level' . $level . '">';
            $html[] = $htmlChildren;
            $html[] = '</ul>';
            if ($childrenWrapClass) {
                $html[] = '</div>';
            }
        }

        $html[] = '</li>';

        $html = implode("\n", $html);
        return $html;
    }
}
