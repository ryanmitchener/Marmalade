<?php
namespace Marmalade;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * ArrayRouterNav
 * Nav builder based on the ArrayRouter class
 */
class ArrayRouterNav extends Nav {
    // Variable that contains the nav item ids of all ancestors of the current page
    private $ancestor_ids = array();

    /**
     * Get the default navigation object based on the router
     */ 
    public function get_nav_object() {       
        // Instantiate variables
        $marmalade = Marmalade::get_instance();
        $nav = array();

        // Build the nav
        $count = 0;
        foreach ($this->routes as $route) {
            if ($route->path === "|root|") {
                continue;
            } else if ($route->option(Route::HIDDEN)) {
                continue;
            } else if (!isset($route->actions["GET"])) {
                continue;
            }
            $path_parts = $route->get_path_parts();
            $depth = count($path_parts) - 1;
            $item = (Object) array(
                "path_part" => $path_parts[$depth],
                "label" => ($route->option(Route::NAV_LABEL) !== null) ? $route->option(Route::NAV_LABEL) : ucfirst($path_parts[$depth]),
                "depth" => $depth,
                "title_attribute" => ($route->option(Route::TITLE_ATTRIBUTE) !== null) ? $route->option(Route::TITLE_ATTRIBUTE) : ucfirst($path_parts[$depth]),
                "url" => ROOT_URL."/{$route->path}",
                "current_item" => false,
                "current_item_ancestor" => false,
                "nav_item_id" => $count,
                "nav_item_parent" => -1,
                "has_children" => false);

            // Get the parent id
            if ($item->depth > 0) {
                for ($i = count($nav) - 1; $i >= 0; $i--) { 
                    if ($nav[$i]->depth === $depth - 1) {
                        $item->nav_item_parent = $i;
                        $nav[$i]->has_children = true; // Set the parent to have children
                        break;
                    }
                }
            }

            // Mark ancestors and current item
            $current_route_path_parts = $marmalade->route->get_path_parts();
            if ($depth < count($current_route_path_parts)) {
                foreach ($path_parts as $index => $part) {
                    if ($part !== $current_route_path_parts[$index]) {
                        break;
                    } else if ($index === $depth) {
                        $item->current_item = ($depth === count($current_route_path_parts) - 1) ? true : false;
                        $item->current_item_ancestor = !$item->current_item;
                        if ($item->current_item_ancestor) {
                            $this->ancestor_ids[] = $count;
                        }
                    }
                }
            }

            // Add item to the array
            $nav[] = $item;
            $count++;
        }
        return $nav;
    }


    /**
     * Build a navigation menu object
     * Returns an array of important information for building a navigation menu in HTML based
     * on the routes contained in the router.
     *
     * @return string HTML of the navigation menu
     */
    public function build() {
        $start_depth = (isset($this->options['start_depth'])) ? $this->options['start_depth'] : 0; 
        $end_depth = (isset($this->options['end_depth'])) ? $this->options['end_depth'] : 99; 
        $branch_mode = (isset($this->options['branch_mode'])) ? true : false;
        $ancestor_mode = (isset($this->options['ancestor_mode'])) ? true : false;
        $sibling_mode = (isset($this->options['sibling_mode'])) ? true : false;
        $siblings_only_mode = ($sibling_mode && !$ancestor_mode) ? true : false;
        $hide_if_empty = (isset($this->options['hide_if_empty'])) ? true : false;
        $flatten = (isset($this->options['flatten'])) ? true : false;
        $nav_class = (isset($this->options['class'])) ? " {$this->options['class']}" : "";
        $nav = $this->get_nav_object();
        $depth = $start_depth;
        $count = 0; // Represents number of items added
        $list = "";
        $current_item = (object) array("nav_item_parent" => null, "nav_item_id" => null);

        // Find the current nav item parent for sibling mode
        if ($sibling_mode || $branch_mode) {
            foreach ($nav as $item) {
                if ($item->current_item) {
                    $current_item = $item;
                    break;
                }
            }
        }

        // Loop through nav items and build HTML nav
        foreach ($nav as $item) {
            // Ignore unwanted items if in branch, ancestor, or sibling mode
            // Branch mode shows children of current nav item, ancestors of current item, and siblings of ancestors
            if ($branch_mode) {
                if (!in_array($item->nav_item_parent, $this->ancestor_ids) && 
                        !in_array($item->nav_item_id, $this->ancestor_ids) &&
                        $item->nav_item_parent !== $current_item->nav_item_id) {
                    continue;
                }
            } else {
                // Ancestor and Sibling mode
                if ($ancestor_mode && $sibling_mode) {
                    if ($item->nav_item_parent !== $current_item->nav_item_parent && !$item->current_item && !$item->current_item_ancestor) {
                        continue;
                    }
                } else if ($ancestor_mode && !$item->current_item && !$item->current_item_ancestor) {
                    continue;
                } else if ($sibling_mode && $item->nav_item_parent !== $current_item->nav_item_parent) {
                    continue;
                }
            }

            // Handle min/max depth and nav flattening
            if ($item->depth < $start_depth || $item->depth > $end_depth) {
                continue;
            } else if ($flatten || $siblings_only_mode) {
                if ($count > 0) {
                    $list .= "</li>";
                }
            } else {
                if ($item->depth > $depth) {
                    $list .= "<ul class='sub-nav'>";
                } else if ($item->depth < $depth) {
                    for ($i = 0, $l = $depth - $item->depth; $i < $l; $i++) {
                        if ($i === 0) {
                            $list .= "</li>";
                        }
                        $list .= "</ul></li>";
                    }
                } else if ($count > 0) {
                    $list .= "</li>";
                }
            }

            // Create list element
            $classes = ($item->current_item) ? " current-nav-item" : "";
            $classes .= ($item->current_item_ancestor) ? " current-nav-item-ancestor" : "";
            $classes .= ($item->has_children) ? " nav-item-has-children" : "";
            $item_html = "<a class='nav-item-link' href='{$item->url}' title='{$item->title_attribute}'>{$item->label}</a>";
            $list .= "<li class='nav-item{$classes}' data-depth='{$item->depth}'>{$item_html}";
            $depth = $item->depth;
            $count++;
        }

        // Close off any leftover tags
        if ($count > 0) {
            $list .= "</li>";
        }
        if ((!$flatten && !$siblings_only_mode)) {
            for ($i = 0, $l = $depth - $start_depth; $i < $l; $i++) {
                $list .= "</ul></li>";
            }
        }

        // Display nothing if hide_if_empty is set and there are no items
        if ($count === 0 && $hide_if_empty) {
            return "";
        }

        $no_items_class = ($count === 0) ? " nav--no-items" : "";
        $html = "<ul class='nav{$nav_class}{$no_items_class}'>{$list}</ul>";

        // Return the nav        
        return $html;
    }
}