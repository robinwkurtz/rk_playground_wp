<?php
get_header();

if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div class="wrapper content">
		<div class="inner-wrapper">
			<div class="row align-center">
				<div class="columns small-12">
					<?php
					the_content();
					?>
				</div>
			</div>
		</div>
	</div>
<?php
endwhile;
else:
	get_template_part('404');
endif;
get_footer();
