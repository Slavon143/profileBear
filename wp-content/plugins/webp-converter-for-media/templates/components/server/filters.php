<?php
/**
 * Information about using filters displayed in server configuration widget.
 *
 * @package Converter for Media
 */

use WebpConverter\Service\PathsGenerator;

?>
<h4>Filters</h4>
<table>
	<tbody>
	<tr>
		<td class="e">webpc_site_root</td>
		<td class="v">
			<?php echo esc_html( PathsGenerator::get_wordpress_root_path() ); ?>
		</td>
	</tr>
	<tr>
		<td class="e">webpc_site_url</td>
		<td class="v">
			<?php echo esc_html( apply_filters( 'webpc_site_url', get_site_url() ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e">webpc_dir_path <em>(plugins)</em></td>
		<td class="v">
			<?php echo esc_html( apply_filters( 'webpc_dir_path', '', 'plugins' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e">webpc_dir_path <em>(themes)</em></td>
		<td class="v">
			<?php echo esc_html( apply_filters( 'webpc_dir_path', '', 'themes' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e">webpc_dir_path <em>(uploads)</em></td>
		<td class="v">
			<?php echo esc_html( apply_filters( 'webpc_dir_path', '', 'uploads' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e">webpc_dir_path <em>(webp)</em></td>
		<td class="v">
			<?php echo esc_html( apply_filters( 'webpc_dir_path', '', 'webp' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e">webpc_source_directories</td>
		<td class="v">
			<?php echo esc_html( json_encode( apply_filters( 'webpc_source_directories', [] ) ) ?: '-' ); ?>
		</td>
	</tr>
	<tr>
		<td class="e">webpc_htaccess_rewrite_root</td>
		<td class="v">
			<?php echo esc_html( PathsGenerator::get_rewrite_root() ); ?>
		</td>
	</tr>
	<tr>
		<td class="e">webpc_htaccess_rewrite_path</td>
		<td class="v">
			<?php echo esc_html( PathsGenerator::get_rewrite_path() ); ?>
		</td>
	</tr>
	</tbody>
</table>
