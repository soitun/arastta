<?php
/**
 * @package		Arastta eCommerce
 * @copyright	Copyright (C) 2015 Arastta Association. All rights reserved. (arastta.org)
 * @credits		See CREDITS.txt for credits and other copyright notices.
 * @license		GNU General Public License version 3; see LICENSE.txt
 */

class ModelCatalogInformation extends Model {
	public function addInformation($data) {
		$this->trigger->fire('pre.admin.information.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "information SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "'");

		$information_id = $this->db->getLastId();

		foreach ($data['information_description'] as $language_id => $value) {
			empty($value['meta_title']) ? $value['meta_title'] = $value['title'] : $value['meta_title'];

			$this->db->query("INSERT INTO " . DB_PREFIX . "information_description SET information_id = '" . (int)$information_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_to_store SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_to_layout SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

        foreach ($data['seo_url'] as $language_id => $value) {
            $alias = empty($value) ? $data['information_description'][$language_id]['title'] : $value;

            $alias = $this->model_catalog_url_alias->generateAlias($alias, $information_id);

            if ($alias) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'information_id=" . (int)$information_id . "', keyword = '" . $this->db->escape($alias) . "', language_id = '" . $language_id . "'");
            }
        }

		$this->cache->delete('information');

		$this->trigger->fire('post.admin.information.add', $information_id);

		return $information_id;
	}

	public function editInformation($information_id, $data) {
		$this->trigger->fire('pre.admin.information.edit', $data);

		$this->db->query("UPDATE " . DB_PREFIX . "information SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "' WHERE information_id = '" . (int)$information_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$information_id . "'");

		foreach ($data['information_description'] as $language_id => $value) {
			empty($value['meta_title']) ? $value['meta_title'] = $value['title'] : $value['meta_title'];

			$this->db->query("INSERT INTO " . DB_PREFIX . "information_description SET information_id = '" . (int)$information_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "information_to_store WHERE information_id = '" . (int)$information_id . "'");

		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_to_store SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "information_to_layout WHERE information_id = '" . (int)$information_id . "'");

		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_to_layout SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

        foreach ($data['seo_url'] as $language_id => $value) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'information_id=" . (int)$information_id . "' AND language_id = '" . $this->db->escape($language_id) . "'");

            $alias = empty($value) ? $data['information_description'][$language_id]['title'] : $value;

            $alias = $this->model_catalog_url_alias->generateAlias($alias, $information_id);

            if ($alias) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'information_id=" . (int)$information_id . "', keyword = '" . $this->db->escape($alias) . "', language_id = '" . $language_id . "'");
            }
        }

		$this->cache->delete('information');

		$this->trigger->fire('post.admin.information.edit', $information_id);
	}

	public function deleteInformation($information_id) {
		$this->trigger->fire('pre.admin.information.delete', $information_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "information WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_to_store WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_to_layout WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'information_id=" . (int)$information_id . "'");
		
		// Main Menu Item 
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "menu_description` AS md LEFT JOIN `" . DB_PREFIX . "menu` AS m ON m.menu_id = md.menu_id WHERE m.menu_type = 'information' AND md.link = '" . (int)$information_id . "'");
		 
		if(!empty($query->row['menu_id'])){
			$menu_id = $query->row['menu_id'];
			$this->db->query("DELETE FROM `" . DB_PREFIX . "menu` WHERE menu_id = '" . (int)$menu_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "menu_description` WHERE menu_id = '" . (int)$menu_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "menu_to_store` WHERE menu_id = '" . (int)$menu_id . "'");
		}
		
		// Child Menu Item
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "menu_child_description` AS mcd LEFT JOIN `" . DB_PREFIX . "menu_child` AS mc ON mc.menu_child_id = mcd.menu_child_id WHERE mc.menu_type = 'information' AND mcd.link = '" . (int)$information_id . "'");
		
		if(!empty($query->row['menu_child_id'])){
			$menu_child_id = $query->row['menu_child_id'];
			$this->db->query("DELETE FROM `" . DB_PREFIX . "menu_child` WHERE menu_child_id = '" . (int)$menu_child_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "menu_child_description` WHERE menu_child_id = '" . (int)$menu_child_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "menu_child_to_store` WHERE menu_child_id = '" . (int)$menu_child_id . "'");
		}

		$this->cache->delete('information');

		$this->trigger->fire('post.admin.information.delete', $information_id);
	}

	public function getInformation($information_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "information WHERE information_id = '" . (int)$information_id . "'");

        $information = $query->row;
        $information['seo_url'] = array();

        $query = $this->db->query("SELECT keyword, language_id FROM " . DB_PREFIX . "url_alias WHERE query = 'information_id=" . (int)$information_id . "'");

        if ($query->rows) {
            foreach ($query->rows as $row) {
                $information['seo_url'][$row['language_id']] = $row['keyword'];
            }
        }

        return $information;
	}

	public function getInformations($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			$sort_data = array(
				'i.status',
				'i.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY id.title";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;
		} else {
			$information_data = $this->cache->get('information.' . (int)$this->config->get('config_language_id'));

			if (!$information_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY id.title");

				$information_data = $query->rows;

				$this->cache->set('information.' . (int)$this->config->get('config_language_id'), $information_data);
			}

			return $information_data;
		}
	}

	public function getInformationDescriptions($information_id) {
		$information_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $information_description_data;
	}

	public function getInformationStores($information_id) {
		$information_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_to_store WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_store_data[] = $result['store_id'];
		}

		return $information_store_data;
	}

	public function getInformationLayouts($information_id) {
		$information_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_to_layout WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $information_layout_data;
	}

	public function getTotalInformations() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information");

		return $query->row['total'];
	}

	public function getTotalInformationsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}
}