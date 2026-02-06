<?php
namespace WGMSRM\Traits;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait CategoryCRUD: Category CRUD operation doing here
 */
trait CategoryCRUD
{
	/**
	 * To save new category
	 */
	public function save_category()
	{

		global $wpdb;


		$data = isset($_POST['category_data']) && is_array($_POST['category_data']) ? map_deep(wp_unslash($_POST['category_data']), 'sanitize_text_field') : [];
		
		$name = isset($data['name']) ? sanitize_text_field($data['name']) : '';
		$icon = isset($data['icon']) ? esc_url_raw($data['icon']) : '';
		$parent_id = isset($data['parent_id']) ? intval($data['parent_id']) : 0;

		if (empty($name)) {
			wp_send_json_error(['message' => esc_html__('Category name is required.', 'gmap-embed')]);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$inserted = $wpdb->insert(
			"{$wpdb->prefix}wgm_categories",
			[
				'name' => $name,
				'icon' => $icon,
				'parent_id' => $parent_id,
				'created_at' => current_time('mysql'),
			],
			['%s', '%s', '%d', '%s']
		);

		if ($inserted) {
			wp_send_json_success([
				'message' => esc_html__('Category saved successfully.', 'gmap-embed'),
				'id' => $wpdb->insert_id
			]);
		} else {
			wp_send_json_error(['message' => esc_html__('Failed to save category.', 'gmap-embed')]);
		}
	}

	/**
	 * To update existing category
	 */
	public function update_category()
	{

		global $wpdb;


		$data = isset($_POST['category_data']) && is_array($_POST['category_data']) ? map_deep(wp_unslash($_POST['category_data']), 'sanitize_text_field') : [];
		
		$id = isset($data['id']) ? intval($data['id']) : 0;
		$name = isset($data['name']) ? sanitize_text_field($data['name']) : '';
		$icon = isset($data['icon']) ? esc_url_raw($data['icon']) : '';
		$parent_id = isset($data['parent_id']) ? intval($data['parent_id']) : 0;

		if (empty($id) || empty($name)) {
			wp_send_json_error(['message' => esc_html__('Invalid data.', 'gmap-embed')]);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$updated = $wpdb->update(
			"{$wpdb->prefix}wgm_categories",
			[
				'name' => $name,
				'icon' => $icon,
				'parent_id' => $parent_id,
			],
			['id' => $id],
			['%s', '%s', '%d'],
			['%d']
		);

		if ($updated !== false) {
			wp_send_json_success(['message' => esc_html__('Category updated successfully.', 'gmap-embed')]);
		} else {
			wp_send_json_error(['message' => esc_html__('Failed to update category.', 'gmap-embed')]);
		}
	}

	/**
	 * To delete a category
	 */
	public function delete_category()
	{

		global $wpdb;


		$category_id = isset($_POST['id']) ? intval(sanitize_text_field(wp_unslash($_POST['id']))) : 0;
		if (empty($category_id)) {
			wp_send_json_error(['message' => esc_html__('Invalid ID.', 'gmap-embed')]);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->delete(
			"{$wpdb->prefix}wgm_categories",
			['id' => $category_id],
			['%d']
		);

		if ($deleted) {
			wp_send_json_success(['message' => esc_html__('Category deleted successfully.', 'gmap-embed')]);
		} else {
			wp_send_json_error(['message' => esc_html__('Failed to delete category.', 'gmap-embed')]);
		}
	}

	/**
	 * Fetch all categories for datatable
	 */
	public function get_categories_for_dt()
	{
		global $wpdb;

		$categories = wp_cache_get('gmap_embed_categories_dt', 'gmap_embed');
		if (false === $categories) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wgm_categories ORDER BY id DESC");
			wp_cache_set('gmap_embed_categories_dt', $categories, 'gmap_embed');
		}
		
		$data = [];
		if ($categories) {
			foreach ($categories as $cat) {
				$parent_name = 'None';
				if ($cat->parent_id > 0) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$parent = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}wgm_categories WHERE id = %d", $cat->parent_id));
					$parent_name = $parent ? esc_html($parent) : 'None';
				}

				$icon_html = $cat->icon ? '<img src="' . esc_url($cat->icon) . '" width="30" height="30" style="object-fit: contain;">' : 'None';

				$data[] = [
					'id' => intval($cat->id),
					'name' => esc_html($cat->name),
					'icon' => $icon_html,
					'parent' => $parent_name,
					'action' => '<button class="button button-small wgm-edit-category" data-id="' . intval($cat->id) . '"><i class="fas fa-edit"></i></button> 
								 <button class="button button-small wgm-delete-category" data-id="' . intval($cat->id) . '" style="color:red;"><i class="fas fa-trash"></i></button>'
				];
			}
		}

		wp_send_json(['data' => $data]);
	}

    /**
     * Get single category data
     */
    public function get_category_data()
    {
        global $wpdb;



        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (empty($id)) {
            wp_send_json_error(['message' => 'Invalid ID']);
        }

        $category = wp_cache_get('gmap_embed_category_' . $id, 'gmap_embed');
        if (false === $category) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            $category = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wgm_categories WHERE id = %d", $id));
            wp_cache_set('gmap_embed_category_' . $id, $category, 'gmap_embed');
        }
        
        if ($category) {
            wp_send_json_success($category);
        } else {
            wp_send_json_error(['message' => 'Category not found']);
        }
    }
}
