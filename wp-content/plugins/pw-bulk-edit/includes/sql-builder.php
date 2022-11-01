<?php

/*
Copyright (C) Pimwick, LLC

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PWBE_SQL_Builder' ) ) :

final class PWBE_SQL_Builder {

	public function get_products( $post ) {
		global $wpdb;

		$wpdb->show_errors();

		$common_fields = "
			post.ID AS post_id,
			post.post_content AS post_content,
            post.post_excerpt AS post_excerpt,
			post.post_status AS post_status,
			post.menu_order AS menu_order,
			parent.ID AS parent_post_id,
			parent.post_title AS post_title,

            meta__sku.meta_value AS _sku,
            meta__regular_price.meta_value AS _regular_price,
            meta__sale_price.meta_value AS _sale_price
		";

        $common_joins = "
            LEFT JOIN
                {$wpdb->postmeta} AS meta__sku ON (meta__sku.post_id = post.ID AND meta__sku.meta_key = '_sku')
            LEFT JOIN
                {$wpdb->postmeta} AS meta__regular_price ON (meta__regular_price.post_id = post.ID AND meta__regular_price.meta_key = '_regular_price')
            LEFT JOIN
                {$wpdb->postmeta} AS meta__sale_price ON (meta__sale_price.post_id = post.ID AND meta__sale_price.meta_key = '_sale_price')
        ";

        if ( !empty( $post['order_by'] ) ) {
            $column = PWBE_Columns::get_by_field( $post['order_by'] );
            if ( !empty( $column ) ) {
                if ( $column['field'] == '_visibility' ) {
                    $common_fields .= ",
                        visibility_exclude_from_catalog.product_id AS `visibility_exclude_from_catalog_product_id`,
                        visibility_exclude_from_search.product_id AS `visibility_exclude_from_search_product_id`
                    ";
                    $common_joins = $this->maybe_add_catalog_visibility_joins( $common_joins );

                } else if ( $column['table'] == 'meta' && !in_array( $column['field'], array( '_sku', '_regular_price', '_sale_price' ) ) ) {
                    if ( $column['field'] == '_featured' && PW_Bulk_Edit::wc_min_version( '3.0' ) ) {
                        $common_fields .= "
                            , CASE WHEN featured_products.product_id IS NOT NULL THEN 'yes' ELSE 'no' END AS `is_featured_product`
                        ";

                        $common_joins .= "
                            LEFT JOIN (
                                SELECT
                                    r.object_id AS product_id
                                FROM
                                    {$wpdb->term_taxonomy} AS tax
                                JOIN
                                    {$wpdb->terms} AS t ON (t.term_id = tax.term_id AND t.name = 'featured')
                                JOIN
                                    {$wpdb->term_relationships} AS r ON (r.term_taxonomy_id = tax.term_taxonomy_id)
                                WHERE
                                    tax.taxonomy = 'product_visibility'
                            ) AS featured_products ON (featured_products.product_id = post.ID)
                        ";
                    } else {
                        $common_fields .= '
                            , `meta_' . $column['field'] . '`.meta_value AS `' . $column['field'] . '`
                        ';

                        $common_joins .= '
                            LEFT JOIN
                                ' . $wpdb->postmeta . ' AS `meta_' . $column['field'] . '` ON (`meta_' . $column['field'] . '`.post_id = post.ID AND `meta_' . $column['field'] . '`.meta_key = \'' . $column['field'] . '\')
                        ';
                    }
                }
            }
        }

        $common_where = $this->build_common_sql( '-0', $post['main_group_type'], $post, $common_fields, $common_joins );

        $common_fields = apply_filters( 'pwbe_common_fields', $common_fields );
        $common_joins = apply_filters( 'pwbe_common_joins', $common_joins );
        $common_where = apply_filters( 'pwbe_common_where', $common_where );

		@set_time_limit( 0 );

		$wpdb->query("SET SQL_BIG_SELECTS=1");

		$wpdb->query("DROP TABLE IF EXISTS pwbe_variations");
        $wpdb->query("CREATE TEMPORARY TABLE pwbe_variations (post_id BIGINT(20) UNSIGNED, parent_post_id BIGINT(20) UNSIGNED, PRIMARY KEY (post_id) )");

        $variations_sql = "
            INSERT INTO pwbe_variations
                SELECT
                    post.ID AS post_id,
                    MAX(parent.ID) AS parent_post_id
                FROM
                    {$wpdb->posts} AS post
                JOIN
                    {$wpdb->posts} AS parent ON (parent.ID = post.post_parent)
                $common_joins
                WHERE
                    post.post_type = 'product_variation'
                    AND ($common_where)
        ";

        if ( PWBE_PREFILTER_VARIATIONS ) {
            $variations_sql .= "
                    AND (
                        SELECT
                            terms.slug
                        FROM
                            {$wpdb->term_relationships} AS term_relationships
                        JOIN
                            {$wpdb->term_taxonomy} AS term_taxonomy ON (term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id)
                        JOIN
                            {$wpdb->terms} AS terms ON (terms.term_id = term_taxonomy.term_id)
                        WHERE
                            term_relationships.object_id = post.post_parent
                            AND term_taxonomy.taxonomy = 'product_type'
                        LIMIT 1
                    ) IN ( 'variable', 'variable-subscription' )
            ";
        }

        $variations_sql .= "
               GROUP BY
                    post.ID
        ";

		$wpdb->query( $variations_sql );

		$wpdb->query("DROP TABLE IF EXISTS pwbe_products");
        $wpdb->query("CREATE TEMPORARY TABLE pwbe_products (post_id BIGINT(20) UNSIGNED, PRIMARY KEY (post_id) )");
		$wpdb->query("
			INSERT INTO pwbe_products
				SELECT
					DISTINCT
					post.ID AS post_id
				FROM
					{$wpdb->posts} AS post
				JOIN
					{$wpdb->posts} AS parent ON (parent.ID = post.ID)
				$common_joins
				WHERE
					post.post_type = 'product'
					AND (
						($common_where)
						OR post.ID IN (SELECT parent_post_id FROM pwbe_variations)
					)
		");

		$products = PWBE_DB::query( "
			SELECT
				DISTINCT
				(
					SELECT
						terms.slug
					FROM
						{$wpdb->term_relationships} AS term_relationships
					JOIN
						{$wpdb->term_taxonomy} AS term_taxonomy ON (term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id)
					JOIN
						{$wpdb->terms} AS terms ON (terms.term_id = term_taxonomy.term_id)
					WHERE
						term_relationships.object_id = post.ID
						AND term_taxonomy.taxonomy = 'product_type'
					LIMIT 1
				) AS product_type,
				$common_fields
			FROM
				pwbe_products
			JOIN
				{$wpdb->posts} AS post ON (post.ID = pwbe_products.post_id)
			JOIN
				{$wpdb->posts} AS parent ON (parent.ID = post.ID)
			$common_joins
			WHERE
				post.post_type = 'product'

			UNION ALL

			SELECT
				DISTINCT
				'variation' AS product_type,
				$common_fields
			FROM
				pwbe_variations
			JOIN
				{$wpdb->posts} AS post ON (post.ID = pwbe_variations.post_id)
			JOIN
				{$wpdb->posts} AS parent ON (parent.ID = post.post_parent)
			$common_joins
			WHERE
				post.post_type = 'product_variation'
				AND (
					SELECT
						terms.slug
					FROM
						{$wpdb->term_relationships} AS term_relationships
					JOIN
						{$wpdb->term_taxonomy} AS term_taxonomy ON (term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id)
					JOIN
						{$wpdb->terms} AS terms ON (terms.term_id = term_taxonomy.term_id)
					WHERE
						term_relationships.object_id = post.post_parent
						AND term_taxonomy.taxonomy = 'product_type'
					LIMIT 1
                ) = 'variable'

			ORDER BY
				" . $this->build_order_by( $post ) . "
		" );

        if ( $products !== false ) {
            return $products;
        } else {
            return 'MySQL Error: ' . PWBE_DB::error();
        }
	}

    public function build_common_sql( $suffix, $group_type, $fields, &$sql_fields, &$sql_joins ) {
		global $wpdb;

		$sql_where = "(";

		// Inside each group, loop through the nested statements.
		for ($row_index = 0; $row_index < count( $fields['row'] ); $row_index++ ) {

			$field_name = '';
			$filter_type = '';
			$field_value = '';
			$field_value2 = '';

			if ( isset ( $fields[$row_index . 'filter_name' . $suffix] ) ) {
				$field_name = $fields[$row_index . 'filter_name' . $suffix];
			}

			if ( isset ( $fields[$row_index . 'filter_type' . $suffix] ) ) {
				$filter_type = $fields[$row_index . 'filter_type' . $suffix];
			}

			// Value is either filter_value or filter_select.
			if ( isset( $fields[$row_index . 'filter_value' . $suffix] ) ) {
				$field_value = $fields[$row_index . 'filter_value' . $suffix];

				// Value2 is optional
				if ( isset( $fields[$row_index . 'filter_value2' . $suffix] ) ) {
					$field_value2 = $fields[$row_index . 'filter_value2' . $suffix];
				}

			} else if ( isset( $fields[$row_index . 'filter_select' . $suffix] ) ) {
				$field_value = $fields[$row_index . 'filter_select' . $suffix];
			}

			switch ( $field_name ) {
				case 'pwbe_and':
				case 'pwbe_or':
					$row_sql = $this->build_common_sql( "$suffix-$row_index", $field_name, $fields, $sql_fields, $sql_joins );
				break;

				case 'categories':
					$row_sql = $this->taxonomy_search( 'product_cat', $filter_type, $field_value );
				break;

				case 'tags':
					$row_sql = $this->taxonomy_search( 'product_tag', $filter_type, $field_value );
				break;

				case 'post_content':
					$row_sql = $this->string_search( 'parent.post_content', $filter_type, $field_value );
				break;

				case 'post_title':
					$row_sql = $this->string_search( 'parent.post_title', $filter_type, $field_value );
				break;

				case 'regular_price':
					$row_sql = $this->numeric_search( 'meta__regular_price.meta_value', $filter_type, $field_value, $field_value2 );
				break;

				case 'sale_price':
					$row_sql = $this->numeric_search( 'meta__sale_price.meta_value', $filter_type, $field_value, $field_value2 );
				break;

				case 'post_excerpt':
                    $row_sql = $this->string_search( 'parent.post_excerpt', $filter_type, $field_value );
				break;

				case 'sku':
					$row_sql = $this->string_search( 'meta__sku.meta_value', $filter_type, $field_value );
				break;

				default:
                    if ( $filter_type == 'is checked' || $filter_type == 'is not checked' ) {
                        $row_sql = $this->boolean_search( "`{$field_name}`.meta_value", $filter_type, $field_value );

                    } else {
						if ( PW_Bulk_Edit::starts_with( 'pa_', $field_name ) ) {
							$row_sql = $this->attributes_search( $field_name, $filter_type, $field_value );

						} else {
							if ( $group_type == 'pwbe_and' ) {
								$row_sql = ' 1 = 1 ';

							} else if ( $group_type == 'pwbe_or' ) {
								$row_sql = ' 1 != 1  ';
							}
						}
					}
				break;
			}

			$sql_where .= apply_filters( 'pwbe_where_clause', $row_sql, $field_name, $filter_type, $field_value, $field_value2, $group_type );

			if ( $group_type == 'pwbe_and' ) {
				$sql_where .= " AND ";

			} else if ( $group_type == 'pwbe_or' ) {
				$sql_where .= " OR  ";
			}
		}

		// Yank the trailing AND/OR.
		$sql_where = substr($sql_where, 0, -5);

		$sql_where .= ") ";

		return $sql_where;
	}

	public function string_search( $field_name, $filter_type, $value ) {
		global $wpdb;

		switch( $filter_type ) {
			case 'is':
				return $wpdb->prepare("$field_name = %s", $value);
			break;

			case 'is not':
				return $wpdb->prepare("$field_name != %s", $value);
			break;

			case 'contains':
				return $wpdb->prepare("$field_name LIKE %s", '%' . str_replace( '_', '\_', $value ) . '%');
			break;

			case 'does not contain':
				return $wpdb->prepare("$field_name NOT LIKE %s", '%' . str_replace( '_', '\_', $value ) . '%');
			break;

			case 'begins with':
				return $wpdb->prepare("$field_name LIKE %s", str_replace( '_', '\_', $value ) . '%');
			break;

			case 'ends with':
				return $wpdb->prepare("$field_name LIKE %s", '%' . str_replace( '_', '\_', $value ) );
			break;
		}
	}

	public function numeric_search( $field_name, $filter_type, $value, $value2 ) {
		global $wpdb;

		//$field_sql = "$field_name IS NOT NULL AND $field_name != '' AND CAST($field_name AS DECIMAL(12, 2))";
		$field_sql = "$field_name IS NOT NULL AND $field_name != '' AND $field_name";

		switch( $filter_type ) {
			case 'is':
				return $wpdb->prepare("$field_sql = %f", $value);
			break;

			case 'is not':
				return $wpdb->prepare("$field_sql != %f", $value);
			break;

			case 'is greater than':
				return $wpdb->prepare("$field_sql > %f", $value);
			break;

			case 'is less than':
				return $wpdb->prepare("$field_sql < %f", $value);
			break;

			case 'is in the range':
				return $wpdb->prepare("($field_sql >= %f AND CAST($field_name AS DECIMAL(12, 2)) <= %f)", $value, $value2);
			break;
		}
	}

	public function taxonomy_search( $taxonomy, $filter_type, $values ) {
		global $wpdb;

		$placeholders = implode( ', ', array_fill( 0, count( $values ), '%s' ) );

		if ( !empty( $values ) ) {
			array_unshift( $values, $taxonomy );
		}

		switch( $filter_type ) {
			case 'is any of':
			case 'is none of':
				$negator = ( $filter_type == 'is none of' ) ? 'NOT' : '';
				return $wpdb->prepare("$negator EXISTS (SELECT 1 FROM {$wpdb->term_taxonomy} AS tax JOIN {$wpdb->term_relationships} AS r ON (r.term_taxonomy_id = tax.term_taxonomy_id) JOIN {$wpdb->terms} AS t ON (t.term_id = tax.term_id) WHERE tax.taxonomy = %s AND r.object_id = parent.ID AND t.slug IN ($placeholders))", $values);
			break;

			case 'is all of':
				array_push( $values, count($values) - 1 );
				return $wpdb->prepare("(SELECT COUNT(*) FROM {$wpdb->term_taxonomy} AS tax JOIN {$wpdb->term_relationships} AS r ON (r.term_taxonomy_id = tax.term_taxonomy_id) JOIN {$wpdb->terms} AS t ON (t.term_id = tax.term_id) WHERE tax.taxonomy = %s AND r.object_id = parent.ID AND t.slug IN ($placeholders)) = %d", $values );
			break;

			case 'is empty':
			case 'is not empty':
				$negator = ( $filter_type == 'is empty' ) ? 'NOT' : '';
				return $wpdb->prepare("$negator EXISTS (SELECT 1 FROM {$wpdb->term_taxonomy} AS tax JOIN {$wpdb->term_relationships} AS r ON (r.term_taxonomy_id = tax.term_taxonomy_id) JOIN {$wpdb->terms} AS t ON (t.term_id = tax.term_id) WHERE tax.taxonomy = %s AND r.object_id = parent.ID)", $taxonomy);
			break;
		}
	}

    public function boolean_search( $field_name, $filter_type, $value ) {
        global $wpdb;

        switch( $filter_type ) {
            case 'is checked':
                return "LOWER(TRIM($field_name)) IN ('yes', 'true')";
            break;

            default:
                return "COALESCE(NULLIF(LOWER(TRIM($field_name)), ''), 'no') IN ('no', 'false')";
            break;
        }
    }

	public function attributes_search( $field_name, $filter_type, $values ) {
		global $wpdb;

		$slugs = implode( ', ', array_fill( 0, count( $values ), '%s' ) );

		switch( $filter_type ) {
			case 'is any of':
			case 'is none of':
				$negator = ( $filter_type == 'is none of' ) ? 'NOT' : '';
				$simple_attribute = $wpdb->prepare("
					post.post_type != 'product_variation' AND (
						$negator EXISTS (
							SELECT 1
							FROM {$wpdb->term_relationships} AS r
							JOIN {$wpdb->term_taxonomy} AS tax ON (tax.term_taxonomy_id = r.term_taxonomy_id)
							JOIN {$wpdb->terms} AS t ON (t.term_id = tax.term_id)
							WHERE r.object_id = post.ID AND t.slug IN ($slugs)
						)
					)
				",
				$values);

				$variable_attribute = $this->variation_attributes_search( $field_name, $filter_type, $values );

				return "($simple_attribute OR $variable_attribute)";
			break;

			case 'is all of':

                $wpdb_values = $values;
                $wpdb_values[] = $field_name;
                $wpdb_values[] = count( $values );

				$simple_attribute = $wpdb->prepare("
					post.post_type != 'product_variation' AND (
						SELECT COUNT(*)
						FROM {$wpdb->term_relationships} AS r
						JOIN {$wpdb->term_taxonomy} AS tax ON (tax.term_taxonomy_id = r.term_taxonomy_id)
						JOIN {$wpdb->terms} AS t ON (t.term_id = tax.term_id)
						WHERE r.object_id = post.ID AND t.slug IN ($slugs) AND tax.taxonomy = %s
					) = %d
				",
				$wpdb_values );

				$variable_attribute = $this->variation_attributes_search( $field_name, $filter_type, $values );

				return "($simple_attribute OR $variable_attribute)";
			break;
		}
	}

	public function variation_attributes_search( $field_name, $filter_type, $values ) {
		global $wpdb;

		$slugs = implode( ', ', array_fill( 0, count( $values ), '%s' ) );

		switch( $filter_type ) {
			case 'is any of':
			case 'is none of':
				$negator = ( $filter_type == 'is none of' ) ? 'NOT' : '';
				array_unshift( $values, 'attribute_' . $field_name );
				return $wpdb->prepare("
					post.post_type = 'product_variation' AND (
						$negator EXISTS (
							SELECT 1
							FROM {$wpdb->postmeta} AS m
							WHERE
								m.post_id = post.ID
								AND m.meta_key = %s
								AND m.meta_value IN ($slugs)
						)
					)
				",
				$values);
			break;

			case 'is all of':
				array_push( $values, count($values) );
				array_unshift( $values, 'attribute_' . $field_name );
				return $wpdb->prepare("
					post.post_type = 'product_variation' AND (
						SELECT COUNT(*)
						FROM {$wpdb->postmeta} AS m
						WHERE
							m.post_id = post.ID
							AND m.meta_key = %s
							AND m.meta_value IN ($slugs)
					) = %d
				",
				$values);
			break;

			case 'is empty':
			case 'is not empty':
				$negator = ( $filter_type == 'is empty' ) ? 'NOT' : '';
				return $wpdb->prepare("
					post.post_type = 'product_variation' AND $negator EXISTS (
						SELECT 1
						FROM {$wpdb->postmeta} AS m
						WHERE
							m.post_id = post.ID
							AND m.meta_key = %s
					)
				",
				$field_name);
			break;

		}
	}

	public function build_order_by( $post ) {
		$order_by = 'post_title';
		$direction = 'ASC';

		if ( !empty( $post['order_by_desc'] ) ) {
			$direction = 'DESC';
		}

		if ( !empty( $post['order_by'] ) ) {
            $column = PWBE_Columns::get_by_field( $post['order_by'] );
            if ( !empty( $column ) ) {
                if ( $column['type'] == 'currency' ) {
                    $order_by = "LENGTH(`$column[field]`) $direction, `$column[field]` $direction";
                } else {
                    if ( $column['field'] == '_featured' && PW_Bulk_Edit::wc_min_version( '3.0' ) ) {
                        $order_by = "is_featured_product $direction";
                    } else if ( $column['field'] == '_visibility' ) {
                        $order_by = "
                            CASE
                                WHEN (visibility_exclude_from_catalog_product_id IS NULL AND visibility_exclude_from_search_product_id IS NULL) THEN 1
                                WHEN (visibility_exclude_from_catalog_product_id IS NOT NULL AND visibility_exclude_from_search_product_id IS NOT NULL) THEN 2
                                WHEN (visibility_exclude_from_catalog_product_id IS NOT NULL AND visibility_exclude_from_search_product_id IS NULL) THEN 3
                                ELSE 4
                            END $direction
                        ";
                    } else if ( $column['type'] == 'number' ) {
                        $order_by = "CAST( `$column[field]` AS DECIMAL(10,6) ) $direction";
                    } else {
                        $order_by = "`$column[field]` $direction";
                    }
                }
			}
		}

        $order_by .= ", COALESCE(NULLIF(parent_post_id, 0), post_id), CASE WHEN product_type = 'variation' THEN menu_order ELSE -1000 END, post_id";

		return $order_by;
	}

    public function maybe_add_catalog_visibility_joins( $sql_joins ) {
        global $wpdb;

        if ( strpos( $sql_joins, 'visibility_exclude_from_catalog' ) === false ) {
            $sql_joins .= "
                LEFT JOIN (
                    SELECT
                        r.object_id AS product_id
                    FROM
                        {$wpdb->term_taxonomy} AS tax
                    JOIN
                        {$wpdb->terms} AS t ON (t.term_id = tax.term_id AND t.name = 'exclude-from-catalog')
                    JOIN
                        {$wpdb->term_relationships} AS r ON (r.term_taxonomy_id = tax.term_taxonomy_id)
                    WHERE
                        tax.taxonomy = 'product_visibility'
                ) AS visibility_exclude_from_catalog ON (visibility_exclude_from_catalog.product_id = post.ID)
                LEFT JOIN (
                    SELECT
                        r.object_id AS product_id
                    FROM
                        {$wpdb->term_taxonomy} AS tax
                    JOIN
                        {$wpdb->terms} AS t ON (t.term_id = tax.term_id AND t.name = 'exclude-from-search')
                    JOIN
                        {$wpdb->term_relationships} AS r ON (r.term_taxonomy_id = tax.term_taxonomy_id)
                    WHERE
                        tax.taxonomy = 'product_visibility'
                ) AS visibility_exclude_from_search ON (visibility_exclude_from_search.product_id = post.ID)
            ";
        }

        return $sql_joins;
    }
}

endif;

?>