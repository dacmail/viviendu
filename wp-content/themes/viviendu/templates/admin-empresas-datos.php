<?php /* Template Name: (Admin) Empresas con datos */ ?>
<?php if (!is_user_logged_in()) { wp_redirect(site_url());} ?>
<?php get_header() ?>
<div id="container" class="page section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-md-12 col-sm-12">
				<table width="100%" border="1">
				<?php $terms = get_terms(array('comercio'));
				foreach ($terms as $term) : ?>
					<?php $location_info = viviendu_location_info($term->term_id);  ?>
					<tr>
						<td><a href="https://viviendu.com/wp-admin/edit-tags.php?action=edit&taxonomy=comercio&tag_ID=<?php echo $term->term_id ?>" target="blank"><?php echo $term->name; ?></a></td>
						<td><?php echo $location_info['phone']; ?></td>
						<td><?php echo $location_info['url']; ?></td>
						<td><?php echo $location_info['address']; ?></td>
						<td><?php echo $location_info['email']; ?></td>
					</tr>
				<?php endforeach; ?>
				</table>
			</div>
		</div>
	</div>
</div>
<?php get_footer() ?>
