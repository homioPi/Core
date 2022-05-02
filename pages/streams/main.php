<?php 
	$streams = \HomioPi\Streams\get_all();
?>
<div class="btn-list" data-type="single">
	<?php foreach($streams as $stream) : ?>
		<?php $category = new \HomioPi\Categories\Category($stream['category']);	?>
		<a 
			tabindex="0" 
			href="/#/streams/view/?id=<?php echo($stream['id']); ?>"
			class="btn btn-tertiary bg-secondary mb-2 transition-fade-order" 
			data-category="<?php echo($stream['category']); ?>" 
			data-page-search="<?php echo($stream['name']); ?>">
			<div class="row">
				<div class="col-auto d-none d-sm-flex pl-0 pr-2">
					<?php echo(create_icon($stream['icon'], 'xl', ['text-category'])); ?>
				</div>
				<div class="col">
					<div class="tile-title mb-0 mb-sm-1"><?php echo($stream['name']); ?></div>
				</div>
			</div>
		</a>
	<?php endforeach; ?>
</div>