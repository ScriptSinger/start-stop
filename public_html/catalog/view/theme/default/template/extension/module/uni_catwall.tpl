<?php if(isset($categories)) { ?>
	<?php if($uni_catwall_view_res == 768) {
		$res = 'hidden-xs';
		} else if ($uni_catwall_view_res == 992)  {
		$res = 'hidden-xs hidden-sm';
		} else if ($uni_catwall_view_res == 1200) {
		$res = 'hidden-sm hidden-sm hidden-md';
		} else {
		$res = '';
	}
	?>
	<div id="uni_catwall" class="<?php echo $res; ?>">
		<?php if ($show_name) { ?>
			<h3 class="heading"><span><?php echo $heading_title; ?></span></h3>
		<?php } ?>	
		<?php if($uni_catwall_type == 1) { ?>
			<?php if($uni_catwall_style == 1) {
				$style = 'list-unstyled';
				} else if ($uni_catwall_style == 2)  {
				$style = 'list-inline';
				} else {
				$style = '';
			}
			?>
			<div class="row row-eq-height">
				<?php foreach ($categories as $category) { ?>
					<div class="col col-md-3 col-sm-6 col-xs-12">
						<p><a href="<?php echo $category['href']; ?>"><img src="<?php echo $category['thumb']; ?>" alt="<?php echo $category['name']; ?>" title="<?php echo $category['name']; ?>" class="img-responsive"/></a></p>
						<p><a href="<?php echo $category['href']; ?>"><strong><?php echo $category['name']; ?></strong></a></p>
						<?php if ($category['children']) { ?>
							<ul class="<?php echo $style; ?>">
								<?php foreach ($category['children'] as $child) { ?>
									<li><a href="<?php echo $child['href']; ?>" ><span><?php echo $child['name']; ?></span></a></li> <?php if($uni_catwall_style == 2) { ?> &middot; <?php } ?>
								<?php } ?>
							</ul>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
		<?php if($uni_catwall_type == 2) { ?>
			<div class="row" style="padding-top:25px;">
				<?php foreach ($categories as $category) { ?>
					<div class="product-layout col-md-3 col-sm-6 col-xs-12">
						<div class="product-thumb transition">
							<div class="image" style="padding:5px;"><a href="<?php echo $category['href']; ?>"><img src="<?php echo $category['thumb']; ?>" alt="<?php echo $category['name']; ?>" title="<?php echo $category['name']; ?>" class="img-responsive" /></a></div>
							<div>
								<h4 style="text-align:center;"> <a href="<?php echo $category['href']; ?>" class=""><?php echo $category['name']; ?></a></h4>       
							</div>   
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
		<?php if($uni_catwall_type == 3) { ?>
			<div class="row">
				<?php foreach ($categories as $category) { ?>
					<div class="wall col-md-4 col-sm-6 col-xs-12">
						<a href="<?php echo $category['href']; ?>"><img src="<?php echo $category['thumb']; ?>" alt="<?php echo $category['name']; ?>" title="<?php echo $category['name']; ?>" class="img-responsive"/></a>
						<div class="wallwrapp">
							<h4 class="heading"><span><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a></span></h4>
							<p class="description"><?php echo $category['description']; ?></p>
							<?php if ($category['children']) { ?>
								<div class="dropdown">
									<button class="btn  dropdown-toggle  btn-primary" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<?php echo $text_view; ?>
									</button>
									<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
										<?php foreach ($category['children'] as $child) { ?>
											<a href="<?php echo $child['href']; ?>" class="dropdown-item"><?php echo $child['name']; ?></a>
										<?php } ?>
									</div>
								</div>
								<?php } else { ?>
								<a class="btn  btn-primary" href="<?php echo $category['href']; ?>"><?php echo $text_view_cat; ?></a>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
<?php } ?>