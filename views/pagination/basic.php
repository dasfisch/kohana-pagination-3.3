<p class="pagination">

	<?php if ($first_page !== FALSE): ?>
		<?php echo HTML::anchor($page->url($first_page), __('First')) ?>
	<?php else: ?>
		<?php echo __('First') ?>
	<?php endif ?>

	<?php if ($previous_page !== FALSE): ?>
		<?php echo HTML::anchor($page->url($previous_page), __('Previous')) ?>
	<?php else: ?>
		<?php echo __('Previous') ?>
	<?php endif ?>

	<?php for ($i = 1; $i <= $total_pages; $i++): ?>

		<?php if ($i == $current_page): ?>
			<strong><?php echo $i ?></strong>
		<?php else: ?>
			<?php echo HTML::anchor($page->url($i), $i) ?>
		<?php endif ?>

	<?php endfor ?>

	<?php if ($next_page !== FALSE): ?>
		<?php echo HTML::anchor($page->url($next_page), __('Next')) ?>
	<?php else: ?>
		<?php echo __('Next') ?>
	<?php endif ?>

	<?php if ($last_page !== FALSE): ?>
		<?php echo HTML::anchor($page->url($last_page), __('Last')) ?>
	<?php else: ?>
		<?php echo __('Last') ?>
	<?php endif ?>

</p><!-- .pagination -->
